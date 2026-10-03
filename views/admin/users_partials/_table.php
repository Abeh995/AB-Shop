<?php
/**
 * Admin Users Partial: Interactive Table
 */

$avatarGradients = [
    'linear-gradient(135deg, #4F46E5, #7C3AED)',
    'linear-gradient(135deg, #059669, #10B981)',
    'linear-gradient(135deg, #D97706, #F59E0B)',
    'linear-gradient(135deg, #0284C7, #38BDF8)',
    'linear-gradient(135deg, #E11D48, #F43F5E)',
    'linear-gradient(135deg, #7C3AED, #A855F7)',
];
?>
<div class="usr-table-container">
    <table class="usr-table" id="adminUsersTable">
        <thead>
            <tr>
                <th>مشخصات مدیر</th>
                <th>اطلاعات تماس</th>
                <th>سطح دسترسی</th>
                <th>وضعیت</th>
                <th>آخرین ورود</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($admins as $index => $a): 
                $isMe = ((int)$a['id'] === (int)$currentAdminId);
                $initial = mb_substr($a['full_name'] ?: $a['username'], 0, 1, 'UTF-8');
                $grad = $avatarGradients[$a['id'] % count($avatarGradients)];
                $roleClass = ($a['role'] === 'super_admin') ? 'usr-role-super' : 'usr-role-admin';
                $roleLabel = ($a['role'] === 'super_admin') ? 'مدیر ارشد' : 'مدیر عادی';
                $searchIndex = mb_strtolower(($a['username'] ?? '') . ' ' . ($a['full_name'] ?? '') . ' ' . ($a['email'] ?? '') . ' ' . ($a['phone'] ?? ''));
            ?>
            <tr class="usr-row" 
                data-role="<?= e($a['role']) ?>" 
                data-status="<?= $a['is_active'] ? 'active' : 'inactive' ?>"
                data-search="<?= e($searchIndex) ?>">
                
                <!-- Profile Cell -->
                <td>
                    <div class="usr-profile-cell">
                        <div class="usr-avatar" style="background:<?= $grad ?>;">
                            <?= e($initial) ?>
                        </div>
                        <div class="usr-profile-details">
                            <span class="usr-profile-name">
                                <?= e($a['full_name'] ?: $a['username']) ?>
                                <?php if ($isMe): ?>
                                    <span class="usr-self-badge">شما</span>
                                <?php endif; ?>
                            </span>
                            <span class="usr-profile-username">@<?= e($a['username']) ?></span>
                        </div>
                    </div>
                </td>

                <!-- Contact Cell -->
                <td>
                    <div class="usr-contact-row">
                        <?php if (!empty($a['phone'])): ?>
                            <span class="usr-contact-item" title="شماره تماس">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                <span dir="ltr"><?= e($a['phone']) ?></span>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($a['email'])): ?>
                            <span class="usr-contact-item" title="ایمیل">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                <span dir="ltr"><?= e($a['email']) ?></span>
                            </span>
                        <?php endif; ?>
                        <?php if (empty($a['phone']) && empty($a['email'])): ?>
                            <span style="color:var(--usr-text-muted); font-size:0.75rem;">—</span>
                        <?php endif; ?>
                    </div>
                </td>

                <!-- Role Badge -->
                <td>
                    <span class="usr-role-badge <?= $roleClass ?>">
                        <?php if ($a['role'] === 'super_admin'): ?>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <?php endif; ?>
                        <span><?= $roleLabel ?></span>
                    </span>
                </td>

                <!-- Status Pill -->
                <td>
                    <span class="usr-status-pill">
                        <span class="usr-pulse-dot <?= $a['is_active'] ? 'usr-pulse-active' : 'usr-pulse-inactive' ?>"></span>
                        <span><?= $a['is_active'] ? 'فعال' : 'غیرفعال' ?></span>
                    </span>
                </td>

                <!-- Last Session -->
                <td>
                    <div class="usr-session-cell">
                        <?php if (!empty($a['last_login_at'])): ?>
                            <span class="usr-session-time"><?= toPersianDigits(date('Y/m/d H:i', strtotime($a['last_login_at']))) ?></span>
                            <span class="usr-session-ip"><?= e($a['last_login_ip'] ?? '127.0.0.1') ?></span>
                        <?php else: ?>
                            <span style="color:var(--usr-text-muted); font-size:0.75rem;">ورود ثبت نشده</span>
                        <?php endif; ?>
                    </div>
                </td>

                <!-- Actions -->
                <td>
                    <div class="usr-actions">
                        <!-- Edit Button -->
                        <button type="button" class="usr-btn-icon" title="ویرایش مشخصات" 
                                onclick='openEditModal(<?= htmlspecialchars(json_encode([
                                    'id' => (int)$a['id'],
                                    'username' => $a['username'],
                                    'full_name' => $a['full_name'] ?? '',
                                    'role' => $a['role'],
                                    'phone' => $a['phone'] ?? '',
                                    'email' => $a['email'] ?? '',
                                ]), ENT_QUOTES, 'UTF-8') ?>)'>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                        </button>

                        <!-- Change Password Button -->
                        <button type="button" class="usr-btn-icon" title="تغییر رمز عبور" 
                                onclick="openPasswordModal(<?= (int)$a['id'] ?>, '<?= e(addslashes($a['username'])) ?>')">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </button>

                        <?php if (!$isMe): ?>
                            <!-- Toggle Active/Inactive -->
                            <form method="post" style="display:inline; margin:0;">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="toggle_active">
                                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                                <button type="submit" class="usr-btn-icon" title="<?= $a['is_active'] ? 'غیرفعال کردن حساب' : 'فعال‌سازی حساب' ?>">
                                    <?php if ($a['is_active']): ?>
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                    <?php else: ?>
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                    <?php endif; ?>
                                </button>
                            </form>

                            <!-- Delete Admin -->
                            <form method="post" onsubmit="return confirm('آیا از حذف دائمی این حساب مدیر اطمینان دارید؟ این عمل غیرقابل بازگشت است.');" style="display:inline; margin:0;">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                                <button type="submit" class="usr-btn-icon usr-btn-danger" title="حذف حساب">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
