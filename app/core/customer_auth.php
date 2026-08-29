<?php
/**
 * Customer authentication for the storefront (not the admin panel), based on mobile number and password.
 *
 * Design decision from v1.2.0: login uses a password rather than OTP so it works without a live SMS provider.
 *
 * v1.2.1 change: the mobile number must now be verified by SMS before the account becomes fully active:
 * 1) customerSignup() creates the account, stores $_SESSION['pending_customer_id'], and redirects to /verify-phone.
 * 2) After a valid code, completeCustomerLogin() creates the full customer session.
 * Existing customers are automatically marked as verified by the migration because verification did not exist at signup.
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
 * Register a new customer without creating a full session until mobile verification succeeds.
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
 * Authenticate a customer with a password.
 * @return array ['ok'=>bool, 'error'=>string|null, 'needs_verification'=>bool, 'customer_id'=>int|null]
 */
function attemptCustomerLogin(string $phone, string $password): array
{
    $stmt = db()->prepare("SELECT id, password_hash, phone_verified_at FROM customers WHERE phone = ?");
    $stmt->execute([$phone]);
    $customer = $stmt->fetch();

    if (!$customer || !password_verify($password, $customer['password_hash'])) {
        usleep(400000); // Add a small delay to slow brute-force attempts.
        return ['ok' => false, 'error' => 'شماره موبایل یا رمز عبور اشتباه است.', 'needs_verification' => false, 'customer_id' => null];
    }

    if (empty($customer['phone_verified_at'])) {
        // Registration is incomplete; redirect to mobile verification instead of creating a full session.
        $_SESSION['pending_customer_id'] = (int) $customer['id'];
        return ['ok' => false, 'error' => null, 'needs_verification' => true, 'customer_id' => (int) $customer['id']];
    }

    completeCustomerLogin((int) $customer['id']);
    return ['ok' => true, 'error' => null, 'needs_verification' => false, 'customer_id' => (int) $customer['id']];
}

/**
 * Complete the customer login after successful mobile verification during signup or login.
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
