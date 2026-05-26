-- ============================================================
--  ISP System — Missing Tables & Columns Fix Script
--  Run: docker exec -i isp_db mysql -u root -prootpass123 isp_db < db_fixes.sql
-- ============================================================

-- ============================================================
-- 1. branches — missing: code
-- (already done, but safe to run again)
-- ============================================================
ALTER TABLE `branches`
  ADD COLUMN IF NOT EXISTS `code` VARCHAR(20) DEFAULT NULL AFTER `name`;

-- ============================================================
-- 2. tickets — missing: branch_id
-- ============================================================
ALTER TABLE `tickets`
  ADD COLUMN IF NOT EXISTS `branch_id` INT UNSIGNED DEFAULT NULL AFTER `customer_id`;

-- ============================================================
-- 3. recharge — missing: months
-- ============================================================
ALTER TABLE `recharge`
  ADD COLUMN IF NOT EXISTS `months` TINYINT UNSIGNED DEFAULT 1 AFTER `amount`;

-- ============================================================
-- 4. network_alerts — missing: device_id, status
--    (PHP uses device_id & status, but DB has device_ip & resolved)
-- ============================================================
ALTER TABLE `network_alerts`
  ADD COLUMN IF NOT EXISTS `device_id`  INT UNSIGNED DEFAULT NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `status`     VARCHAR(20) DEFAULT 'active' AFTER `message`;

-- ============================================================
-- 5. hotspot_rooms — missing: room_number, floor, mac_address
--    (DB has room_no, PHP uses room_number + floor + mac_address)
-- ============================================================
ALTER TABLE `hotspot_rooms`
  ADD COLUMN IF NOT EXISTS `room_number` VARCHAR(20) DEFAULT NULL AFTER `hotel_id`,
  ADD COLUMN IF NOT EXISTS `floor`       VARCHAR(10) DEFAULT NULL AFTER `room_number`,
  ADD COLUMN IF NOT EXISTS `mac_address` VARCHAR(17) DEFAULT NULL AFTER `floor`;

-- ============================================================
-- 6. hotspot_users — missing: mac_address
--    (PHP uses mac_address, DB has mac)
-- ============================================================
ALTER TABLE `hotspot_users`
  ADD COLUMN IF NOT EXISTS `mac_address` VARCHAR(17) DEFAULT NULL AFTER `mac`;

-- ============================================================
-- 7. activity_log — missing: user_id, username, ip_address
--    (security.php inserts these but DB only has admin_id, ip)
-- ============================================================
ALTER TABLE `activity_log`
  ADD COLUMN IF NOT EXISTS `user_id`    INT UNSIGNED DEFAULT NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `username`   VARCHAR(60)  DEFAULT NULL AFTER `user_id`,
  ADD COLUMN IF NOT EXISTS `ip_address` VARCHAR(45)  DEFAULT NULL AFTER `ip`;

-- ============================================================
-- 8. wallet_transactions — table does not exist at all
--    (used by khalti_verify.php, esewa_verify.php, recharge_wallet.php)
-- ============================================================
CREATE TABLE IF NOT EXISTS `wallet_transactions` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username`   VARCHAR(60)  NOT NULL,
  `amount`     DECIMAL(10,2) NOT NULL DEFAULT 0,
  `gateway`    VARCHAR(50)  DEFAULT NULL,
  `status`     VARCHAR(20)  DEFAULT 'pending',
  `txn_id`     VARCHAR(100) DEFAULT NULL,
  `note`       TEXT         DEFAULT NULL,
  `created_at` DATETIME     DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_username` (`username`),
  INDEX `idx_status`   (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Verify all fixes
-- ============================================================
SELECT 'branches'          AS tbl, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='branches' AND COLUMN_NAME='code'
UNION ALL
SELECT 'tickets',            COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='tickets' AND COLUMN_NAME='branch_id'
UNION ALL
SELECT 'recharge',           COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='recharge' AND COLUMN_NAME='months'
UNION ALL
SELECT 'network_alerts/device_id', COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='network_alerts' AND COLUMN_NAME='device_id'
UNION ALL
SELECT 'network_alerts/status', COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='network_alerts' AND COLUMN_NAME='status'
UNION ALL
SELECT 'hotspot_rooms/room_number', COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='hotspot_rooms' AND COLUMN_NAME='room_number'
UNION ALL
SELECT 'hotspot_rooms/mac_address', COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='hotspot_rooms' AND COLUMN_NAME='mac_address'
UNION ALL
SELECT 'hotspot_users/mac_address', COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='hotspot_users' AND COLUMN_NAME='mac_address'
UNION ALL
SELECT 'activity_log/user_id', COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='activity_log' AND COLUMN_NAME='user_id'
UNION ALL
SELECT 'activity_log/username', COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='activity_log' AND COLUMN_NAME='username'
UNION ALL
SELECT 'wallet_transactions', COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='wallet_transactions' AND COLUMN_NAME='id';
