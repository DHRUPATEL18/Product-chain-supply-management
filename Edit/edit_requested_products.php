<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$id = intval($_COOKIE["edit_id"] ?? 0);

if ($id === 0) {
    echo "Invalid ID.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"] ?? '';
    $category = $_POST["category"] ?? '';
    $color = $_POST["color"] ?? '';
    $specifications = $_POST["specifications"] ?? '';
    $quantity = $_POST["quantity"] ?? '';
    $status = $_POST["status"] ?? '';
    $retailer_id = $_POST["retailer_id"] ?? '';
    $distributor_id = $_POST["distributor_id"] ?? '';

    $qr = "UPDATE requested_products SET name = ?, category = ?, color = ?, specifications = ?, quantity = ?, status = ?, retailer_id = ?, distributor_id = ?, date_time = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "sissssiii", $name, $category, $color, $specifications, $quantity, $status,$retailer_id, $distributor_id, $id);

    if (mysqli_stmt_execute($stmt)) {
        setcookie("edit_id", "", time() - 3600);
        header("Location: ../tablegrid.php?tn=requested_products");
        exit();
    } else {
        echo "Failed to update record.";
    }
}

$qr = "SELECT * FROM requested_products WHERE id = ?";
$stmt = mysqli_prepare($cn, $qr);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
?>

<link rel="stylesheet" href="style.css">

<form method="post">
    <h2>Edit Requested Product</h2>
    <div class="form-columns">
        <div class="form-left">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" value="<?= $row['name'] ?>" required>
            </div>

            <div class="form-group">
                <label>Category:</label>
                <input type="number" name="category" value="<?= $row['category'] ?>" required>
            </div>

            <div class="form-group">
                <label>Color:</label>
                <input type="text" name="color" value="<?= $row['color'] ?>" required>
            </div>

            <div class="form-group">
                <label>Specifications:</label>
                <input type="text" name="specifications" value="<?= $row['specifications'] ?>" required>
            </div>

            <div class="form-group">
                <label>Quantity:</label>
                <input type="number" name="quantity" value="<?= $row['quantity'] ?>" required>
            </div>

            <div class="form-group">
                <label>Status:</label>
                <input type="text" name="status" value="<?= $row['status'] ?>" required>
            </div>

            <div class="form-group">
                <label>Retailer ID:</label>
                <input type="number" name="retailer_id" value="<?= $row['retailer_id'] ?>" required>
            </div>

            <div class="form-group">
                <label>Distributor ID:</label>
                <input type="number" name="distributor_id" value="<?= $row['distributor_id'] ?>" required>
            </div>
        </div>
    </div>
    <input type="submit" value="Update">
</form>
