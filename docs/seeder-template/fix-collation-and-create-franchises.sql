-- ================================================================
-- FIX COLLATION ISSUES AND CREATE FRANCHISES TABLE
-- ================================================================
-- This script fixes collation mismatches and creates the franchises table
-- Run this after the main migration.sql
-- ================================================================

USE `saas_seeder`;

-- ================================================================
-- STEP 1: Fix collation for tbl_users columns
-- ================================================================
ALTER TABLE `tbl_users`
  MODIFY COLUMN `username` VARCHAR(50) NOT NULL COLLATE utf8mb4_unicode_ci,
  MODIFY COLUMN `email` VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci,
  MODIFY COLUMN `password_hash` VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci,
  MODIFY COLUMN `first_name` VARCHAR(50) NOT NULL COLLATE utf8mb4_unicode_ci,
  MODIFY COLUMN `last_name` VARCHAR(50) NOT NULL COLLATE utf8mb4_unicode_ci,
  MODIFY COLUMN `phone` VARCHAR(20) NULL COLLATE utf8mb4_unicode_ci,
  MODIFY COLUMN `photo_path` VARCHAR(255) NULL COLLATE utf8mb4_unicode_ci,
  MODIFY COLUMN `password_reset_token` VARCHAR(100) NULL COLLATE utf8mb4_unicode_ci;

-- ================================================================
-- STEP 2: Create tbl_franchises table
-- ================================================================
CREATE TABLE IF NOT EXISTS `tbl_franchises` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `franchise_name` VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci,
  `franchise_code` VARCHAR(20) UNIQUE NOT NULL COLLATE utf8mb4_unicode_ci COMMENT 'Unique identifier/slug for the franchise',
  `business_name` VARCHAR(150) NULL COLLATE utf8mb4_unicode_ci COMMENT 'Legal/registered business name',
  `business_type` ENUM('sole_proprietor','partnership','corporation','llc','non_profit','other') DEFAULT 'corporation',

  -- Contact Information
  `email` VARCHAR(100) NULL COLLATE utf8mb4_unicode_ci,
  `phone` VARCHAR(20) NULL COLLATE utf8mb4_unicode_ci,
  `website` VARCHAR(255) NULL COLLATE utf8mb4_unicode_ci,

  -- Address Information
  `address_line1` VARCHAR(255) NULL COLLATE utf8mb4_unicode_ci,
  `address_line2` VARCHAR(255) NULL COLLATE utf8mb4_unicode_ci,
  `city` VARCHAR(100) NULL COLLATE utf8mb4_unicode_ci,
  `state_province` VARCHAR(100) NULL COLLATE utf8mb4_unicode_ci,
  `postal_code` VARCHAR(20) NULL COLLATE utf8mb4_unicode_ci,
  `country` VARCHAR(2) DEFAULT 'US' COLLATE utf8mb4_unicode_ci COMMENT 'ISO 3166-1 alpha-2 country code',

  -- Business Details
  `tax_id` VARCHAR(50) NULL COLLATE utf8mb4_unicode_ci COMMENT 'Tax ID / VAT number',
  `registration_number` VARCHAR(50) NULL COLLATE utf8mb4_unicode_ci COMMENT 'Business registration number',
  `industry` VARCHAR(100) NULL COLLATE utf8mb4_unicode_ci,
  `timezone` VARCHAR(50) DEFAULT 'UTC' COLLATE utf8mb4_unicode_ci,
  `currency` VARCHAR(3) DEFAULT 'USD' COLLATE utf8mb4_unicode_ci COMMENT 'ISO 4217 currency code',
  `language` VARCHAR(5) DEFAULT 'en' COLLATE utf8mb4_unicode_ci COMMENT 'ISO 639-1 language code',

  -- Subscription & Billing
  `subscription_plan` VARCHAR(50) NULL COLLATE utf8mb4_unicode_ci COMMENT 'Current subscription plan',
  `subscription_status` ENUM('trial','active','suspended','cancelled','expired') DEFAULT 'trial',
  `trial_ends_at` DATETIME NULL COMMENT 'Trial period end date',
  `subscription_starts_at` DATETIME NULL COMMENT 'Paid subscription start date',
  `subscription_ends_at` DATETIME NULL COMMENT 'Subscription expiry date',
  `billing_cycle` ENUM('monthly','quarterly','annually','lifetime') DEFAULT 'monthly',
  `next_billing_date` DATE NULL,

  -- System Configuration
  `max_users` INT UNSIGNED DEFAULT 10 COMMENT 'Maximum number of users allowed',
  `max_storage_mb` INT UNSIGNED DEFAULT 1024 COMMENT 'Maximum storage in MB',
  `custom_domain` VARCHAR(255) NULL COLLATE utf8mb4_unicode_ci,
  `logo_url` VARCHAR(500) NULL COLLATE utf8mb4_unicode_ci,
  `favicon_url` VARCHAR(500) NULL COLLATE utf8mb4_unicode_ci,

  -- Features & Permissions
  `enabled_features` JSON NULL COMMENT 'JSON array of enabled features',
  `custom_settings` JSON NULL COMMENT 'JSON object for franchise-specific settings',
  `permission_version` INT UNSIGNED DEFAULT 1 COMMENT 'Increment to invalidate cached permissions',

  -- Status & Metadata
  `status` ENUM('active','inactive','suspended','pending_approval','deleted') DEFAULT 'pending_approval',
  `notes` TEXT NULL COLLATE utf8mb4_unicode_ci COMMENT 'Internal notes about the franchise',
  `onboarding_completed` TINYINT(1) DEFAULT 0,
  `onboarding_step` INT DEFAULT 0 COMMENT 'Current step in onboarding process',

  -- Owner Information (will be added after initial creation)
  `owner_user_id` BIGINT UNSIGNED NULL COMMENT 'Reference to tbl_users.id for franchise owner',

  -- Timestamps
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL COMMENT 'Soft delete timestamp',

  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_franchise_code` (`franchise_code`),
  KEY `idx_status` (`status`),
  KEY `idx_subscription_status` (`subscription_status`),
  KEY `idx_country` (`country`),
  KEY `idx_owner` (`owner_user_id`),
  KEY `idx_created` (`created_at`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- STEP 3: Add foreign key constraint (if not exists)
-- ================================================================
-- Note: We do this separately to avoid errors if constraint already exists
SET @constraint_exists = (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = 'saas_seeder'
    AND TABLE_NAME = 'tbl_franchises'
    AND CONSTRAINT_NAME = 'fk_franchise_owner'
);

SET @sql = IF(@constraint_exists = 0,
  'ALTER TABLE `tbl_franchises` ADD CONSTRAINT `fk_franchise_owner` FOREIGN KEY (`owner_user_id`) REFERENCES `tbl_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE',
  'SELECT "Foreign key already exists" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ================================================================
