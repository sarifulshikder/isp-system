# ISP Management System — Project Summary
> **এই ফাইলটি AI agent handoff-এর জন্য তৈরি।**
> সম্পূর্ণ codebase বিশ্লেষণ করে লেখা হয়েছে। নতুন AI এজেন্ট এই ফাইল পড়ে সরাসরি কাজ শুরু করতে পারবে।

---

## 1. প্রজেক্ট পরিচিতি

| বিষয় | বিবরণ |
|-------|--------|
| **নাম** | ISP Management System |
| **Version** | 2.0.0 |
| **ধরন** | ISP/WISP Billing ও Network Management (Splynx-এর মতো) |
| **Language** | PHP 8.2 (OOP + Procedural মিশ্রণ) |
| **Database** | MySQL 8.0 |
| **Web Server** | Apache 2 (mod_rewrite চালু) |
| **Deployment** | Docker Compose (4 container) |
| **PHP Dependencies** | `phpoffice/phpspreadsheet`, `twilio/sdk` (Composer) |

---

## 2. Docker Architecture

```
docker-compose.yml
├── isp_app        → PHP 8.2 + Apache (port 80)
│                    Volume: ./  → /var/www/html
├── isp_db         → MySQL 8.0 (port 3306)
│                    Volume: db_data, ./db.sql (init schema)
├── isp_phpmyadmin → phpMyAdmin (port 8080)
│                    root pass: rootpass123
└── isp_radius     → FreeRADIUS (উল্লেখ আছে কিন্তু docker-compose.yml-এ নেই — BUG)
```

**Environment Variables (docker-compose):**
```
DB_HOST=db
DB_USER=isp_user
DB_PASS=isp_pass123
DB_NAME=isp_db
MYSQL_ROOT_PASSWORD=${DB_ROOT_PASS}   ← .env থেকে নেয়, কিন্তু .env ফাইল নেই (BUG)
```
> **⚠️ গুরুত্বপূর্ণ:** `.env` ফাইল `.gitignore`-এ আছে, তাই repo-তে নেই। Server-এ manually তৈরি করতে হবে।
> healthcheck-এ hardcoded আছে: `-prootpass123`

---

## 3. ডিরেক্টরি স্ট্রাকচার (সম্পূর্ণ)

