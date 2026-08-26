<?php
/**
 * Customer authentication for the storefront, based on mobile number and password.
 *
 * Design decision: use mobile number + password instead of SMS OTP.
 * The SMS subsystem runs in log-only mode by default, so OTP authentication
 * would not work out of the box until a real SMS provider is connected.
 * Password-based authentication provides a stable and secure default without additional configuration.
 * If a real SMS provider is added later, OTP login can be introduced as an optional
 * second authentication method in this file rather than replacing passwords.
 */

function isCustomerLoggedIn(): bool
{
    return !empty($_SESSION['customer_id']);
}

function requireCustomer(): void
{
    if (!isCustomerLoggedIn()) {
        setFlash('error', 'برای ادامه ابتدا وارد حساب کاربری خود شوید.');
        redirect('/login?next=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
    }
}

function currentCustomer(): ?array
{
    if (!isCustomerLoggedIn()) return null;
    static $cached = null;
    if ($cached === null) {
        $stmt = db()->prepare("SELECT id, phone, full_name FROM customers WHERE id = ?");
        $stmt->execute([$_SESSION['customer_id']]);
        $cached = $stmt->fetch() ?: null;
    }
    return $cached;
}

/**
 * Register a new customer using a mobile number.
 * @return array ['ok'=>bool, 'error'=>string|null]
 */
function customerSignup(string $phone, string $password, ?string $fullName): array
{
    if (!isValidIranPhone($phone)) {
        return ['ok' => false, 'error' => 'شماره موبایل معتبر نیست (مثال: 09123456789).'];
    }
    if (mb_strlen($password) < 6) {
        return ['ok' => false, 'error' => 'رمز عبور باید حداقل ۶ کاراکتر باشد.'];
    }

    $check = db()->prepare("SELECT id FROM customers WHERE phone = ?");
    $check->execute([$phone]);
    if ($check->fetch()) {
        return ['ok' => false, 'error' => 'حسابی با این شماره موبایل قبلاً ثبت شده است. وارد شوید یا رمز را فراموشی بزنید.'];
    }

    $stmt = db()->prepare("INSERT INTO customers (phone, password_hash, full_name) VALUES (?, ?, ?)");
    $stmt->execute([$phone, password_hash($password, PASSWORD_BCRYPT), $fullName ?: null]);
    $customerId = (int) db()->lastInsertId();

    session_regenerate_id(true);
    $_SESSION['customer_id'] = $customerId;

    mergeGuestCartIntoCustomerCart($customerId);

    return ['ok' => true, 'error' => null];
}

/**
 * Authenticate an existing customer.
 */
function attemptCustomerLogin(string $phone, string $password): bool
{
    $stmt = db()->prepare("SELECT id, password_hash FROM customers WHERE phone = ?");
    $stmt->execute([$phone]);
    $customer = $stmt->fetch();

    if ($customer && password_verify($password, $customer['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['customer_id'] = $customer['id'];
        mergeGuestCartIntoCustomerCart((int) $customer['id']);
        return true;
    }

    usleep(400000); // Slow down brute-force attempts.
    return false;
}

function customerLogout(): void
{
    unset($_SESSION['customer_id'], $_SESSION['coupon'], $_SESSION['cart']);
    session_regenerate_id(true);
}
