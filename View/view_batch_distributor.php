<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$id = intval($_COOKIE["view_id"] ?? 0);

if ($id === 0) {
    echo "Invalid ID.";
    exit();
}


$qr = "SELECT * FROM batch_distributor   WHERE id = ?";
$stmt = mysqli_prepare($cn, $qr);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
?>

<h2 style="text-align:center;"> Batch Distributor</h2>
<form method="post" class="two-column-form">
    <div class="form-group">
        <label>Assigned by:</label>
        <input type="text" name="assigned_by" value="<?= $row['assigned_by'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Assigned to:</label>
        <input type="text" name="assigned_to" value="<?= $row['assigned_to'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Status:</label>
        <input type="text" name="status" value="<?= $row['status'] ?>" readonly>
    </div>

</form>

<link rel="stylesheet" href="View/style.css">