```
isp-system-main/
│
├── config.php                  ← DB connection, constants, DEBUG_MODE
├── user-config.php             ← Customer portal-এর আলাদা DB config (localhost hardcoded — BUG)
├── db.sql                      ← সম্পূর্ণ database schema + seed data
├── docker-compose.yml
├── Dockerfile
├── composer.json / composer.lock
│
├── index.php                   ← Admin login page (Security class ব্যবহার করে)
├── login.php                   ← পুরনো/alternative login (Security class ব্যবহার করে না — BUG)
├── logout.php                  ← Session destroy + redirect
├── dashboard.php               ← Admin dashboard (stats, revenue, alerts)
│
├── includes/                   ← Library / shared files
│   ├── auth.php                ← Session check, role helpers, timeout logic
│   ├── security.php            ← Security class: brute force, IP lockout, activity log
│   ├── header.php              ← HTML head, CSS, navigation শুরু
│   ├── footer.php              ← JS scripts, closing tags
│   ├── sidebar.php             ← Admin sidebar navigation (321 lines)
│   ├── topbar.php              ← Admin topbar
│   ├── user-header.php         ← Customer portal header
│   ├── customer.php            ← Customer helper functions
│   ├── mikrotik_api.php        ← RouterOS API client class (452 lines)
│   ├── mikrotik_snmp.php       ← MikroTik SNMP helper
│   ├── mikrotik_web.php        ← MikroTik Web API wrapper
│   ├── bdcom_olt.php           ← BDCOM OLT driver (SNMP + Telnet, 363 lines)
│   ├── bdcom_snmp.php          ← BDCOM SNMP functions
│   ├── bdcom_telnet.php        ← BDCOM Telnet/SSH client (350 lines)
│   ├── olt_api.php             ← Universal OLT API (BDCOM, Huawei, HSGQ)
│   ├── vsol_snmp.php           ← VSOL OLT SNMP driver
│   ├── payment_gateway.php     ← PaymentGateway class (Khalti, eSewa, Bank, Cash)
│   ├── notification.php        ← Notification class (SMS, Email)
│   ├── messaging.php           ← sendSMS() helper function (multiple gateways)
│   ├── genieacs_api.php        ← GenieACS TR-069 API client
│   └── tr069_pppoe.php         ← TR-069 PPPoE provisioning helper
│
├── customer/                   ← Customer self-service portal
│   ├── index.php               ← Redirect to login
│   ├── login.php               ← Customer login (user-config.php দিয়ে localhost connect)
│   ├── logout.php              ← Customer session destroy
│   ├── register.php            ← Customer self-registration (354 lines)
│   ├── dashboard.php           ← Customer dashboard (usage, expiry, plan info)
│   ├── profile.php             ← Profile edit (SQL injection আছে — BUG)
│   ├── invoices.php            ← Customer invoice list
│   ├── usage_history.php       ← Data usage history with formatBytes()
│   ├── tickets.php             ← Ticket list
│   ├── ticket_new.php          ← New ticket form
│   ├── ticket_view.php         ← Ticket detail + reply
│   ├── kb.php                  ← Knowledge base browser
│   └── wifi_settings.php       ← WiFi SSID/password view
│
├── billing/                    ← Billing module
│   ├── index.php               ← Billing dashboard (PaymentGateway stats)
│   ├── invoices.php            ← Invoice management (516 lines)
│   ├── payments.php            ← Payment history (375 lines)
│   ├── subscriptions.php       ← Subscription management (500 lines)
│   └── gateways.php            ← Payment gateway configuration (518 lines)
│
├── hotspot/                    ← Hotspot / Captive Portal module
│   ├── index.php               ← Hotspot user login page (voucher/SMS/PIN, 907 lines)
│   ├── captive_portal.php      ← Captive portal customization UI (598 lines)
│   ├── logout.php              ← Hotspot logout
│   ├── success.php             ← Login success page
│   ├── includes/
│   │   ├── auth.php            ← HotspotAuth class (593 lines)
│   │   ├── plan_manager.php    ← Hotspot plan/voucher management (506 lines)
│   │   └── voucher.php         ← VoucherSystem class
│   └── admin/
│       ├── index.php           ← Hotspot admin dashboard (497 lines)
│       ├── users.php           ← Hotspot user management (423 lines)
│       ├── plans.php           ← Hotspot plan management (449 lines)
│       ├── blacklist.php       ← MAC/IP blacklist (483 lines)
│       ├── hotel.php           ← Hotel mode management (399 lines)
│       ├── settings.php        ← Hotspot settings (318 lines)
│       └── add_profile.php     ← Add hotspot profile
│
├── api/                        ← JSON API endpoints
│   ├── global_search.php       ← Global search API
│   ├── mikrotik_olt_integration.php ← MikroTik-OLT bridge API
│   ├── network_topology.php    ← Network topology data API
│   ├── olt_ont.php             ← OLT ONT management API (274 lines)
│   ├── resolve_alert.php       ← Alert resolution API
│   ├── snmp_monitor.php        ← SNMP monitoring API (308 lines)
│   └── payment/
│       └── get_details.php     ← Payment detail API
│
├── payment/                    ← Payment gateway callbacks
│   ├── khalti_pay.php          ← Khalti payment initiation
│   ├── khalti_verify.php       ← Khalti payment verification
│   ├── esewa_pay.php           ← eSewa payment initiation (57 lines)
│   ├── esewa_verify.php        ← eSewa payment verification
│   └── recharge_wallet.php     ← Wallet recharge
│
├── report/                     ← Report pages
│   ├── index.php               ← Report dashboard
│   ├── active_users.php        ← Active user report
│   ├── expired_users.php       ← Expired user report
│   ├── expiring_users.php      ← Expiring soon report
│   ├── new_users.php           ← New user report
│   ├── export_active_users.php ← Excel export (phpspreadsheet)
│   ├── export_expired_users.php
│   ├── export_expiring_users.php
│   └── export_new_users.php
│
├── scripts/                    ← Cron job scripts
│   ├── auto_invoice.php        ← Monthly invoice auto-generation
│   ├── auto_disable_expired.php ← Expired user auto-disable
│   ├── db_backup.php           ← Database backup (SQL dump)
│   └── metrics_collector.php  ← OLT/device metrics collection (every 5 min)
│
├── monitoring/                 ← SNMP monitoring sub-system
│   ├── dashboard.php           ← Monitoring dashboard
│   ├── check.php               ← Device health check
│   ├── add_device.php          ← Add monitored device
│   ├── delete_device.php       ← Delete device
│   ├── db.php                  ← Monitoring DB connection
│   ├── twilio.php              ← Twilio SMS alerts
│   ├── viber_webhook.php       ← Viber alert webhook
│   ├── test_whatsapp.php       ← WhatsApp test
│   └── composer-setup.php      ← Composer installer (1788 lines)
│
├── config/
│   ├── genieacs.php            ← GenieACS server config (URL, credentials)
│   └── khalti_config.php       ← Khalti API keys
│
├── assets/
│   ├── css/                    ← Stylesheets
│   ├── js/                     ← JavaScript files
│   └── img/                    ← Images
│
├── radius/                     ← FreeRADIUS config (docker-compose reference করে কিন্তু folder নেই — BUG)
│
│--- Admin Pages (root level) ---
│
├── users.php                   ← Customer list + online status (MAIN customer management)
├── user_view.php               ← Customer detail view (707 lines)
├── user_edit.php               ← Customer edit form (436 lines)
├── user_add.php                ← Add new customer (280 lines)
├── user_add_befor_plug/        ← Pre-provisioning user add (folder)
├── user_expiry.php             ← Expiry management
├── user_status.php             ← User status toggle API
├── user_graph.php              ← User bandwidth graph
├── user_graph_data.php         ← Graph data API
├── user_live_graph.php         ← Live bandwidth graph
├── user_live_graph_data.php    ← Live graph data API
├── user_log.php                ← User activity log
├── user_usage_data.php         ← Usage data API
├── user_view_scripts.php       ← Scripts for user_view page
│
├── recharge.php                ← User renewal / recharge
├── quick_renew.php             ← Quick renewal
├── expire.php                  ← Force expire user
├── expired_users.php           ← Expired users page (stub)
├── pending_users.php           ← Pending activation users (stub)
├── online_users.php            ← Online users page (stub)
├── pppoe_users.php             ← PPPoE users page (stub)
├── hotspot_users.php           ← Hotspot users page (stub)
│
├── nas.php                     ← Network device (NAS) management
├── nas_edit.php                ← NAS edit
├── plans.php                   ← Service plan management (FUP tiers)
├── tickets.php                 ← Support ticket list
├── ticket_new.php              ← New ticket
├── ticket_detail.php           ← Ticket detail + reply
├── ticket_view.php             ← Ticket view
│
├── admin.php                   ← Admin user management (278 lines)
├── admin_edit.php              ← Admin user edit
├── admin_logs.php              ← Admin activity logs (60 lines)
├── change_password.php         ← Password change
│
├── branches.php                ← Branch management
├── branch_add.php              ← Add branch
├── branch_edit.php             ← Edit branch
├── branch_delete.php           ← Delete branch
│
├── dashboard.php               ← Main admin dashboard (278 lines)
├── network_monitoring.php      ← Network monitoring page
├── network_topology.php        ← NOC map / topology viewer (1754 lines)
├── network_alerts.php          ← Network alert management
├── network_observability.php   ← Observability dashboard
├── noc_dashboard.php           ← NOC dashboard
├── map.php                     ← FTTH/GIS map (523 lines, Leaflet.js)
├── map_api.php                 ← Map CRUD API (SQL injection আছে — BUG)
├── map_api_temp.php            ← Map API temporary/draft version
│
├── olt_dashboard.php           ← OLT management dashboard (773 lines)
├── olt_power_sync.php          ← ONU optical power sync
├── onu_power_api.php           ← ONU power API
├── mikrotik_dashboard.php      ← MikroTik router dashboard (315 lines)
├── mikrotik_manager.php        ← MikroTik management
├── mikrotik_connect.php        ← MikroTik connection test
├── mikrotik_test.php           ← MikroTik test page
├── mikrotik_traffic_api.php    ← MikroTik traffic data API (43 lines)
├── switch_dashboard.php        ← Switch management
│
├── mobile_tech.php             ← Field technician mobile app (978 lines, QR+GPS)
├── mobile_tech_api.php         ← Field tech API
│
├── genieacs_devices.php        ← GenieACS TR-069 device list (281 lines)
├── provisioning_api.php        ← CPE provisioning API
│
├── knowledge_base.php          ← Knowledge base admin
├── kb_view.php                 ← KB article view
│
├── leads.php                   ← Lead/prospect management (310 lines)
├── inventory.php               ← Equipment inventory
├── invoices.php                ← Invoice list (root level)
├── advanced_reports.php        ← Advanced reporting
├── export_report.php           ← Report export
├── report.php                  ← Report page
├── report_api.php              ← Report data API
│
├── system_config.php           ← System settings UI
├── system_logs.php             ← System log viewer (279 lines)
├── notification_settings.php   ← SMS/Email notification settings
├── sms_outbox.php              ← SMS outbox viewer
│
├── work_diary.php              ← Technician work diary
├── work_diary_api.php          ← Work diary API
├── faults.php                  ← Fault/incident management
├── fup_test.php                ← FUP (Fair Usage Policy) test
│
├── disconnect_user.php         ← Force disconnect user from RADIUS
├── online.php                  ← Online users (alternative)
│
├── radius_logs.php             ← FreeRADIUS log viewer
├── import_customers.php        ← Bulk customer import (Excel)
├── download_template.php       ← Download import template
│
├── api_docs.php                ← API documentation page
├── api_mikrotik.php            ← MikroTik API proxy
├── api_mikrotik_snmp.php       ← MikroTik SNMP API proxy
├── api_network_status.php      ← Network status API
├── api_status.php              ← System status API
├── wire_lease_api.php          ← Wire/cable lease API
│
├── check_tables.php            ← DB table check utility
├── check_billing_tables.php    ← Billing table check utility
├── debug_observability.php     ← Observability debug page
├── fup_test.php                ← FUP testing utility
│
└── test_*.php                  ← Various test/debug files (production-এ রাখা উচিত নয়)
    (test.php, test_login.php, test_pass.php, test_pass2.php,
     test_post.php, test_session.php, test_mikrotik_conn.php)
```

