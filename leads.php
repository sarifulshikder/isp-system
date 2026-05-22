<?php
session_start();
$page_title = "Leads Management";
$base_path = '';

include_once 'config.php';
include_once 'includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $lead_id = intval($_POST['lead_id'] ?? 0);
    
    if ($_POST['action'] == 'add_lead') {
        $name = $conn->real_escape_string($_POST['name']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $email = $conn->real_escape_string($_POST['email']);
        $company = $conn->real_escape_string($_POST['company']);
        $address = $conn->real_escape_string($_POST['address']);
        $plan_interested = $conn->real_escape_string($_POST['plan_interested']);
        $source = $conn->real_escape_string($_POST['source']);
        $notes = $conn->real_escape_string($_POST['notes']);
        
        $conn->query("INSERT INTO leads (name, phone, email, company, address, plan_interested, source, status, created_by) 
                      VALUES ('$name', '$phone', '$email', '$company', '$address', '$plan_interested', '$source', 'new', {$_SESSION['user_id']})");
        $message = 'Lead added successfully';
    }
    
    if ($_POST['action'] == 'update_status') {
        $status = $conn->real_escape_string($_POST['status']);
        $conn->query("UPDATE leads SET status = '$status', updated_at = NOW() WHERE id = $lead_id");
        $message = 'Lead status updated';
    }
    
    if ($_POST['action'] == 'delete_lead') {
        $conn->query("DELETE FROM leads WHERE id = $lead_id");
        $message = 'Lead deleted';
    }
    
    if ($_POST['action'] == 'convert_lead') {
        $lead = $conn->query("SELECT * FROM leads WHERE id = $lead_id")->fetch_assoc();
        if ($lead) {
            $conn->query("INSERT INTO customers (username, full_name, phone, email, address, created_at) 
                          VALUES ('" . strtolower(str_replace(' ', '', $lead['name'])) . "', '{$lead['name']}', '{$lead['phone']}', '{$lead['email']}', '{$lead['address']}', NOW())");
            $conn->query("UPDATE leads SET status = 'converted', updated_at = NOW() WHERE id = $lead_id");
            $message = 'Lead converted to customer!';
        }
    }
}

$filter_status = $_GET['status'] ?? '';
$where = $filter_status ? "WHERE status = '$filter_status'" : "";

$leads = $conn->query("SELECT * FROM leads $where ORDER BY created_at DESC");

$stats = [
    'total' => $conn->query("SELECT COUNT(*) as c FROM leads")->fetch_assoc()['c'],
    'new' => $conn->query("SELECT COUNT(*) as c FROM leads WHERE status = 'new'")->fetch_assoc()['c'],
    'qualified' => $conn->query("SELECT COUNT(*) as c FROM leads WHERE status = 'qualified'")->fetch_assoc()['c'],
    'converted' => $conn->query("SELECT COUNT(*) as c FROM leads WHERE status = 'converted'")->fetch_assoc()['c'],
];

$plans_res = $conn->query("SELECT * FROM plans ORDER BY name");

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>

<div class="animate-fade-in">
    
    <?php if ($message): ?>
    <div class="badge badge-success mb-4 w-full justify-center" style="padding: 1rem; border-radius: var(--radius);">
        <i class="fa fa-check-circle mr-2"></i> <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>
    
    <div class="flex-between mb-4 flex-wrap gap-4">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">Sales Leads Management</h1>
            <p class="text-muted" style="font-size: 0.875rem;">Track and convert potential customers into active users</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeadModal">
            <i class="fa fa-plus"></i> New Lead
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body flex items-center gap-4">
                <div style="width: 48px; height: 48px; background: var(--primary-soft); color: var(--primary); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fa fa-users"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?= $stats['total'] ?></div>
                    <div class="text-muted fw-600" style="font-size: 0.75rem; text-transform: uppercase;">Total Leads</div>
                </div>
            </div>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body flex items-center gap-4">
                <div style="width: 48px; height: 48px; background: var(--info-soft); color: var(--info); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fa fa-star"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?= $stats['new'] ?></div>
                    <div class="text-muted fw-600" style="font-size: 0.75rem; text-transform: uppercase;">New Leads</div>
                </div>
            </div>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body flex items-center gap-4">
                <div style="width: 48px; height: 48px; background: var(--warning-soft); color: var(--warning); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fa fa-check-circle"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?= $stats['qualified'] ?></div>
                    <div class="text-muted fw-600" style="font-size: 0.75rem; text-transform: uppercase;">Qualified</div>
                </div>
            </div>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body flex items-center gap-4">
                <div style="width: 48px; height: 48px; background: var(--success-soft); color: var(--success); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fa fa-trophy"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?= $stats['converted'] ?></div>
                    <div class="text-muted fw-600" style="font-size: 0.75rem; text-transform: uppercase;">Converted</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filter Links -->
    <div class="card">
        <div class="card-body flex gap-2 flex-wrap">
            <a href="leads.php" class="btn <?= !$filter_status ? 'btn-primary' : 'btn-secondary' ?> btn-sm">All (<?= $stats['total'] ?>)</a>
            <a href="leads.php?status=new" class="btn <?= $filter_status == 'new' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">New</a>
            <a href="leads.php?status=qualified" class="btn <?= $filter_status == 'qualified' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Qualified</a>
            <a href="leads.php?status=converted" class="btn <?= $filter_status == 'converted' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Converted</a>
            <a href="leads.php?status=lost" class="btn <?= $filter_status == 'lost' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Lost</a>
        </div>
    </div>

    <!-- Leads Table -->
    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Lead Info</th>
                        <th>Interest</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($leads && $leads->num_rows > 0): ?>
                        <?php while ($lead = $leads->fetch_assoc()): 
                            $status_class = 'badge-info';
                            if($lead['status'] == 'new') $status_class = 'badge-info';
                            if($lead['status'] == 'qualified') $status_class = 'badge-warning';
                            if($lead['status'] == 'converted') $status_class = 'badge-success';
                            if($lead['status'] == 'lost') $status_class = 'badge-danger';
                        ?>
                        <tr>
                            <td>
                                <div class="fw-600"><?= htmlspecialchars($lead['name']) ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($lead['company'] ?: 'Personal Lead') ?></div>
                            </td>
                            <td>
                                <div class="fw-600"><?= htmlspecialchars($lead['plan_interested'] ?: 'Not Specified') ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;"><span class="text-muted">Source:</span> <?= ucfirst($lead['source']) ?></div>
                            </td>
                            <td>
                                <div style="font-size: 0.875rem;"><i class="fa fa-phone text-muted" style="width: 14px;"></i> <?= $lead['phone'] ?: '-' ?></div>
                                <div style="font-size: 0.875rem;"><i class="fa fa-envelope text-muted" style="width: 14px;"></i> <?= $lead['email'] ?: '-' ?></div>
                            </td>
                            <td>
                                <span class="badge <?= $status_class ?>"><?= ucfirst($lead['status']) ?></span>
                            </td>
                            <td>
                                <div class="text-muted" style="font-size: 0.875rem;"><?= date('M d, Y', strtotime($lead['created_at'])) ?></div>
                            </td>
                            <td style="text-align: right;">
                                <div class="flex gap-2 justify-end">
                                    <button class="btn btn-secondary btn-sm" onclick="viewLead(<?= $lead['id'] ?>)" title="View" style="padding: 0.4rem; width: 32px;"><i class="fa fa-eye"></i></button>
                                    <?php if ($lead['status'] != 'converted'): ?>
                                        <button class="btn btn-secondary btn-sm" onclick="convertLead(<?= $lead['id'] ?>)" title="Convert to Customer" style="padding: 0.4rem; width: 32px; color: var(--success);"><i class="fa fa-user-check"></i></button>
                                    <?php endif; ?>
                                    <button class="btn btn-secondary btn-sm" onclick="deleteLead(<?= $lead['id'] ?>)" title="Delete" style="padding: 0.4rem; width: 32px; color: var(--danger);"><i class="fa fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; padding: 4rem; color: var(--text-muted);">No leads found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Lead Modal -->
<div class="modal-overlay" id="addLeadModalOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
    <div class="modal card" style="max-width: 600px; width: 95%; margin-bottom: 0;">
        <div class="card-header flex-between">
            <h3 class="card-title"><i class="fa fa-plus-circle text-primary"></i> Add New Sales Lead</h3>
            <button class="modal-close" data-bs-dismiss="modal" style="border:none; background:transparent; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <form method="POST">
            <div class="card-body">
                <input type="hidden" name="action" value="add_lead">
                <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">Customer Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="Full name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="98XXXXXXXX">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="mail@example.com">
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">Company / Organization</label>
                        <input type="text" name="company" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Interested Plan</label>
                        <select name="plan_interested" class="form-control">
                            <option value="">Select Plan</option>
                            <?php while ($p = $plans_res->fetch_assoc()): ?>
                                <option value="<?= $p['name'] ?>"><?= $p['name'] ?> - Rs.<?= $p['price'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lead Source</label>
                        <select name="source" class="form-control">
                            <option value="website">Website</option>
                            <option value="facebook">Facebook</option>
                            <option value="referral">Referral</option>
                            <option value="call">Phone Call</option>
                            <option value="visit">Field Visit</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">Notes / Requirements</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="card-body" style="border-top:1px solid var(--border); background: var(--bg-soft);">
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary w-full">Save Lead</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function viewLead(id) { alert('View lead #' + id); }
    function convertLead(id) {
        if (confirm('Convert this lead to customer?')) {
            submitAction('convert_lead', id);
        }
    }
    function deleteLead(id) {
        if (confirm('Are you sure you want to delete this lead?')) {
            submitAction('delete_lead', id);
        }
    }
    function submitAction(action, id) {
        let form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="action" value="${action}"><input type="hidden" name="lead_id" value="${id}">`;
        document.body.appendChild(form);
        form.submit();
    }
    
    // Simple Modal Trigger Fix
    document.querySelectorAll('[data-bs-target="#addLeadModal"]').forEach(el => {
        el.onclick = () => {
            document.getElementById('addLeadModalOverlay').style.display = 'flex';
        };
    });
    document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(el => {
        el.onclick = () => {
            document.getElementById('addLeadModalOverlay').style.display = 'none';
        };
    });
</script>

<?php include 'includes/footer.php'; ?>
