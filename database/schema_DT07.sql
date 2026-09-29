-- CSE703073 - DT-07
-- San luu tru cong dong homestay gan voi san pham dia phuong
-- Thiet ke CSDL logic + vat ly - Tuan/Buoi 4 (Project week 4 / Tuan 09)
-- MySQL 8.0+, utf8mb4

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS dt07_homestay;
CREATE DATABASE dt07_homestay
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;
USE dt07_homestay;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(180) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','guest','host','seller') NOT NULL,
  full_name VARCHAR(160) NOT NULL,
  phone VARCHAR(20) NULL,
  status ENUM('active','locked') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT uq_users_email UNIQUE (email)
) ENGINE=InnoDB;

CREATE TABLE homestays (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  owner_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(180) NOT NULL,
  slug VARCHAR(200) NOT NULL,
  province VARCHAR(100) NOT NULL,
  district VARCHAR(100) NULL,
  address_line VARCHAR(255) NOT NULL,
  lat DECIMAL(10,7) NULL,
  lng DECIMAL(10,7) NULL,
  description TEXT NULL,
  checkin_time TIME NOT NULL DEFAULT '14:00:00',
  checkout_time TIME NOT NULL DEFAULT '12:00:00',
  status ENUM('pending','approved','rejected','suspended') NOT NULL DEFAULT 'pending',
  avg_rating DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uq_homestays_slug UNIQUE (slug),
  CONSTRAINT ck_homestays_rating CHECK (avg_rating BETWEEN 0 AND 5),
  CONSTRAINT fk_homestays_owner FOREIGN KEY (owner_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_hs_search (province, status, avg_rating),
  INDEX idx_hs_geo (lat, lng),
  INDEX idx_hs_owner (owner_id, status)
) ENGINE=InnoDB;

CREATE TABLE homestay_verifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  homestay_id BIGINT UNSIGNED NOT NULL,
  document_type ENUM('business_license','id_document','ownership_proof','other') NOT NULL,
  document_ref VARCHAR(120) NOT NULL,
  submitted_at DATETIME NOT NULL,
  reviewed_by BIGINT UNSIGNED NULL,
  reviewed_at DATETIME NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  note VARCHAR(255) NULL,
  CONSTRAINT fk_hsv_homestay FOREIGN KEY (homestay_id) REFERENCES homestays(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_hsv_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_hsv_status (status, submitted_at),
  INDEX idx_hsv_homestay (homestay_id, status)
) ENGINE=InnoDB;

CREATE TABLE rooms (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  homestay_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  room_type ENUM('private_room','family_room','dorm','bungalow','whole_house') NOT NULL,
  max_guests TINYINT UNSIGNED NOT NULL,
  bed_count TINYINT UNSIGNED NOT NULL,
  base_price DECIMAL(12,2) NOT NULL,
  quantity SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT ck_rooms_guests CHECK (max_guests BETWEEN 1 AND 20),
  CONSTRAINT ck_rooms_beds CHECK (bed_count >= 1),
  CONSTRAINT ck_rooms_price CHECK (base_price >= 0),
  CONSTRAINT ck_rooms_quantity CHECK (quantity >= 1),
  CONSTRAINT fk_rooms_homestay FOREIGN KEY (homestay_id) REFERENCES homestays(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_rooms_homestay (homestay_id, active),
  INDEX idx_rooms_filter (room_type, max_guests, base_price, active)
) ENGINE=InnoDB;

CREATE TABLE amenities (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  icon_key VARCHAR(60) NULL,
  category ENUM('room','property','service','accessibility') NOT NULL,
  CONSTRAINT uq_amenities_name UNIQUE (name)
) ENGINE=InnoDB;

CREATE TABLE room_amenities (
  room_id BIGINT UNSIGNED NOT NULL,
  amenity_id BIGINT UNSIGNED NOT NULL,
  is_highlighted BOOLEAN NOT NULL DEFAULT FALSE,
  note VARCHAR(120) NULL,
  PRIMARY KEY (room_id, amenity_id),
  CONSTRAINT fk_ra_room FOREIGN KEY (room_id) REFERENCES rooms(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_ra_amenity FOREIGN KEY (amenity_id) REFERENCES amenities(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_ra_amenity (amenity_id, room_id)
) ENGINE=InnoDB;

CREATE TABLE room_availability (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  room_id BIGINT UNSIGNED NOT NULL,
  stay_date DATE NOT NULL,
  units_total SMALLINT UNSIGNED NOT NULL,
  units_held SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  units_sold SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  price_override DECIMAL(12,2) NULL,
  status ENUM('open','closed') NOT NULL DEFAULT 'open',
  CONSTRAINT uq_av_room_date UNIQUE (room_id, stay_date),
  CONSTRAINT ck_av_units CHECK (units_held + units_sold <= units_total),
  CONSTRAINT ck_av_price CHECK (price_override IS NULL OR price_override >= 0),
  CONSTRAINT fk_av_room FOREIGN KEY (room_id) REFERENCES rooms(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_av_date_status (stay_date, status),
  INDEX idx_av_room_date (room_id, stay_date),
  INDEX idx_av_search (stay_date, status, price_override, units_total, units_held, units_sold)
) ENGINE=InnoDB;

CREATE TABLE seasonal_prices (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  room_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  price_per_night DECIMAL(12,2) NOT NULL,
  priority TINYINT UNSIGNED NOT NULL DEFAULT 1,
  CONSTRAINT ck_sp_dates CHECK (end_date >= start_date),
  CONSTRAINT ck_sp_price CHECK (price_per_night >= 0),
  CONSTRAINT fk_sp_room FOREIGN KEY (room_id) REFERENCES rooms(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_sp_room_dates (room_id, start_date, end_date, priority)
) ENGINE=InnoDB;

CREATE TABLE bookings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code CHAR(12) NOT NULL,
  guest_id BIGINT UNSIGNED NOT NULL,
  room_id BIGINT UNSIGNED NOT NULL,
  checkin_date DATE NOT NULL,
  checkout_date DATE NOT NULL,
  guest_count TINYINT UNSIGNED NOT NULL,
  unit_price DECIMAL(12,2) NOT NULL,
  nights SMALLINT UNSIGNED NOT NULL,
  total_amount DECIMAL(14,2) NOT NULL,
  status ENUM('draft','holding','pending_payment','confirmed','cancelled','expired','refunded','completed') NOT NULL DEFAULT 'draft',
  hold_expires_at DATETIME NULL,
  cancel_reason VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uq_bookings_code UNIQUE (code),
  CONSTRAINT ck_bk_dates CHECK (checkout_date > checkin_date),
  CONSTRAINT ck_bk_guests CHECK (guest_count BETWEEN 1 AND 20),
  CONSTRAINT ck_bk_nights CHECK (nights >= 1),
  CONSTRAINT ck_bk_amounts CHECK (unit_price >= 0 AND total_amount >= 0),
  CONSTRAINT ck_bk_total CHECK (total_amount = unit_price * nights),
  CONSTRAINT fk_bk_guest FOREIGN KEY (guest_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_bk_room FOREIGN KEY (room_id) REFERENCES rooms(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_bk_guest (guest_id, created_at),
  INDEX idx_bk_room_dates (room_id, checkin_date, checkout_date, status),
  INDEX idx_bk_status (status, created_at)
) ENGINE=InnoDB;

CREATE TABLE booking_status_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id BIGINT UNSIGNED NOT NULL,
  from_status VARCHAR(30) NULL,
  to_status VARCHAR(30) NOT NULL,
  actor_id BIGINT UNSIGNED NULL,
  reason VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bsl_booking FOREIGN KEY (booking_id) REFERENCES bookings(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_bsl_actor FOREIGN KEY (actor_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_bsl_booking_time (booking_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE payments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id BIGINT UNSIGNED NOT NULL,
  gateway VARCHAR(40) NOT NULL,
  txn_ref VARCHAR(80) NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  status ENUM('initiated','paid','failed','refunded') NOT NULL DEFAULT 'initiated',
  paid_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uq_payments_txn UNIQUE (txn_ref),
  CONSTRAINT ck_payments_amount CHECK (amount >= 0),
  CONSTRAINT fk_payments_booking FOREIGN KEY (booking_id) REFERENCES bookings(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_payments_booking (booking_id, status)
) ENGINE=InnoDB;

CREATE TABLE local_products (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seller_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(180) NOT NULL,
  origin_place VARCHAR(180) NOT NULL,
  description TEXT NULL,
  unit VARCHAR(40) NOT NULL,
  price DECIMAL(12,2) NOT NULL,
  stock_qty INT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('draft','published','hidden') NOT NULL DEFAULT 'draft',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT ck_lp_price CHECK (price >= 0),
  CONSTRAINT fk_lp_seller FOREIGN KEY (seller_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_lp_seller_status (seller_id, status),
  INDEX idx_lp_search (status, price, origin_place)
) ENGINE=InnoDB;

CREATE TABLE homestay_products (
  homestay_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  featured BOOLEAN NOT NULL DEFAULT FALSE,
  display_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (homestay_id, product_id),
  CONSTRAINT fk_hp_homestay FOREIGN KEY (homestay_id) REFERENCES homestays(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_hp_product FOREIGN KEY (product_id) REFERENCES local_products(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_hp_product (product_id, homestay_id),
  INDEX idx_hp_display (homestay_id, featured, display_order)
) ENGINE=InnoDB;

CREATE TABLE product_orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code CHAR(12) NOT NULL,
  buyer_id BIGINT UNSIGNED NOT NULL,
  homestay_id BIGINT UNSIGNED NULL,
  status ENUM('pending','confirmed','packing','delivered','cancelled') NOT NULL DEFAULT 'pending',
  subtotal DECIMAL(14,2) NOT NULL,
  total_amount DECIMAL(14,2) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uq_product_orders_code UNIQUE (code),
  CONSTRAINT ck_po_amounts CHECK (subtotal >= 0 AND total_amount >= 0),
  CONSTRAINT fk_po_buyer FOREIGN KEY (buyer_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_po_homestay FOREIGN KEY (homestay_id) REFERENCES homestays(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_po_buyer (buyer_id, created_at),
  INDEX idx_po_homestay_status (homestay_id, status, created_at)
) ENGINE=InnoDB;

CREATE TABLE product_order_items (
  order_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  quantity SMALLINT UNSIGNED NOT NULL,
  unit_price DECIMAL(12,2) NOT NULL,
  line_total DECIMAL(14,2) NOT NULL,
  PRIMARY KEY (order_id, product_id),
  CONSTRAINT ck_poi_quantity CHECK (quantity >= 1),
  CONSTRAINT ck_poi_price CHECK (unit_price >= 0),
  CONSTRAINT ck_poi_total CHECK (line_total = quantity * unit_price),
  CONSTRAINT fk_poi_order FOREIGN KEY (order_id) REFERENCES product_orders(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_poi_product FOREIGN KEY (product_id) REFERENCES local_products(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_poi_product (product_id, order_id)
) ENGINE=InnoDB;

CREATE TABLE experiences (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  homestay_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL,
  description TEXT NULL,
  start_at DATETIME NOT NULL,
  duration_minutes SMALLINT UNSIGNED NOT NULL,
  capacity SMALLINT UNSIGNED NOT NULL,
  booked_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  price DECIMAL(12,2) NOT NULL,
  status ENUM('open','full','cancelled','completed') NOT NULL DEFAULT 'open',
  CONSTRAINT ck_exp_duration CHECK (duration_minutes >= 1),
  CONSTRAINT ck_exp_capacity CHECK (capacity >= 1 AND booked_count <= capacity),
  CONSTRAINT ck_exp_price CHECK (price >= 0),
  CONSTRAINT fk_exp_homestay FOREIGN KEY (homestay_id) REFERENCES homestays(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_exp_homestay_time (homestay_id, start_at, status)
) ENGINE=InnoDB;

CREATE TABLE experience_bookings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  experience_id BIGINT UNSIGNED NOT NULL,
  guest_id BIGINT UNSIGNED NOT NULL,
  people_count SMALLINT UNSIGNED NOT NULL,
  total_amount DECIMAL(14,2) NOT NULL,
  status ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT ck_eb_people CHECK (people_count >= 1),
  CONSTRAINT ck_eb_amount CHECK (total_amount >= 0),
  CONSTRAINT fk_eb_experience FOREIGN KEY (experience_id) REFERENCES experiences(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_eb_guest FOREIGN KEY (guest_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_eb_experience (experience_id, status),
  INDEX idx_eb_guest (guest_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE reviews (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id BIGINT UNSIGNED NOT NULL,
  from_user_id BIGINT UNSIGNED NOT NULL,
  to_user_id BIGINT UNSIGNED NOT NULL,
  direction ENUM('guest_to_host','host_to_guest') NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  content TEXT NULL,
  visible BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uq_reviews_booking_direction UNIQUE (booking_id, direction),
  CONSTRAINT ck_reviews_rating CHECK (rating BETWEEN 1 AND 5),
  CONSTRAINT fk_reviews_booking FOREIGN KEY (booking_id) REFERENCES bookings(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_reviews_from FOREIGN KEY (from_user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_reviews_to FOREIGN KEY (to_user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_reviews_to (to_user_id, visible, created_at)
) ENGINE=InnoDB;

CREATE TABLE loyalty_transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  booking_id BIGINT UNSIGNED NULL,
  order_id BIGINT UNSIGNED NULL,
  points_delta INT NOT NULL,
  balance_after INT UNSIGNED NOT NULL,
  reason VARCHAR(160) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT ck_loyalty_balance CHECK (balance_after >= 0),
  CONSTRAINT fk_lt_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_lt_booking FOREIGN KEY (booking_id) REFERENCES bookings(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_lt_order FOREIGN KEY (order_id) REFERENCES product_orders(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_lt_user_time (user_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  type VARCHAR(50) NOT NULL,
  title VARCHAR(160) NOT NULL,
  message VARCHAR(500) NOT NULL,
  reference_type VARCHAR(40) NULL,
  reference_id BIGINT UNSIGNED NULL,
  read_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_notifications_user_read (user_id, read_at, created_at)
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_id BIGINT UNSIGNED NULL,
  action VARCHAR(60) NOT NULL,
  entity VARCHAR(60) NOT NULL,
  entity_id BIGINT UNSIGNED NULL,
  before_json JSON NULL,
  after_json JSON NULL,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_audit_actor FOREIGN KEY (actor_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_audit_actor_time (actor_id, created_at),
  INDEX idx_audit_entity (entity, entity_id, created_at)
) ENGINE=InnoDB;

-- View phuc vu trang cong khai va dashboard, khong thay the truy van nghiep vu chi tiet.
CREATE OR REPLACE VIEW vw_public_homestays AS
SELECT
  h.id, h.name, h.slug, h.province, h.district, h.lat, h.lng,
  h.avg_rating,
  MIN(r.base_price) AS min_base_price,
  COUNT(DISTINCT r.id) AS active_room_types
FROM homestays h
JOIN rooms r ON r.homestay_id = h.id AND r.active = TRUE
WHERE h.status = 'approved'
GROUP BY h.id, h.name, h.slug, h.province, h.district, h.lat, h.lng, h.avg_rating;

CREATE OR REPLACE VIEW vw_room_daily_inventory AS
SELECT
  ra.id, ra.room_id, ra.stay_date, ra.units_total, ra.units_held, ra.units_sold,
  (ra.units_total - ra.units_held - ra.units_sold) AS units_available,
  COALESCE(ra.price_override, r.base_price) AS effective_price,
  ra.status
FROM room_availability ra
JOIN rooms r ON r.id = ra.room_id;

DELIMITER $$
CREATE TRIGGER trg_availability_guard_bi
BEFORE INSERT ON room_availability
FOR EACH ROW
BEGIN
  IF NEW.units_held + NEW.units_sold > NEW.units_total THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'units_held + units_sold must be <= units_total';
  END IF;
END$$

CREATE TRIGGER trg_availability_guard_bu
BEFORE UPDATE ON room_availability
FOR EACH ROW
BEGIN
  IF NEW.units_held + NEW.units_sold > NEW.units_total THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'units_held + units_sold must be <= units_total';
  END IF;
END$$
DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;

-- Kiem tra nhanh sau khi chay schema + seed:
-- SHOW TABLES;
-- SELECT COUNT(*) AS homestays FROM homestays;
-- SELECT COUNT(*) AS rooms FROM rooms;
-- SELECT COUNT(*) AS availability_rows FROM room_availability;
-- EXPLAIN ANALYZE SELECT ... (xem benchmark_mysql.sql)