---

## 4. Database Schema (সম্পূর্ণ টেবিল তালিকা)

```
db.sql — মোট ~55টি টেবিল
```

| Category | Tables |
|----------|--------|
| **Auth/Users** | `admins`, `branches`, `roles`, `role_permissions`, `login_attempts`, `activity_log` |
| **Customers** | `customers`, `data_usage`, `usage_logs` |
| **RADIUS** | `radcheck`, `radreply`, `radusergroup`, `radacct`, `radpostauth` |
| **Network** | `nas`, `network_alerts`, `network_faults`, `network_topology_links`, `uptime_logs`, `performance_metrics` |
| **FTTH/GIS** | `ftth_nodes`, `fiber_routes`, `port_assignments`, `wire_leases` |
| **OLT** | `olt_onu_signal`, `onu_power_history` |
| **Plans** | `plans` |
| **Billing** | `invoices`, `recharge`, `billing_invoices`, `customer_subscriptions`, `payment_gateways`, `payment_transactions`, `auto_invoice_log` |
| **Tickets** | `tickets`, `ticket_replies` |
| **Knowledge Base** | `kb_categories`, `knowledge_base` |
| **Hotspot** | `hotspot_profiles`, `hotspot_users`, `hotspot_vouchers`, `hotspot_access_logs`, `hotspot_access_lists`, `hotspot_hotels`, `hotspot_rooms`, `hotspot_settings`, `hotspot_invoices` |
| **Other** | `leads`, `inventory_items`, `work_diary`, `diary_comments`, `sms_logs`, `system_settings`, `system_config` |

