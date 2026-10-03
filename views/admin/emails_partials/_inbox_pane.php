<?php
/**
 * Email Studio: Split Inbox and Reader Workspace
 */
?>
<div class="email-split-container <?= $activeMessage ? 'viewing-mode' : '' ?>">

    <!-- 1. Message List Pane -->
    <div class="email-list-pane">
        <div class="email-list-header">
            <?php if (!empty($accounts)): ?>
                <select class="email-account-selector" onchange="location.href='/admin/emails.php?account='+this.value">
                    <?php foreach ($accounts as $a): ?>
                        <option value="<?= (int)$a['id'] ?>" <?= $selectedId === (int)$a['id'] ? 'selected' : '' ?>>
                            <?= e($a['display_name']) ?> (<?= e($a['email_address']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <form method="get" action="/admin/emails.php" class="email-search-wrap">
                <input type="hidden" name="account" value="<?= (int)$selectedId ?>">
                <svg class="email-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" name="q" class="email-search-input" placeholder="جستجو در موضوع ایمیل‌ها..." value="<?= e($search) ?>">
            </form>
        </div>

        <div class="email-items-scroll">
            <?php if (!$account): ?>
                <div style="padding:32px 16px; text-align:center; color:var(--mail-text-muted);">
                    <p style="margin:0 0 10px; font-size:.9rem;">هیچ حساب ایمیلی در سیستم فعال نیست.</p>
                    <a href="/admin/emails.php?tab=accounts" class="btn btn-sm btn-primary">افزودن حساب ایمیل</a>
                </div>
            <?php elseif (empty($messages)): ?>
                <div style="padding:48px 16px; text-align:center; color:var(--mail-text-muted);">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:8px; opacity:0.5;"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    <p style="margin:0; font-size:.88rem; font-weight:600;">صندوق ورودی خالی است.</p>
                    <small style="font-size:.76rem; opacity:0.8;">هیچ پیامی با مشخصات انتخابی یافت نشد.</small>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $m): ?>
                    <a href="/admin/emails.php?account=<?= $selectedId ?>&view=<?= $m['uid'] ?>" 
                       class="email-item-card <?= !$m['seen'] ? 'unread' : '' ?> <?= ($activeMessage && $activeMessage['uid'] === $m['uid']) ? 'active' : '' ?>">
                        <div class="email-item-lead">
                            <span class="email-sender-name">
                                <?php if (!$m['seen']): ?>
                                    <span class="unread-bullet"></span>
                                <?php endif; ?>
                                <?= e($m['from']) ?>
                            </span>
                            <span class="email-date-str"><?= e(date('M d, H:i', strtotime($m['date'] ?: 'now'))) ?></span>
                        </div>
                        <div class="email-item-subject"><?= e($m['subject']) ?></div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- 2. Message Reader Pane -->
    <div class="email-view-pane">
        <?php if ($activeMessage): ?>
            <div class="email-view-toolbar">
                <a href="/admin/emails.php?account=<?= $selectedId ?>" class="btn btn-sm btn-outline" style="display:inline-flex; align-items:center; gap:4px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    <span>بازگشت به لیست</span>
                </a>

                <div style="display:flex; align-items:center; gap:8px;">
                    <button type="button" class="btn btn-sm btn-outline" onclick="openReplyModal(<?= htmlspecialchars(json_encode($activeMessage['from']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode('پاسخ: ' . $activeMessage['subject']), ENT_QUOTES) ?>)">
                        <span>↩️ پاسخ مستقیم</span>
                    </button>

                    <form method="post" action="/admin/emails.php?account=<?= $selectedId ?>" onsubmit="return confirm('آیا از حذف این ایمیل از سرور مطمئن هستید؟');" style="margin:0;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete_message">
                        <input type="hidden" name="uid" value="<?= (int)$activeMessage['uid'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger" style="background:#ef4444; color:#fff; border:none; padding:7px 12px; border-radius:8px; cursor:pointer;">
                            حذف پیام
                        </button>
                    </form>
                </div>
            </div>

            <div class="email-view-meta">
                <h2 class="email-view-subject"><?= e($activeMessage['subject']) ?></h2>
                <div class="email-meta-row">
                    <div class="email-meta-from">
                        <b>فرستنده:</b> <?= e($activeMessage['from']) ?>
                        <br>
                        <b>گیرنده:</b> <?= e($activeMessage['to'] ?: $account['email_address']) ?>
                    </div>
                    <div style="direction:ltr; font-family:monospace; font-size:.82rem;">
                        <?= e($activeMessage['date']) ?>
                    </div>
                </div>
            </div>

            <div class="email-body-content">
                <?php if (!empty($activeMessage['body_html'])): ?>
                    <div class="email-html-body" style="background:#fff;">
                        <?= $activeMessage['body_html'] ?>
                    </div>
                <?php else: ?>
                    <pre><?= e($activeMessage['body_plain'] ?: '(پیام فاقد متن است)') ?></pre>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div class="email-view-empty">
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="margin-bottom:12px; opacity:0.35;"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                <h3 style="margin:0 0 6px; font-size:1.1rem; color:var(--mail-text-main);">پیامی برای نمایش انتخاب نشده است</h3>
                <p style="margin:0; font-size:.85rem; max-width:320px; line-height:1.5;">
                    یک ایمیل را از ستون کناری انتخاب کنید تا جزییات، فرستنده و متن آن در این بخش نمایش داده شود.
                </p>
            </div>
        <?php endif; ?>
    </div>

</div>
