-- Migration: Add admin_users table for Shoebox Admin authentication
-- Run this if you already imported database/schema.sql and want to switch admin logins to the database.

USE shoebox_db;

CREATE TABLE IF NOT EXISTS admin_users (
  id              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  email           VARCHAR(120)     NOT NULL,
  password_hash   VARCHAR(255)     NOT NULL,
  full_name       VARCHAR(120)     NOT NULL,
  role            ENUM('admin', 'store_manager') NOT NULL DEFAULT 'store_manager',
  store_id        VARCHAR(30)      NULL,
  phone           VARCHAR(20)      NOT NULL DEFAULT '',
  is_active       TINYINT(1)       NOT NULL DEFAULT 1,
  last_login_at   DATETIME         NULL,
  created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admin_users_email (email),
  KEY idx_admin_users_role (role),
  KEY idx_admin_users_store (store_id)
) ENGINE=InnoDB;

INSERT INTO admin_users (id, email, password_hash, full_name, role, store_id, phone, is_active, last_login_at, created_at) VALUES
(1, 'admin@shoebox.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Shoebox Admin', 'admin', NULL, '', 1, NULL, '2026-06-22 10:00:00'),
(2, 'kochimanager@shoebox.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kochi Store Manager', 'store_manager', 'kochi', '', 1, NULL, '2026-06-22 10:00:00'),
(3, 'kozhikodemanager@shoebox.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kozhikode Store Manager', 'store_manager', 'kozhikode', '', 1, NULL, '2026-06-22 10:00:00'),
(4, 'thrissurmanager@shoebox.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Thrissur Store Manager', 'store_manager', 'thrissur', '', 1, NULL, '2026-06-22 10:00:00')
ON DUPLICATE KEY UPDATE
  password_hash = VALUES(password_hash),
  full_name = VALUES(full_name),
  role = VALUES(role),
  store_id = VALUES(store_id),
  phone = VALUES(phone),
  is_active = VALUES(is_active);
