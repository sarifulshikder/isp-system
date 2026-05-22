<?php
$base_path = '../';
include $base_path . 'config.php';
include $base_path . 'includes/auth.php';

$page_title = "Expired Customers List";
$active = "reports";

// Check if $conn exists
if (!isset($conn)) {
    die("Database connection failed.");
}

// Fetch Expired Users with Plan Info - REMOVED u.id
$query = "
    SELECT 
        u.username,
        u.expiry,
        u.status,
        u.phone,
        p.name AS plan_name,
        p.speed
    FROM customers u
    LEFT JOIN plans p ON u.plan_id = p.id
    WHERE u.expiry < CURDATE()
    ORDER BY u.expiry DESC
";
$result = $conn->query($query);

include $base_path . 'includes/header.php';
include $base_path . 'includes/sidebar.php';
include $base_path . 'includes/topbar.php';
?>

<div class="animate-fade-in">
    
    <div class="flex-between mb-4 flex-wrap gap-4">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">Expired Subscriptions</h1>
            <p class="text-muted" style="font-size: 0.875rem;">Showing customers whose plans have already expired</p>
        </div>
        <div class="flex gap-2">
            <form method="post" action="export_expired_users.php">
                <button type="submit" class="btn btn-secondary">
                    <i class="fa fa-file-csv mr-1"></i> Export Report
                </button>
            </form>
            <span class="badge badge-danger" style="padding: 0.5rem 1rem;">
                <i class="fa fa-user-xmark mr-1"></i> <?= $result ? $result->num_rows : 0 ?> Expired
            </span>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Subscriber</th>
                        <th>Expired Package</th>
                        <th>Contact</th>
                        <th>Expiry Timeline</th>
                        <th>Status</th>
                        <th style="text-align: right;">Quick Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): 
                            $days_ago = floor((time() - strtotime($row['expiry'])) / 86400);
                        ?>
                            <tr>
                                <td>
                                    <div class="fw-600"><?= htmlspecialchars($row['username']) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;">Account Suspended</div>
                                </td>
                                <td>
                                    <div class="fw-600"><?= htmlspecialchars($row['plan_name'] ?? 'N/A') ?></div>
                                    <div style="font-size: 0.75rem; color: var(--success); font-weight: 700;"><?= htmlspecialchars($row['speed'] ?? '-') ?></div>
                                </td>
                                <td>
                                    <div style="font-size: 0.8125rem;"><i class="fa fa-phone text-muted mr-1"></i> <?= htmlspecialchars($row['phone'] ?? 'N/A') ?></div>
                                </td>
                                <td>
                                    <div class="text-danger fw-600" style="font-size: 0.875rem;"><?= date('M d, Y', strtotime($row['expiry'])) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;"><?= $days_ago ?> days past due</div>
                                </td>
                                <td><span class="badge badge-danger">EXPIRED</span></td>
                                <td style="text-align: right;">
                                    <div class="flex gap-2 justify-end">
                                        <a href="<?= $base_path ?>user_view.php?user=<?= urlencode($row['username']) ?>" class="btn btn-secondary btn-sm" title="View Details" style="padding: 0.4rem; width: 32px;"><i class="fa fa-eye"></i></a>
                                        <a href="<?= $base_path ?>recharge.php?user=<?= urlencode($row['username']) ?>" class="btn btn-primary btn-sm" title="Recharge Now" style="font-size: 10px; padding: 0.25rem 0.6rem;"><i class="fa fa-bolt mr-1"></i> Renew</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; padding: 4rem; color: var(--text-muted);">No expired subscriptions found. All customers are currently active.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>