**Default Admin:**
- Username: `admin`
- Password: `admin123` (bcrypt hashed)
- Role: `superadmin`
- Branch: 1 (Main Branch)

---

## 5. Authentication System

### Admin Authentication (দুটো আলাদা flow — inconsistency)

**Flow 1 — `index.php` (সঠিক):**
```
POST index.php
  → Security::isLockedOut() check
  → prepared statement দিয়ে DB query
  → password_verify()
  → Security::recordLoginAttempt()
  → Security::logActivity()
  → SESSION set → redirect dashboard.php
```

**Flow 2 — `login.php` (ত্রুটিপূর্ণ):**
```
POST login.php
  → real_escape_string (not prepared)
  → password_verify()
  → SESSION set → redirect dashboard.php
  ❌ Security class একেবারে ব্যবহার নেই
  ❌ last_activity SESSION variable set নেই
```

### Session Variables (admin):
```php
$_SESSION['user_id']      // admin ID
$_SESSION['username']     // admin username
$_SESSION['role']         // superadmin | manager | support
$_SESSION['branch_id']    // branch ID (null for superadmin)
$_SESSION['login_time']   // login timestamp
$_SESSION['last_activity'] // ← index.php দিয়ে login করলে SET হয় না (BUG)
```

### Customer Authentication:
```php
$_SESSION['customer_user']   // customer username
$_SESSION['customer_id']     // customer DB id
```

