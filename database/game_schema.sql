-- Game module database schema

-- Create games table
CREATE TABLE IF NOT EXISTS games (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    genre VARCHAR(100),
    release_date DATE,
    platform VARCHAR(100),
    price DECIMAL(10, 2),
    image VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_title (title),
    INDEX idx_genre (genre),
    INDEX idx_platform (platform),
    INDEX idx_is_active (is_active)
);

-- Insert sample games for testing
INSERT INTO games (title, description, genre, release_date, platform, price, image, is_active) VALUES
('Super Mario Odyssey', 'An action-adventure platform game featuring Mario and Cappy', 'Platform', '2017-10-27', 'Nintendo Switch', 59.99, 'mario-odyssey.jpg', 1),
('The Legend of Zelda: Breath of the Wild', 'Open-world action-adventure game in the Zelda series', 'Adventure', '2017-03-03', 'Nintendo Switch', 69.99, 'zelda-botw.jpg', 1),
('Cyberpunk 2077', 'Action role-playing video game set in Night City', 'RPG', '2020-12-10', 'PC, PS4, PS5, Xbox One, Xbox Series X/S', 59.99, 'cyberpunk2077.jpg', 1),
('The Last of Us Part II', 'Post-apocalyptic survival horror game', 'Action', '2020-06-19', 'PlayStation 4', 49.99, 'last-of-us-part2.jpg', 1),
('Red Dead Redemption 2', 'Western action-adventure game set in 1899', 'Action', '2018-10-26', 'PC, PS4, Xbox One', 59.99, 'red-dead-redemption2.jpg', 1);

-- Create view for active games
CREATE OR REPLACE VIEW v_active_games AS
SELECT 
    id,
    title,
    description,
    genre,
    release_date,
    platform,
    price,
    image,
    is_active,
    created_at,
    updated_at
FROM games
WHERE is_active = 1;