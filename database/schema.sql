-- ==========================================
-- Admin Panel Database Schema
-- Database: campusera_simpeg
-- ==========================================

-- Create database
CREATE DATABASE IF NOT EXISTS campusera_simpeg;
USE campusera_simpeg;

-- ==========================================
-- Table: roles
-- ==========================================
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default roles
INSERT INTO roles (role_name, description) VALUES
('admin', 'Full system access with all permissions'),
('dosen', 'Management level access with limited permissions'),
('tendik', 'Basic staff access for daily operations'),
('user', 'Regular user access with minimal permissions');

-- ==========================================
-- Table: users
-- ==========================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone VARCHAR(20),
    avatar VARCHAR(255) DEFAULT 'default-avatar.png',
    role_id INT NOT NULL DEFAULT 4,
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role_id (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
-- Username: admin
-- Password: admin123
INSERT INTO users (username, email, password, first_name, last_name, role_id, is_active) VALUES
('admin', 'admin@example.com', '$2y$10$vuT0T56.1nqR1mzWBGKKH.lILeAA7EvUjyBTnBmaCVwuixoZgfKqy', 'Admin', 'User', 1, 1);

-- ==========================================
-- Table: activity_logs
-- ==========================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample activity logs
INSERT INTO activity_logs (user_id, activity_type, description, ip_address) VALUES
(1, 'login', 'User logged in', '127.0.0.1'),
(1, 'create_user', 'Created new user: manager1', '127.0.0.1'),
(1, 'update_profile', 'User updated profile information', '127.0.0.1'),
(2, 'login', 'User logged in', '127.0.0.1'),
(3, 'login', 'User logged in', '127.0.0.1');

-- ==========================================
-- OPTIONAL: Additional useful tables
-- ==========================================

-- Table: sessions (for advanced session management)
CREATE TABLE IF NOT EXISTS sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- Views for easy data access
-- ==========================================

-- View: users with role information
CREATE OR REPLACE VIEW v_users_with_roles AS
SELECT 
    u.id,
    u.username,
    u.email,
    u.first_name,
    u.last_name,
    u.phone,
    u.avatar,
    u.is_active,
    u.last_login,
    u.created_at,
    u.updated_at,
    r.role_name,
    r.description as role_description
FROM users u
JOIN roles r ON u.role_id = r.id;

-- View: recent activities with user info
CREATE OR REPLACE VIEW v_recent_activities AS
SELECT 
    al.id,
    al.activity_type,
    al.description,
    al.ip_address,
    al.created_at,
    u.username,
    u.first_name,
    u.last_name,
    u.email
FROM activity_logs al
LEFT JOIN users u ON al.user_id = u.id
ORDER BY al.created_at DESC;

-- ==========================================
-- Stored Procedures (Optional)
-- ==========================================

-- Procedure: Get user statistics
DELIMITER //
CREATE PROCEDURE sp_get_user_statistics()
BEGIN
    SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_users,
        SUM(CASE WHEN role_id = 1 THEN 1 ELSE 0 END) as admin_count,
        SUM(CASE WHEN role_id = 2 THEN 1 ELSE 0 END) as manager_count,
        SUM(CASE WHEN role_id = 3 THEN 1 ELSE 0 END) as staff_count,
        SUM(CASE WHEN role_id = 4 THEN 1 ELSE 0 END) as user_count
    FROM users;
END //
DELIMITER ;

-- Procedure: Clean old activity logs (older than 90 days)
DELIMITER //
CREATE PROCEDURE sp_clean_old_logs()
BEGIN
    DELETE FROM activity_logs 
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
END //
DELIMITER ;

-- ==========================================
-- Triggers
-- ==========================================

-- Trigger: Log when user is created
DELIMITER //
CREATE TRIGGER tr_user_created
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO activity_logs (user_id, activity_type, description, ip_address)
    VALUES (NEW.id, 'user_created', CONCAT('New user account created: ', NEW.username), '127.0.0.1');
END //
DELIMITER ;

-- ==========================================
-- Indexes for performance optimization
-- ==========================================

-- Already created above with table definitions
-- Additional composite indexes if needed:

CREATE INDEX idx_users_role_active ON users(role_id, is_active);
CREATE INDEX idx_activity_user_date ON activity_logs(user_id, created_at);

-- ==========================================
-- Grant permissions (adjust as needed)
-- ==========================================

-- Example for production:
-- CREATE USER 'admin_panel_user'@'localhost' IDENTIFIED BY 'your_secure_password';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON campusera_simpeg.* TO 'admin_panel_user'@'localhost';
-- FLUSH PRIVILEGES;

-- ==========================================
-- Database information
-- ==========================================

SELECT 'Database created successfully!' as message;
SELECT 'Default admin credentials:' as info, 'Username: admin, Password: admin123' as credentials;
SELECT 'Total tables created:' as info, COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'campusera_simpeg';