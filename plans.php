<?php
$base_path = './';
include 'config.php';
include 'includes/auth.php';

// Temporary Schema Migration for FUP Tier 3
$check_fup3 = $conn->query("SHOW COLUMNS FROM plans LIKE 'fup3_speed'");
if ($check_fup3->num_rows == 0) {
    $conn->query("ALTER TABLE plans ADD COLUMN fup3_speed VARCHAR(50) DEFAULT NULL AFTER fup2_limit");
    $conn->query("ALTER TABLE plans ADD COLUMN fup3_limit INT DEFAULT NULL AFTER fup3_speed");
}

/* Page info */
$page_title = "Service Packages & Plans";
$active = "plans";

/* Plan Form Template */
/* Plan Form Template */
function renderPlanForm($id_prefix, $isEdit = false) { ?>
    <form method="post">
        <?php if($isEdit) echo '<input type="hidden" name="id" id="edit_id">'; ?>
        <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
            <div class="form-group">
                <label class="form-label">Package / Plan Name</label>
                <input class="form-control" name="name" id="<?= $id_prefix ?>_name" placeholder="e.g. Ultra Fiber 100M" required>
            </div>
            <div class="form-group">
                <label class="form-label">Base Speed</label>
                <input class="form-control" name="speed" id="<?= $id_prefix ?>_speed" placeholder="e.g. 100M/100M" required>
            </div>
            <div class="form-group">
                <label class="form-label">Price (NPR)</label>
                <input type="number" step="0.01" class="form-control" name="price" id="<?= $id_prefix ?>_price" required>
            </div>
            <div class="form-group">
                <label class="form-label">Validity (Days)</label>
                <input type="number" class="form-control" name="validity" id="<?= $id_prefix ?>_validity" value="30" required>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Total Data Quota (GB)</label>
                <input type="number" class="form-control" name="data_limit" id="<?= $id_prefix ?>_data_limit" value="1000">
            </div>
        </div>

        <div style="background: var(--bg-soft); padding: 1.25rem; border-radius: var(--radius); margin-top: 1rem; border: 1px solid var(--border);">
            <span class="text-muted fw-600 mb-3 block" style="font-size: 0.75rem; text-transform: uppercase;"><i class="fa fa-bolt"></i> Fair Usage Policy (FUP) Tiers</span>
            
            <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group mb-0">
                    <label class="form-label" style="font-size: 0.75rem;">Tier 1 Limit (GB)</label>
                    <input type="number" class="form-control" name="fup1_limit" id="<?= $id_prefix ?>_fup1_limit">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label" style="font-size: 0.75rem;">Tier 1 Speed</label>
                    <input class="form-control" name="fup1_speed" id="<?= $id_prefix ?>_fup1_speed" placeholder="e.g. 50M">
                </div>
            </div>

            <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group mb-0">
                    <label class="form-label" style="font-size: 0.75rem;">Tier 2 Limit (GB)</label>
                    <input type="number" class="form-control" name="fup2_limit" id="<?= $id_prefix ?>_fup2_limit">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label" style="font-size: 0.75rem;">Tier 2 Speed</label>
                    <input class="form-control" name="fup2_speed" id="<?= $id_prefix ?>_fup2_speed" placeholder="e.g. 20M">
                </div>
            </div>

            <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group mb-0">
                    <label class="form-label" style="font-size: 0.75rem;">Tier 3 Limit (GB)</label>
                    <input type="number" class="form-control" name="fup3_limit" id="<?= $id_prefix ?>_fup3_limit">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label" style="font-size: 0.75rem;">Tier 3 Speed</label>
                    <input class="form-control" name="fup3_speed" id="<?= $id_prefix ?>_fup3_speed" placeholder="e.g. 10M">
                </div>
            </div>
        </div>

        <button type="submit" name="<?= $isEdit?'edit':'add' ?>" class="btn btn-primary w-full mt-4" style="padding: 1rem;">
            <i class="fa fa-save"></i> <?= $isEdit?'Update':'Create' ?> Package
        </button>
    </form>
<?php }

