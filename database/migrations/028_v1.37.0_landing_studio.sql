-- ============================================================
-- Migration 028 — v1.37.0
-- Landing Studio: published snapshot, working draft and publish archive
-- for the React storefront landing page.
--
-- landing_config           : singleton row (id = 1) with the live snapshot
--                            and the working draft edited in the Studio.
-- landing_config_revisions : append-only archive written on every publish,
--                            used by the Studio history drawer to roll back.
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `landing_config` (
    `id`             TINYINT UNSIGNED NOT NULL,
    `published_json` LONGTEXT NULL,
    `draft_json`     LONGTEXT NULL,
    `published_at`   DATETIME NULL DEFAULT NULL,
    `published_by`   INT UNSIGNED NULL DEFAULT NULL,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_by`     INT UNSIGNED NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `landing_config_revisions` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `config_json` LONGTEXT NOT NULL,
    `note`        VARCHAR(190) NULL DEFAULT NULL,
    `created_by`  INT UNSIGNED NULL DEFAULT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_landing_revisions_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Singleton row: the storefront keeps rendering the shipped defaults until an
-- admin publishes from the Studio, so this insert alone changes nothing visible.
INSERT IGNORE INTO `landing_config` (`id`) VALUES (1);
