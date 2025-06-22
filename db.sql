
CREATE DATABASE IF NOT EXISTS news_site CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE news_site;


CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE articles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    excerpt TEXT,
    image_url VARCHAR(500),
    category_id INT,
    author VARCHAR(100) DEFAULT 'Admin',
    views INT DEFAULT 0,
    featured BOOLEAN DEFAULT FALSE,
    status ENUM('draft', 'published') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_created (created_at)
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


INSERT INTO users (username, password) VALUES (
    'admin',
    '$2y$10$qkb55eNwXGd.bMYACGINiOMpHmENWHuH6CdALzvBgYVjMK2LIQ/MO'
);


INSERT INTO categories (name, slug) VALUES
('Europa', 'europa'),
('Tehnologija', 'tehnologija'),
('Politika', 'politika'),
('Ekonomija', 'ekonomija'),
('Sport', 'sport'),
('Kultura', 'kultura');