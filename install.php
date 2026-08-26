<?php
/**
 * Initial setup. Run this script once after deployment: yoursite.com/install.php
 * The installer locks itself automatically when an admin account already exists in the database.
 * No additional admin account can then be created through this page.
 */
require_once __DIR__ . '/app/bootstrap.php';

$existingAdminCount = (int) db()->query("SELECT COUNT(*) FROM admins")->fetchColumn();
$locked = $existingAdminCount > 0;

$message = '';
$success = false;

if (!$locked && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if (mb_strlen($username) < 3) {
        $message = 'نام کاربری باید حداقل ۳ کاراکتر باشد.';
    } elseif (mb_strlen($password) < 8) {
        $message = 'رمز عبور باید حداقل ۸ کاراکتر باشد.';
    } elseif ($password !== $passwordConfirm) {
        $message = 'تکرار رمز عبور مطابقت ندارد.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = db()->prepare("INSERT INTO admins (username, password_hash, full_name, role, is_active) VALUES (?, ?, ?, 'super_admin', 1)");
        $stmt->execute([$username, $hash, 'مدیر فروشگاه']);
        $success = true;
        $locked = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>نصب اولیه فروشگاه</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
<div class="login-wrap">
    <div class="login-box" style="max-width:440px;">
        <h1>نصب اولیه فروشگاه</h1>

        <?php if ($success): ?>
            <div class="alert alert-success">
                حساب ادمین با موفقیت ساخته شد. اکنون می‌توانید وارد پنل مدیریت شوید.
            </div>
            <a href="/admin/login.php" class="btn btn-primary btn-block">ورود به پنل مدیریت</a>
            <p style="font-size:.8rem; color:var(--color-muted); margin-top:14px; text-align:center;">
                برای امنیت، بهتر است این فایل (install.php) را از روی هاست حذف کنید.
            </p>

        <?php elseif ($locked): ?>
            <div class="alert alert-info">
                نصب قبلاً انجام شده است. برای امنیت این صفحه غیرفعال است.
            </div>
            <a href="/admin/login.php" class="btn btn-primary btn-block">ورود به پنل مدیریت</a>

        <?php else: ?>
            <p style="color:var(--color-muted); font-size:.9rem; margin-bottom:18px;">
                این فرم فقط یک‌بار قابل استفاده است و اولین حساب مدیریت فروشگاه را می‌سازد.
            </p>
            <?php if ($message): ?><div class="alert alert-error"><?= e($message) ?></div><?php endif; ?>
            <form method="post">
                <?= csrfField() ?>
                <div class="form-group">
                    <label>نام کاربری ادمین</label>
                    <input class="form-control" type="text" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label>رمز عبور (حداقل ۸ کاراکتر)</label>
                    <input class="form-control" type="password" name="password" required minlength="8">
                </div>
                <div class="form-group">
                    <label>تکرار رمز عبور</label>
                    <input class="form-control" type="password" name="password_confirm" required minlength="8">
                </div>
                <button type="submit" class="btn btn-primary btn-block">ساخت حساب ادمین</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
