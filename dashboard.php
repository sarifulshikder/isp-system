<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';
include 'includes/genieacs_api.php';

$page_title = "Dashboard";
$active = "dashboard";

include 'includes/auth.php';

// Main stats
$total_users   = $conn->query("SELECT COUNT(*) c FROM customers")->fetch_assoc()['c'] ?? 0;
$active_users  = $conn->query("SELECT COUNT(*) c FROM customers WHERE status='active'")->fetch_assoc()['c'] ?? 0;
$expired_users = $conn->query("SELECT COUNT(*) c FROM customers WHERE expiry < CURDATE()")->fetch_assoc()['c'] ?? 0;
$online_users  = $conn->query("SELECT COUNT(*) c FROM radacct WHERE acctstoptime IS NULL")->fetch_assoc()['c'] ?? 0;

// Financial stats
$today_revenue = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM invoices WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['total'] ?? 0;
$month_revenue = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM invoices WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetch_assoc()['total'] ?? 0;

// Expiring soon
$expiring_soon = $conn->query("SELECT COUNT(*) c FROM customers WHERE expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)")->fetch_assoc()['c'] ?? 0;
$total_tickets = $conn->query("SELECT COUNT(*) c FROM tickets WHERE status='Open'")->fetch_assoc()['c'] ?? 0;

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>

<div class="flex-between mb-30 animate-fade-in">
        <div>
            <h1 class="h3 mb-5">Infrastructure Monitor</h1>
            <p class="text-muted small">Real-time backend service analytics and system performance</p>
        </div>
        <div class="flex gap-10">
            <button class="btn btn-secondary" onclick="updateMonitor()">
                <i class="fa fa-sync-alt"></i> Refresh
            </button>
            <a href="network_monitoring.php" class="btn btn-primary">
                <i class="fa fa-chart-line"></i> Full Analytics
            </a>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="stats-grid animate-fade-in stagger-1">
        <div class="stat-card info">
            <div class="stat-icon"><i class="fa fa-users"></i></div>
            <div class="stat-content">
                <div class="stat-label">Total Customers</div>
                <div class="stat-value"><?= number_format($total_users) ?></div>
            </div>
        </div>
        <div class="stat-card success">
            <div class="stat-icon"><i class="fa fa-signal"></i></div>
            <div class="stat-content">
                <div class="stat-label">Online Users</div>
                <div class="stat-value"><?= number_format($online_users) ?></div>
            </div>
        </div>
        <div class="stat-card danger">
            <div class="stat-icon"><i class="fa fa-calendar-xmark"></i></div>
            <div class="stat-content">
                <div class="stat-label">Expired Users</div>
                <div class="stat-value"><?= number_format($expired_users) ?></div>
            </div>
        </div>
        <div class="stat-card warning">
            <div class="stat-icon"><i class="fa fa-ticket"></i></div>
            <div class="stat-content">
                <div class="stat-label">Open Tickets</div>
                <div class="stat-value"><?= number_format($total_tickets) ?></div>
            </div>
        </div>
    </div>

    <!-- Infrastructure Monitors -->
    <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- Database Card -->
        <div class="card animate-fade-in stagger-2">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-database text-primary"></i> Database Server</h3>
                <span class="badge badge-success" id="db-status"><i class="status-dot dot-online"></i> Online</span>
            </div>
            <div class="card-body">
                <div class="flex-between mb-10">
                    <span class="text-muted">Uptime</span>
                    <span id="db-uptime" class="fw-bold text-main">Loading...</span>
                </div>
                <div class="flex-between">
                    <span class="text-muted">Version</span>
                    <span id="db-version" class="fw-bold text-main">Loading...</span>
                </div>
            </div>
        </div>

        <!-- RADIUS Card -->
        <div class="card animate-fade-in stagger-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-shield-alt text-primary"></i> RADIUS Engine</h3>
                <span id="rad-status" class="badge badge-info">Checking...</span>
            </div>
            <div class="card-body">
                <div class="flex-between mb-10">
                    <span class="text-muted">Auth Success (Today)</span>
                    <span id="rad-auth" class="text-success fw-bold">0</span>
                </div>
                <div class="flex-between">
                    <span class="text-muted">Rejections</span>
                    <span id="rad-fail" class="text-danger fw-bold">0</span>
                </div>
            </div>
        </div>

        <!-- GenieACS Card -->
        <div class="card animate-fade-in stagger-4">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-microchip text-warning"></i> GenieACS API</h3>
                <span id="acs-status" class="badge badge-info">Checking...</span>
            </div>
            <div class="card-body">
                <div class="flex-between mb-10">
                    <span class="text-muted">Task Queue</span>
                    <span id="acs-tasks" class="text-main fw-bold">0 Pending</span>
                </div>
                <div class="flex-between">
                    <span class="text-muted">API Latency</span>
                    <span id="acs-lat" class="text-success fw-bold">Stable</span>
                </div>
            </div>
        </div>

        <!-- System Resources Card -->
        <div class="card animate-fade-in stagger-5">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-server text-muted"></i> System Health</h3>
                <span class="badge badge-primary">Real-time</span>
            </div>
            <div class="card-body">
                <div class="flex-between mb-5">
                    <span class="text-muted">CPU Load</span>
                    <span id="sys-cpu" class="fw-bold">0.00</span>
                </div>
                <div class="flex-between mb-10">
                    <span class="text-muted">RAM Usage</span>
                    <span id="sys-mem" class="fw-bold">0%</span>
                </div>
                <div class="progress mb-10">
                    <div id="mem-bar" class="progress-bar" style="width: 0%"></div>
                </div>
                <div class="text-muted small" id="sys-uptime" style="font-size: 11px;">Loading uptime...</div>
            </div>
        </div>
    </div>

    <!-- Network Alarms & Real-time Feeds -->
    <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 20px;">
        <div class="card border-0 shadow animate-fade-in stagger-5" style="border-left: 5px solid var(--danger) !important;">
            <div class="card-header">
                <h3 class="card-title text-danger"><i class="fa fa-triangle-exclamation"></i> Critical Network Alarms</h3>
                <span class="badge badge-danger animate-pulse">Live</span>
            </div>
            <div class="card-body p-0">
                <div id="network-alarms" style="max-height: 300px; overflow-y: auto;">
                    <div class="p-20 text-center text-muted">
                        <i class="fa fa-spinner fa-spin"></i> Analyzing network nodes...
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card animate-fade-in stagger-5">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-chart-line text-success"></i> Revenue Snapshot</h3>
                <a href="billing/" class="btn btn-sm btn-secondary">Billing</a>
            </div>
            <div class="card-body">
                <div class="flex-between mb-15">
                    <div>
                        <div class="text-muted small uppercase fw-bold mb-5">Today Revenue</div>
                        <div class="h4 mb-0">NPR <?= number_format($today_revenue) ?></div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small uppercase fw-bold mb-5">This Month</div>
                        <div class="h4 mb-0 text-success">NPR <?= number_format($month_revenue) ?></div>
                    </div>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-success" style="width: 75%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Shortcuts -->
    <div class="mt-30 animate-fade-in stagger-5">
        <h3 class="h5 mb-15">Quick Actions</h3>
        <div class="flex gap-10 flex-wrap">
            <a href="add_user.php" class="btn btn-primary shadow-sm"><i class="fa fa-user-plus"></i> Add Customer</a>
            <a href="tickets.php?action=new" class="btn btn-warning shadow-sm"><i class="fa fa-ticket"></i> New Ticket</a>
            <a href="recharge.php" class="btn btn-success shadow-sm"><i class="fa fa-credit-card"></i> Recharge</a>
            <a href="leads.php" class="btn btn-info shadow-sm text-white"><i class="fa fa-funnel-dollar"></i> Add Lead</a>
        </div>
    </div>

