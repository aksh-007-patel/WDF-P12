-- Practical 12: Event Management CRUD with Poster Upload and File Validation
-- Database: event_db
-- Import: http://localhost/phpmyadmin > Import > database.sql
-- Or via shell: mysql -u root < database.sql

CREATE DATABASE IF NOT EXISTS event_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE event_db;

-- Events table with sample event records and optional poster images
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    venue VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL DEFAULT 'General',
    status ENUM('Open','Closed') NOT NULL DEFAULT 'Open',
    poster VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Audit log for event changes (Advanced extension)
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT DEFAULT NULL,
    action VARCHAR(50) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Sample data
INSERT INTO events (title, description, event_date, venue, category, status, poster) VALUES
('Tech Fest 2026', 'Annual technical festival with coding contests, robotics and workshops.', '2026-11-15', 'Main Auditorium', 'Technical', 'Open', NULL),
('Cultural Night', 'Music, dance and drama performances by students.', '2026-10-25', 'Open Air Theatre', 'Cultural', 'Open', NULL),
('Placement Drive', 'Campus recruitment drive for final year students.', '2026-09-30', 'Seminar Hall B', 'Career', 'Closed', NULL);
