-- ============================================================
-- Migration 007 — upgrade from v1.4.0 to v1.5.0
-- Run this once against the existing production database (phpMyAdmin > SQL tab).
--
-- Adds an admin-managed theme system: a `themes` table (one row per named
-- theme, exactly one of which is active) plus a generic `theme_tokens`
-- table (key/value design tokens per theme, grouped by category). Color is
-- the only token group used today, but the structure is intentionally
-- generic (token_group + token_key + token_value) so future token types
-- (typography, spacing, radius, shadows, component-level tokens) can be
-- added without a schema change.
--
-- Four starting themes are seeded, including the palette the storefront is
-- currently using (kept active, so this migration is visually a no-op).
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS themes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Exactly one row should have is_active = 1 at any time; this is enforced
-- in the application layer (a transaction clears every row, then sets one),
-- not with a database constraint, since MySQL has no direct way to express
-- "at most one TRUE per table" without triggers.
CREATE TABLE IF NOT EXISTS theme_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    theme_id INT UNSIGNED NOT NULL,
    token_group VARCHAR(40) NOT NULL DEFAULT 'color',
    token_key VARCHAR(60) NOT NULL,
    token_value VARCHAR(255) NOT NULL,
    FOREIGN KEY (theme_id) REFERENCES themes(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_theme_token (theme_id, token_group, token_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Seed: the palette currently live on the storefront ----------
INSERT INTO themes (id, name, is_active) VALUES (1, 'پیش‌فرض', 1);
INSERT INTO theme_tokens (theme_id, token_group, token_key, token_value) VALUES
    (1, 'color', 'bg', '#F9E9DA'),
    (1, 'color', 'surface', '#F5E5D6'),
    (1, 'color', 'text', '#7D5141'),
    (1, 'color', 'muted', '#9C7C6C'),
    (1, 'color', 'border', '#E7D2BF'),
    (1, 'color', 'primary', '#582B1C'),
    (1, 'color', 'primary-dark', '#3E1D12'),
    (1, 'color', 'primary-light', '#EAD6C7'),
    (1, 'color', 'accent', '#B89180');

-- ---------- Seed: three additional ready-to-use palettes ----------
INSERT INTO themes (id, name, is_active) VALUES (2, 'هم‌رنگ لوگو', 0);
INSERT INTO theme_tokens (theme_id, token_group, token_key, token_value) VALUES
    (2, 'color', 'bg', '#F3E4D6'),
    (2, 'color', 'surface', '#FFFFFF'),
    (2, 'color', 'text', '#6B4130'),
    (2, 'color', 'muted', '#9A7A68'),
    (2, 'color', 'border', '#E6D3C2'),
    (2, 'color', 'primary', '#4A2A1D'),
    (2, 'color', 'primary-dark', '#331C12'),
    (2, 'color', 'primary-light', '#EFDDD0'),
    (2, 'color', 'accent', '#A97C68');

INSERT INTO themes (id, name, is_active) VALUES (3, 'صورتی ملایم', 0);
INSERT INTO theme_tokens (theme_id, token_group, token_key, token_value) VALUES
    (3, 'color', 'bg', '#FBF1EC'),
    (3, 'color', 'surface', '#FFFFFF'),
    (3, 'color', 'text', '#6B4A47'),
    (3, 'color', 'muted', '#9C807C'),
    (3, 'color', 'border', '#EDDAD3'),
    (3, 'color', 'primary', '#4A302E'),
    (3, 'color', 'primary-dark', '#331F1D'),
    (3, 'color', 'primary-light', '#F1DFDA'),
    (3, 'color', 'accent', '#C9A29A');

INSERT INTO themes (id, name, is_active) VALUES (4, 'کنتراست بالا', 0);
INSERT INTO theme_tokens (theme_id, token_group, token_key, token_value) VALUES
    (4, 'color', 'bg', '#F7EAE0'),
    (4, 'color', 'surface', '#FFFFFF'),
    (4, 'color', 'text', '#5C3A2E'),
    (4, 'color', 'muted', '#8A6B5C'),
    (4, 'color', 'border', '#E2CFC0'),
    (4, 'color', 'primary', '#331C12'),
    (4, 'color', 'primary-dark', '#1F110B'),
    (4, 'color', 'primary-light', '#E8D5C7'),
    (4, 'color', 'accent', '#8B5E4A');

-- End of Migration 007
