-- ============================================================
-- Migration 022 — v1.24.0
-- Store Settings Hub Enhancement: Order & inventory workflow policies,
-- banking/shaba details, invoice customization, and admin alert settings.
-- ============================================================

SET NAMES utf8mb4;

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('min_order_amount', '0'),
    ('c2c_timeout_hours', '24'),
    ('store_order_status', 'active'),
    ('store_paused_message', 'ثبت سفارش موقتاً به دلیل انبارگردانی یا تعطیلات متوقف شده است.'),
    ('low_stock_threshold', '3'),
    ('store_shaba', ''),
    ('store_bank_name', ''),
    ('store_economic_code', ''),
    ('store_national_id', ''),
    ('invoice_footer_note', 'از خرید و اعتماد شما به جوراب AB سپاسگزاریم.'),
    ('admin_alert_mobile', ''),
    ('tax_enabled', '0'),
    ('tax_percentage', '0');
