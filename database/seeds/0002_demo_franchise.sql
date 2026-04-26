-- SaaS Seeder Template - optional demo tenant seed
INSERT INTO tbl_franchises (name, code, business_name, email, status, subscription_status, onboarding_completed)
VALUES ('Demo Franchise', 'demo', 'Demo Franchise', 'demo@example.test', 'active', 'trial', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), status = VALUES(status);

INSERT INTO tbl_franchise_modules (franchise_id, module_code, status, enabled_at)
SELECT f.id, m.code, 'enabled', CURRENT_TIMESTAMP
FROM tbl_franchises f
JOIN tbl_modules m ON m.is_core = 1
WHERE f.code = 'demo'
ON DUPLICATE KEY UPDATE status = VALUES(status), enabled_at = VALUES(enabled_at);