</div>

<style>
    .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 5px; }
    .dot-online { background: var(--success); box-shadow: 0 0 8px rgba(16, 185, 129, 0.5); }
    .dot-offline { background: var(--danger); }
    .animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function updateMonitor() {
    $.getJSON('api_status.php', function(data) {
        // DB
        $('#db-uptime').text(data.db.uptime);
        $('#db-version').text(data.db.version);
        
        // RADIUS
        $('#rad-auth').text(data.radius.auth_success);
        $('#rad-fail').text(data.radius.auth_fail);
        $('#rad-status').text(data.radius.process).attr('class', data.radius.process === 'Running' ? 'badge badge-success' : 'badge badge-danger');
        
        // ACS
        $('#acs-status').text(data.acs.status).attr('class', data.acs.status === 'Online' ? 'badge badge-success' : 'badge badge-danger');
        $('#acs-tasks').text(data.acs.tasks + ' Pending');
        
        // System
        $('#sys-cpu').text(data.system.cpu);
        $('#sys-mem').text(data.system.mem_percent + '%');
        $('#mem-bar').css('width', data.system.mem_percent + '%');
        $('#sys-uptime').text('Server uptime: ' + data.system.uptime);
    });

    $.getJSON('api_network_status.php', function(data) {
        let alarmHtml = '';
        let offlineCount = 0;
        data.forEach(d => {
            if(!d.status) {
                offlineCount++;
                alarmHtml += `<div style="padding:15px; border-bottom:1px solid var(--border-light); color:var(--danger); display:flex; align-items:center; gap:10px;">
                    <i class="fa fa-times-circle"></i> <div><b>${d.name}</b><br><small>${d.ip}</small></div>
                </div>`;
            }
        });
        if(offlineCount === 0) {
            $('#network-alarms').html('<div class="p-30 text-center text-success"><i class="fa fa-check-circle fa-2x mb-10"></i><br>All network nodes are stable</div>');
        } else {
            $('#network-alarms').html(alarmHtml);
        }
    });
}

$(document).ready(function() {
    updateMonitor();
    setInterval(updateMonitor, 10000);
});
</script>

<?php include 'includes/footer.php'; ?>
