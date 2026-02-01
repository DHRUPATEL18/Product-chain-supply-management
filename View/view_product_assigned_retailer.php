<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$id = intval($_COOKIE["view_id"] ?? 0);

if ($id === 0) {
    echo "Invalid ID.";
    exit();
}

$qr = "SELECT * FROM product_assigned_retailer WHERE id = ?";
$stmt = mysqli_prepare($cn, $qr);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
?>
<h2 style="text-align:center;"> Product Assigned to Retailer</h2>
<form method="post" class="two-column-form">
    <div class="form-group">
        <label>Batch ID:</label>
        <input type="number" name="batch_id" value="<?= $row['batch_id'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Product ID:</label>
        <input type="number" name="product_id" value="<?= $row['product_id'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Quantity:</label>
        <input type="number" name="quantity" value="<?= $row['quantity'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Status:</label>
        <input type="text" name="status" value="<?= $row['status'] ?>" readonly>
    </div>
</form>

<link rel="stylesheet" href="View/style.css">

