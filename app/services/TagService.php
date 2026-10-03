<?php
/**
 * Product Tag management and catalog taxonomy service.
 * Manages tags, slugs, product associations, and taxonomy hygiene.
 */

class TagService
{
    /**
     * Fetch all tags with associated products count and optional search query.
     */
    public static function getAllWithCounts(?string $search = null): array
    {
        $sql = "
            SELECT t.id, t.name, t.slug, t.created_at, COUNT(pt.product_id) AS products_count
            FROM tags t
            LEFT JOIN product_tags pt ON pt.tag_id = t.id
        ";
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $sql .= " WHERE t.name LIKE ? OR t.slug LIKE ?";
            $params[] = '%' . trim($search) . '%';
            $params[] = '%' . trim($search) . '%';
        }

        $sql .= " GROUP BY t.id, t.name, t.slug, t.created_at ORDER BY products_count DESC, t.name ASC";

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Tag metrics for Bento KPI cards.
     */
    public static function getStats(): array
    {
        $pdo = db();
        $totalTags = (int) $pdo->query("SELECT COUNT(*) FROM tags")->fetchColumn();
        $usedTags = (int) $pdo->query("SELECT COUNT(DISTINCT tag_id) FROM product_tags")->fetchColumn();
        $unusedTags = max(0, $totalTags - $usedTags);

        return [
            'total_tags' => $totalTags,
            'used_tags' => $usedTags,
            'unused_tags' => $unusedTags,
        ];
    }

    /**
     * Fetch single tag by ID.
     */
    public static function getById(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM tags WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Save (insert or update) a tag record.
     */
    public static function save(array $data, ?int $id = null): array
    {
        $name = trim($data['name'] ?? '');
        $slug = trim($data['slug'] ?? '');

        if ($name === '') {
            return ['ok' => false, 'error' => 'عنوان برچسب نمی‌تواند خالی باشد.'];
        }

        if ($slug === '') {
            $slug = slugify($name);
        } else {
            $slug = slugify($slug);
        }

        if ($slug === '') {
            $slug = 'tag-' . bin2hex(random_bytes(2));
        }

        $pdo = db();

        // Check uniqueness of name and slug
        if ($id && $id > 0) {
            $check = $pdo->prepare("SELECT id FROM tags WHERE (name = ? OR slug = ?) AND id != ?");
            $check->execute([$name, $slug, $id]);
        } else {
            $check = $pdo->prepare("SELECT id FROM tags WHERE name = ? OR slug = ?");
            $check->execute([$name, $slug]);
        }

        if ($check->fetch()) {
            return ['ok' => false, 'error' => 'برچسبی با این عنوان یا نامک قبلاً ثبت شده است.'];
        }

        if ($id && $id > 0) {
            $stmt = $pdo->prepare("UPDATE tags SET name = ?, slug = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO tags (name, slug) VALUES (?, ?)");
            $stmt->execute([$name, $slug]);
        }

        return ['ok' => true, 'error' => null];
    }

    /**
     * Delete a tag. Associated product_tags cascade automatically.
     */
    public static function delete(int $id): array
    {
        db()->prepare("DELETE FROM tags WHERE id = ?")->execute([$id]);
        return ['ok' => true, 'error' => null];
    }

    /**
     * Delete all unused orphan tags (tags without any linked products).
     */
    public static function cleanupUnused(): array
    {
        $stmt = db()->prepare("
            DELETE FROM tags 
            WHERE id NOT IN (SELECT DISTINCT tag_id FROM product_tags)
        ");
        $stmt->execute();
        $deleted = $stmt->rowCount();

        return ['ok' => true, 'deleted_count' => $deleted, 'error' => null];
    }
}
