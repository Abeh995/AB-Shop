-- ============================================================
-- Migration 026 — v1.29.0
-- Admin Users & Activity Audit Logging Architecture
-- Adds contact info, last login tracking to admins table,
-- and introduces the admin_audit_logs table.
-- ============================================================

SET NAMES utf8mb4;

-- 1. Guarded column addition for phone in admins
SET @col_phone = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'admins' 
      AND COLUMN_NAME = 'phone'
);
SET @sql_phone = IF(@col_phone = 0, 
    'ALTER TABLE admins ADD COLUMN phone VARCHAR(20) DEFAULT NULL AFTER full_name', 
    'SELECT 1'
);
PREPARE stmt_phone FROM @sql_phone; 
EXECUTE stmt_phone; 
DEALLOCATE PREPARE stmt_phone;

-- 2. Guarded column addition for email in admins
SET @col_email = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'admins' 
      AND COLUMN_NAME = 'email'
);
SET @sql_email = IF(@col_email = 0, 
    'ALTER TABLE admins ADD COLUMN email VARCHAR(150) DEFAULT NULL AFTER phone', 
    'SELECT 1'
);
PREPARE stmt_email FROM @sql_email; 
EXECUTE stmt_email; 
DEALLOCATE PREPARE stmt_email;

-- 3. Guarded column addition for last_login_at in admins
SET @col_login_at = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'admins' 
      AND COLUMN_NAME = 'last_login_at'
);
SET @sql_login_at = IF(@col_login_at = 0, 
    'ALTER TABLE admins ADD COLUMN last_login_at TIMESTAMP NULL DEFAULT NULL AFTER is_active', 
    'SELECT 1'
);
PREPARE stmt_login_at FROM @sql_login_at; 
EXECUTE stmt_login_at; 
DEALLOCATE PREPARE stmt_login_at;

-- 4. Guarded column addition for last_login_ip in admins
SET @col_login_ip = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'admins' 
      AND COLUMN_NAME = 'last_login_ip'
);
SET @sql_login_ip = IF(@col_login_ip = 0, 
    'ALTER TABLE admins ADD COLUMN last_login_ip VARCHAR(45) DEFAULT NULL AFTER last_login_at', 
    'SELECT 1'
);
PREPARE stmt_login_ip FROM @sql_login_ip; 
EXECUTE stmt_login_ip; 
DEALLOCATE PREPARE stmt_login_ip;

-- 5. Create admin_audit_logs table if not exists
CREATE TABLE IF NOT EXISTS admin_audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) DEFAULT NULL,
    entity_id INT UNSIGNED DEFAULT NULL,
    description VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
    INDEX idx_admin_action (admin_id, action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- End of Migration 026
