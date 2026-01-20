CREATE TABLE master_cuti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT,
    total_cuti INT DEFAULT 12,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);