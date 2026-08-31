-- ============================================================
-- Migration 006 — upgrade from v1.2.2 to v1.3.0
-- Run this once against the existing production database (phpMyAdmin > SQL tab).
--
-- What this version adds:
--   1) Site logo upload (stored as a setting; file lives in uploads/branding/)
--   2) Header announcement bar (site-wide banner under the header)
--   3) Configurable footer content: about-page teaser link, shipping badge
--      text, store phone number
--   4) Admin-toggleable social links (Instagram, Telegram, Bale, Torob) and
--      an eNamad trust-seal embed slot
--
-- No new tables or columns are required — `settings` is already a generic
-- key/value store, so this migration only seeds the new keys with sensible
-- empty/disabled defaults. INSERT IGNORE is used so re-running this file is
-- always safe and never overwrites values an admin has already configured.
-- ============================================================

SET NAMES utf8mb4;

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('site_logo', ''),
    ('announcement_bar_enabled', '0'),
    ('announcement_bar_text', ''),
    ('announcement_bar_link', ''),
    ('footer_about_teaser_text', ''),
    ('footer_shipping_badge_text', ''),
    ('store_phone', ''),
    ('social_instagram_enabled', '0'),
    ('social_instagram_url', ''),
    ('social_telegram_enabled', '0'),
    ('social_telegram_url', ''),
    ('social_bale_enabled', '0'),
    ('social_bale_url', ''),
    ('social_torob_enabled', '0'),
    ('social_torob_url', ''),
    ('enamad_enabled', '0'),
    ('enamad_embed_code', '');

-- End of Migration 006