-- STEP 4: Create default system franchise
-- ================================================================
INSERT INTO `tbl_franchises` (
  `franchise_name`,
  `franchise_code`,
  `business_name`,
  `email`,
  `country`,
  `timezone`,
  `currency`,
  `subscription_plan`,
  `subscription_status`,
  `max_users`,
  `status`,
  `onboarding_completed`
) VALUES (
  'System Administration',
  'system',
  'SaaS Seeder System',
  'admin@system.local',
  'US',
  'UTC',
  'USD',
  'enterprise',
  'active',
  999999,
  'active',
  1
) ON DUPLICATE KEY UPDATE
  `updated_at` = CURRENT_TIMESTAMP;

-- ================================================================
-- STEP 5: Fix stored procedures to use explicit collation
-- ================================================================
DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_authenticate_user`$$
CREATE PROCEDURE `sp_authenticate_user`(
  IN p_username VARCHAR(50),
  IN p_franchise_id BIGINT UNSIGNED,
  OUT p_user_id BIGINT UNSIGNED,
  OUT p_status VARCHAR(50),
  OUT p_password_hash VARCHAR(255)
)
BEGIN
  DECLARE v_user_id BIGINT UNSIGNED DEFAULT NULL;
  DECLARE v_status VARCHAR(50) DEFAULT NULL;
  DECLARE v_hash VARCHAR(255) DEFAULT NULL;

  -- Use COLLATE to ensure consistent collation comparison
  IF p_franchise_id IS NULL THEN
    SELECT id, status, password_hash INTO v_user_id, v_status, v_hash
    FROM tbl_users
    WHERE (username COLLATE utf8mb4_unicode_ci = p_username COLLATE utf8mb4_unicode_ci
           OR email COLLATE utf8mb4_unicode_ci = p_username COLLATE utf8mb4_unicode_ci)
      AND franchise_id IS NULL
    LIMIT 1;
  ELSE
    SELECT id, status, password_hash INTO v_user_id, v_status, v_hash
    FROM tbl_users
    WHERE (username COLLATE utf8mb4_unicode_ci = p_username COLLATE utf8mb4_unicode_ci
           OR email COLLATE utf8mb4_unicode_ci = p_username COLLATE utf8mb4_unicode_ci)
      AND franchise_id = p_franchise_id
    LIMIT 1;
  END IF;

  IF v_user_id IS NULL THEN
    SET p_user_id = 0;
    SET p_status = 'USER_NOT_FOUND';
    SET p_password_hash = NULL;
  ELSEIF v_status <> 'active' THEN
    SET p_user_id = v_user_id;
    SET p_status = 'ACCOUNT_INACTIVE';
    SET p_password_hash = v_hash;
  ELSE
    SET p_user_id = v_user_id;
    SET p_status = 'SUCCESS';
    SET p_password_hash = v_hash;
  END IF;
END$$

DELIMITER ;

-- ================================================================
-- DONE!
-- ================================================================
SELECT 'Collation fixed and franchises table created successfully!' AS status;
