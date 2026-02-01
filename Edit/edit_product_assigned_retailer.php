<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$id = intval($_COOKIE["edit_id"] ?? 0);

if ($id === 0) {
    echo "Invalid ID.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $batch_id = $_POST["batch_id"] ?? '';
    $product_id = $_POST["product_id"] ?? '';
    $quantity = $_POST["quantity"] ?? '';
    $status = $_POST["status"] ?? '';

    $qr = "UPDATE product_assigned_retailer SET batch_id = ?, product_id = ?, quantity = ?, status = ?,assigned_at = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "iiisi", $batch_id, $product_id, $quantity, $status, $id);



    if (mysqli_stmt_execute($stmt)) {
        setcookie("edit_id", "", time() - 3600);
        header("Location: ../tablegrid.php?tn=product_assigned_retailer");
        exit();
    } else {
        echo "Failed to update record.";
    }
}

$qr = "SELECT * FROM product_assigned_retailer WHERE id = ?";
$stmt = mysqli_prepare($cn, $qr);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
?>

<link rel="stylesheet" href="style.css">

<form method="post">
    <h2>Edit Product Assigned Retailer</h2>
    <div class="form-columns">
        <div class="form-left">
            <div class="form-group">
                <label>Batch ID:</label>
                <input type="number" name="batch_id" value="<?= $row['batch_id'] ?>">
            </div>

            <div class="form-group">
                <label>Product ID:</label>
                <input type="number" name="product_id" value="<?= $row['product_id'] ?>">
            </div>

            <div class="form-group">
                <label>Quantity:</label>
                <input type="number" name="quantity" value="<?= $row['quantity'] ?>">
            </div>

            <div class="form-group">
                <label>Status:</label>
                <input type="text" name="status" value="<?= $row['status'] ?>">
            </div>
        </div>
    </div>
    <input type="submit" value="Update">
</form>
