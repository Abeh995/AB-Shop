<?php
/**
 * Landing Studio persistence & publishing service.
 *
 * Storage model (single-row config + append-only revisions):
 *  - landing_config          : one row (id = 1) holding the published snapshot
 *                              and the working draft JSON.
 *  - landing_config_revisions: immutable snapshot of every publish, used by the
 *                              Studio history drawer to roll back.
 *
 * The storefront always renders the *published* snapshot (falling back to the
 * shipped defaults); the Studio preview renders the *draft*. Nothing goes live
 * until an admin presses Publish, and every publish is attributed + reversible.
 *
 * When the migration has not been applied yet (fresh deploy ordered wrongly) the
 * read helpers degrade to the shipped defaults instead of throwing.
 */

const LANDING_REVISION_LIMIT = 25;

/**
 * Make sure the singleton row exists.
 */
function landingEnsureRow(): void
{
    db()->prepare('INSERT IGNORE INTO landing_config (id, updated_at) VALUES (1, NOW())')->execute();
}

/**
 * Raw singleton row (published_json / draft_json / metadata).
 */
function landingRow(bool $resetCache = false): array
{
    static $cache = null;
    if ($resetCache) {
        $cache = null;
        return [];
    }
    if ($cache !== null) {
        return $cache;
    }

    try {
        landingEnsureRow();
        $stmt = db()->query('SELECT * FROM landing_config WHERE id = 1 LIMIT 1');
        $cache = $stmt->fetch() ?: [];
    } catch (\Throwable $e) {
        $cache = [];
    }

    return $cache;
}

/**
 * Drop the per-request row cache after a write.
 */
function landingForgetCache(): void
{
    landingRow(true);
}

/**
 * Decode one JSON column of the singleton row.
 */
function landingDecodeColumn(string $column): ?array
{
    $row = landingRow();
    $raw = trim((string) ($row[$column] ?? ''));
    if ($raw === '') {
        return null;
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? landingSanitizeConfig($decoded) : null;
}

/**
 * Config currently served to visitors.
 */
function getLandingPublishedConfig(): array
{
    return landingDecodeColumn('published_json') ?? landingDefaultConfig();
}

/**
 * Config the admin Studio is editing (draft if present, otherwise published).
 */
function getLandingDraftConfig(): array
{
    return landingDecodeColumn('draft_json') ?? getLandingPublishedConfig();
}

/**
 * Flatten a config into path => scalar pairs for diffing.
 */
function landingFlattenConfig(array $config, string $prefix = ''): array
{
    $flat = [];
    foreach ($config as $key => $value) {
        $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;
        if (is_array($value)) {
            $flat += landingFlattenConfig($value, $path);
            continue;
        }
        $flat[$path] = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
    }

    return $flat;
}

/**
 * Paths that differ between the draft and the published snapshot.
 *
 * @return string[] Human-readable "section.field" paths, capped for the UI.
 */
function landingPendingChanges(array $draft, array $published): array
{
    $draftFlat = landingFlattenConfig($draft);
    $publishedFlat = landingFlattenConfig($published);

    $changed = [];
    foreach ($draftFlat as $path => $value) {
        if (($publishedFlat[$path] ?? null) !== $value) {
            $changed[] = $path;
        }
    }
    foreach ($publishedFlat as $path => $value) {
        if (!array_key_exists($path, $draftFlat)) {
            $changed[] = $path;
        }
    }

    return $changed;
}

/**
 * Everything the Studio shell needs to render its status chips.
 */
function getLandingStudioMeta(): array
{
    $row = landingRow();
    $draft = getLandingDraftConfig();
    $published = getLandingPublishedConfig();
    $changed = landingPendingChanges($draft, $published);

    $publishedBy = null;
    $updatedBy = null;
    $adminNames = landingAdminNames([(int) ($row['published_by'] ?? 0), (int) ($row['updated_by'] ?? 0)]);
    if (!empty($row['published_by'])) {
        $publishedBy = $adminNames[(int) $row['published_by']] ?? ('#' . (int) $row['published_by']);
    }
    if (!empty($row['updated_by'])) {
        $updatedBy = $adminNames[(int) $row['updated_by']] ?? ('#' . (int) $row['updated_by']);
    }

    return [
        'hasPublished'   => !empty($row['published_json']),
        'hasDraft'       => !empty($row['draft_json']),
        'publishedAt'    => $row['published_at'] ?? null,
        'publishedBy'    => $publishedBy,
        'draftUpdatedAt' => $row['updated_at'] ?? null,
        'draftUpdatedBy' => $updatedBy,
        'changeCount'    => count($changed),
        'changes'        => array_slice($changed, 0, 200),
        'revisionCount'  => landingRevisionCount(),
    ];
}

/**
 * Total number of archived publishes.
 */
function landingRevisionCount(): int
{
    try {
        return (int) db()->query('SELECT COUNT(*) FROM landing_config_revisions')->fetchColumn();
    } catch (\Throwable $e) {
        return 0;
    }
}

/**
 * Resolve admin usernames for attribution labels.
 */
function landingAdminNames(array $ids): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
    if ($ids === []) {
        return [];
    }

    try {
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = db()->prepare("SELECT id, username FROM admins WHERE id IN ($in)");
        $stmt->execute($ids);
        $names = [];
        foreach ($stmt->fetchAll() as $row) {
            $names[(int) $row['id']] = (string) $row['username'];
        }
        return $names;
    } catch (\Throwable $e) {
        return [];
    }
}

