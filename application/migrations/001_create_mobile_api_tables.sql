-- Migration: Create Mobile API Tables
-- Date: 2026-02-06
-- Description: Tables for mobile inventory application

-- Table for API authentication tokens
CREATE TABLE IF NOT EXISTS ospos_api_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    token VARCHAR(500) NOT NULL,
    device_info VARCHAR(255) NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token(255)),
    INDEX idx_employee (employee_id),
    INDEX idx_expires (expires_at),
    CONSTRAINT fk_api_tokens_employee FOREIGN KEY (employee_id)
        REFERENCES ospos_employees(person_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Table for inventory sessions
CREATE TABLE IF NOT EXISTS ospos_inventory_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    session_type ENUM('full', 'rolling_category', 'rolling_date') NOT NULL,
    category_id INT NULL,
    days_threshold INT NULL COMMENT 'For rolling_date: items not checked in X days',
    status ENUM('in_progress', 'completed', 'cancelled') DEFAULT 'in_progress',
    total_items INT DEFAULT 0,
    items_counted INT DEFAULT 0,
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    notes TEXT NULL,
    INDEX idx_employee (employee_id),
    INDEX idx_status (status),
    INDEX idx_type (session_type),
    CONSTRAINT fk_inv_sessions_employee FOREIGN KEY (employee_id)
        REFERENCES ospos_employees(person_id) ON DELETE CASCADE,
    CONSTRAINT fk_inv_sessions_category FOREIGN KEY (category_id)
        REFERENCES ospos_categories(category_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Table for scanned items in a session
CREATE TABLE IF NOT EXISTS ospos_inventory_session_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    item_id INT NOT NULL,
    expected_quantity DECIMAL(15,3) NOT NULL DEFAULT 0,
    counted_quantity DECIMAL(15,3) NOT NULL DEFAULT 0,
    variance DECIMAL(15,3) GENERATED ALWAYS AS (counted_quantity - expected_quantity) STORED,
    scanned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    synced TINYINT(1) DEFAULT 1,
    INDEX idx_session (session_id),
    INDEX idx_item (item_id),
    INDEX idx_synced (synced),
    CONSTRAINT fk_session_items_session FOREIGN KEY (session_id)
        REFERENCES ospos_inventory_sessions(id) ON DELETE CASCADE,
    CONSTRAINT fk_session_items_item FOREIGN KEY (item_id)
        REFERENCES ospos_items(item_id) ON DELETE CASCADE,
    UNIQUE KEY unique_session_item (session_id, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
