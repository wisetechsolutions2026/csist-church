CREATE DATABASE IF NOT EXISTS csist_new CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE csist_new;

DROP TABLE IF EXISTS gallery_images;
DROP TABLE IF EXISTS gallery_categories;
DROP TABLE IF EXISTS fellowships;
DROP TABLE IF EXISTS leaders;
DROP TABLE IF EXISTS magazines;
DROP TABLE IF EXISTS settings;

CREATE TABLE settings (
  setting_key VARCHAR(64) PRIMARY KEY,
  setting_value TEXT
) ENGINE=InnoDB;

CREATE TABLE leaders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role VARCHAR(100) NOT NULL,
  name VARCHAR(150) NOT NULL,
  contact VARCHAR(50),
  sort_order INT DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE fellowships (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(80) UNIQUE NOT NULL,
  title VARCHAR(150) NOT NULL,
  tagline VARCHAR(255),
  description TEXT,
  gallery_slug VARCHAR(80),
  sort_order INT DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE gallery_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(80) UNIQUE NOT NULL,
  title VARCHAR(150) NOT NULL,
  folder VARCHAR(80) NOT NULL,
  sort_order INT DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE gallery_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  filename VARCHAR(255) NOT NULL,
  alt VARCHAR(255),
  FOREIGN KEY (category_id) REFERENCES gallery_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE magazines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  period VARCHAR(50) NOT NULL,
  filename VARCHAR(255),
  sort_order INT DEFAULT 0
) ENGINE=InnoDB;

DROP TABLE IF EXISTS celebrations;
CREATE TABLE celebrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('birthday','anniversary') NOT NULL,
  name VARCHAR(150) NOT NULL,
  occasion_date VARCHAR(50) NOT NULL,
  photo VARCHAR(255) DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT DEFAULT 0
) ENGINE=InnoDB;