/**
 * Persist the working draft. Returns the sanitized config that was stored.
 */
function saveLandingDraft(array $config, int $adminId = 0): array
{
    $clean = landingSanitizeConfig($config);
    $json = json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    landingEnsureRow();
    $stmt = db()->prepare('UPDATE landing_config SET draft_json = ?, updated_by = ?, updated_at = NOW() WHERE id = 1');
    $stmt->execute([$json, $adminId > 0 ? $adminId : null]);
    landingForgetCache();

    return $clean;
}

/**
 * Promote the draft to the live snapshot and archive it as a revision.
 */
function publishLandingDraft(int $adminId = 0, string $note = ''): array
{
    $clean = getLandingDraftConfig();
    $json = json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    landingEnsureRow();
    $stmt = db()->prepare(
        'UPDATE landing_config
            SET published_json = ?, published_at = NOW(), published_by = ?, updated_at = NOW()
          WHERE id = 1'
    );
    $stmt->execute([$json, $adminId > 0 ? $adminId : null]);

    $note = landingSanitizeText($note, 180);
    $rev = db()->prepare('INSERT INTO landing_config_revisions (config_json, note, created_by) VALUES (?, ?, ?)');
    $rev->execute([$json, $note !== '' ? $note : 'انتشار از استودیو لندینگ', $adminId > 0 ? $adminId : null]);

    landingPruneRevisions();
    landingForgetCache();

    return $clean;
}

/**
 * Keep the newest N revisions.
 */
function landingPruneRevisions(): void
{
    try {
        $keep = LANDING_REVISION_LIMIT;
        $stmt = db()->query("SELECT id FROM landing_config_revisions ORDER BY id DESC LIMIT 1 OFFSET $keep");
        $cutoff = $stmt->fetchColumn();
        if ($cutoff) {
            db()->prepare('DELETE FROM landing_config_revisions WHERE id <= ?')->execute([(int) $cutoff]);
        }
    } catch (\Throwable $e) {
        // Pruning is best-effort; never break a publish because of it.
    }
}

/**
 * Drop the draft so the Studio starts again from the published snapshot.
 */
function discardLandingDraft(int $adminId = 0): void
{
    landingEnsureRow();
    db()->prepare('UPDATE landing_config SET draft_json = NULL, updated_by = ?, updated_at = NOW() WHERE id = 1')
        ->execute([$adminId > 0 ? $adminId : null]);
    landingForgetCache();
}

/**
 * Reset exactly one studio section (or the page order) back to the shipped default.
 */
function resetLandingSection(string $sectionKey, int $adminId = 0): array
{
    $defaults = landingDefaultConfig();
    $config = getLandingDraftConfig();

    if ($sectionKey === 'pageOrder') {
        $config['sectionOrder'] = $defaults['sectionOrder'];
    } else {
        [$root, $child] = array_pad(explode('.', $sectionKey, 2), 2, null);
        if (!array_key_exists($root, $defaults)) {
            return $config;
        }
        if ($child !== null && isset($config[$root][$child])) {
            $config[$root][$child] = $defaults[$root][$child];
        } elseif ($child === null) {
            $config[$root] = $defaults[$root];
        }
    }

    return saveLandingDraft($config, $adminId);
}

