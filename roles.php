<?php
$page_title = "Roles & Permissions";
$active = "settings";

require_once '/var/www/html/config.php';
require_once '/var/www/html/includes/auth.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'add_role') {
        $name = $conn->real_escape_string($_POST['name']);
        $description = $conn->real_escape_string($_POST['description'] ?? '');
        $conn->query("INSERT INTO roles (name, description) VALUES ('$name', '$description')");
        $message = "Role created successfully!";
    }

    if ($action == 'save_permissions' && isset($_POST['role_id'])) {
        $roleId = (int)$_POST['role_id'];
        $conn->query("DELETE FROM role_permissions WHERE role_id = $roleId");
        if (!empty($_POST['permissions'])) {
            foreach ($_POST['permissions'] as $perm) {
                $perm = $conn->real_escape_string($perm);
                $conn->query("INSERT INTO role_permissions (role_id, permission) VALUES ($roleId, '$perm')");
            }
        }
        $message = "Permissions updated!";
    }

    if ($action == 'delete_role' && isset($_POST['role_id'])) {
        $roleId = (int)$_POST['role_id'];
        $conn->query("DELETE FROM role_permissions WHERE role_id = $roleId");
        $conn->query("DELETE FROM roles WHERE id = $roleId");
        $message = "Role deleted!";
    }
}

$roles = $conn->query("SELECT * FROM roles ORDER BY id");

$permissions = [
    'dashboard'     => 'Dashboard Access',
    'customers'     => 'Customer Management',
    'users'         => 'User Management',
    'hotspot'       => 'Hotspot Portal',
    'plans_vouchers'=> 'Plans & Vouchers',
    'hotel'         => 'Hotel Solution',
    'access_control'=> 'Access Control',
    'network'       => 'Network Devices',
    'olt'           => 'OLT Management',
    'mikrotik'      => 'MikroTik Management',
    'tr069'         => 'TR-069 Devices',
    'reports'       => 'Reports',
    'finance'       => 'Finance & Billing',
    'payments'      => 'Payments',
    'inventory'     => 'Inventory',
    'tickets'       => 'Tickets',
    'settings'      => 'System Settings',
    'admin_users'   => 'Admin Users',
    'branches'      => 'Branches',
    'roles'         => 'Roles & Permissions',
];

$rolePermissions = [];
$perms = $conn->query("SELECT role_id, permission FROM role_permissions");
while ($p = $perms->fetch_assoc()) {
    $rolePermissions[$p['role_id']][] = $p['permission'];
}

include '/var/www/html/includes/header.php';
include '/var/www/html/includes/sidebar.php';
include '/var/www/html/includes/topbar.php';
?>

<div class="page-content">
<div class="container-fluid p-4">
    <div class="page-header mb-4">
        <h2><i class="fas fa-user-shield"></i> Roles & Permissions</h2>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-list"></i> Roles</h5></div>
                <div class="card-body">
                    <form method="POST" class="mb-3">
                        <input type="hidden" name="action" value="add_role">
                        <input type="text" name="name" class="form-control mb-2" placeholder="Role name" required>
                        <input type="text" name="description" class="form-control mb-2" placeholder="Description">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-plus"></i> Add Role
                        </button>
                    </form>
                    <div class="list-group">
                        <?php while ($role = $roles->fetch_assoc()): ?>
                        <a href="?role_id=<?= $role['id'] ?>" class="list-group-item list-group-item-action <?= ($_GET['role_id'] ?? '') == $role['id'] ? 'active' : '' ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= htmlspecialchars($role['name']) ?></strong><br>
                                    <small><?= htmlspecialchars($role['description'] ?? '') ?></small>
                                </div>
                                <?php if ($role['id'] > 1): ?>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Delete this role?')">
                                    <input type="hidden" name="action" value="delete_role">
                                    <input type="hidden" name="role_id" value="<?= $role['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <?php if (!empty($_GET['role_id'])): ?>
                <?php
                $roleId = (int)$_GET['role_id'];
                $currentRole = $conn->query("SELECT * FROM roles WHERE id = $roleId")->fetch_assoc();
                ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Permissions: <?= htmlspecialchars($currentRole['name']) ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="save_permissions">
                            <input type="hidden" name="role_id" value="<?= $roleId ?>">
                            <div class="row">
                                <?php foreach ($permissions as $key => $label): ?>
                                <div class="col-6 col-md-4 mb-2">
                                    <div class="form-check">
                                        <input type="checkbox" name="permissions[]" value="<?= $key ?>"
                                            id="perm_<?= $key ?>" class="form-check-input"
                                            <?= in_array($key, $rolePermissions[$roleId] ?? []) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="perm_<?= $key ?>">
                                            <?= htmlspecialchars($label) ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Permissions
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-arrow-left fa-2x mb-3 d-block"></i>
                        Select a role from the left to manage permissions
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<?php include '/var/www/html/includes/footer.php'; ?>