### Role System (⚠️ Mismatch আছে):
| Role | db.sql ENUM | auth.php helpers |
|------|------------|-----------------|
| Super Admin | `superadmin` | `isSuperAdmin()` ✅ |
| Branch Admin | `manager` | `isBranchAdmin()` → checks `branchadmin` ❌ |
| Staff | `support` | `isStaff()` → checks `staff` ❌ |

---

## 6. Key Classes ও তাদের কাজ

### `Security` class (`includes/security.php`)
```
- isLockedOut($username)         → login_attempts টেবিল চেক করে
- recordLoginAttempt($u, $bool)  → attempt লগ করে, 5 বারে block করে
- logActivity($id, $u, $action)  → activity_log-এ INSERT করে
- getClientIP()                  → IP detect (spoofable via X-Forwarded-For)
- getSetting($key)               → system_settings থেকে value নেয়
- updateSetting($key, $val)      → system_settings upsert করে
```
> ⚠️ activity_log-এ INSERT করে `user_id`, `username`, `ip_address` column দিয়ে,
> কিন্তু টেবিলে আছে `admin_id`, `ip` — column mismatch, সব INSERT fail করে।

### `RouterosAPI` class (`includes/mikrotik_api.php`)
```
- RouterOS API via TCP socket (port 8728)
- connect(), login(), write(), read(), comm()
- Supports: PPPoE user management, queue, firewall, DHCP
```

### `BDCOM_OLT` class (`includes/bdcom_olt.php`)
```
- BDCOM OLT management via SNMP + Telnet
- getONUList(), getSignalStrength(), enableONU(), disableONU()
- rebootONU(), getONTStatus()
```

### `PaymentGateway` class (`includes/payment_gateway.php`)
```
- Khalti, eSewa, Bank Transfer, Cash support
- initiate(), verify(), getHistory()
- Config stored in payment_gateways table
```