/* Handle Add Plan */
if(isset($_POST['add'])){
    $name       = $conn->real_escape_string($_POST['name']);
    $speed      = $conn->real_escape_string($_POST['speed']);
    $price      = $conn->real_escape_string($_POST['price']);
    $validity   = $conn->real_escape_string($_POST['validity']);
    $data_limit = (float)$_POST['data_limit'] * 1073741824;
    
    $fup1_limit = (float)$_POST['fup1_limit'] * 1073741824;
    $fup1_speed = $conn->real_escape_string($_POST['fup1_speed']);
    $fup2_limit = (float)$_POST['fup2_limit'] * 1073741824;
    $fup2_speed = $conn->real_escape_string($_POST['fup2_speed']);
    $fup3_limit = (float)($_POST['fup3_limit']??0) * 1073741824;
    $fup3_speed = $conn->real_escape_string($_POST['fup3_speed']??'');

    $conn->query("
        INSERT INTO plans (name, speed, price, validity, data_limit, fup1_limit, fup1_speed, fup2_limit, fup2_speed, fup3_limit, fup3_speed)
        VALUES ('$name', '$speed', '$price', '$validity', '$data_limit', '$fup1_limit', '$fup1_speed', '$fup2_limit', '$fup2_speed', '$fup3_limit', '$fup3_speed')
    ");
    header("Location: plans.php?msg=added");
    exit;
}

/* Handle Delete Plan */
if(isset($_GET['del'])){
    $id = (int)$_GET['del'];
    $conn->query("DELETE FROM plans WHERE id='$id'");
    header("Location: plans.php?msg=deleted");
    exit;
}

/* Handle Edit Plan */
if(isset($_POST['edit'])){
    $id         = (int)$_POST['id'];
    $name       = $conn->real_escape_string($_POST['name']);
    $speed      = $conn->real_escape_string($_POST['speed']);
    $price      = $conn->real_escape_string($_POST['price']);
    $validity   = $conn->real_escape_string($_POST['validity']);
    $data_limit = (float)$_POST['data_limit'] * 1073741824;
    
    $fup1_limit = (float)$_POST['fup1_limit'] * 1073741824;
    $fup1_speed = $conn->real_escape_string($_POST['fup1_speed']);
    $fup2_limit = (float)$_POST['fup2_limit'] * 1073741824;
    $fup2_speed = $conn->real_escape_string($_POST['fup2_speed']);
    $fup3_limit = (float)($_POST['fup3_limit']??0) * 1073741824;
    $fup3_speed = $conn->real_escape_string($_POST['fup3_speed']??'');

    $conn->query("
        UPDATE plans
        SET name='$name', speed='$speed', price='$price', validity='$validity', data_limit='$data_limit',
            fup1_limit='$fup1_limit', fup1_speed='$fup1_speed',
            fup2_limit='$fup2_limit', fup2_speed='$fup2_speed',
            fup3_limit='$fup3_limit', fup3_speed='$fup3_speed'
        WHERE id='$id'
    ");
    header("Location: plans.php?msg=updated");
    exit;
}

$plans_res = $conn->query("SELECT * FROM plans ORDER BY id DESC");

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>

<div class="animate-fade-in">
    
    <div class="flex-between mb-4 flex-wrap gap-4">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">Service Packages & Plans</h1>
            <p class="text-muted" style="font-size: 0.875rem;">Manage bandwidth packages, pricing, and FUP policies</p>
        </div>
        <div class="flex gap-2">
            <div class="btn-group" style="display: flex; background: var(--bg-soft); padding: 0.25rem; border-radius: var(--radius);">
                <button onclick="showPlanTab('list')" class="btn btn-secondary btn-sm active" id="tab-btn-list" style="border:none;">Package List</button>
                <button onclick="showPlanTab('fuptest')" class="btn btn-secondary btn-sm" id="tab-btn-fuptest" style="border:none;">FUP Test</button>
            </div>
            <button onclick="openModal('addPlanModal')" class="btn btn-primary"><i class="fa fa-plus-circle"></i> Create Package</button>
        </div>
    </div>

    <div id="plan-list-tab" class="plan-tab-content">
        <div class="card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Package Info</th>
                            <th>Base Speed</th>
                            <th>FUP Policies</th>
                            <th>Price</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($plans_res && $plans_res->num_rows > 0): ?>
                            <?php while($p = $plans_res->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="fw-600"><?= htmlspecialchars($p['name']) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;"><?= $p['validity'] ?> Days Validity</div>
                                </td>
                                <td><span class="badge badge-info"><?= htmlspecialchars($p['speed']) ?></span></td>
                                <td>
                                    <div style="font-size: 0.75rem; line-height: 1.6;">
                                        <?php if($p['fup1_limit'] > 0): ?>
                                            <div><span class="text-muted">T1:</span> <?= round($p['fup1_limit']/1073741824) ?>GB &rarr; <b><?= $p['fup1_speed'] ?></b></div>
                                        <?php endif; ?>
                                        <?php if($p['fup2_limit'] > 0): ?>
                                            <div><span class="text-muted">T2:</span> <?= round($p['fup2_limit']/1073741824) ?>GB &rarr; <b><?= $p['fup2_speed'] ?></b></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><span class="badge badge-success">NPR <?= number_format($p['price']) ?></span></td>
                                <td style="text-align: right;">
                                    <div class="flex gap-2 justify-end">
                                        <button onclick='openEditModal(<?= json_encode($p) ?>)' class="btn btn-secondary btn-sm" title="Edit" style="padding: 0.4rem; width: 32px; color: var(--success);">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <a href="?del=<?= $p['id'] ?>" class="btn btn-secondary btn-sm" title="Delete" style="padding: 0.4rem; width: 32px; color: var(--danger);" onclick="return confirm('Delete plan?')">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; padding: 4rem; color: var(--text-muted);">No service packages configured.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="plan-fuptest-tab" class="plan-tab-content" style="display:none;">
        <div class="card" style="height: 700px; padding: 0; overflow: hidden;">
            <iframe src="fup_test.php" style="width:100%; height:100%; border:none;"></iframe>
        </div>
    </div>
</div>

<!-- Modals -->
<div id="addPlanModal" class="modal-overlay" onclick="closeModal('addPlanModal')">
    <div class="modal" onclick="event.stopPropagation()" style="max-width: 700px;">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa fa-plus-circle text-primary"></i> Create New Package</h3>
            <button class="modal-close" onclick="closeModal('addPlanModal')">&times;</button>
        </div>
        <div class="modal-body"><?php renderPlanForm('add', false); ?></div>
    </div>
</div>

<div id="editPlanModal" class="modal-overlay" onclick="closeModal('editPlanModal')">
    <div class="modal" onclick="event.stopPropagation()" style="max-width: 700px;">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa fa-edit text-primary"></i> Edit Package Configuration</h3>
            <button class="modal-close" onclick="closeModal('editPlanModal')">&times;</button>
        </div>
        <div class="modal-body"><?php renderPlanForm('edit', true); ?></div>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
    document.body.style.overflow = 'auto';
}

function showPlanTab(tab) {
    document.querySelectorAll('.plan-tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.btn-group .btn').forEach(el => el.classList.remove('active', 'btn-primary'));
    document.querySelectorAll('.btn-group .btn').forEach(el => el.classList.add('btn-secondary'));
    
    document.getElementById('plan-' + tab + '-tab').style.display = 'block';
    const btn = document.getElementById('tab-btn-' + tab);
    btn.classList.remove('btn-secondary');
    btn.classList.add('active', 'btn-primary');
}

function openEditModal(p) {
    document.getElementById('edit_id').value = p.id;
    document.getElementById('edit_name').value = p.name;
    document.getElementById('edit_speed').value = p.speed;
    document.getElementById('edit_price').value = p.price;
    document.getElementById('edit_validity').value = p.validity;
    document.getElementById('edit_data_limit').value = p.data_limit / 1073741824;
    document.getElementById('edit_fup1_limit').value = p.fup1_limit / 1073741824;
    document.getElementById('edit_fup1_speed').value = p.fup1_speed;
    document.getElementById('edit_fup2_limit').value = p.fup2_limit / 1073741824;
    document.getElementById('edit_fup2_speed').value = p.fup2_speed;
    document.getElementById('edit_fup3_limit').value = p.fup3_limit / 1073741824;
    document.getElementById('edit_fup3_speed').value = p.fup3_speed;
    openModal('editPlanModal');
}
</script>

<style>
/* Local overrides for plan tabs if needed */
.plan-tab-content { animation: fadeIn 0.3s ease-out; }
</style>

<?php include 'includes/footer.php'; ?>
