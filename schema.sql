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
  photo VARCHAR(255) DEFAULT NULL,
  photo_pos_x INT DEFAULT 50,
  photo_pos_y INT DEFAULT 20,
  photo_zoom INT DEFAULT 100,
  sort_order INT DEFAULT 0
) ENGINE=InnoDB;

-- (live fellowships + settings tables were converted to utf8mb4 so Tamil text and the rupee sign can be stored)
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

-- Gallery events (photos are grouped into dated events; gallery_images.event_id points here)
CREATE TABLE IF NOT EXISTS gallery_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  title VARCHAR(160) NOT NULL,
  event_date DATE DEFAULT NULL,
  cover VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES gallery_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;
-- ALTER TABLE gallery_images ADD COLUMN event_id INT DEFAULT NULL, ADD INDEX (event_id);

-- Admin users (log in at /admin; role 'super' = everything, 'limited' = service/celebrations/magazines/gallery)
CREATE TABLE IF NOT EXISTS admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(40) NOT NULL UNIQUE,
  display_name VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('super','limited') NOT NULL DEFAULT 'limited',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Branch churches (managed at /admin/branches)
CREATE TABLE IF NOT EXISTS branch_churches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  location VARCHAR(150) NOT NULL DEFAULT '',
  catechist VARCHAR(150) NOT NULL DEFAULT '',
  photo VARCHAR(255) DEFAULT NULL,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quiz results (managed at /admin/quiz; public page /quiz). marks = comma-separated per month, empty = absent.
CREATE TABLE IF NOT EXISTS quiz_results (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sno INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  sub VARCHAR(20) DEFAULT NULL,
  marks VARCHAR(120) NOT NULL,
  total DECIMAL(6,1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Obituary notices (managed at /admin/obituaries; public /obituaries; shown on the home page until the day after the funeral)
CREATE TABLE IF NOT EXISTS obituaries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  age VARCHAR(10) NOT NULL DEFAULT '',
  photo VARCHAR(255) DEFAULT NULL,
  passed_on DATE DEFAULT NULL,
  address VARCHAR(300) NOT NULL DEFAULT '',
  phone VARCHAR(60) NOT NULL DEFAULT '',
  service_title VARCHAR(120) NOT NULL DEFAULT 'Funeral Service',
  service_place VARCHAR(250) NOT NULL DEFAULT '',
  service_date DATE DEFAULT NULL,
  service_time VARCHAR(10) NOT NULL DEFAULT '',
  note VARCHAR(300) NOT NULL DEFAULT '',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
