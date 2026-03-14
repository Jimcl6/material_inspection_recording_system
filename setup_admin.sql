-- Insert admin role
INSERT INTO roles (name, slug, description, created_at, updated_at) 
VALUES ('Administrator', 'admin', 'Has full access to all features', NOW(), NOW());

-- Get the admin role ID and update first user
SET @admin_id = LAST_INSERT_ID();
UPDATE users SET role_id = @admin_id WHERE id = 1 LIMIT 1;
