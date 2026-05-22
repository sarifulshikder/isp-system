<?php
include 'config.php';
include 'includes/auth.php';

$page_title = "Stock Management Dashboard";
$active = "inventory";

// 1. Add Stock Logic
if (isset($_POST['add_stock'])) {
    $name = $_POST['item_name'];
    $brand = $_POST['brand'];
    $sn = $_POST['serial_number'];
    $mac = $_POST['mac_address'];
    $stmt = $conn->prepare("INSERT INTO inventory_items (item_name, brand, serial_number, mac_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $brand, $sn, $mac);
    $stmt->execute();
}

// 2. Issue Stock Logic
if (isset($_POST['issue_stock'])) {
    $id = $_POST['item_id'];
    $user = $_POST['customer_user'];
    $stmt = $conn->prepare("UPDATE inventory_items SET status='issued', issued_to_user=? WHERE id=?");
    $stmt->bind_param("si", $user, $id);
    $stmt->execute();
    $conn->query("UPDATE customers SET onu_mac = (SELECT mac_address FROM inventory_items WHERE id=$id) WHERE username='$user'");
}

// 3. Handle Item Return
if (isset($_GET['return'])) {
    $id = intval($_GET['return']);
    $conn->query("UPDATE inventory_items SET status='in_stock', issued_to_user=NULL WHERE id=$id");
    header("Location: inventory.php?msg=returned");
    exit;
}

// 4. Handle Mark Faulty
if (isset($_GET['faulty'])) {
    $id = intval($_GET['faulty']);
    $conn->query("UPDATE inventory_items SET status='faulty' WHERE id=$id");
    header("Location: inventory.php?msg=faulty");
    exit;
}

// Fetch Data for Charts & Table
$type_stats = $conn->query("SELECT item_name, COUNT(*) as count FROM inventory_items GROUP BY item_name")->fetch_all(MYSQLI_ASSOC);
$status_stats = $conn->query("SELECT status, COUNT(*) as count FROM inventory_items GROUP BY status")->fetch_all(MYSQLI_ASSOC);
$recent = $conn->query("SELECT * FROM inventory_items WHERE status != 'in_stock' ORDER BY created_at DESC LIMIT 5");
$items = $conn->query("SELECT * FROM inventory_items ORDER BY status ASC, created_at DESC");

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>

<div class="animate-fade-in">
    
    <div class="flex-between mb-4 flex-wrap gap-4">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">Inventory & Stock Management</h1>
            <p class="text-muted" style="font-size: 0.875rem;">Track hardware assets, assign devices, and manage warehouse health</p>
        </div>
        <button onclick="openModal('addStockModal')" class="btn btn-primary">
            <i class="fa fa-plus-circle"></i> Register New Stock
        </button>
    </div>

    <!-- Analytics Row -->
    <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <h4 class="card-title text-muted" style="font-size: 0.75rem; text-transform: uppercase;">Stock Distribution</h4>
            </div>
            <div class="card-body">
                <div style="height: 200px;"><canvas id="typeChart"></canvas></div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 0; background: var(--bg-sidebar); border-color: rgba(255,255,255,0.1);">
            <div class="card-header" style="background: transparent; border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h4 class="card-title text-light" style="font-size: 0.75rem; text-transform: uppercase;">Warehouse Health</h4>
            </div>
            <div class="card-body">
                <?php foreach($status_stats as $s): 
                    $badge_class = ($s['status'] == 'in_stock') ? 'badge-success' : (($s['status'] == 'issued') ? 'badge-info' : 'badge-danger');
                ?>
                    <div class="flex-between mb-3" style="padding-bottom: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <span class="text-light" style="font-size: 0.875rem;"><?= ucfirst(str_replace('_', ' ', $s['status'])) ?></span>
                        <span class="badge <?= $badge_class ?>"><?= $s['count'] ?> Items</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <h4 class="card-title text-muted" style="font-size: 0.75rem; text-transform: uppercase;">Recent Assignments</h4>
            </div>
            <div class="card-body p-0">
                <?php while($r = $recent->fetch_assoc()): ?>
                    <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); border-left: 4px solid var(--primary);">
                        <div class="fw-600" style="font-size: 0.875rem;"><?= $r['item_name'] ?></div>
                        <div class="text-muted" style="font-size: 0.75rem;">Assigned to: <span class="fw-600"><?= $r['issued_to_user'] ?></span></div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Hardware Info</th>
                        <th>Serial / MAC</th>
                        <th>Status</th>
                        <th>Current Location</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($items && $items->num_rows > 0): ?>
                        <?php while($i = $items->fetch_assoc()): 
                            $status_class = ($i['status'] == 'in_stock') ? 'badge-success' : (($i['status'] == 'issued') ? 'badge-info' : 'badge-danger');
                        ?>
                        <tr>
                            <td>
                                <div class="fw-600"><?= htmlspecialchars($i['item_name']) ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($i['brand']) ?></div>
                            </td>
                            <td>
                                <div style="font-size: 0.8125rem;"><span class="text-muted">SN:</span> <?= htmlspecialchars($i['serial_number']) ?></div>
                                <div style="font-size: 0.75rem;"><span class="text-muted">MAC:</span> <?= htmlspecialchars($i['mac_address'] ?: 'N/A') ?></div>
                            </td>
                            <td><span class="badge <?= $status_class ?>"><?= strtoupper(str_replace('_', ' ', $i['status'])) ?></span></td>
                            <td>
                                <div class="fw-600" style="font-size: 0.875rem;"><?= $i['issued_to_user'] ? '<i class="fa fa-user text-muted mr-1"></i> ' . $i['issued_to_user'] : '<i class="fa fa-warehouse text-muted mr-1"></i> Main Warehouse' ?></div>
                            </td>
                            <td style="text-align: right;">
                                <div class="flex gap-2 justify-end">
                                    <?php if($i['status'] == 'in_stock'): ?>
                                        <button onclick="openIssueModal(<?= $i['id'] ?>, '<?= addslashes($i['item_name']) ?>')" class="btn btn-primary btn-sm" style="font-size: 10px; padding: 0.25rem 0.6rem;">Issue</button>
                                    <?php else: ?>
                                        <a href="?return=<?= $i['id'] ?>" class="btn btn-secondary btn-sm" title="Return to Stock" style="padding: 0.4rem; width: 32px;"><i class="fa fa-rotate-left"></i></a>
                                    <?php endif; ?>
                                    <a href="?faulty=<?= $i['id'] ?>" class="btn btn-secondary btn-sm" title="Mark as Faulty" style="padding: 0.4rem; width: 32px; color: var(--danger);"><i class="fa fa-triangle-exclamation"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; padding: 4rem; color: var(--text-muted);">Warehouse is currently empty.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Stock -->
