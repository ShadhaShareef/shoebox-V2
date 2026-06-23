-- ============================================================
-- SHOEBOX — MySQL Database Schema
-- Kerala Premium Sneaker E-Commerce (XAMPP / phpMyAdmin)
-- ============================================================
-- Import: Open phpMyAdmin → Import → select this file → Go
-- Or CLI:  mysql -u root -p < database/schema.sql
--
-- Demo login: shadhamol2020@gmail.com / password
--             aswathy@gmail.com / password
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS shoebox_db;
CREATE DATABASE shoebox_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE shoebox_db;

-- ------------------------------------------------------------
-- LOYALTY TIERS
-- ------------------------------------------------------------
CREATE TABLE loyalty_tiers (
  id            TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name          VARCHAR(80)      NOT NULL,
  min_points    INT UNSIGNED     NOT NULL DEFAULT 0,
  text_color    VARCHAR(40)      NOT NULL DEFAULT 'text-neutral-500',
  bg_color      VARCHAR(60)      NOT NULL DEFAULT 'bg-neutral-50',
  badge_bg      VARCHAR(80)      NOT NULL DEFAULT 'bg-neutral-100',
  border_color  VARCHAR(60)      NOT NULL DEFAULT 'border-neutral-200',
  sort_order    TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE loyalty_tier_benefits (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tier_id     TINYINT UNSIGNED NOT NULL,
  benefit     VARCHAR(255) NOT NULL,
  sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_tier (tier_id),
  CONSTRAINT fk_benefit_tier FOREIGN KEY (tier_id) REFERENCES loyalty_tiers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- USERS
-- ------------------------------------------------------------
CREATE TABLE users (
  id              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  email           VARCHAR(120)     NOT NULL,
  password_hash   VARCHAR(255)     NOT NULL,
  full_name       VARCHAR(120)     NOT NULL,
  phone           VARCHAR(20)      NOT NULL DEFAULT '',
  district        VARCHAR(80)      NOT NULL DEFAULT 'Kochi (Ernakulam)',
  street_address  VARCHAR(255)     NOT NULL DEFAULT '',
  pincode         VARCHAR(10)      NOT NULL DEFAULT '',
  loyalty_points  INT UNSIGNED     NOT NULL DEFAULT 0,
  member_tier_id  TINYINT UNSIGNED NOT NULL DEFAULT 1,
  joined_at       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_tier (member_tier_id),
  CONSTRAINT fk_users_tier FOREIGN KEY (member_tier_id) REFERENCES loyalty_tiers(id)
) ENGINE=InnoDB;

CREATE TABLE admin_users (
  id              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  email           VARCHAR(120)     NOT NULL,
  password_hash   VARCHAR(255)     NOT NULL,
  full_name       VARCHAR(120)     NOT NULL,
  role            ENUM('admin', 'store_manager') NOT NULL DEFAULT 'store_manager',
  store_id        VARCHAR(30)      NULL,
  phone           VARCHAR(20)      NOT NULL DEFAULT '',
  is_active       TINYINT(1)       NOT NULL DEFAULT 1,
  last_login_at   DATETIME         NULL,
  created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admin_users_email (email),
  KEY idx_admin_users_role (role),
  KEY idx_admin_users_store (store_id)
) ENGINE=InnoDB;
CREATE TABLE loyalty_claims (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id     INT UNSIGNED NOT NULL,
  claim_type  ENUM('instagram_follow','phone_verify') NOT NULL,
  claimed_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_user_claim (user_id, claim_type),
  CONSTRAINT fk_claim_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE wishlist (
  user_id    INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  added_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, product_id),
  CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- PRODUCTS
-- ------------------------------------------------------------
CREATE TABLE products (
  id              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  sku             VARCHAR(30)      NOT NULL,
  name            VARCHAR(150)     NOT NULL,
  brand           VARCHAR(80)      NOT NULL,
  price           DECIMAL(10,2)    NOT NULL,
  original_price  DECIMAL(10,2)    NULL,
  rating          DECIMAL(2,1)     NOT NULL DEFAULT 4.5,
  category        VARCHAR(40)      NOT NULL,
  description     TEXT             NOT NULL,
  color_class     VARCHAR(80)      NOT NULL DEFAULT '',
  shoe_color      CHAR(7)          NOT NULL DEFAULT '#FFFFFF',
  accent_color    CHAR(7)          NOT NULL DEFAULT '#FF3B00',
  is_exclusive    TINYINT(1)       NOT NULL DEFAULT 0,
  is_accessory    TINYINT(1)       NOT NULL DEFAULT 0,
  stock_units     SMALLINT UNSIGNED NULL,
  image_url       VARCHAR(255)     NULL DEFAULT NULL,
  is_active       TINYINT(1)       NOT NULL DEFAULT 1,
  created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_products_sku (sku),
  KEY idx_products_brand (brand),
  KEY idx_products_category (category),
  KEY idx_products_price (price)
) ENGINE=InnoDB;

CREATE TABLE product_sizes (
  product_id INT UNSIGNED     NOT NULL,
  size_uk    TINYINT UNSIGNED NOT NULL,
  stock_qty  SMALLINT UNSIGNED NOT NULL DEFAULT 10,
  PRIMARY KEY (product_id, size_uk),
  CONSTRAINT fk_size_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE product_specs (
  product_id    INT UNSIGNED NOT NULL,
  material      VARCHAR(255) NOT NULL,
  midsole       VARCHAR(255) NOT NULL,
  weight        VARCHAR(80)  NOT NULL,
  resiliency    VARCHAR(255) NOT NULL,
  culture_match VARCHAR(255) NOT NULL,
  traction      VARCHAR(120) NOT NULL,
  origin        VARCHAR(120) NOT NULL,
  PRIMARY KEY (product_id),
  CONSTRAINT fk_spec_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- STORES (Kerala boutiques)
-- ------------------------------------------------------------
CREATE TABLE stores (
  id             VARCHAR(30)  NOT NULL,
  name           VARCHAR(120) NOT NULL,
  location       VARCHAR(255) NOT NULL,
  coordinates    VARCHAR(80)  NOT NULL,
  directions_url VARCHAR(500) NOT NULL,
  phone          VARCHAR(20)  NOT NULL,
  hours          VARCHAR(80)  NOT NULL,
  sort_order     TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE store_features (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id    VARCHAR(30)  NOT NULL,
  feature     VARCHAR(150) NOT NULL,
  sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_store_features (store_id),
  CONSTRAINT fk_feature_store FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- ORDERS
-- ------------------------------------------------------------
CREATE TABLE orders (
  id                VARCHAR(20)   NOT NULL,
  user_id           INT UNSIGNED  NULL,
  full_name         VARCHAR(120)  NOT NULL,
  email             VARCHAR(120)  NOT NULL,
  phone             VARCHAR(20)   NOT NULL,
  district          VARCHAR(80)   NOT NULL,
  street_address    VARCHAR(255)  NOT NULL,
  pincode           VARCHAR(10)   NOT NULL,
  subtotal          DECIMAL(10,2) NOT NULL,
  shipping_fee      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total_amount      DECIMAL(10,2) NOT NULL,
  status            ENUM('placed','authenticated','packed','transit','delivered') NOT NULL DEFAULT 'placed',
  courier           VARCHAR(120)  NOT NULL DEFAULT 'Kerala Express Logistics',
  payment_method    ENUM('upi','card','cod') NOT NULL DEFAULT 'upi',
  razorpay_order_id VARCHAR(80)   NULL,
  razorpay_payment_id VARCHAR(80) NULL,
  created_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_orders_razorpay_payment (razorpay_payment_id),
  KEY idx_orders_user (user_id),
  KEY idx_orders_email (email),
  KEY idx_orders_status (status),
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  order_id     VARCHAR(20)   NOT NULL,
  product_id   INT UNSIGNED  NULL,
  name         VARCHAR(150)  NOT NULL,
  brand        VARCHAR(80)   NOT NULL DEFAULT 'Shoebox Gear',
  size_uk      TINYINT UNSIGNED NULL,
  quantity     TINYINT UNSIGNED NOT NULL DEFAULT 1,
  unit_price   DECIMAL(10,2) NOT NULL,
  shoe_color   CHAR(7)       NOT NULL DEFAULT '#FFFFFF',
  accent_color CHAR(7)       NOT NULL DEFAULT '#FF3B00',
  PRIMARY KEY (id),
  KEY idx_order_items_order (order_id),
  CONSTRAINT fk_item_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_item_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE order_timeline (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id    VARCHAR(20)  NOT NULL,
  status      ENUM('placed','authenticated','packed','transit','delivered') NOT NULL,
  event_date  VARCHAR(40)  NOT NULL,
  event_time  VARCHAR(20)  NOT NULL,
  description TEXT         NOT NULL,
  location    VARCHAR(120) NOT NULL,
  sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_timeline_order (order_id),
  CONSTRAINT fk_timeline_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- FAQ & CONTACT
-- ------------------------------------------------------------
CREATE TABLE faq_items (
  id          VARCHAR(20)  NOT NULL,
  category    VARCHAR(60)  NOT NULL,
  question    VARCHAR(255) NOT NULL,
  answer      TEXT         NOT NULL,
  sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_faq_category (category)
) ENGINE=InnoDB;

CREATE TABLE contact_messages (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name   VARCHAR(120) NOT NULL,
  email       VARCHAR(120) NOT NULL,
  phone       VARCHAR(20)  NULL,
  subject     VARCHAR(120) NOT NULL DEFAULT 'General Inquiry',
  message     TEXT         NOT NULL,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE newsletter_subscribers (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email        VARCHAR(120) NOT NULL,
  subscribed_at DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_newsletter_email (email)
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Loyalty tiers
INSERT INTO loyalty_tiers (id, name, min_points, text_color, bg_color, badge_bg, border_color, sort_order) VALUES
(1, 'ROOKIE SNEAKERHEAD', 0, 'text-neutral-500', 'bg-neutral-50', 'bg-neutral-100 border-neutral-200', 'border-neutral-200', 1),
(2, 'INSIDER ELITE', 500, 'text-indigo-600', 'bg-indigo-50/40', 'bg-indigo-100/60 border-indigo-200 text-indigo-700', 'border-indigo-100', 2),
(3, 'SNEAKERHEAD ELITE', 1200, 'text-[#FF3B00]', 'bg-orange-50/40', 'bg-orange-100/60 border-orange-200 text-[#FF3B00]', 'border-orange-100', 3),
(4, 'LEGEND VAULT COLLECTOR', 2500, 'text-amber-600', 'bg-amber-50/40', 'bg-amber-100/60 border-amber-200 text-amber-800', 'border-amber-100', 4);

INSERT INTO loyalty_tier_benefits (tier_id, benefit, sort_order) VALUES
(1, 'Standard launch-day server queue clearance', 1),
(1, 'Earn 5% flat loyalty points on all shoe checkouts', 2),
(1, 'Free physical returns at any Kerala Flagship Hubs', 3),
(2, 'Priority fast-queue dispatch for hyped releases', 1),
(2, 'Earn 7% elevated points on all shoe checkouts', 2),
(2, 'Priority restock alerts (30-minute head start)', 3),
(2, 'Complimentary Shoebox Sneaker Wipes pack with orders', 4),
(3, 'Pre-sale access invitations to regional tier drops', 1),
(3, 'Earn 10% premium points on all shoe checkouts', 2),
(3, 'Raffle entry weight multiplier (2x odds on hyped releases)', 3),
(3, 'Complimentary VIP sneaker cleaning kit after every 3 orders', 4),
(3, 'Dedicated concierge support line on WhatsApp', 5),
(4, 'Guaranteed allocations - reserve 1 major drop per calendar year', 1),
(4, 'Earn 15% maximum points on all shoe checkouts', 2),
(4, 'Super raffle entry weight multiplier (4x odds)', 3),
(4, 'Complimentary premium deep cleaning service at any Kerala physical lounge', 4),
(4, 'Direct contact connection with Shoebox elite collectors & curators', 5);

-- Demo users (password: password)
INSERT INTO users (id, email, password_hash, full_name, phone, district, street_address, pincode, loyalty_points, member_tier_id, joined_at) VALUES
(1, 'shadhamol2020@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Shadha Mol', '+91 94475 88990', 'Kochi (Ernakulam)', 'Flat No. 5C, Skyline Heritage Apartments, Kakkanad', '682030', 320, 1, '2025-11-15 10:00:00'),
(2, 'aswathy@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Aswathy Menon', '+91 98455 77112', 'Kozhikode', 'House No. 12/450, Near Malabar Christian College, Kozhikode', '673001', 180, 1, '2026-01-20 14:30:00');

INSERT INTO wishlist (user_id, product_id) VALUES (1, 1), (1, 6);

-- Admin users
INSERT INTO admin_users (id, email, password_hash, full_name, role, store_id, phone, is_active, last_login_at, created_at) VALUES
(1, 'admin@shoebox.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Shoebox Admin', 'admin', NULL, '', 1, NULL, '2026-06-22 10:00:00'),
(2, 'kochimanager@shoebox.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kochi Store Manager', 'store_manager', 'kochi', '', 1, NULL, '2026-06-22 10:00:00'),
(3, 'kozhikodemanager@shoebox.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kozhikode Store Manager', 'store_manager', 'kozhikode', '', 1, NULL, '2026-06-22 10:00:00'),
(4, 'thrissurmanager@shoebox.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Thrissur Store Manager', 'store_manager', 'thrissur', '', 1, NULL, '2026-06-22 10:00:00');


-- Products (catalogue + exclusive drop + accessories)
INSERT INTO products (id, sku, name, brand, price, original_price, rating, category, description, color_class, shoe_color, accent_color, is_exclusive, is_accessory, stock_units) VALUES
(1,  'NB-550-WRC',   '550 ''White Rain Cloud''',           'New Balance',      14999.00, NULL,     4.8, 'New Arrival', 'The legendary 550 is a tribute to the 1989 basketball original. Built with premium leather and raw suede accents for a modern Kochi-ready terrace vibe.', 'bg-zinc-100 border-zinc-300', '#EAE9E4', '#FF3B00', 0, 0, NULL),
(2,  'ADI-SAMBA-GS', 'Samba OG ''Gum Sole''',             'Adidas Originals', 10999.00, NULL,     4.9, 'Trending',    'Born on the pitch, the Samba OG is a timeless icon of street style. Features soft leather overlays, contrast black stripes, and the recognizable rubber gum outsole.', 'bg-stone-300 border-stone-400', '#FFFFFF', '#1A1A1A', 0, 0, NULL),
(3,  'NK-AJ1-SHDW',  'Air Jordan 1 Low ''Shadow''',       'Nike',              8295.00, NULL,     5.0, 'New Arrival', 'An all-time favorite in a classic neutral colorway. Combines the signature Jordan profile with dark satin-style leather panels and a heavy-duty rubber cupsole.', 'bg-neutral-400 border-neutral-500', '#8E9196', '#FF3B00', 0, 0, NULL),
(4,  'CV-70-EGRET',  'Chuck 70 High-Top ''Egret''',       'Converse',          5999.00, NULL,     4.7, 'Classic',     'The Chuck 70 pays homage to the original 1970s design with premium heavy-grade canvas, high-gloss egret rubber sidewalls, and comfortable Ortholite cushioning.', 'bg-amber-50 border-amber-200', '#FFFDF2', '#CC3300', 0, 0, NULL),
(5,  'PU-PAL-COB',   'Palermo ''Cobalt Blue''',           'Puma',              7499.00, 8999.00, 4.6, 'Sale',        'Direct from the archives, the Palermo returns with its iconic T-toe construction and classic terrace flair. Styled in deep cobalt blue with high-energy gold highlights.', 'bg-blue-300 border-blue-400', '#3B82F6', '#F59E0B', 0, 0, NULL),
(6,  'AS-K14-MET',   'GEL-KAYANO 14 ''Metropolis''',      'Asics',            13999.00, NULL,     4.9, 'Trending',    'Reimagined with a technical retro-running aesthetic. Combines layered synthetic leather and metallic mesh overlays with responsive, signature GEL cushioning segments.', 'bg-slate-300 border-slate-400', '#A1A1AA', '#10B981', 0, 0, NULL),
(7,  'NK-AF1-TW',    'Air Force 1 ''07 ''Triple White''', 'Nike',              9695.00, NULL,     4.9, 'Classic',     'The splendor lives on in the basketball original. Features high-gloss premium leather overlays paired with a solid triple white finish and comfortable lightweight air cushioning.', 'bg-white border-zinc-100', '#FFFFFF', '#E2E8F0', 0, 0, NULL),
(8,  'NB-1906-SLV',  '1906R ''Silver Metallic''',          'New Balance',      16999.00, NULL,     4.8, 'New Arrival', 'Tech-focused running shoe from the mid-2000s, redeveloped with modern stability. Features N-ergy shock absorbing outsoles and a highly breathable knit mesh base.', 'bg-stone-200 border-stone-300', '#E4E4E7', '#FF3B00', 0, 0, NULL),
(100,'VOLT-ELITE-V1','Volt Elite V1 ''Obsidian Crimson''', 'Shoebox Labs',     18999.00, NULL,     5.0, 'Exclusive Drop', 'Kerala-exclusive engineered performance silhouette. Triple-density midsole, reflective 3M cord lacing system, and hand-finished obsidian crimson leather panels.', 'bg-neutral-900 border-neutral-700', '#1A1A1A', '#FF3B00', 1, 0, 7),
(901,'ACC-SHORTS',   'Utility Shorts / Noir',              'Shoebox Gear',      2499.00, NULL,     4.5, 'Accessory',   'Double-reinforced drop pockets with lightweight waterproof nylon matrix layers.', '', '#1A1A1A', '#FF3B00', 0, 1, NULL),
(902,'ACC-TEE',      'Essential Tee / Optic White',        'Shoebox Gear',      1899.00, NULL,     4.5, 'Accessory',   'Heavyweight ring-spun cotton loop fitted in an editorial relaxed box drop profile.', '', '#FFFFFF', '#FF3B00', 0, 1, NULL),
(903,'ACC-SOCKS',    'Crew Socks / Bolt Red',              'Shoebox Gear',       699.00, NULL,     4.5, 'Accessory',   'Premium cushioned crew socks with reinforced heel and toe zones.', '', '#FFFFFF', '#FF3B00', 0, 1, NULL),
(904,'ACC-CAP',      'Logo Cap / Stealth',                 'Shoebox Gear',      1299.00, NULL,     4.5, 'Accessory',   'Structured six-panel cap with embroidered Shoebox monogram.', '', '#1A1A1A', '#FF3B00', 0, 1, NULL);

INSERT INTO product_sizes (product_id, size_uk, stock_qty) VALUES
(1, 7, 10), (1, 8, 10), (1, 9, 10), (1, 10, 10), (1, 11, 10),
(2, 6, 10), (2, 7, 10), (2, 8, 10), (2, 9, 10), (2, 10, 10),
(3, 8, 10), (3, 9, 10), (3, 10, 10), (3, 11, 10),
(4, 7, 10), (4, 8, 10), (4, 9, 10), (4, 10, 10),
(5, 7, 10), (5, 8, 10), (5, 9, 10), (5, 10, 10), (5, 11, 10),
(6, 8, 10), (6, 9, 10), (6, 10, 10), (6, 11, 10),
(7, 7, 10), (7, 8, 10), (7, 9, 10), (7, 10, 10), (7, 11, 10),
(8, 8, 10), (8, 9, 10), (8, 10, 10), (8, 11, 10),
(100, 7, 5), (100, 8, 8), (100, 9, 7), (100, 10, 10), (100, 11, 6);

INSERT INTO product_specs (product_id, material, midsole, weight, resiliency, culture_match, traction, origin) VALUES
(1, 'Full grain drum-tanned leather & rich plush hairy suede overlays', 'High-density compression EVA with structural stitched rubber cupsole', 'Heavy-weight structured (450g)', 'Good. Coated leather wards off debris; pristine suede demands dry days', 'Modern classic terrace collector, clean aesthetic tailored for minimalist fits', 'Retro concentric basketball grip rings', 'Boston, USA (Archival reissue)'),
(2, 'Soft supple full-grain leather with premium pigskin T-toe suede wrapper', 'Streamlined low-profile natural gum rubber cupsole', 'Ultra-lightweight sleek (310g)', 'Excellent & incredibly easy to wipe down. Designed for high wear', 'Block-core signature, pristine vintage soccer vibes currently ruling Kochi streets', 'Treaded football pivot-circles', 'Herzogenaurach, Germany (1950 Heritage)'),
(3, 'Semi-matte tumble leather panels bonded on wear-resistant synthetic liners', 'Encapsulated Nike Air-Sole unit locked within tight-grip rubber walling', 'Medium balanced (380g)', 'Superb. Resists dirt easily, heavy rubber walls provide outstanding ankle shielding', 'Universal street icon, goes with everything from baggy cargos to clean shorts', 'Pivot point concentric basketball lines', 'Beaverton, Oregon (1985 Hardwood Grail)'),
(4, '12oz heavy-grade double-ply organic cotton canvas with metallic side eyelets', 'Varnish-glazed high sidewall vulcanized rubber, supportive OrthoLite foam layer', 'Medium comfort canvas (390g)', 'Fair. Perfect for breezy Kerala summer; canvas absorbs water on monsoon runouts', 'Alternative artsy retro vibe, comfortable and timelessly casual', 'Classic cross-thatch diamond pattern', 'Malden, Massachusetts (1970 Timeless)'),
(5, 'Supple short-pile Italian sports suede with heavy leather formstrip detailing', 'Textured archival rubber platform with retro core support grids', 'Lightweight flexible (330g)', 'Moderate. Requires weather treatment protectant spray for heavy outdoors', 'European relaxed sportswear vintage charm, bright eye-catching casual color pop', 'Grid waffle cupsole pattern', 'Herzogenaurach, Germany (1980 Stadium Legend)'),
(6, 'Multi-layered breathable synthetic leather skeleton over athletic mesh cells', 'Dual-density molded EVA with dynamic visible green GEL cushioning pods', 'Comfort performance (360g)', 'Outstanding wet-weather transpiration. Highly breathable athletic fabrics dry in hours', 'Futuristic techwear utility & classic Y2K dad-shoe aesthetic', 'ASICS AHAR durable rubber zones', 'Kobe, Japan (2008 Running Renaissance)'),
(7, 'Premium pristine vacuum-coated genuine box leather, steel dubrae lace tag', 'Thick rubber cupsole enclosing a full-length high-pressure Nike Air system', 'Heavy high-density tank (470g)', 'Absolute armor. Incredibly water-resistant, wipe-clean premium leather', 'The ultimate streetwear foundational canvas, crispy white clean aesthetic', 'Concentric rotation circle court pattern', 'Beaverton, Oregon (1982 Court Icon)'),
(8, 'Deconstructed open-cell cooling mesh matrix reinforced by liquid metallic overlay panels', 'ACTEVA LITE cushioned base with N-ergy shock rings and Stability Web arch cage', 'Responsive light-medium (355g)', 'High breathability ensures zero internal humidity, low water block', 'High-spec tech runner, premium gorpcore and streetwear staple', 'Ndurance high-abrasion compounds', 'Boston, USA (2000s Tech Revival)'),
(100, 'Hand-finished premium nubuck and reflective mesh composite', 'Triple-density EVA with carbon-fiber stability plate', 'Performance balanced (395g)', 'Excellent monsoon resistance with hydrophobic coating', 'Kerala-exclusive drop engineered for terrace culture and urban exploration', 'Multi-directional grip pods', 'Kochi, Kerala (Shoebox Labs)');

-- Stores
INSERT INTO stores (id, name, location, coordinates, directions_url, phone, hours, sort_order) VALUES
('kochi',     'Lulu Mall, Kochi',     'Level 1, Lulu Mall, Edappally, Kochi',                              'LAT: 9.9312° N, LON: 76.2673° E',  'https://maps.google.com/?q=Lulu+Mall+Kochi',      '+91 484 2727 888', '10:00 AM - 10:00 PM (Daily)', 1),
('kozhikode', 'HiLite, Kozhikode',    'Ground Floor, HiLite Mall, G.A. College Road, Kozhikode',             'LAT: 11.2588° N, LON: 75.7804° E', 'https://maps.google.com/?q=HiLite+Mall+Kozhikode', '+91 495 2434 999', '10:00 AM - 10:30 PM (Daily)', 2),
('thrissur',  'Sobha City, Thrissur', 'First Floor, Sobha City Mall, Puzhakkal, Thrissur',                 'LAT: 10.5276° N, LON: 76.2144° E', 'https://maps.google.com/?q=Sobha+City+Mall+Thrissur', '+91 487 2323 777', '10:00 AM - 10:00 PM (Daily)', 3);

INSERT INTO store_features (store_id, feature, sort_order) VALUES
('kochi',     'Kochi Exclusive Launch Pad', 1),
('kochi',     'Bespoke Sneaker Cleaning Bar', 2),
('kochi',     'Live Customization Station', 3),
('kozhikode', 'Heritage Collector Display', 1),
('kozhikode', 'Quick-Strike Release Zone', 2),
('kozhikode', 'Premium Lace Lounge', 3),
('thrissur',  'Community Sneaker Exchange', 1),
('thrissur',  'Limited Drops Safe', 2),
('thrissur',  'Toddler Sneaker Lab', 3);

-- FAQ
INSERT INTO faq_items (id, category, question, answer, sort_order) VALUES
('faq-1', 'Authenticity & Trust', 'Are all sneakers in the Shoebox vault 100% authentic?', 'Absolutely. Every single sneaker listed on our storefront goes through a rigorous, multi-point verification checklist by our footwear authenticators before list and delivery. We inspect stitching precision, leather grain, scent, typography, weight, and internal label SKU codes. Every pair is shipped with a tamper-proof Shoebox Authenticity Tag attached.', 1),
('faq-2', 'Kerala Logistics', 'How is regional delivery handled across Kerala?', 'We operate our private fleet of hand-delivery drivers and premium partners. We provide double-boxed delivery to Ernakulam (Kochi), Kozhikode, Thrissur, Trivandrum, Kottayam, Malappuram, and Kollam. Standard delivery takes 1-3 business days. Every package contains your pair blanketed in protective bubble-wrapping to seal the original product box in mint condition.', 2),
('faq-3', 'Sizing & Selection', 'Can I request an exchange if the sneaker does not fit me correctly?', 'Yes, we facilitate easy size exchanges within 7 days of hand-delivery. The pair must be completely unworn, clean on the outsole, and must retain the original security serial tag. The original shoebox must also be returned undamaged. You can initiate an exchange by contacting our WhatsApp concierge directly.', 3),
('faq-4', 'Payment Modes', 'Do you offer Cash on Delivery (COD) and UPI options?', 'Yes, we support local preferences! We accept instant UPI payments (Google Pay, PhonePe, Paytm, BHIM), major Credit/Debit Cards, and Cash on Delivery (COD). With our Cash on Delivery mode, our logistics agent allows you to visually inspect the authenticity seal before handing over cash or scanning our regional scanner.', 4),
('faq-5', 'Boutique Pickup', 'Can I buy online and inspect or pick up my pair physically?', 'Indeed! At the checkout stage, you can select "Boutique Pickup" and choose from our flagship spaces: Lulu Mall (Kochi), HiLite Mall (Kozhikode), or Sobha City Mall (Thrissur). Our staff will prepare your size-specific sneaker, place it on the showcase mirror table, and provide high-end packaging.', 5),
('faq-6', 'Limited Launches', 'How do I secure high-demand queue releases or upcoming drops?', 'Limited releases are designated as "Highly Demanded" or "Exclusive Drop". Regular shoppers with active Shoebox accounts accumulate loyalty points that qualify them for prioritized access, WhatsApp reservations, and early raffle tickets. Ensure you stay signed in and have correct contact indicators filled in.', 6);

-- Sample orders
INSERT INTO orders (id, user_id, full_name, email, phone, district, street_address, pincode, subtotal, shipping_fee, total_amount, status, courier, payment_method, created_at) VALUES
('SHBX-KL-123456', 1, 'Shadha Mol', 'shadhamol2020@gmail.com', '+91 94475 88990', 'Kochi (Ernakulam)', 'Flat No. 5C, Skyline Heritage Apartments, Kakkanad', '682030', 23294.00, 0.00, 23294.00, 'transit', 'Kerala Express Logistics (KEL-604)', 'upi', '2026-06-13 11:15:00'),
('SHBX-KL-789012', 2, 'Aswathy Menon', 'aswathy@gmail.com', '+91 98455 77112', 'Kozhikode', 'House No. 12/450, Near Malabar Christian College, Kozhikode', '673001', 7499.00, 350.00, 7849.00, 'delivered', 'Malabar Speedliner Dispatch (MSD-019)', 'cod', '2026-05-28 09:00:00');

INSERT INTO order_items (order_id, product_id, name, brand, size_uk, quantity, unit_price, shoe_color, accent_color) VALUES
('SHBX-KL-123456', 1, '550 ''White Rain Cloud''', 'New Balance', 8, 1, 14999.00, '#EAE9E4', '#FF3B00'),
('SHBX-KL-123456', 3, 'Air Jordan 1 Low ''Shadow''', 'Nike', 9, 1, 8295.00, '#8E9196', '#FF3B00'),
('SHBX-KL-789012', 5, 'Palermo ''Cobalt Blue''', 'Puma', 7, 1, 7499.00, '#3B82F6', '#F59E0B');

INSERT INTO order_timeline (order_id, status, event_date, event_time, description, location, sort_order) VALUES
('SHBX-KL-123456', 'placed',        'June 13, 2026', '11:15 AM', 'Order placed on Shoebox digital storefront. Reference checkout verified.', 'Kakkanad, Kochi', 1),
('SHBX-KL-123456', 'authenticated', 'June 13, 2026', '03:40 PM', 'Authenticity verified at Kochi Lulu Mall Hub. Stitching, leather structure, and SKU verified by Specialist #08.', 'Lulu Mall, Kochi', 2),
('SHBX-KL-123456', 'packed',        'June 13, 2026', '05:00 PM', 'Double protective cardboard wrapping applied. Tamper-evident holographic security tag attached.', 'Edappally Warehouse, Kochi', 3),
('SHBX-KL-123456', 'transit',       'June 14, 2026', '09:30 AM', 'In transit. Handed over to Kerala Express Logistics. Expected dispatch arrival within 24 hours.', 'Ernakulam sorting center', 4),
('SHBX-KL-789012', 'placed',        'May 28, 2026',  '09:00 AM', 'Order placed on Shoebox digital storefront.', 'Kochi Headquarters', 1),
('SHBX-KL-789012', 'authenticated', 'May 28, 2026',  '11:30 AM', 'Authenticity and premium grade standards confirmed by authenticator.', 'Lulu Mall, Kochi', 2),
('SHBX-KL-789012', 'packed',        'May 28, 2026',  '02:00 PM', 'Securely wrapped with waterproof film to endure Kerala monsoon rain.', 'Edappally Warehouse, Kochi', 3),
('SHBX-KL-789012', 'transit',       'May 28, 2026',  '04:30 PM', 'Loaded onto regional transit liner. Route: Ernakulam - Thrissur - Kozhikode.', 'Kozhikode Distribution Deck', 4),
('SHBX-KL-789012', 'delivered',     'May 29, 2026',  '02:15 PM', 'Successfully delivered. Handed directly to Aswathy Menon with digital signature received.', 'Kozhikode City', 5);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- END OF SCHEMA
-- ============================================================


