-- =====================================================
-- FRANCHISES TABLE CREATION
-- =====================================================
-- This table stores information about each franchise/tenant
-- in the multi-tenant SaaS system
-- =====================================================

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

  -- Owner Information
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
  KEY `idx_created` (`created_at`),

  -- Foreign key to users table
  CONSTRAINT `fk_franchise_owner`
    FOREIGN KEY (`owner_user_id`)
    REFERENCES `tbl_users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CREATE DEFAULT SUPER ADMIN FRANCHISE
-- =====================================================
-- This creates a default "System" franchise for super admins
-- Super admins don't belong to any specific franchise
-- =====================================================

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

-- =====================================================
-- NOTES ON FRANCHISE TABLE USAGE
-- =====================================================
--
-- 1. FRANCHISE CODE:
--    - Must be unique and URL-friendly (lowercase, no spaces)
--    - Used for subdomain routing (e.g., franchise-code.yoursaas.com)
--    - Cannot be changed once set
--
-- 2. SUBSCRIPTION STATUS:
--    - trial: New franchises on trial period
--    - active: Paying, active subscription
--    - suspended: Temporarily suspended (billing issue, policy violation)
--    - cancelled: User cancelled, may have grace period
--    - expired: Subscription ended, account locked
--
-- 3. ENABLED FEATURES:
--    - JSON array: ["invoicing", "inventory", "reports", "api_access"]
--    - Use for feature flagging per franchise
--
-- 4. CUSTOM SETTINGS:
--    - JSON object: {"theme": "dark", "default_view": "dashboard"}
--    - Store franchise-specific configuration
--
-- 5. PERMISSION VERSION:
--    - Increment this when franchise permissions change
--    - Invalidates cached permissions for all franchise users
--    - Forces permission re-check on next request
--
-- 6. MAX USERS & MAX STORAGE:
--    - Enforce these limits in application logic
--    - Check before creating new users or uploading files
--
-- 7. SOFT DELETE:
--    - Use deleted_at for soft deletes
--    - Don't actually DELETE rows (preserve data)
--    - Filter with WHERE deleted_at IS NULL
--
-- =====================================================
