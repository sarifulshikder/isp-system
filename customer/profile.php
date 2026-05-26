<?php
include '../user-config.php';
include '../includes/customer.php';
include '../includes/user-header.php';


$id = $_SESSION['customer_id'];


if(isset($_POST['save'])){
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'] ?? '';
    
    $stmt = $conn->prepare("UPDATE customers SET phone = ?, address = ?, email = ? WHERE id = ?");
    $stmt->bind_param("sssi", $phone, $address, $email, $id);
    $stmt->execute();
    
    echo "<div style='color:green; padding:10px;'>Profile updated successfully!</div>";
}


$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$c = $stmt->get_result()->fetch_assoc();
?>


<form method="post">
<input name="phone" value="<?= $c['phone'] ?>">
<input name="email" value="<?= $c['email'] ?>">
<input name="address" value="<?=$c['address']?>">
<button name="save">Save</button>
</form>
