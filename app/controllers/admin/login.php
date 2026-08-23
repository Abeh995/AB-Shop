<?php
if (isAdminLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (attemptAdminLogin($username, $password)) {
        redirect('index.php');
    } else {
        $error = 'نام کاربری یا رمز عبور اشتباه است، یا این حساب غیرفعال شده است.';
    }
}

renderView('admin/login', compact('error'));
