<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$id = intval($_COOKIE["view_id"] ?? 0);

if ($id === 0) {
    echo "Invalid ID.";
    exit();
}

$qr = "SELECT * FROM product_assignments_backup WHERE id = ?";
$stmt = mysqli_prepare($cn, $qr);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
?>

<h2 style="text-align:center;">Product Assignment Backup</h2>

<form method="post" class="two-column-form">

    <div class="form-group">
        <label>Batch ID:</label>
        <input type="number" value="<?= $row['batch_id'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Product ID:</label>
        <input type="number" value="<?= $row['product_id'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Quantity:</label>
        <input type="number" value="<?= $row['quantity'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Assigned At:</label>
        <input type="text" value="<?= $row['assigned_at'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Status:</label>
        <input type="text" value="<?= $row['status'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Operation:</label>
        <input type="text" value="<?= $row['operation'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Operation DateTime:</label>
        <input type="text" value="<?= $row['operation_dt_tm'] ?>" readonly>
    </div>

</form>

<link rel="stylesheet" href="View/style.css">