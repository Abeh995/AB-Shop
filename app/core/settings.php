<?php
/**
 * General store settings backed by the settings table using a simple key/value structure.
 * Suitable for configuration that does not require dedicated columns in other tables, such as cart price guarantees.
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
