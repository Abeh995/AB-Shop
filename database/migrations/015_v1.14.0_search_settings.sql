-- Migration 015 — v1.14.0: Storefront live search settings
SET NAMES utf8mb4;

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('search_live_enabled', '1'),
    ('search_suggest_limit', '6'),
    ('search_min_chars', '2'),
    ('search_scope_name', '1'),
    ('search_scope_description', '1'),
    ('search_include_categories', '1')
ON DUPLICATE KEY UPDATE setting_value = setting_value;
