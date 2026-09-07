<?php
include 'includes/db.php';
$conn->query("ALTER TABLE `graphic_design_categories` ADD COLUMN `delivery_time` VARCHAR(100) DEFAULT NULL");
$conn->query("ALTER TABLE `graphic_design_categories` ADD COLUMN `revisions` VARCHAR(100) DEFAULT NULL");
$conn->query("ALTER TABLE `graphic_design_categories` ADD COLUMN `info_items` TEXT DEFAULT NULL");
echo 'Done';
