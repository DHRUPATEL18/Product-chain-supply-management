<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$id = intval($_COOKIE["view_id"] ?? 0);

if ($id === 0) {
    echo "Invalid ID.";
    exit();
}


$qr = "SELECT * FROM requested_products WHERE id = ?";
$stmt = mysqli_prepare($cn, $qr);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
?>

<h2 style="text-align:center;"> Requested Products</h2>
<form method="post" class="two-column-form">
    <div class="form-group">
        <label>Name:</label>
        <input type="text" name="name" value="<?= $row['name'] ?>" required>
    </div>

    <div class="form-group">
        <label>Category:</label>
        <input type="number" name="category" value="<?= $row['category'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Color:</label>
        <input type="text" name="color" value="<?= $row['color'] ?>" required>
    </div>

    <div class="form-group">
        <label>Specifications:</label>
        <input type="text" name="specifications" value="<?= $row['specifications'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Quantity:</label>
        <input type="number" name="quantity" value="<?= $row['quantity'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Status:</label>
        <input type="text" name="status" value="<?= $row['status'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Retailer ID:</label>
        <input type="number" name="retailer_id" value="<?= $row['retailer_id'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Distributor ID:</label>
        <input type="number" name="distributor_id" value="<?= $row['distributor_id'] ?>" readonly>
    </div>
</form>

<link rel="stylesheet" href="View/style.css">

