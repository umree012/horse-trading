CREATE DATABASE horse_trading;
USE horse_trading;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('buyer', 'seller', 'both') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Horses table
CREATE TABLE horses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    gender ENUM('male', 'female', 'gelding') NOT NULL,
    height DECIMAL(5,2) NOT NULL,
    color VARCHAR(50) NOT NULL,
    breed VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    location VARCHAR(100) NOT NULL,
    description TEXT,
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id)
);

-- Favorites table
CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    horse_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (horse_id) REFERENCES horses(id),
    UNIQUE KEY unique_favorite (user_id, horse_id)
);