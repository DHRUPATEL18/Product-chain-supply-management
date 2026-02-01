<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$id = intval($_COOKIE["view_id"] ?? 0);

if ($id === 0) {
    echo "Invalid ID.";
    exit();
}


$qr = "SELECT * FROM offers WHERE id = ?";
$stmt = mysqli_prepare($cn, $qr);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
?>

<h2 style="text-align:center;">View Offers</h2>
<form method="post" class="two-column-form" enctype="multipart/form-data">
    <div class="form-group">
        <label>Title:</label>
        <input type="text" name="title" value="<?= $row['title'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Image:</label>
        <a href="../paragmax_onelife_distributor/Uploads/<?= $row['img'] ?>" download><img src="../paragmax_onelife_distributor/Uploads/<?= $row['img'] ?>" alt="img link" with="300px" height="150px"></a>
        <input type="text" name="file" value="<?= $row['img'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Description:</label>
        <input type="text" name="description" value="<?= $row['description'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Status:</label>
        <input type="text" name="status" value="<?= $row['status'] ?>" readonly>
    </div>
</form>

<link rel="stylesheet" href="View/style.css">