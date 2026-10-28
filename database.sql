-- Run ONLY if your sneakers_db is missing these. Safe to skip tables you already have.
USE sneakers_db;
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  mobile VARCHAR(20) NOT NULL,
  address VARCHAR(200) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','customer') NOT NULL DEFAULT 'customer'
);
-- Run this line once (error "Duplicate column" means it is already there):
ALTER TABLE orders ADD user_id INT NULL;

-- Activity log: records every action (admins see it on the dashboard)
CREATE TABLE IF NOT EXISTS activity_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  user_name VARCHAR(100) NOT NULL,
  role VARCHAR(20) NOT NULL,
  action VARCHAR(80) NOT NULL,
  details VARCHAR(255) DEFAULT '',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (name, email, mobile, address, password_hash, role) VALUES
('Sneakers Room Admin', 'sneakersroom.admin@gmail.com', '09077768906', 'poblacion',
 'sneakersroom', 'admin');