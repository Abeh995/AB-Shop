<?php
/**
 * General store settings (the `settings` table) — a simple key/value store
 * for settings that don't need a dedicated column in another table (e.g.
 * the cart price guarantee).
 */

function getSetting(string $key, $default = null)
{
    static $cache = [];
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }
    $stmt = db()->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    $cache[$key] = ($value === false) ? $default : $value;
    return $cache[$key];
}

function setSetting(string $key, string $value): void
{
    $stmt = db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $stmt->execute([$key, $value]);
}
