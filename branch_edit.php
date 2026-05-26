<?php
include 'config.php';
include 'includes/auth.php';

if(!isSuperAdmin()) die("Access Denied");

$id = (int)$_GET['id'];
$branch = $conn->query("SELECT * FROM branches WHERE id=$id")->fetch_assoc();
$error = '';

if(isset($_POST['update'])){
    $name   = $_POST['name'];
    $code   = $_POST['code'];
    $address = $_POST['address'];
    $phone  = $_POST['phone'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE branches SET name=?, code=?, address=?, phone=?, status=? WHERE id=?");
    $stmt->bind_param("sssssi", $name, $code, $address, $phone, $status, $id);
    if($stmt->execute()){
        header("Location: branches.php");
        exit;
    } else {
        $error = "Failed to update branch";
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>
<div class="main">
<?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<h2>Edit Branch</h2>
<form method="post">
    <input type="text" name="name" value="<?= htmlspecialchars($branch['name']) ?>" class="form-control" required><br>
    <input type="text" name="code" value="<?= htmlspecialchars($branch['code'] ?? '') ?>" class="form-control" placeholder="Branch Code"><br>
    <textarea name="address" class="form-control"><?= htmlspecialchars($branch['address'] ?? '') ?></textarea><br>
    <input type="text" name="phone" value="<?= htmlspecialchars($branch['phone'] ?? '') ?>" class="form-control"><br>
    <select name="status" class="form-control">
        <option value="active"   <?= ($branch['status']=='active'  ?'selected':'') ?>>Active</option>
        <option value="inactive" <?= ($branch['status']=='inactive'?'selected':'') ?>>Inactive</option>
    </select><br>
    <button type="submit" name="update" class="btn btn-primary">Update Branch</button>
    <a href="branches.php" class="btn btn-secondary">Cancel</a>
</form>
</div>
<?php include 'includes/footer.php'; ?>
