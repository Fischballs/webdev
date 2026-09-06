-- ============================================================
-- schema.sql
-- Miguel Gutib — database schema + starter data
--
-- HOW TO USE (XAMPP):
--   1. Start Apache + MySQL in the XAMPP control panel.
--   2. Open http://localhost/phpmyadmin
--   3. Click "Import" (or "New" > paste into the SQL tab) and run this file.
--      This creates the miguel_gutib database and all tables for you.
--   4. That's it — config/database.php (below) already points at this
--      database name, so no further setup is needed on the PHP side.
--
-- This file itself does NOT need to live inside htdocs / your web root.
-- Keep it anywhere convenient on your machine — it's only ever run once
-- through phpMyAdmin, never requested by a browser.
-- ============================================================

CREATE DATABASE IF NOT EXISTS miguel_gutib
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE miguel_gutib;

-- ------------------------------------------------------------
-- users — one row per registered account
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(150)        NOT NULL,
    email         VARCHAR(190)        NOT NULL,
    password_hash VARCHAR(255)        NOT NULL,
    created_at    TIMESTAMP           DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- products — the watch catalog
-- image_path is nullable on purpose: while it's NULL, home.php
-- falls back to asset_placeholder(). Fill it in with a real path
-- (e.g. 'assets/extracted_image_1.jpg') once you have the photo,
-- and home.php will automatically render a real <img> instead.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150)     NOT NULL,
    price       DECIMAL(10,2)    NOT NULL,
    image_path  VARCHAR(255)     NULL,
    tab_group   ENUM('classic','best_sellers','collaboration') NOT NULL DEFAULT 'best_sellers',
    category    ENUM('field','sports','romance') NULL,
    is_featured TINYINT(1)       NOT NULL DEFAULT 0,
    description TEXT             NULL,
    created_at  TIMESTAMP        DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO products (name, price, tab_group, category, is_featured, description) VALUES
('M.G Tempus Nocturnum', 1700.00, 'best_sellers', 'field',   0, 'A classic field watch with a black nylon strap.'),
('M.G Romantic Pair',    3500.00, 'best_sellers', 'romance', 0, 'Matching his-and-hers skeleton dial watches.'),
('M.G Emmersione',       2000.00, 'best_sellers', 'sports',  0, 'A rugged sport watch built for the water.'),
('M.G Militiare Chrono', 1000.00, 'best_sellers', 'field',   0, 'An olive-dial field chronograph.'),
('M.G Noir Chrono',      1945.00, 'collaboration', NULL,     1, 'Limited edition of only 100 pieces — full black metallic bezel with a textured rubber strap.');

-- ------------------------------------------------------------
-- cart_items — each logged-in user's shopping cart
-- one row per (user, product) pair; adding the same product
-- again just increases quantity instead of duplicating rows
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cart_items (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity   INT UNSIGNED NOT NULL DEFAULT 1,
    added_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_product (user_id, product_id),
    CONSTRAINT fk_cart_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- orders / order_items — created once a cart is checked out
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    total      DECIMAL(10,2) NOT NULL,
    status     ENUM('pending','paid','shipped','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id   INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity   INT UNSIGNED NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_orderitem_order   FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    CONSTRAINT fk_orderitem_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;