/**
 * Reset every editable value (draft falls back to the shipped defaults).
 */
function resetLandingAll(int $adminId = 0): array
{
    return saveLandingDraft(landingDefaultConfig(), $adminId);
}

/**
 * Publish archive for the history drawer.
 */
function listLandingRevisions(int $limit = LANDING_REVISION_LIMIT): array
{
    try {
        $limit = max(1, min(50, $limit));
        $rows = db()->query(
            "SELECT id, note, created_by, created_at, CHAR_LENGTH(config_json) AS size_bytes
               FROM landing_config_revisions ORDER BY id DESC LIMIT $limit"
        )->fetchAll();
    } catch (\Throwable $e) {
        return [];
    }

    $names = landingAdminNames(array_column($rows, 'created_by'));
    foreach ($rows as &$row) {
        $row['author'] = $names[(int) $row['created_by']] ?? '—';
    }

    return $rows;
}

/**
 * Load an archived snapshot back into the draft (never straight to live).
 */
function restoreLandingRevision(int $revisionId, int $adminId = 0): array
{
    $stmt = db()->prepare('SELECT config_json FROM landing_config_revisions WHERE id = ? LIMIT 1');
    $stmt->execute([$revisionId]);
    $raw = $stmt->fetchColumn();
    if (!$raw) {
        return ['ok' => false, 'error' => 'نسخه انتخابی پیدا نشد.'];
    }

    $decoded = json_decode((string) $raw, true);
    if (!is_array($decoded)) {
        return ['ok' => false, 'error' => 'محتوای این نسخه قابل خواندن نیست.'];
    }

    return ['ok' => true, 'config' => saveLandingDraft($decoded, $adminId)];
}

/**
 * Import a JSON export produced by the Studio (or a hand-edited copy).
 */
function importLandingConfig(string $json, int $adminId = 0): array
{
    $decoded = json_decode($json, true);
    if (!is_array($decoded)) {
        return ['ok' => false, 'error' => 'فایل JSON معتبر نیست.'];
    }

    // Accept both { "config": {...} } exports and a bare config object.
    $payload = isset($decoded['config']) && is_array($decoded['config']) ? $decoded['config'] : $decoded;
    if ($payload === []) {
        return ['ok' => false, 'error' => 'فایل ورودی خالی است.'];
    }

    return ['ok' => true, 'config' => saveLandingDraft($payload, $adminId)];
}

/**
 * Current draft as a downloadable JSON document.
 */
function exportLandingConfig(): string
{
    $payload = [
        'app'         => 'AB-Socks',
        'kind'        => 'landing-config',
        'exported_at' => date('c'),
        'version'     => APP_VERSION,
        'config'      => getLandingDraftConfig(),
    ];

    return (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

/**
 * Store an uploaded landing image and return its public URL.
 */
function saveLandingMedia(array $file): array
{
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['ok' => false, 'error' => 'فایلی دریافت نشد.'];
    }
    if (($file['size'] ?? 0) > 4194304) {
        return ['ok' => false, 'error' => 'حجم تصویر نباید بیشتر از ۴ مگابایت باشد.'];
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = (string) finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowed[$mime])) {
        return ['ok' => false, 'error' => 'فقط تصاویر JPG، PNG یا WEBP مجاز هستند.'];
    }
    if (@getimagesize($file['tmp_name']) === false) {
        return ['ok' => false, 'error' => 'فایل انتخابی یک تصویر معتبر نیست.'];
    }

    if (!is_dir(LANDING_UPLOAD_DIR)) {
        @mkdir(LANDING_UPLOAD_DIR, 0755, true);
    }

    $filename = generateStandardFilename('landing', 0, 'media', $allowed[$mime]);
    $destination = LANDING_UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['ok' => false, 'error' => 'خطا در ذخیره فایل روی سرور.'];
    }
    @chmod($destination, 0644);

    return [
        'ok'   => true,
        'url'  => LANDING_UPLOAD_URL . $filename,
        'name' => $filename,
    ];
}

/**
 * Category slugs offered as suggestions for the catalog tab order field.
 */
function landingCategorySuggestions(): array
{
    try {
        $rows = db()->query('SELECT name, slug FROM categories WHERE is_active = 1 ORDER BY sort_order ASC')->fetchAll();
    } catch (\Throwable $e) {
        return [];
    }

    return array_map(static fn ($row) => ['value' => (string) $row['slug'], 'label' => (string) $row['name']], $rows);
}

