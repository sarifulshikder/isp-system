<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$base_path = './';
include 'config.php';
include 'includes/auth.php';

$page_title = "Customer Management";
$active = "users";

/* ============================
   ONLINE USERS LIST
============================ */
$online_list = [];
$res_online = $conn->query("SELECT DISTINCT username FROM radacct WHERE acctstoptime IS NULL");
if($res_online){
    while ($row = $res_online->fetch_assoc()) {
        $online_list[] = $row['username'];
    }
}

/*=====================
   STATS CALCULATION
============================ */
$total_users   = $conn->query("SELECT COUNT(*) c FROM customers")->fetch_assoc()['c'];
$active_users  = $conn->query("SELECT COUNT(*) c FROM customers WHERE status='active'")->fetch_assoc()['c'];
$expired_users = $conn->query("SELECT COUNT(*) c FROM customers WHERE expiry < CURDATE()")->fetch_assoc()['c'];
$online_users  = $conn->query("SELECT COUNT(DISTINCT username) c FROM radacct WHERE acctstoptime IS NULL")->fetch_assoc()['c'];

/* ============================
   SEARCH & FILTER LOGIC
============================ */
$q = $_GET['q'] ?? '';
$status_filter = $_GET['status'] ?? '';
$q_safe = $conn->real_escape_string($q);

$where_clauses = [];
if ($q) {
    $where_clauses[] = "(c.username LIKE '%$q_safe%' OR c.full_name LIKE '%$q_safe%' OR c.phone LIKE '%$q_safe%' OR c.address LIKE '%$q_safe%')";
}
if ($status_filter == 'active') {
    $where_clauses[] = "c.expiry >= CURDATE()";
} elseif ($status_filter == 'expired') {
    $where_clauses[] = "c.expiry < CURDATE()";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

$query = "
    SELECT c.*, p.name AS plan_name, p.speed, b.name AS branch_name
    FROM customers c
    LEFT JOIN plans p ON c.plan_id = p.id
    LEFT JOIN branches b ON c.branch_id = b.id
    $where_sql
    ORDER BY c.created_at DESC
";
$users = $conn->query($query);

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>

<div class="animate-fade-in">
    
    <!-- Stats Cards -->
    <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body flex items-center gap-4">
                <div style="width: 48px; height: 48px; background: var(--primary-soft); color: var(--primary); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fa fa-users"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?= number_format($total_users) ?></div>
                    <div class="text-muted fw-600" style="font-size: 0.75rem; text-transform: uppercase;">Total Customers</div>
                </div>
            </div>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body flex items-center gap-4">
                <div style="width: 48px; height: 48px; background: var(--success-soft); color: var(--success); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fa fa-signal"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?= number_format($online_users) ?></div>
                    <div class="text-muted fw-600" style="font-size: 0.75rem; text-transform: uppercase;">Currently Online</div>
                </div>
            </div>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body flex items-center gap-4">
                <div style="width: 48px; height: 48px; background: var(--info-soft); color: var(--info); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fa fa-user-check"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?= number_format($active_users) ?></div>
                    <div class="text-muted fw-600" style="font-size: 0.75rem; text-transform: uppercase;">Active Subscriptions</div>
                </div>
            </div>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body flex items-center gap-4">
                <div style="width: 48px; height: 48px; background: var(--danger-soft); color: var(--danger); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fa fa-user-xmark"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?= number_format($expired_users) ?></div>
                    <div class="text-muted fw-600" style="font-size: 0.75rem; text-transform: uppercase;">Expired Users</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="card">
        <div class="card-body flex-between flex-wrap gap-4">
            <form method="GET" class="flex gap-2 flex-wrap" style="flex: 1; max-width: 600px;">
                <input type="text" name="q" class="form-control" style="flex: 1; min-width: 200px;" placeholder="Search name, phone or username..." value="<?= htmlspecialchars($q) ?>">
                <select name="status" class="form-control" style="width: 150px;">
                    <option value="">All Status</option>
                    <option value="active" <?= $status_filter=='active'?'selected':'' ?>>Active</option>
                    <option value="expired" <?= $status_filter=='expired'?'selected':'' ?>>Expired</option>
                </select>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
            
            <a href="user_add.php" class="btn btn-primary">
                <i class="fa fa-user-plus"></i> New Customer
            </a>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Plan & Speed</th>
                        <th>PPP Status</th>
                        <th>Expiry Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users && $users->num_rows > 0): ?>
                        <?php while($u = $users->fetch_assoc()): 
                            $initials = strtoupper(substr($u['username'], 0, 2));
                            $is_online = in_array($u['username'], $online_list);
                            $is_expired = strtotime($u['expiry']) < time();
                        ?>
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div style="width: 40px; height: 40px; background: var(--bg-soft); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--text-muted); font-size: 0.875rem;">
                                        <?= $initials ?>
                                    </div>
                                    <div>
                                        <div class="fw-600"><?= htmlspecialchars($u['username']) ?></div>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($u['full_name'] ?? 'No Name') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-600" style="font-size: 0.875rem;"><?= htmlspecialchars($u['phone'] ?? '-') ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($u['address'] ?? 'No Address') ?></div>
                            </td>
                            <td>
                                <div class="fw-600"><?= htmlspecialchars($u['plan_name'] ?? 'N/A') ?></div>
                                <div style="font-size: 0.75rem; color: var(--success); font-weight: 700;"><?= htmlspecialchars($u['speed'] ?? '-') ?></div>
                            </td>
                            <td>
                                <?php if($is_online): ?>
                                    <span class="badge badge-success"><i class="fa fa-circle" style="font-size: 6px; margin-right: 4px;"></i> Online</span>
                                <?php else: ?>
                                    <span class="badge" style="background: var(--bg-soft); color: var(--text-muted);">Offline</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($is_expired): ?>
                                    <span class="badge badge-danger">Expired</span>
                                    <div style="font-size: 0.75rem; color: var(--danger); margin-top: 4px; font-weight: 600;"><?= date('M d, Y', strtotime($u['expiry'])) ?></div>
                                <?php else: ?>
                                    <span class="badge badge-info">Active</span>
                                    <div style="font-size: 0.75rem; color: var(--info); margin-top: 4px; font-weight: 600;"><?= date('M d, Y', strtotime($u['expiry'])) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div class="flex gap-2 justify-end">
                                    <a href="user_view.php?user=<?= urlencode($u['username']) ?>" class="btn btn-secondary btn-sm" title="View" style="padding: 0.4rem; width: 32px;">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="user_edit.php?user=<?= urlencode($u['username']) ?>" class="btn btn-secondary btn-sm" title="Edit" style="padding: 0.4rem; width: 32px; color: var(--success);">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="users.php?del=<?= urlencode($u['username']) ?>" class="btn btn-secondary btn-sm" title="Delete" style="padding: 0.4rem; width: 32px; color: var(--danger);" onclick="return confirm('Permanently delete this customer?')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; padding: 4rem; color: var(--text-muted);">No customers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
