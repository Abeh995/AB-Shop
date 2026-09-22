<?php
/** Secure mailbox credential storage helpers. */
function encryptMailboxSecret(string $plain): string
{
    $key = hash('sha256', APP_SECRET, true);
    $iv = random_bytes(12);
    $tag = '';
    $cipher = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    if ($cipher === false) { throw new RuntimeException('Mailbox encryption failed.'); }
    return base64_encode($iv . $tag . $cipher);
}
function decryptMailboxSecret(string $encoded): string
{
    $raw = base64_decode($encoded, true);
    if ($raw === false || strlen($raw) < 28) { throw new RuntimeException('Invalid mailbox secret.'); }
    $key = hash('sha256', APP_SECRET, true);
    $iv = substr($raw, 0, 12); $tag = substr($raw, 12, 16); $cipher = substr($raw, 28);
    $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    if ($plain === false) { throw new RuntimeException('Mailbox decryption failed.'); }
    return $plain;
}
