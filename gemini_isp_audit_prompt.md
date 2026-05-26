# ISP System Admin Panel — Full Audit & Fix Prompt for Gemini CLI

## তোমার কাজ
তুমি একজন expert full-stack web developer। আমার একটি PHP-based ISP Management System আছে যার admin panel-এ অনেক UI/UX সমস্যা এবং functional bug আছে। তোমাকে নিচের instructions অনুযায়ী সম্পূর্ণ project টি **audit করে, সব সমস্যা চিহ্নিত করে, এবং fix করতে হবে**। কোনো কাজ skip করবে না।

---

## প্রথমে Project Structure বোঝো

```
প্রথমে এই commands গুলো run করো:
1. find . -type f -name "*.php" | head -60
2. find . -type f -name "*.css" | head -20
3. find . -type f -name "*.js" | head -20
4. ls -la
```

তারপর প্রতিটি main PHP file পড়ো এবং বোঝো কোন file কী কাজ করে।

---

## Audit করার বিষয়গুলো (সব fix করতে হবে)

### ১. Navigation & Routing
- প্রতিটি sidebar menu item (Dashboard, Customers, Packages/Plans, Tickets, Network, Operations, Leads, Hotspot Portal, Reports, Settings, Account/Finance, Knowledge Base) এর link সঠিকভাবে কাজ করছে কিনা চেক করো
- Dropdown menus (Customers, Tickets, Network, Operations, Leads, Hotspot Portal, Reports, Settings, Account/Finance) expand/collapse ঠিকমতো কাজ করছে কিনা
- প্রতিটি page-এ **Back button** কাজ করছে কিনা — না করলে fix করো
- Browser back button এ গেলে সঠিক page-এ যাচ্ছে কিনা
- Active menu item highlight সঠিকভাবে দেখাচ্ছে কিনা

### ২. Button Functionality
প্রতিটি page-এর প্রতিটি button চেক করো:
- **Customers page:** Search button, New Customer button, View (👁) button, Edit (✏️) button, Delete (🗑️) button
- **Network Infrastructure page:** Add New Device button, Edit button, Delete button
- **Hotspot Portal:** Generate PINs button, Plans/Users/Hotel/Account links
- **Dashboard:** Refresh button, Analytics button
- যেকোনো form-এর Submit/Save/Cancel button
- Modal/popup এর Close button

প্রতিটি non-working button-এ proper JavaScript event handler এবং PHP action যোগ করো।

### ৩. Form Validation & Submission
- সব form-এ client-side validation আছে কিনা (required fields, email format, phone format)
- Form submit হলে proper success/error message দেখাচ্ছে কিনা
- Form submit এর পর সঠিক page-এ redirect হচ্ছে কিনা
- CSRF protection আছে কিনা — না থাকলে যোগ করো

### ৪. Mobile Responsiveness
Screenshots দেখে বোঝা যাচ্ছে এটি mobile-এ use হচ্ছে। Fix করো:
- Sidebar mobile-এ properly toggle হচ্ছে কিনা
- Table গুলো mobile-এ horizontally scroll করা যাচ্ছে কিনা
- Buttons এবং text mobile screen-এ readable কিনা
- Table columns mobile-এ overflow হচ্ছে কিনা (যেমন: Network Infrastructure, Customers table)
- Touch events properly কাজ করছে কিনা

### ৫. Dashboard Stats Cards
Dashboard-এ এই cards গুলোর data সঠিকভাবে load হচ্ছে কিনা:
- Total Customers
- Online Users / Currently Online
- Active Subscriptions
- Expired Users
- Total Vouchers, Available, Used, Profiles (Hotspot)

যদি data না দেখায় বা blank থাকে — database query fix করো।

### ৬. Network Infrastructure Page
- Device list (RB7100, P3310C, Cudy, v16508) সঠিকভাবে দেখাচ্ছে কিনা
- SNMP Config column-এর data দেখাচ্ছে কিনা
- Monitoring status (ONLINE/OFFLINE) real-time বা refresh-এ update হচ্ছে কিনা
- Add New Device form কাজ করছে কিনা
- Edit/Delete actions কাজ করছে কিনা

### ৭. Customer Management
- Customer search (name, phone, username) কাজ করছে কিনা
- Status filter (All Status dropdown) কাজ করছে কিনা
- PPP Status (ONLINE/OFFLINE) দেখাচ্ছে কিনা
- Plan & Speed সঠিক দেখাচ্ছে কিনা
- Expiry Status এবং date দেখাচ্ছে কিনা
- New Customer form-এর সব fields কাজ করছে কিনা

### ৮. UI/UX Improvements
- Loading states: API call বা page load-এ spinner/loader দেখাচ্ছে কিনা
- Empty states: কোনো data না থাকলে "No data found" message দেখাচ্ছে কিনা
- Error handling: কোনো error হলে user-friendly message দেখাচ্ছে কিনা
- Success notifications: কোনো action সফল হলে toast/alert দেখাচ্ছে কিনা
- Confirmation dialogs: Delete করার আগে confirm করছে কিনা

### ৯. PHP Errors & Warnings
```bash
# Error log চেক করো:
tail -100 /var/log/apache2/error.log
# অথবা
tail -100 /var/log/nginx/error.log
# অথবা project folder-এ
find . -name "error_log" -exec tail -50 {} \;
```
সব PHP error, warning, notice fix করো।

### ১০. Database Issues
- সব database queries properly PDO/MySQLi prepared statements use করছে কিনা (SQL injection prevention)
- Foreign key relationships সঠিক আছে কিনা
- Missing indexes যোগ করো

---

## Fix করার পদ্ধতি

1. **একটা একটা করে file audit করো** — সব issue list করো
2. **Priority অনুযায়ী fix করো:** Critical bugs → Broken buttons → UI issues → Improvements
3. **প্রতিটি fix-এর পর** সংক্ষেপে বলো কী fix করলে
4. **কোনো file delete করবে না** — শুধু edit করবে
5. **Backup:** কোনো major change-এর আগে বলো যেন আমি backup নিতে পারি

---

## Fix শেষে Summary দাও

এই format-এ একটি summary দাও:

```
## Audit Summary

### Files Modified:
- file1.php — [কী fix হয়েছে]
- file2.js — [কী fix হয়েছে]

### Bugs Fixed:
1. [Bug description] → [Fix description]
2. ...

### Still Needs Manual Attention:
- [যেগুলো তুমি fix করতে পারোনি]

### Recommendations:
- [Future improvements]
```

---

## শুরু করো

এখন project directory-তে যাও এবং উপরের সব steps follow করে কাজ শুরু করো। প্রথমে full project structure দেখাও, তারপর audit শুরু করো।
