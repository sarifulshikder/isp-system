<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include 'config.php';
include 'includes/auth.php';

$page_title = "Network Devices";
$active = "nas";

/* ADD NETWORK DEVICE */
if(isset($_POST['add'])){
    $nasname = $_POST['nasname'] ?? '';
    $shortname = $_POST['shortname'] ?? $nasname;
    $secret = $_POST['secret'] ?? 'secret';
    $ports = ($_POST['ports'] ?? '') ?: 1812;
    $ip_address = $_POST['ip_address'] ?? '';
    $api_user = $_POST['api_user'] ?? '';
    $api_pass = $_POST['api_pass'] ?? '';
    $api_port = ($_POST['api_port'] ?? '') ?: 8728;
    $device_type = $_POST['device_type'] ?? 'mikrotik';
    $snmp_community = $_POST['snmp_community'] ?? 'public';
    $snmp_version = $_POST['snmp_version'] ?? '2c';
    $snmp_port = intval(($_POST['snmp_port'] ?? '') ?: 161);
    $brand = $_POST['brand'] ?? 'generic';
    $pon_ports = intval(($_POST['pon_ports'] ?? '') ?: 8);
    
    $stmt = $conn->prepare("
        INSERT INTO nas
        (nasname, shortname, type, secret, ports, server, ip_address, api_user, api_pass, api_port, status, device_type, snmp_community, snmp_version, snmp_port, brand, pon_ports)
        VALUES (?, ?, 'mikrotik', ?, ?, 'mikrotik', ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssisssisssisi", $nasname, $shortname, $secret, $ports, $ip_address, $api_user, $api_pass, $api_port, $device_type, $snmp_community, $snmp_version, $snmp_port, $brand, $pon_ports);
    $stmt->execute();
}

/* DELETE DEVICE */
if(isset($_GET['del'])){
    $id = intval($_GET['del']);
    $conn->query("DELETE FROM nas WHERE id=$id");
}

/* FETCH DEVICES */
$bng = $conn->query("SELECT * FROM nas ORDER BY id DESC");

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>

<div class="animate-fade-in">
    
    <div class="flex-between mb-4">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">Network Infrastructure</h1>
            <p class="text-muted" style="font-size: 0.875rem;">Manage core routers, OLTs, and managed switches</p>
        </div>
        <button id="toggleAddForm" class="btn btn-primary">
            <i class="fa fa-plus"></i> Add New Device
        </button>
    </div>

    <!-- Add Form (Hidden by default) -->
    <div id="addPlanFormContainer" style="display:none;" class="mb-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-plus text-primary"></i> Register New Network Device</h3>
            </div>
            <form method="post">
                <div class="card-body">
                    <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label">Device Name / NASNAME</label>
                            <input type="text" name="nasname" class="form-control" placeholder="E.g. Core-Router-01" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Device Type</label>
                            <select name="device_type" class="form-control">
                                <option value="mikrotik">MikroTik Router</option>
                                <option value="olt">OLT (GPON/EPON)</option>
                                <option value="switch">Managed Switch</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">IP Address</label>
                            <input type="text" name="ip_address" class="form-control" placeholder="192.168.88.1" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">RADIUS Secret</label>
                            <input type="text" name="secret" class="form-control" placeholder="Shared secret for RADIUS">
                        </div>
                        
                        <!-- SNMP Settings -->
                        <div class="form-group">
                            <label class="form-label">SNMP Community</label>
                            <input type="text" name="snmp_community" class="form-control" value="public">
                        </div>
                        <div class="form-group">
                            <label class="form-label">SNMP Version</label>
                            <select name="snmp_version" class="form-control">
                                <option value="1">v1</option>
                                <option value="2c" selected>v2c</option>
                                <option value="3">v3</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">SNMP Port</label>
                            <input type="number" name="snmp_port" class="form-control" value="161">
                        </div>

                        <!-- API Settings -->
                        <div class="form-group">
                            <label class="form-label">API User</label>
                            <input type="text" name="api_user" class="form-control" placeholder="Admin/API user">
                        </div>
                        <div class="form-group">
                            <label class="form-label">API Password</label>
                            <input type="password" name="api_pass" class="form-control" placeholder="API password">
                        </div>
                        <div class="form-group">
                            <label class="form-label">OLT Brand</label>
                            <select name="brand" class="form-control">
                                <option value="generic">Generic / Default</option>
                                <option value="bdcom">BDCOM</option>
                                <option value="huawei">Huawei</option>
                                <option value="zte">ZTE</option>
                                <option value="vsol">VSOL</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">PON Ports</label>
                            <input type="number" name="pon_ports" class="form-control" value="8">
                        </div>
                    </div>
                </div>
                <div class="card-body" style="border-top: 1px solid var(--border); background: var(--bg-soft);">
                    <div class="flex gap-2">
                        <button type="submit" name="add" class="btn btn-primary">
                            <i class="fa fa-save"></i> Save Device
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('addPlanFormContainer').style.display='none'">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- NAS Table -->
    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Device Info</th>
                        <th>Type</th>
                        <th>SNMP Config</th>
                        <th>Monitoring</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($bng && $bng->num_rows > 0): ?>
                        <?php while($n=$bng->fetch_assoc()){ 
                            $type_icon = ($n['device_type'] == 'mikrotik') ? 'fa-server' : (($n['device_type'] == 'olt') ? 'fa-boxes' : 'fa-network-wired');
                        ?>
                        <tr>
                            <td>
                                <div class="fw-600"><?= htmlspecialchars($n['nasname']) ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($n['ip_address']) ?></div>
                            </td>
                            <td>
                                <span class="badge badge-info" style="font-size: 10px;">
                                    <i class="fa <?= $type_icon ?>" style="margin-right: 4px;"></i> <?= strtoupper($n['device_type']) ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-size: 0.8125rem;"><span class="text-muted">Comm:</span> <?= htmlspecialchars($n['snmp_community']) ?></div>
                                <div style="font-size: 0.75rem;"><span class="text-muted">Ver:</span> <?= htmlspecialchars($n['snmp_version']) ?></div>
                            </td>
                            <td>
                                <span id="status-<?= $n['id'] ?>" class="badge <?= $n['status'] ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $n['status'] ? 'Online' : 'Offline' ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div class="flex gap-2 justify-end">
                                    <a href="nas_edit.php?id=<?= $n['id'] ?>" class="btn btn-secondary btn-sm" title="Edit" style="padding: 0.4rem; width: 32px; color: var(--success);">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="?del=<?= $n['id'] ?>" class="btn btn-secondary btn-sm" title="Delete" style="padding: 0.4rem; width: 32px; color: var(--danger);" onclick="return confirm('Delete this device?')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; padding: 4rem; color: var(--text-muted);">No network devices configured.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('toggleAddForm').addEventListener('click', function() {
    var container = document.getElementById('addPlanFormContainer');
    container.style.display = (container.style.display === 'none') ? 'block' : 'none';
    if(container.style.display === 'block') {
        container.scrollIntoView({ behavior: 'smooth' });
    }
});

function pollDevices() {
    fetch('api_network_status.php')
    .then(r => r.json())
    .then(data => {
        data.forEach(d => {
            let el = document.getElementById('status-' + d.id);
            if(el) {
                el.className = 'badge ' + (d.status ? 'badge-success' : 'badge-danger');
                el.innerText = d.status ? 'Online' : 'Offline';
            }
        });
    });
}
setInterval(pollDevices, 15000);
pollDevices();
</script>

<?php include 'includes/footer.php'; ?>