<div class="modal-overlay" id="addStockModal" onclick="closeModal('addStockModal')">
    <div class="modal card" style="max-width: 450px;" onclick="event.stopPropagation()">
        <div class="card-header flex-between">
            <h3 class="card-title"><i class="fa fa-plus-circle text-primary"></i> Register Hardware</h3>
            <button class="modal-close" onclick="closeModal('addStockModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Item Category</label>
                    <select name="item_name" class="form-control" required>
                        <option value="XPON ONU">XPON ONU</option>
                        <option value="WiFi 6 Router">WiFi 6 Router</option>
                        <option value="Dual Band Router">Dual Band Router</option>
                        <option value="Fiber Roll">Fiber Roll (1KM)</option>
                        <option value="SFP Module">SFP Module</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Brand / Manufacturer</label>
                    <input type="text" name="brand" class="form-control" placeholder="E.g. Huawei, Nokia, Syrotech">
                </div>
                <div class="form-group">
                    <label class="form-label">Serial Number (SN)</label>
                    <input type="text" name="serial_number" class="form-control" placeholder="Unique device serial" required>
                </div>
                <div class="form-group">
                    <label class="form-label">MAC Address</label>
                    <input type="text" name="mac_address" class="form-control" placeholder="AA:BB:CC:DD:EE:FF">
                </div>
            </div>
            <div class="card-body" style="border-top: 1px solid var(--border); background: var(--bg-soft);">
                <div class="flex gap-2">
                    <button type="submit" name="add_stock" class="btn btn-primary w-full">Save to Warehouse</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addStockModal')">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Issue Stock -->
<div class="modal-overlay" id="issueModal" onclick="closeModal('issueModal')">
    <div class="modal card" style="max-width: 450px;" onclick="event.stopPropagation()">
        <div class="card-header flex-between">
            <h3 class="card-title"><i class="fa fa-user-check text-primary"></i> Issue Hardware</h3>
            <button class="modal-close" onclick="closeModal('issueModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="card-body">
                <div class="badge badge-info mb-4 w-full" id="issueItemNameBadge" style="justify-content: center; padding: 0.75rem;"></div>
                <input type="hidden" name="item_id" id="issueItemId">
                <div class="form-group">
                    <label class="form-label">Customer Username</label>
                    <input type="text" name="customer_user" class="form-control" placeholder="Enter target username" required autofocus>
                    <small class="text-muted mt-2 block">Device will be automatically linked to this customer profile.</small>
                </div>
            </div>
            <div class="card-body" style="border-top: 1px solid var(--border); background: var(--bg-soft);">
                <div class="flex gap-2">
                    <button type="submit" name="issue_stock" class="btn btn-primary w-full">Complete Assignment</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('issueModal')">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function openModal(id) {
        document.getElementById(id).classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('show');
        document.body.style.overflow = 'auto';
    }

    new Chart(document.getElementById('typeChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($type_stats, 'item_name')) ?>,
            datasets: [{ 
                data: <?= json_encode(array_column($type_stats, 'count')) ?>, 
                backgroundColor: '#4f46e5', 
                borderRadius: 8,
                barThickness: 30
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });

    function openIssueModal(id, name) {
        document.getElementById('issueItemId').value = id;
        document.getElementById('issueItemNameBadge').innerText = 'Issuing: ' + name;
        openModal('issueModal');
    }
</script>
<?php include 'includes/footer.php'; ?>
