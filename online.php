<?php
include 'config.php';
include 'includes/auth.php';

$page_title = "Online Users";
$active = "online";

/* Fetch online users */
$q = $conn->query("
    SELECT radacct.username, radacct.acctstarttime, radacct.acctsessiontime,
           radacct.callingstationid,
           customers.plan_id, plans.name AS plan_name, plans.speed
    FROM radacct
    LEFT JOIN customers ON radacct.username = customers.username
    LEFT JOIN plans ON customers.plan_id = plans.id
    WHERE radacct.acctstoptime IS NULL
    ORDER BY radacct.acctstarttime DESC
");

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>

<div class="animate-fade-in">

    <div id="msg-box" style="margin-bottom:10px;"></div>

    <div class="flex-between mb-4 flex-wrap gap-4">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">Active Online Sessions</h1>
            <p class="text-muted" style="font-size: 0.875rem;">Currently connected PPPoE users across all routers</p>
        </div>
        <div class="flex gap-2">
            <span class="badge badge-success" style="padding: 0.5rem 1rem;">
                <i class="fa fa-signal mr-1"></i> <?= $q->num_rows ?> Online
            </span>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Subscriber</th>
                        <th>Service Plan</th>
                        <th>IP / MAC Address</th>
                        <th>Session Start</th>
                        <th>Duration</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($q && $q->num_rows > 0): ?>
                        <?php while($u = $q->fetch_assoc()):
                            $login = strtotime($u['acctstarttime']);
                            $duration = time() - $login;
                            $hours = floor($duration/3600);
                            $mins  = floor(($duration%3600)/60);
                            $secs  = $duration%60;
                        ?>
                        <tr id="row-<?= htmlspecialchars($u['username']) ?>">
                            <td>
                                <div class="fw-600"><?= htmlspecialchars($u['username']) ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;">PPPoE Session</div>
                            </td>
                            <td>
                                <div class="fw-600"><?= htmlspecialchars($u['plan_name'] ?: 'N/A') ?></div>
                                <div style="font-size: 0.75rem; color: var(--success); font-weight: 700;"><?= htmlspecialchars($u['speed'] ?: '-') ?></div>
                            </td>
                            <td>
                                <div style="font-size: 0.8125rem;"><span class="text-muted">IP:</span> <?= htmlspecialchars($u['callingstationid']) ?></div>
                                <div style="font-size: 0.75rem;"><span class="text-muted">MAC:</span> <?= htmlspecialchars($u['callingstationid']) ?></div>
                            </td>
                            <td>
                                <div style="font-size: 0.8125rem;"><?= date('M d, H:i', $login) ?></div>
                            </td>
                            <td>
                                <span class="badge badge-info" style="font-family: monospace; font-size: 10px;">
                                    <?= sprintf("%02dh %02dm %02ds", $hours, $mins, $secs) ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <button type="button"
                                    class="btn btn-danger btn-sm disconnect-btn"
                                    data-username="<?= htmlspecialchars($u['username']) ?>"
                                    style="font-size: 10px; padding: 0.25rem 0.6rem;">
                                    <i class="fa fa-power-off mr-1"></i> Drop
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; padding: 4rem; color: var(--text-muted);">No active sessions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('.disconnect-btn').on('click', function(){
    if(!confirm('Force disconnect this PPPoE user?')) return;

    var username = $(this).data('username');
    var btn = $(this);

    $.post('disconnect_user.php', {username: username}, function(data){
        try {
            var res = JSON.parse(data);
            if(res.success){
                $('#row-' + username).fadeOut();
                // Show a clean notification (we could use a better toast here but keeping it simple for now)
                alert(res.msg);
            } else {
                alert('Error: ' + res.msg);
            }
        } catch(e) {
            console.error('Invalid response:', data);
            alert('Operation completed with unknown response status.');
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>

