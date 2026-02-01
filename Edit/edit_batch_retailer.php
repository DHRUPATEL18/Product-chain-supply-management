<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$id = intval($_COOKIE["edit_id"] ?? 0);

if ($id === 0) {
    echo "Invalid ID.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $assigned_by = $_POST["assigned_by"] ?? '';
    $assigned_to = $_POST["assigned_to"] ?? '';
    $assigned_at = $_POST["assigned_at"] ?? '';
    $status	 = $_POST["status"] ?? '';

    $qr = "UPDATE batch_retailer SET assigned_by = ?, assigned_to = ?, assigned_at = NOW(), status	 = ? WHERE id = ?";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "issi", $assigned_by, $assigned_to, $status, $id);


    if (mysqli_stmt_execute($stmt)) {
        setcookie("edit_id", "", time() - 3600);
        header("Location: ../tablegrid.php?tn=batch_retailer");
        exit();
    } else {
        echo "Failed to update record.";
    }
}

$qr = "SELECT * FROM batch_retailer WHERE id = ?";
$stmt = mysqli_prepare($cn, $qr);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
?>

<link rel="stylesheet" href="style.css">

<form method="post">
    <h2>Edit Batch Retailer</h2>
    <div class="form-columns">
        <div class="form-left">
            <div class="form-group">
                <label>Assigned By:</label>
                <input type="number" name="assigned_by" value="<?= $row['assigned_by'] ?>">
            </div>

            <div class="form-group">
                <label>Assigned To:</label>
                <input type="number" name="assigned_to" value="<?= $row['assigned_to'] ?>">
            </div>

            <div class="form-group">
                <label>Status:</label>
                <input type="text" name="status" value="<?= $row['status'] ?>">
            </div>
        </div>
    </div>
    <input type="submit" value="Update">
</form>