### `HotspotAuth` class (`hotspot/includes/auth.php`)
```
- Voucher, SMS OTP, PIN, username/password authentication
- Session management for hotspot users
- MikroTik Hotspot API integration
```

---

## 7. External Integrations

| Integration | ফাইল | উদ্দেশ্য |
|------------|------|----------|
| MikroTik RouterOS | `includes/mikrotik_api.php` | PPPoE, queue, firewall |
| BDCOM OLT | `includes/bdcom_olt.php` | GPON/EPON ONU management |
| Huawei OLT | `includes/olt_api.php` | ONU management |
| GenieACS | `includes/genieacs_api.php`, `config/genieacs.php` | TR-069 CPE management |
| FreeRADIUS | DB tables: radcheck/radreply/radacct | PPPoE authentication |
| Khalti | `payment/khalti_*.php`, `config/khalti_config.php` | Nepal payment gateway |
| eSewa | `payment/esewa_*.php` | Nepal payment gateway |
| Twilio | `monitoring/twilio.php` | SMS alerts |
| Leaflet.js | `map.php` | FTTH GIS mapping |
| SNMP | `includes/bdcom_snmp.php`, `api/snmp_monitor.php` | Device monitoring |

---

## 8. ⚠️ সমস্যাসমূহ (Priority অনুযায়ী)

### 🔴 Critical

| # | সমস্যা | ফাইল | Line |
|---|--------|------|------|
| 1 | `map_api.php`-এ authentication নেই — যেকেউ access করতে পারবে | `map_api.php` | top |
| 2 | SQL Injection — DELETE query-তে unfiltered `$_POST['id']` | `map_api.php` | 112 |
| 3 | SQL Injection — SELECT query-তে unfiltered `$_GET['id']` | `map_api.php` | 46-49 |
| 4 | `DEBUG_MODE = true` hardcoded — production-এ error expose | `config.php` | 32 |
| 5 | `display_errors = On` — customer login-এ error সরাসরি browser-এ | `customer/login.php` | 3-4 |
| 6 | SQL Injection — customer profile update-এ unfiltered POST | `customer/profile.php` | 9-11 |

### 🟠 High

| # | সমস্যা | ফাইল | বিবরণ |
|---|--------|------|-------|
| 7 | Role enum mismatch | `auth.php` + `db.sql` | `isBranchAdmin()` ও `isStaff()` কখনো true হবে না |
| 8 | `activity_log` column mismatch | `security.php` + `db.sql` | সব security log INSERT fail করছে silently |
| 9 | `login.php`-এ brute force protection নেই | `login.php` | Security class use করা হয়নি |
| 10 | `last_activity` SESSION login-এ set হয় না | `login.php` | idle timeout কাজ করে না |
| 11 | `user-config.php`-এ hardcoded `localhost` | `user-config.php` | Docker-এ কাজ করবে না, separate DB connect |
| 12 | `radius/` folder নেই | docker-compose reference | FreeRADIUS volume mount fail করবে |
| 13 | `.env` ফাইল নেই | docker-compose | `${DB_ROOT_PASS}`, `${DB_PASS}` resolve হবে না |

### 🟡 Medium

| # | সমস্যা | ফাইল | বিবরণ |
|---|--------|------|-------|
| 14 | `getClientIP()` spoofable | `security.php` | X-Forwarded-For header দিয়ে IP lockout bypass |
| 15 | `login.php` prepared statement ব্যবহার করে না | `login.php` | real_escape_string ব্যবহার |
| 16 | `test_*.php` ফাইলগুলো production-এ আছে | root | debug info expose হতে পারে |
| 17 | `olt_dashboard.php`-এ unfiltered `$_GET['id']` | `olt_dashboard.php` | 12 |
| 18 | `dashboard.php`-এ `display_errors = On` | `dashboard.php` | 1-3 |
| 19 | `users.php`-এ `display_errors = On` | `users.php` | 1-3 |