/**
 * Single entry point behind ajax/landing_studio.php.
 *
 * Controllers stay thin (Rule 7): the endpoint only authenticates, validates the
 * token and forwards the request here, together with the acting admin id.
 *
 * @param array $post  Sanitised request payload ($_POST)
 * @param array $files Uploaded files ($_FILES)
 */
function landingStudioDispatch(string $action, array $post, array $files, int $adminId): array
{
    $config = null;

    switch ($action) {
        case 'save_draft':
            $payload = json_decode((string) ($post['config'] ?? ''), true);
            if (!is_array($payload)) {
                return ['ok' => false, 'error' => 'داده ارسالی نامعتبر است.'];
            }
            $config = saveLandingDraft($payload, $adminId);
            $message = 'پیش‌نویس ذخیره شد.';
            break;

        case 'publish':
            $config = publishLandingDraft($adminId, (string) ($post['note'] ?? ''));
            $message = 'تغییرات روی صفحه اصلی سایت منتشر شد.';
            break;

        case 'discard':
            discardLandingDraft($adminId);
            $config = getLandingDraftConfig();
            $message = 'پیش‌نویس دور ریخته شد و نسخه منتشرشده بازگردانی شد.';
            break;

        case 'reset_section':
            $section = preg_replace('/[^a-zA-Z0-9_.]/', '', (string) ($post['section'] ?? ''));
            if ($section === '') {
                return ['ok' => false, 'error' => 'بخش انتخابی نامعتبر است.'];
            }
            $config = resetLandingSection($section, $adminId);
            $message = 'این بخش به مقادیر پیش‌فرض برگشت.';
            break;

        case 'reset_all':
            $config = resetLandingAll($adminId);
            $message = 'همه فیلدها به مقادیر پیش‌فرض بازگشتند (هنوز منتشر نشده).';
            break;

        case 'restore_revision':
            $res = restoreLandingRevision((int) ($post['id'] ?? 0), $adminId);
            if (empty($res['ok'])) {
                return $res;
            }
            $config = $res['config'];
            $message = 'نسخه انتخابی در پیش‌نویس بارگذاری شد؛ برای اعمال، منتشر کنید.';
            break;

        case 'import':
            $res = importLandingConfig((string) ($post['json'] ?? ''), $adminId);
            if (empty($res['ok'])) {
                return $res;
            }
            $config = $res['config'];
            $message = 'تنظیمات از فایل JSON در پیش‌نویس بارگذاری شد.';
            break;

        case 'upload':
            $res = saveLandingMedia($files['file'] ?? []);
            if (empty($res['ok'])) {
                return $res;
            }
            return ['ok' => true, 'url' => $res['url'], 'message' => 'تصویر بارگذاری شد.'];

        case 'meta':
            $message = '';
            break;

        default:
            return ['ok' => false, 'error' => 'درخواست نامعتبر است.'];
    }

    if ($action === 'meta') {
        $config = getLandingDraftConfig();
    }

    // Audit trail: every publishing-side action is attributable (SECURITY.md §audit).
    if (function_exists('logAdminAction') && !in_array($action, ['meta', 'save_draft'], true)) {
        $auditLabels = [
            'publish'          => 'انتشار تغییرات صفحه اصلی از استودیو لندینگ',
            'discard'          => 'دور ریختن پیش‌نویس صفحه اصلی',
            'reset_section'    => 'بازنشانی یک بخش صفحه اصلی به پیش‌فرض',
            'reset_all'        => 'بازنشانی کامل تنظیمات صفحه اصلی',
            'restore_revision' => 'بازگردانی نسخه آرشیوی صفحه اصلی در پیش‌نویس',
            'import'           => 'ورود تنظیمات صفحه اصلی از فایل JSON',
            'upload'           => 'بارگذاری تصویر جدید برای صفحه اصلی',
        ];
        logAdminAction($adminId, 'landing_' . $action, 'landing', 1, $auditLabels[$action] ?? $action);
    }

    return [
        'ok'       => true,
        'message'  => $message,
        'config'   => $config,
        'meta'     => getLandingStudioMeta(),
        'revisions' => listLandingRevisions(),
    ];
}
