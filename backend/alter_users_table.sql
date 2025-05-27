ALTER TABLE users
ADD COLUMN role VARCHAR(20) DEFAULT 'user' NOT NULL;

-- Update existing users to have the 'user' role
UPDATE users SET role = 'user' WHERE role IS NULL;

-- Optionally, you can set an admin user (replace with your admin's email)
UPDATE users SET role = 'admin' WHERE email = 'your_admin_email@example.com'; 