<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$id = intval($_COOKIE["edit_id"] ?? 0);

if ($id === 0) {
    echo "Invalid ID.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_id = $_POST["product_id"] ?? '';
    $sold_by = $_POST["sold_by"] ?? '';

    $qr = "UPDATE sold_products SET product_id = ?, sold_by = ?, sold_at = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "iii", $product_id, $sold_by, $id);

    if (mysqli_stmt_execute($stmt)) {
        setcookie("edit_id", "", time() - 3600); 
        header("Location: ../tablegrid.php?tn=sold_products");
        exit();
    } else {
        echo "Failed to update user record.";
    }
}

$qr = "SELECT * FROM sold_products WHERE id = ?";
$stmt = mysqli_prepare($cn, $qr);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
?>

<link rel="stylesheet" href="style.css">

<form method="post">
    <h2>Edit Sold Products</h2>
    <div class="form-columns">
        <div class="form-left">
            <div class="form-group">
                <label>Product ID:</label>
                <input type="number" name="product_id" value="<?= $row['product_id'] ?>" required>
            </div>

            <div class="form-group">
                <label>Sold By:</label>
                <input type="number" name="sold_by" value="<?= $row['sold_by'] ?>" required>
            </div>
        </div>
    </div>
    <input type="submit" value="Update">
</form>

