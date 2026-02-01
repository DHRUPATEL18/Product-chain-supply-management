<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$id = intval($_COOKIE["view_id"] ?? 0);
if ($id === 0) {
    echo "Invalid ID.";
    exit();
}

$qr = "SELECT * FROM admin WHERE id = ?";
$stmt = mysqli_prepare($cn, $qr);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
?>

<h2 style="text-align:center;">Admin</h2>
<form method="post" class="two-column-form">
    <div class="form-group">
        <label>Name:</label>
        <input type="text" name="name" value="<?= $row['name'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Username:</label>
        <input type="text" name="username" value="<?= $row['username'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Email:</label>
        <input type="email" name="email" value="<?= $row['email'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Password:</label>
        <input type="text" name="password" value="<?= $row['password'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Contact:</label>
        <input type="text" name="contact" value="<?= $row['contact'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Status:</label>
        <input type="text" name="status" value="<?= $row['status'] ?>" readonly>
    </div>
</form>

<link rel="stylesheet" href="View/style.css">