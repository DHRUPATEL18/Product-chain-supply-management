<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$id = intval($_COOKIE["view_id"] ?? 0);

if ($id === 0) {
    echo "Invalid ID.";
    exit();
}

$qr = "SELECT * FROM products WHERE id = ?";
$stmt = mysqli_prepare($cn, $qr);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
?>

<h2 style="text-align:center;"> Product</h2>
<form method="post" class="two-column-form">
    <div class="form-group">
        <label>Product Category ID:</label>
        <input type="number" name="product_category_id" value="<?= $row['product_category_id'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Product Name:</label>
        <input type="text" name="product_name" value="<?= $row['product_name'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>SKU ID:</label>
        <input type="text" name="sku_id" value="<?= $row['sku_id'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Added By:</label>
        <input type="text" name="added_by" value="<?= $row['added_by'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Status:</label>
        <input type="text" name="status" value="<?= $row['status'] ?>" readonly>
    </div>
</form>

<link rel="stylesheet" href="View/style.css">
