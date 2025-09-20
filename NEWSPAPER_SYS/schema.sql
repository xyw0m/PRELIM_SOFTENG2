-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS school_newspaper;

-- Use the newly created database
USE school_newspaper;

-- Create the 'users' table
CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create the 'categories' table
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
);

-- Create the 'articles' table with category_id
CREATE TABLE IF NOT EXISTS articles (
    article_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(255) NULL,
    author_id VARCHAR(50) NOT NULL,
    category_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('published', 'hidden') NOT NULL DEFAULT 'published',
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
);

-- Create the 'notifications' table
CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    article_id INT NOT NULL,
    admin_id VARCHAR(50) NOT NULL,
    message VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(article_id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create the 'edit_requests' table for article edit requests with new_category_id
CREATE TABLE IF NOT EXISTS edit_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    user_id VARCHAR(50) NOT NULL,
    new_title VARCHAR(255) NOT NULL,
    new_content TEXT NOT NULL,
    new_image_url VARCHAR(255) NULL,
    new_category_id INT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(article_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (new_category_id) REFERENCES categories(category_id) ON DELETE SET NULL
);

INSERT INTO categories (category_name) VALUES ('News Reports'), ('Editorials'), ('Opinion')
ON DUPLICATE KEY UPDATE category_name=category_name;