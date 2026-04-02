-- checksheet.sql
SET NAMES utf8mb4;
SET time_zone = '+07:00';

DROP TABLE IF EXISTS checks_detail;
DROP TABLE IF EXISTS checks_header;
DROP TABLE IF EXISTS check_items;
DROP TABLE IF EXISTS check_sheets;
DROP TABLE IF EXISTS areas;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE areas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    sub_area VARCHAR(100) NOT NULL,
    KEY idx_areas_name (name),
    KEY idx_areas_sub (sub_area)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE check_sheets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE check_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    check_sheet_id INT UNSIGNED NOT NULL,
    item_label VARCHAR(255) NOT NULL,
    has_photo TINYINT(1) NOT NULL DEFAULT 0,
    has_note TINYINT(1) NOT NULL DEFAULT 0,
    input_type VARCHAR(50) NOT NULL DEFAULT 'text',
    KEY idx_items_sheet (check_sheet_id),
    CONSTRAINT fk_items_sheet FOREIGN KEY (check_sheet_id)
        REFERENCES check_sheets(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE checks_header (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    check_sheet_id INT UNSIGNED NOT NULL,
    area_id INT UNSIGNED NOT NULL,
    checked_by INT UNSIGNED NOT NULL,
    checked_at DATETIME NOT NULL,
    remarks TEXT NULL,
    KEY idx_header_sheet (check_sheet_id),
    KEY idx_header_area (area_id),
    KEY idx_header_checked_by (checked_by),
    KEY idx_header_checked_at (checked_at),
    CONSTRAINT fk_header_sheet FOREIGN KEY (check_sheet_id)
        REFERENCES check_sheets(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_header_area FOREIGN KEY (area_id)
        REFERENCES areas(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_header_user FOREIGN KEY (checked_by)
        REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE checks_detail (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    checks_header_id INT UNSIGNED NOT NULL,
    check_item_id INT UNSIGNED NOT NULL,
    value_text TEXT NULL,
    value_option VARCHAR(100) NULL,
    photo_path VARCHAR(255) NULL,
    KEY idx_detail_header (checks_header_id),
    KEY idx_detail_item (check_item_id),
    CONSTRAINT fk_detail_header FOREIGN KEY (checks_header_id)
        REFERENCES checks_header(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detail_item FOREIGN KEY (check_item_id)
        REFERENCES check_items(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin user: username=admin, password=admin123 (bcrypt)
INSERT INTO users (username, password, role) VALUES
(
  'admin',
  '$2b$10$9GokO/Ac7Hmev3xLuKFbiuV0ZEUwgnr/Q.omuOMdK5RHhUQVbTvSm',
  'admin'
);

INSERT INTO areas (name, sub_area) VALUES
('SF','Calender 1'),
('SF','Calender 2'),
('SF','Ply 1'),
('SF','Ply 2'),
('SF','Bead Making 1'),
('SF','Filler'),
('SF','Apex 1'),
('SF','Apex 2'),
('SF','Apex 3'),
('SF','Apex 4'),
('SF','Apex Manual'),
('SF','Mini Slitter'),
('SF','Triplex 1'),
('SF','Triplex 2'),
('SF','Bandina'),

('Building','BTU 1'),
('Building','BTU 2'),
('Building','BTU 3'),
('Building','BTU 4'),
('Building','BTU 5'),
('Building','BTU 6'),
('Building','BTU 7'),
('Building','BTU 8'),
('Building','BTU 9'),
('Building','BTU 10'),
('Building','BTU 11'),
('Building','BTU 12'),
('Building','BTU 13'),
('Building','BTU 14'),
('Building','BTU 15'),
('Building','BTU 16'),
('Building','BTU 17'),
('Building','BTU 18'),
('Building','BTU 19'),
('Building','BTU 20'),
('Building','BTU 21'),
('Building','BTU 22'),
('Building','BTU 23'),
('Building','BTU 24'),

('Building','STU 1'),
('Building','STU 2'),
('Building','STU 3'),
('Building','STU 4'),
('Building','STU 5'),
('Building','STU 6'),
('Building','STU 7'),
('Building','STU 8'),
('Building','STU 9'),
('Building','STU 10'),

('Building','MRU 1'),
('Building','MRU 2'),
('Building','MRU 3'),
('Building','MRU 4'),

('Curring','Auto Boiacca 1'),
('Curring','Auto Boiacca 2'),
('Curring','Manual Boiacca'),
('Curring','Line A'),
('Curring','Line B'),
('Curring','Line C'),
('Curring','Line D'),
('Curring','Line E'),
('Curring','Line F'),
('Curring','Line G');

INSERT INTO check_sheets (id, name, description) VALUES
(1, 'scanner', 'Scanner inspection'),
(2, 'axiomtex', 'Axiomtek panel PC inspection'),
(3, 'printer', 'Printer inspection');

INSERT INTO check_items (check_sheet_id, item_label, has_photo, has_note, input_type) VALUES
(1, 'scanner Condition', 1, 1, 'ok_notok'),
(1, 'basestation Condition', 1, 1, 'ok_notok'),
(1, 'result scanner', 0, 1, 'ok_notok'),
(1, 'check lens scanner', 0, 1, 'ok_notok'),
(1, 'check battery scanner', 0, 1, 'ok_notok'),
(1, 'check connector scanner cable', 1, 1, 'ok_notok'),
(1, 'SN scanner', 0, 0, 'text'),

(2, 'Check Condition Axiomtek', 1, 1, 'ok_notok'),
(2, 'Check Condition LCD, Touch', 1, 1, 'ok_notok'),
(2, 'Check Status Storage Axiomtek', 0, 1, 'text'),
(2, 'Check Fan Panel', 1, 1, 'ok_notok'),
(2, 'Check Fan Axiomtek', 0, 1, 'ok_notok'),
(2, 'Check LAN cable', 1, 1, 'ok_notok'),
(2, 'Check Status Task Manager', 1, 1, 'ok_notok'),

(3, 'Check Condition Printer', 1, 1, 'ok_notok'),
(3, 'Check Ink Level / PrintHead Printer', 0, 1, 'ok_notok'),
(3, 'Check Cable Data Printer', 1, 1, 'ok_notok'),
(3, 'SN Printer', 0, 0, 'text'),
(3, 'Test Print Printer', 1, 1, 'ok_notok');
