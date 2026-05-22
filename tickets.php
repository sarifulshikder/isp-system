<?php
$base_path = './';
include $base_path . 'config.php';
include $base_path . 'includes/auth.php';

$page_title = "Support Tickets";
$active = "tickets";

if (!isset($conn)) { die("Database connection failed."); }

if (isset($_GET['delete'])) {
    $ticket_id = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tickets WHERE id = ?");
    $stmt->bind_param("i", $ticket_id);
    if ($stmt->execute()) {
        header("Location: tickets.php?msg=deleted");
        exit;
    }
}

$total_tickets = $conn->query("SELECT COUNT(*) as c FROM tickets")->fetch_assoc()['c'];
$open_tickets = $conn->query("SELECT COUNT(*) as c FROM tickets WHERE status='Open'")->fetch_assoc()['c'];
$inprogress_tickets = $conn->query("SELECT COUNT(*) as c FROM tickets WHERE status='In Progress'")->fetch_assoc()['c'];
$closed_tickets = $conn->query("SELECT COUNT(*) as c FROM tickets WHERE status='Closed'")->fetch_assoc()['c'];

$status_filter = $_GET['status'] ?? '';
$where_sql = "";
if ($status_filter) {
    $where_sql = "WHERE t.status = '" . $conn->real_escape_string($status_filter) . "'";
}

$tickets = $conn->query("
    SELECT t.*, c.username, c.full_name
    FROM tickets t 
    LEFT JOIN customers c ON t.customer_id = c.id 
    $where_sql
    ORDER BY t.created_at DESC
");

include $base_path . 'includes/header.php';
include $base_path . 'includes/sidebar.php';
include $base_path . 'includes/topbar.php';
?>

<div class="animate-fade-in">
    
    <?php if(isset($_GET['msg']) && $_GET['msg']=='deleted'): ?>
        <div class="badge badge-success mb-4 w-full justify-center" style="padding: 1rem; border-radius: var(--radius);">Ticket deleted successfully!</div>
    <?php endif; ?>
    
    <div class="flex-between mb-4">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">Support Tickets</h1>
            <p class="text-muted" style="font-size: 0.875rem;">Manage and resolve customer support requests</p>
        </div>
        <a href="ticket_new.php" class="btn btn-primary"><i class="fa fa-plus"></i> New Ticket</a>
    </div>

    <!-- Stats Cards -->
    <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body flex items-center gap-4">
                <div style="width: 48px; height: 48px; background: var(--primary-soft); color: var(--primary); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fa fa-ticket-alt"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?= $total_tickets ?></div>
                    <div class="text-muted fw-600" style="font-size: 0.75rem; text-transform: uppercase;">Total Tickets</div>
                </div>
            </div>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body flex items-center gap-4">
                <div style="width: 48px; height: 48px; background: var(--danger-soft); color: var(--danger); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fa fa-envelope-open"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?= $open_tickets ?></div>
                    <div class="text-muted fw-600" style="font-size: 0.75rem; text-transform: uppercase;">Open</div>
                </div>
            </div>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body flex items-center gap-4">
                <div style="width: 48px; height: 48px; background: var(--warning-soft); color: var(--warning); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fa fa-spinner"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?= $inprogress_tickets ?></div>
                    <div class="text-muted fw-600" style="font-size: 0.75rem; text-transform: uppercase;">In Progress</div>
                </div>
            </div>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body flex items-center gap-4">
                <div style="width: 48px; height: 48px; background: var(--success-soft); color: var(--success); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fa fa-check-circle"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?= $closed_tickets ?></div>
                    <div class="text-muted fw-600" style="font-size: 0.75rem; text-transform: uppercase;">Closed</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filter Links -->
    <div class="card">
        <div class="card-body flex gap-4 flex-wrap">
            <a href="tickets.php" class="btn <?= $status_filter=='' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">All (<?= $total_tickets ?>)</a>
            <a href="tickets.php?status=Open" class="btn <?= $status_filter=='Open' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Open (<?= $open_tickets ?>)</a>
            <a href="tickets.php?status=In Progress" class="btn <?= $status_filter=='In Progress' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">In Progress (<?= $inprogress_tickets ?>)</a>
            <a href="tickets.php?status=Closed" class="btn <?= $status_filter=='Closed' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Closed (<?= $closed_tickets ?>)</a>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Subject</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($tickets && $tickets->num_rows > 0): ?>
                        <?php while ($ticket = $tickets->fetch_assoc()): 
                            $status_class = 'badge-info';
                            if($ticket['status'] == 'Open') $status_class = 'badge-danger';
                            if($ticket['status'] == 'In Progress') $status_class = 'badge-warning';
                            if($ticket['status'] == 'Closed') $status_class = 'badge-success';
                            
                            $priority_class = 'badge-info';
                            if($ticket['priority'] == 'Critical' || $ticket['priority'] == 'High') $priority_class = 'badge-danger';
                            if($ticket['priority'] == 'Medium') $priority_class = 'badge-warning';
                            if($ticket['priority'] == 'Low') $priority_class = 'badge-success';
                        ?>
                        <tr>
                            <td class="fw-600">#<?= $ticket['id'] ?></td>
                            <td>
                                <div class="fw-600"><?= htmlspecialchars($ticket['full_name'] ?: $ticket['username'] ?: 'N/A') ?></div>
                                <?php if($ticket['username']): ?>
                                <div class="text-muted" style="font-size: 0.75rem;">@<?= $ticket['username'] ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-600" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= htmlspecialchars($ticket['subject']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $priority_class ?>"><?= $ticket['priority'] ?></span>
                            </td>
                            <td>
                                <span class="badge <?= $status_class ?>"><?= $ticket['status'] ?></span>
                            </td>
                            <td>
                                <div class="text-muted" style="font-size: 0.875rem;"><?= date('M d, Y', strtotime($ticket['created_at'])) ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;"><?= date('H:i', strtotime($ticket['created_at'])) ?></div>
                            </td>
                            <td style="text-align: right;">
                                <div class="flex gap-2 justify-end">
                                    <a href="ticket_view.php?id=<?= $ticket['id'] ?>" class="btn btn-secondary btn-sm" title="View" style="padding: 0.4rem; width: 32px;">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="?delete=<?= $ticket['id'] ?>" class="btn btn-secondary btn-sm" title="Delete" style="padding: 0.4rem; width: 32px; color: var(--danger);" onclick="return confirm('Delete this ticket?')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align: center; padding: 4rem; color: var(--text-muted);">No tickets found matching your criteria.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>
