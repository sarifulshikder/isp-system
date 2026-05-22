<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$base_path = './';
include 'config.php';
include 'includes/auth.php';

$page_title = "Add New Customer";
$active = "users";

/* =========================
   FETCH PLANS & BRANCHES
========================= */
$plans = $conn->query("SELECT * FROM plans");
$branches = $conn->query("SELECT * FROM branches WHERE status='active'");

/* =========================
   HANDLE FORM SUBMIT
========================= */
$msg = '';
$msg_type = '';

if (isset($_POST['add'])) {
    $username   = trim($_POST['username']);
    $password   = trim($_POST['password']);
    $full_name  = trim($_POST['full_name']);
    $phone      = trim($_POST['phone']);
    $email      = trim($_POST['email']);
    $address    = trim($_POST['address']);
    $lat        = !empty($_POST['lat']) ? $_POST['lat'] : NULL;
    $lng        = !empty($_POST['lng']) ? $_POST['lng'] : NULL;
    $plan_id    = (int)$_POST['plan_id'];
    $branch_id  = $_POST['branch_id'] ?: null;
    $expiry     = $_POST['expiry'];
    $onu_serial = trim($_POST['onu_serial']);
    $vlan       = (int)$_POST['vlan'];
    
    // FTTH Fields
    $olt        = trim($_POST['olt']);
    $olt_port   = (int)$_POST['olt_port'];
    $master_box = trim($_POST['master_box']);
    $db_box     = trim($_POST['db_box']);
    $db_port    = (int)$_POST['db_port'];

    // Fetch plan
    $plan_stmt = $conn->prepare("SELECT name, speed FROM plans WHERE id=?");
    $plan_stmt->bind_param("i", $plan_id);
    $plan_stmt->execute();
    $plan = $plan_stmt->get_result()->fetch_assoc();
    
    if (!$plan) {
        $msg = "Invalid plan selected.";
        $msg_type = "error";
    } else {
        $plan_name = $plan['name'];
        $speed = $plan['speed'];
        $expiry_radius = date("d M Y", strtotime($expiry));

        // Auto-generate Wi-Fi credentials
        $wifi_ssid = "ISP_" . $username;
        $wifi_password = substr(md5($username . time()), 0, 10);

        $conn->begin_transaction();
        try {
            // RADIUS CONFIG
            $conn->query("INSERT INTO radreply (username, attribute, op, value) VALUES ('$username','Mikrotik-Rate-Limit',':=','$speed')");
            
            $stmt1 = $conn->prepare("INSERT INTO radcheck (username, attribute, op, value) VALUES (?, 'Cleartext-Password', ':=', ?)");
            $stmt1->bind_param("ss", $username, $password);
            $stmt1->execute();

            $stmt2 = $conn->prepare("INSERT INTO radcheck (username, attribute, op, value) VALUES (?, 'Expiration', ':=', ?)");
            $stmt2->bind_param("ss", $username, $expiry_radius);
            $stmt2->execute();

            $stmt3 = $conn->prepare("INSERT INTO radusergroup (username, groupname, priority) VALUES (?, ?, 1)");
            $stmt3->bind_param("ss", $username, $plan_name);
            $stmt3->execute();

            // CUSTOMER TABLE
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt4 = $conn->prepare("INSERT INTO customers (username, password, full_name, phone, email, address, lat, lng, plan_id, branch_id, expiry, status, onu_serial, vlan, wifi_ssid, wifi_password, created_at, olt, olt_port, master_box, db_box, db_port) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)");
            $stmt4->bind_param("ssssssddiisssssisssi", $username, $hashed, $full_name, $phone, $email, $address, $lat, $lng, $plan_id, $branch_id, $expiry, $onu_serial, $vlan, $wifi_ssid, $wifi_password, $olt, $olt_port, $master_box, $db_box, $db_port);
            $stmt4->execute();

            $conn->commit();
            header("Location: users.php?msg=added");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $msg = "Error: " . $e->getMessage();
            $msg_type = "error";
        }
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>

<div class="animate-fade-in" style="max-width: 1000px; margin: 0 auto;">
    <?php if($msg): ?>
        <div class="badge badge-<?= $msg_type == 'error' ? 'danger' : 'success' ?> mb-4" style="width: 100%; justify-content: center; padding: 1rem; border-radius: var(--radius);">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <!-- Personal Information -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fa fa-user text-primary"></i> Personal Information</h2>
            </div>
            <div class="card-body">
                <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" placeholder="E.g. Ram Bahadur" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="98XXXXXXXX" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="example@mail.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Installation Address</label>
                        <input type="text" name="address" class="form-control" placeholder="City, Ward No, Street">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Geo Location</label>
                        <div class="flex gap-2">
                            <input type="text" name="lat" id="lat" class="form-control" placeholder="Latitude">
                            <input type="text" name="lng" id="lng" class="form-control" placeholder="Longitude">
                            <button type="button" onclick="getLocation()" class="btn btn-secondary">
                                <i class="fa fa-crosshairs"></i> <span class="d-none d-sm-inline">Detect</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Network Details -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fa fa-network-wired text-primary"></i> Network & FTTH Details</h2>
            </div>
            <div class="card-body">
                <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">OLT Name</label>
                        <input type="text" name="olt" class="form-control" placeholder="OLT Identifier">
                    </div>
                    <div class="form-group">
                        <label class="form-label">OLT Port</label>
                        <input type="number" name="olt_port" class="form-control" placeholder="Port #">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Master Box</label>
                        <input type="text" name="master_box" class="form-control" placeholder="Splitter Name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">DB Box</label>
                        <input type="text" name="db_box" class="form-control" placeholder="DB Identifier">
                    </div>
                    <div class="form-group">
                        <label class="form-label">DB Port</label>
                        <input type="number" name="db_port" class="form-control" placeholder="Port #">
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Credentials -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fa fa-key text-primary"></i> Account Credentials</h2>
            </div>
            <div class="card-body">
                <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">PPP Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Unique username" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">PPP Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Secure password" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service Plan -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fa fa-wifi text-primary"></i> Service Configuration</h2>
            </div>
            <div class="card-body">
                <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Internet Plan</label>
                        <select name="plan_id" class="form-control" required>
                            <option value="">Select a plan</option>
                            <?php while($p = $plans->fetch_assoc()): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= $p['speed'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" name="expiry" class="form-control" required value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Assign Branch</label>
                        <select name="branch_id" class="form-control">
                            <option value="">Main Branch</option>
                            <?php while($b = $branches->fetch_assoc()): ?>
                                <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">VLAN ID</label>
                        <input type="number" name="vlan" class="form-control" value="0">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">ONU Serial (TR-069)</label>
                        <input type="text" name="onu_serial" class="form-control" placeholder="Auto-configuration serial number">
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 mb-4">
            <button type="submit" name="add" class="btn btn-primary w-full" style="padding: 1rem;">
                <i class="fa fa-save"></i> Register Customer & Activate
            </button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition, showError);
    } else { 
        alert("Geolocation is not supported by this browser.");
    }
}

function showPosition(position) {
    document.getElementById("lat").value = position.coords.latitude;
    document.getElementById("lng").value = position.coords.longitude;
}

function showError(error) {
    switch(error.code) {
        case error.PERMISSION_DENIED:
            alert("User denied the request for Geolocation.");
            break;
        case error.POSITION_UNAVAILABLE:
            alert("Location information is unavailable.");
            break;
        case error.TIMEOUT:
            alert("The request to get user location timed out.");
            break;
        case error.UNKNOWN_ERROR:
            alert("An unknown error occurred.");
            break;
    }
}
</script>
