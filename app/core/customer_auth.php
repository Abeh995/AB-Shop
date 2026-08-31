<?php
/**
 * Customer authentication (storefront, not the admin panel) — based on
 * mobile number + password.
 *
 * Design decision (unchanged since 1.2.0): login with mobile number +
 * password, not OTP, since a password works from day one without needing
 * a real SMS service.
 *
 * Important change in 1.2.1: from this version on, the mobile number must
 * be verified with an SMS code before the account is fully "active". Steps:
 *   1) customerSignup() creates the account but doesn't grant a full
 *      session yet; instead it sets $_SESSION['pending_customer_id'] and
 *      the controller sends the user to /verify-phone.
 *   2) After entering the correct code (VerificationService::verifyCode),
 *      completeCustomerLogin() is called, which creates the full session
 *      (customer_id).
 * Customers who signed up before this version automatically got
 * phone_verified_at set during the migration (since this requirement
 * didn't exist when they signed up), so their login isn't affected.
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
        $stmt = db()->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$_SESSION['customer_id']]);
        $cached = $stmt->fetch() ?: null;
    }
    return $cached;
}

/**
 * Register a new customer — the account is created but no full session is
 * granted; the customer must first verify their mobile number with an SMS
 * code (completeCustomerLogin).
 * @return array ['ok'=>bool, 'error'=>string|null, 'customer_id'=>int|null]
 */
function customerSignup(string $phone, string $password, ?string $fullName, ?string $email = null): array
{
    if (!isValidIranPhone($phone)) {
        return ['ok' => false, 'error' => 'شماره موبایل معتبر نیست (مثال: 09123456789).', 'customer_id' => null];
    }
    if (mb_strlen($password) < 6) {
        return ['ok' => false, 'error' => 'رمز عبور باید حداقل ۶ کاراکتر باشد.', 'customer_id' => null];
    }
    if ($email !== null && $email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'ایمیل وارد شده معتبر نیست.', 'customer_id' => null];
    }

    $check = db()->prepare("SELECT id FROM customers WHERE phone = ?");
    $check->execute([$phone]);
    if ($check->fetch()) {
        return ['ok' => false, 'error' => 'حسابی با این شماره موبایل قبلاً ثبت شده است. وارد شوید یا رمز را فراموشی بزنید.', 'customer_id' => null];
    }

    $stmt = db()->prepare("INSERT INTO customers (phone, email, password_hash, full_name) VALUES (?, ?, ?, ?)");
    $stmt->execute([$phone, $email ?: null, password_hash($password, PASSWORD_BCRYPT), $fullName ?: null]);
    $customerId = (int) db()->lastInsertId();

    $_SESSION['pending_customer_id'] = $customerId;

    return ['ok' => true, 'error' => null, 'customer_id' => $customerId];
}

/**
 * Customer login with a password.
 * @return array ['ok'=>bool, 'error'=>string|null, 'needs_verification'=>bool, 'customer_id'=>int|null]
 */
function attemptCustomerLogin(string $phone, string $password): array
{
    $stmt = db()->prepare("SELECT id, password_hash, phone_verified_at FROM customers WHERE phone = ?");
    $stmt->execute([$phone]);
    $customer = $stmt->fetch();

    if (!$customer || !password_verify($password, $customer['password_hash'])) {
        usleep(400000); // Slow down brute-force attacks
        return ['ok' => false, 'error' => 'شماره موبایل یا رمز عبور اشتباه است.', 'needs_verification' => false, 'customer_id' => null];
    }

    if (empty($customer['phone_verified_at'])) {
        // Signup wasn't completed yet; redirect to the phone-verification step instead of a full login
        $_SESSION['pending_customer_id'] = (int) $customer['id'];
        return ['ok' => false, 'error' => null, 'needs_verification' => true, 'customer_id' => (int) $customer['id']];
    }

    completeCustomerLogin((int) $customer['id']);
    return ['ok' => true, 'error' => null, 'needs_verification' => false, 'customer_id' => (int) $customer['id']];
}

/**
 * Finalize the login after successful mobile-number verification (whether
 * reached via signup or login).
 */
function completeCustomerLogin(int $customerId): void
{
    session_regenerate_id(true);
    unset($_SESSION['pending_customer_id']);
    $_SESSION['customer_id'] = $customerId;
    mergeGuestCartIntoCustomerCart($customerId);
}

function pendingCustomerId(): ?int
{
    return isset($_SESSION['pending_customer_id']) ? (int) $_SESSION['pending_customer_id'] : null;
}

function customerLogout(): void
{
    unset($_SESSION['customer_id'], $_SESSION['pending_customer_id'], $_SESSION['coupon'], $_SESSION['cart']);
    session_regenerate_id(true);
}
