<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$id = intval($_COOKIE["view_id"] ?? 0);

if ($id === 0) {
    echo "Invalid ID.";
    exit();
}

$qr = "SELECT * FROM asm_attendance WHERE id = ?";
$stmt = mysqli_prepare($cn, $qr);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
?>

<h2 style="text-align:center;"> ASM Attendance</h2>
<form method="post" class="two-column-form">
    <div class="form-group">
        <label>ASM ID:</label>
        <input type="number" name="asm_id" value="<?= $row['asm_id'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Attendance:</label>
        <input type="text" name="attendance" value="<?= $row['attendance'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Location:</label>
        <input type="text" name="location" value="<?= $row['location'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>View in Map:</label>
        <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($row['location']) ?>" target="_blank" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">
            <i class="fas fa-map-marker-alt"></i> View in Google Maps
        </a>
    </div>
</form>

<link rel="stylesheet" href="View/style.css">