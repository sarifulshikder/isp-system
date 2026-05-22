<?php
include 'config.php';

/* Page info */
$page_title = "Admin Users";
$active = "admin";

/* Auth check */
include 'includes/auth.php';

/* Only Super Admin */
if ($_SESSION['role'] !== 'superadmin') {
    die("Access Denied");
}

/* Fetch branches */
$branches = $conn->query("SELECT id,name FROM branches WHERE status='active'");

/* ADD / UPDATE ADMIN */
if (isset($_POST['add'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $branch_id = $_POST['branch_id'] ?: null;

    // Allowed ENUM roles
    $allowed_roles = ['superadmin','manager','support'];
    $role = strtolower($_POST['role'] ?? 'support');

    if (!in_array($role, $allowed_roles)) {
        die("Invalid role selected!");
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO admins (username, password, role, branch_id)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("sssi", $username, $password_hash, $role, $branch_id);
    if ($stmt->execute()) {
        $msg = "Admin added successfully!";
    } else {
        $msg = "Error: " . $stmt->error;
    }
}


/* DELETE ADMIN */
if(isset($_GET['del'])){
    $id = (int)$_GET['del'];
    if($id != $_SESSION['user_id']){
        $conn->query("DELETE FROM admins WHERE id=$id");
        $msg = "Admin deleted!";
    }
}

/* Fetch admin users */
$admins = $conn->query("
    SELECT 
        a.id,
        a.username,
        a.role,
        a.branch_id,
        b.name AS branch_name
    FROM admins a
    LEFT JOIN branches b ON a.branch_id = b.id
    ORDER BY a.id DESC
");

if(isset($_POST['change_password'])){
    $id = (int)$_POST['admin_id'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    /* Prevent superadmin password change */
    $check = $conn->query("SELECT role FROM admins WHERE id=$id")->fetch_assoc();
    if($check['role'] === 'superadmin'){
        die("Action not allowed");
    }

    if($new_password !== $confirm_password){
        $msg = "Error: Passwords do not match!";
    } else {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admins SET password=? WHERE id=?");
        $stmt->bind_param("si",$hash,$id);
        $stmt->execute();
        $msg = "Password changed successfully!";
    }
}

/* Layout */
include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>

<div class="animate-fade-in">
    
    <?php if(isset($msg)): ?>
    <div class="badge badge-info mb-4 w-full justify-center" style="padding: 1rem; border-radius: var(--radius);">
        <i class="fa fa-info-circle mr-2"></i> <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>
    
    <div class="flex-between mb-4 flex-wrap gap-4">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">Admin User Management</h1>
            <p class="text-muted" style="font-size: 0.875rem;">Manage administrative users, roles, and branch access</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('addAdminModal')">
            <i class="fa fa-user-plus"></i> Create Admin
        </button>
    </div>

    <!-- Admin Users Table -->
    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User Identity</th>
                        <th>Access Role</th>
                        <th>Assigned Branch</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($admins && $admins->num_rows > 0): ?>
                        <?php while($a = $admins->fetch_assoc()): 
                            $isSelf = ($a['id'] == $_SESSION['user_id']);
                            $isSuper = ($a['role'] === 'superadmin');
                            
                            $role_class = 'badge-info';
                            if($a['role'] == 'superadmin') $role_class = 'badge-danger';
                            if($a['role'] == 'manager') $role_class = 'badge-warning';
                            if($a['role'] == 'support') $role_class = 'badge-success';
                        ?>
                        <tr>
                            <td class="fw-600">#<?= $a['id'] ?></td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div style="width: 32px; height: 32px; background: var(--bg-soft); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--primary); font-size: 0.75rem;">
                                        <?= strtoupper(substr($a['username'], 0, 2)) ?>
                                    </div>
                                    <div class="fw-600"><?= htmlspecialchars($a['username']) ?></div>
                                </div>
                            </td>
                            <td><span class="badge <?= $role_class ?>"><?= strtoupper($a['role']) ?></span></td>
                            <td>
                                <div class="fw-600" style="font-size: 0.875rem;"><?= $a['branch_name'] ?: '<span class="text-muted">Global Access</span>' ?></div>
                            </td>
                            <td style="text-align: right;">
                                <div class="flex gap-2 justify-end">
                                    <?php if($isSelf): ?>
                                        <span class="badge badge-success" style="font-size: 10px;">YOU</span>
                                    <?php elseif($isSuper): ?>
                                        <span class="badge" style="background: var(--bg-soft); color: var(--text-muted); font-size: 10px;"><i class="fa fa-lock mr-1"></i> PROTECTED</span>
                                    <?php else: ?>
                                        <a href="admin_edit.php?id=<?= $a['id'] ?>" class="btn btn-secondary btn-sm" title="Edit" style="padding: 0.4rem; width: 32px; color: var(--success);"><i class="fa fa-edit"></i></a>
                                        <button onclick="openChangePass(<?= $a['id'] ?>, '<?= addslashes($a['username']) ?>')" class="btn btn-secondary btn-sm" title="Change Password" style="padding: 0.4rem; width: 32px; color: var(--info);"><i class="fa fa-key"></i></button>
                                        <a href="?del=<?= $a['id'] ?>" class="btn btn-secondary btn-sm" title="Delete" style="padding: 0.4rem; width: 32px; color: var(--danger);" onclick="return confirm('Permanently delete this admin user?')"><i class="fa fa-trash"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; padding: 4rem; color: var(--text-muted);">No admin users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Admin -->
<div class="modal-overlay" id="addAdminModal" onclick="closeModal('addAdminModal')">
    <div class="modal card" style="max-width: 500px;" onclick="event.stopPropagation()">
        <div class="card-header flex-between">
            <h3 class="card-title"><i class="fa fa-user-plus text-primary"></i> Create Admin User</h3>
            <button class="modal-close" onclick="closeModal('addAdminModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Unique admin username" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Secure password" required>
                </div>
                <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">System Role</label>
                        <select name="role" class="form-control" required>
                            <option value="support">Support / Staff</option>
                            <option value="manager">Branch Manager</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Assign Branch</label>
                        <select name="branch_id" class="form-control">
                            <option value="">All Branches (Global)</option>
                            <?php $branches->data_seek(0); while($b=$branches->fetch_assoc()){ ?>
                                <option value="<?= $b['id'] ?>"><?= $b['name'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body" style="border-top: 1px solid var(--border); background: var(--bg-soft);">
                <div class="flex gap-2">
                    <button type="submit" name="add" class="btn btn-primary w-full">Save Administrator</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addAdminModal')">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Change Password -->
<div class="modal-overlay" id="changePassModal" onclick="closeModal('changePassModal')">
    <div class="modal card" style="max-width: 400px;" onclick="event.stopPropagation()">
        <div class="card-header flex-between">
            <h3 class="card-title"><i class="fa fa-key text-primary"></i> Update Password</h3>
            <button class="modal-close" onclick="closeModal('changePassModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="card-body">
                <div class="badge badge-info mb-4 w-full" id="passUserBadge" style="justify-content: center; padding: 0.75rem;"></div>
                <input type="hidden" name="admin_id" id="passAdminId">
                <div class="form-group">
                    <label class="form-label">New Secure Password</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
            </div>
            <div class="card-body" style="border-top: 1px solid var(--border); background: var(--bg-soft);">
                <div class="flex gap-2">
                    <button type="submit" name="change_password" class="btn btn-primary w-full">Update Password</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('changePassModal')">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        const el = document.getElementById(id);
        if(el) {
            el.style.display = 'flex';
            el.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }
    function closeModal(id) {
        const el = document.getElementById(id);
        if(el) {
            el.style.display = 'none';
            el.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    }
    function openChangePass(id, username) {
        document.getElementById('passAdminId').value = id;
        document.getElementById('passUserBadge').innerText = 'User: ' + username;
        openModal('changePassModal');
    }
</script>

<?php include 'includes/footer.php'; ?>