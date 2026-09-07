-- noontech - MySQL Relational Database Schema
-- Ready for direct import via phpMyAdmin in InfinityFree or local MySQL databases

CREATE DATABASE IF NOT EXISTS `noontech_db`;
USE `noontech_db`;

-- 1. Users Table Structure (Matches login.php and signup.php hashes)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Inquiries Table Structure (Matches index.php client project form submissions)
CREATE TABLE IF NOT EXISTS `inquiries` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `details` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Graphic Design Categories Model (Dynamic CRUD items)
CREATE TABLE IF NOT EXISTS `graphic_design_categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `icon_svg` TEXT DEFAULT NULL,
  `price` VARCHAR(20) DEFAULT NULL,
  `discount_percent` TINYINT UNSIGNED DEFAULT 0,
  `delivery_time` VARCHAR(100) DEFAULT NULL,
  `revisions` VARCHAR(100) DEFAULT NULL,
  `info_items` TEXT DEFAULT NULL, -- Stored as JSON: [{"message": "...", "link": "https://..."}]
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Migration: add columns if importing into an existing database
ALTER TABLE `graphic_design_categories` ADD COLUMN IF NOT EXISTS `price` VARCHAR(20) DEFAULT NULL;
ALTER TABLE `graphic_design_categories` ADD COLUMN IF NOT EXISTS `discount_percent` TINYINT UNSIGNED DEFAULT 0;
ALTER TABLE `graphic_design_categories` ADD COLUMN IF NOT EXISTS `delivery_time` VARCHAR(100) DEFAULT NULL;
ALTER TABLE `graphic_design_categories` ADD COLUMN IF NOT EXISTS `revisions` VARCHAR(100) DEFAULT NULL;
ALTER TABLE `graphic_design_categories` ADD COLUMN IF NOT EXISTS `info_items` TEXT DEFAULT NULL;

-- Seeding Default Graphic Design Category Records
INSERT INTO `graphic_design_categories` (`title`, `description`, `image_path`, `icon_svg`, `price`, `discount_percent`, `delivery_time`, `revisions`, `info_items`) VALUES
('Logo Design', 'Bespoke corporate identity, wordmarks, and iconic vector logos.', '', '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>', 'Rs 2,500', 20, '2-3 Days', 'Unlimited', '[{"message":"Source files (AI/EPS)","icon":"🔥"},{"message":"Commercial usage rights","icon":"💼"}]'),
('Invitations & Cards', 'Premium personal cards, newsletters, and custom print templates.', '', '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>', 'Rs 1,800', 0, '1-2 Days', '2 Iterations', '[{"message":"Print ready files","icon":"🖨️"},{"message":"Custom dimensions","icon":"📏"}]'),
('UI/UX Prototypes', 'High fidelity interactive app interfaces and digital blueprints.', '', '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>', 'Rs 4,500', 10, '5-7 Days', '3 Iterations', '[{"message":"Figma source file","icon":"🎨"},{"message":"Clickable prototype","icon":"✨"}]'),
('Social Media Kits', 'Optimized graphics for platforms, headers, and post layouts.', '', '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>', 'Rs 3,200', 0, '2-3 Days', '1 Iteration', '[{"message":"5 Platform templates","icon":"📱"},{"message":"Custom branding","icon":"🌟"}]');