---

## 9. কী কাজ বাকি / কী করতে হবে

নিচে suggested task list — next AI agent এখান থেকে কাজ শুরু করতে পারবে:

### Immediate (Security Fix):
1. **`map_api.php`** — শুরুতে `include 'includes/auth.php';` যোগ করুন
2. **`map_api.php` line 112** — `$id = intval($_POST['id']);` করুন
3. **`map_api.php` line 46-49** — prepared statement ব্যবহার করুন
4. **`config.php` line 32** — `DEBUG_MODE` → `false` করুন
5. **`customer/login.php` line 3-4** — `display_errors` লাইন মুছুন
6. **`customer/profile.php`** — SQL injection fix করুন prepared statement দিয়ে

### Role System Fix:
7. **`db.sql` / DB** — admins table-এর ENUM ঠিক করুন:
   ```sql
   ALTER TABLE admins MODIFY role ENUM('superadmin','branchadmin','staff') DEFAULT 'staff';
   ```
8. **অথবা `auth.php`** — `isBranchAdmin()` এ `branchadmin` → `manager`, `isStaff()` এ `staff` → `support`

### Activity Log Fix:
9. **`db.sql` / DB** — activity_log table-এ column যোগ করুন:
   ```sql
   ALTER TABLE activity_log ADD COLUMN user_id INT UNSIGNED DEFAULT NULL;
   ALTER TABLE activity_log ADD COLUMN username VARCHAR(60) DEFAULT NULL;
   ALTER TABLE activity_log ADD COLUMN ip_address VARCHAR(45) DEFAULT NULL;
   ```

### Login Fix:
10. **`login.php`** — `$_SESSION['last_activity'] = time();` যোগ করুন
11. **`login.php`** — Security class integrate করুন (index.php-এর মতো)

### Infrastructure Fix:
12. **`.env` ফাইল তৈরি করুন:**
    ```
    DB_ROOT_PASS=rootpass123
    DB_PASS=isp_pass123
    ```
13. **`user-config.php`** — `localhost` → `db` (Docker container name), credentials ঠিক করুন
14. **`radius/config/`** — FreeRADIUS config folder তৈরি করুন

### Cleanup:
15. Production থেকে সরান: `test.php`, `test_login.php`, `test_pass.php`, `test_pass2.php`, `test_post.php`, `test_session.php`, `test_mikrotik_conn.php`

---

## 10. গুরুত্বপূর্ণ URL সমূহ

| URL | কাজ |
|-----|-----|
| `http://server-ip/` | Admin login |
| `http://server-ip/dashboard.php` | Admin dashboard |
| `http://server-ip/customer/` | Customer portal |
| `http://server-ip/hotspot/` | Hotspot login |
| `http://server-ip:8080/` | phpMyAdmin |
| `http://server-ip/map.php` | FTTH GIS map |
| `http://server-ip/network_topology.php` | NOC topology map |
| `http://server-ip/olt_dashboard.php` | OLT management |
| `http://server-ip/mobile_tech.php` | Field tech app |

---

## 11. Cron Job সমূহ (Server-এ setup করতে হবে)

```bash
# প্রতি 5 মিনিটে metrics collect করুন
*/5 * * * * docker exec isp_app php /var/www/html/scripts/metrics_collector.php

# প্রতিদিন রাত ১২টায় expired user disable করুন
0 0 * * * docker exec isp_app php /var/www/html/scripts/auto_disable_expired.php

# প্রতি মাসের ১ তারিখে invoice generate করুন
0 1 1 * * docker exec isp_app php /var/www/html/scripts/auto_invoice.php

# প্রতিদিন রাত ২টায় DB backup নিন
0 2 * * * docker exec isp_app php /var/www/html/scripts/db_backup.php
```

---

*এই summary `isp-system-main` zip থেকে সরাসরি code বিশ্লেষণ করে তৈরি করা হয়েছে।*
*Last analyzed: 2026-05-26*
