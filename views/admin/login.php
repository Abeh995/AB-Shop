<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ورود به پنل مدیریت</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
<div class="login-wrap">
    <div class="login-box">
        <h1>ورود به پنل مدیریت</h1>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <?= csrfField() ?>
            <div class="form-group">
                <label>نام کاربری</label>
                <input class="form-control" type="text" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label>رمز عبور</label>
                <input class="form-control" type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">ورود</button>
        </form>
    </div>
</div>
</body>
</html>
