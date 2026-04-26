-- Tenant safety invariants. Queries should return zero rows on a healthy install.
SELECT 'franchise modules without franchise' AS issue, fm.id
FROM tbl_franchise_modules fm
LEFT JOIN tbl_franchises f ON f.id = fm.franchise_id
WHERE f.id IS NULL;

SELECT 'non-core module enabled without tenant row' AS issue, m.code
FROM tbl_modules m
WHERE m.is_core = 0
  AND NOT EXISTS (SELECT 1 FROM tbl_franchise_modules fm WHERE fm.module_code = m.code);
