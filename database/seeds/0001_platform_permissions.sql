-- SaaS Seeder Template - platform permissions seed
INSERT INTO tbl_permissions (name, code, module, description) VALUES
('View Dashboard', 'VIEW_DASHBOARD', 'DASHBOARD', 'Access main dashboard'),
('View Users', 'VIEW_USERS', 'USERS', 'View user list'),
('Create Users', 'CREATE_USER', 'USERS', 'Create new users'),
('Edit Users', 'EDIT_USER', 'USERS', 'Edit existing users'),
('Delete Users', 'DELETE_USER', 'USERS', 'Deactivate/delete users'),
('Manage Users', 'MANAGE_USERS', 'USERS', 'Full user management access'),
('View Roles', 'VIEW_ROLES', 'ROLES', 'View roles list'),
('Manage Roles', 'MANAGE_ROLES', 'ROLES', 'Create/edit/delete roles and assign permissions'),
('View Audit Logs', 'VIEW_AUDIT_LOGS', 'ADMIN', 'Access audit logs'),
('Manage Settings', 'MANAGE_SETTINGS', 'ADMIN', 'System and franchise configuration'),
('Manage Franchises', 'MANAGE_FRANCHISES', 'ADMIN', 'Create/edit/suspend franchises'),
('View Notifications', 'VIEW_NOTIFICATIONS', 'NOTIFICATIONS', 'View own notifications'),
('Send Notifications', 'SEND_NOTIFICATIONS', 'NOTIFICATIONS', 'Send notifications to users'),
('Upload Files', 'UPLOAD_FILES', 'FILES', 'Upload files and documents'),
('Delete Files', 'DELETE_FILES', 'FILES', 'Delete uploaded files')
ON DUPLICATE KEY UPDATE name = VALUES(name), module = VALUES(module), description = VALUES(description);

INSERT INTO tbl_global_roles (code, name, description, is_system)
VALUES ('SUPER_ADMIN', 'Super Admin', 'Full system access', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), is_system = VALUES(is_system);
