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

<div class="animate-fade-in">
    <div class="flex-between mb-4">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">Infrastructure Monitor</h1>
            <p class="text-muted" style="font-size: 0.875rem;">Real-time backend service analytics and system performance</p>
        </div>
        <div class="flex gap-2">
            <button class="btn btn-secondary" onclick="updateMonitor()">
                <i class="fa fa-sync-alt"></i> <span class="d-none d-sm-inline">Refresh</span>
            </button>
            <a href="network_monitoring.php" class="btn btn-primary">
                <i class="fa fa-chart-line"></i> <span class="d-none d-sm-inline">Full Analytics</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body flex items-center gap-4">
                <div style="width: 48px; height: 48px; background: var(--primary-soft); color: var(--primary); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fa fa-users"></i>
                </div>
                <div>
                    <div class="text-muted fw-600" style="font-size: 0.75rem; text-transform: uppercase;">Total Customers</div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?= number_format($total_users) ?></div>
                </div>
            </div>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body flex items-center gap-4">
                <div style="width: 48px; height: 48px; background: var(--success-soft); color: var(--success); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fa fa-signal"></i>
                </div>
                <div>
                    <div class="text-muted fw-600" style="font-size: 0.75rem; text-transform: uppercase;">Online Users</div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?= number_format($online_users) ?></div>
                </div>
            </div>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body flex items-center gap-4">
                <div style="width: 48px; height: 48px; background: var(--danger-soft); color: var(--danger); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fa fa-calendar-xmark"></i>
                </div>
                <div>
                    <div class="text-muted fw-600" style="font-size: 0.75rem; text-transform: uppercase;">Expired Users</div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?= number_format($expired_users) ?></div>
                </div>
            </div>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body flex items-center gap-4">
                <div style="width: 48px; height: 48px; background: var(--warning-soft); color: var(--warning); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fa fa-ticket"></i>
                </div>
                <div>
                    <div class="text-muted fw-600" style="font-size: 0.75rem; text-transform: uppercase;">Open Tickets</div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?= number_format($total_tickets) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Infrastructure Monitors -->
    <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Database Card -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header flex-between">
                <h3 class="card-title"><i class="fa fa-database text-primary"></i> Database Server</h3>
                <span class="badge badge-success" id="db-status">Online</span>
            </div>
            <div class="card-body">
                <div class="flex-between mb-2">
                    <span class="text-muted">Uptime</span>
                    <span id="db-uptime" class="fw-600">Loading...</span>
                </div>
                <div class="flex-between">
                    <span class="text-muted">Version</span>
                    <span id="db-version" class="fw-600">Loading...</span>
                </div>
            </div>
        </div>

        <!-- RADIUS Card -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header flex-between">
                <h3 class="card-title"><i class="fa fa-shield-alt text-primary"></i> RADIUS Engine</h3>
                <span id="rad-status" class="badge badge-info">Checking...</span>
            </div>
            <div class="card-body">
                <div class="flex-between mb-2">
                    <span class="text-muted">Auth Success (Today)</span>
                    <span id="rad-auth" class="text-success fw-600">0</span>
                </div>
                <div class="flex-between">
                    <span class="text-muted">Rejections</span>
                    <span id="rad-fail" class="text-danger fw-600">0</span>
                </div>
            </div>
        </div>

        <!-- GenieACS Card -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header flex-between">
                <h3 class="card-title"><i class="fa fa-microchip text-warning"></i> GenieACS API</h3>
                <span id="acs-status" class="badge badge-info">Checking...</span>
            </div>
            <div class="card-body">
                <div class="flex-between mb-2">
                    <span class="text-muted">Task Queue</span>
                    <span id="acs-tasks" class="fw-600">0 Pending</span>
                </div>
                <div class="flex-between">
                    <span class="text-muted">API Latency</span>
                    <span id="acs-lat" class="text-success fw-600">Stable</span>
                </div>
            </div>
        </div>

        <!-- System Resources Card -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header flex-between">
                <h3 class="card-title"><i class="fa fa-server text-muted"></i> System Health</h3>
                <span class="badge badge-info">Real-time</span>
            </div>
            <div class="card-body">
                <div class="flex-between mb-1">
                    <span class="text-muted">CPU Load</span>
                    <span id="sys-cpu" class="fw-600">0.00</span>
                </div>
                <div class="flex-between mb-2">
                    <span class="text-muted">RAM Usage</span>
                    <span id="sys-mem" class="fw-600">0%</span>
                </div>
                <div class="progress mb-2" style="height: 6px;">
                    <div id="mem-bar" class="progress-bar" style="width: 0%"></div>
                </div>
                <div class="text-muted" id="sys-uptime" style="font-size: 0.75rem;">Loading uptime...</div>
            </div>
        </div>
    </div>

    <!-- Network Alarms & Real-time Feeds -->
    <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 1.5rem;">
        <div class="card" style="border-left: 4px solid var(--danger);">
            <div class="card-header flex-between">
                <h3 class="card-title text-danger"><i class="fa fa-triangle-exclamation"></i> Critical Network Alarms</h3>
                <span class="badge badge-danger">Live</span>
            </div>
            <div class="card-body p-0">
                <div id="network-alarms" style="max-height: 300px; overflow-y: auto;">
                    <div class="p-30 text-center text-muted">
                        <i class="fa fa-spinner fa-spin"></i> Analyzing network nodes...
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header flex-between">
                <h3 class="card-title"><i class="fa fa-chart-line text-success"></i> Revenue Snapshot</h3>
                <a href="billing/" class="btn btn-secondary btn-sm">Billing</a>
            </div>
            <div class="card-body">
                <div class="flex-between mb-4">
                    <div>
                        <div class="text-muted fw-600" style="font-size: 0.75rem; text-transform: uppercase;">Today Revenue</div>
                        <div style="font-size: 1.25rem; font-weight: 700;">NPR <?= number_format($today_revenue) ?></div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted fw-600" style="font-size: 0.75rem; text-transform: uppercase;">This Month</div>
                        <div style="font-size: 1.25rem; font-weight: 700; color: var(--success);">NPR <?= number_format($month_revenue) ?></div>
                    </div>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar" style="width: 75%; background: var(--success);"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Shortcuts -->
    <div class="mt-30">
        <h3 class="fw-600 mb-4" style="font-size: 1rem;">Quick Actions</h3>
        <div class="flex gap-2 flex-wrap">
            <a href="add_user.php" class="btn btn-primary"><i class="fa fa-user-plus"></i> Add Customer</a>
            <a href="tickets.php?action=new" class="btn btn-warning"><i class="fa fa-ticket"></i> New Ticket</a>
            <a href="recharge.php" class="btn btn-success"><i class="fa fa-credit-card"></i> Recharge</a>
            <a href="leads.php" class="btn btn-info"><i class="fa fa-funnel-dollar"></i> Add Lead</a>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function updateMonitor() {
    $.getJSON('api_status.php', function(data) {
        // DB
        $('#db-uptime').text(data.db.uptime);
        $('#db-version').text(data.db.version);
        $('#db-status').text(data.db.status).attr('class', data.db.status === 'Online' ? 'badge badge-success' : 'badge badge-danger');
        
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
                alarmHtml += `<div style="padding:1rem; border-bottom:1px solid var(--border); color:var(--danger); display:flex; align-items:center; gap:0.75rem;">
                    <i class="fa fa-times-circle"></i> <div><div class="fw-600">${d.name}</div><div style="font-size:0.75rem;">${d.ip}</div></div>
                </div>`;
            }
        });
        if(offlineCount === 0) {
            $('#network-alarms').html('<div class="p-30 text-center text-success"><i class="fa fa-check-circle" style="font-size: 2rem; margin-bottom: 0.5rem;"></i><br>All network nodes are stable</div>');
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
