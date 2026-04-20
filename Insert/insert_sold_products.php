<?php
session_start();
require_once '../notification_helper.php';
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_id = $_POST["product_id"] ?? '';
    $sold_by = $_POST["sold_by"] ?? '';

    $qr = "CALL add_sold_products(?,?)";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "ii", $product_id, $sold_by);

    if (mysqli_stmt_execute($stmt)) {

        // Create product sold notification
        $soldMessage = "Product ID " . $product_id . " sold by retailer ID " . $sold_by . " - recorded by " . $_SESSION['name'];
        createNotification($soldMessage, 'sold', $_SESSION['user_id'], $_SESSION['role'], 'sold_products', mysqli_insert_id($cn));

        header("Location: ../tablegrid.php?tn=sold_products");
        exit();
    } else {
        echo "Failed to update user record.";
    }
}

?>

<link rel="stylesheet" href="Insert/style.css">

<form method="post" action="Insert/insert_sold_products.php">
    <h2>Edit Sold Products</h2>

    <div class="form-columns">
        <div class="form-left">
            <div class="form-group">
                <label>Product ID:</label>
                <input type="number" name="product_id" required>
            </div>
        </div>

        <div class="form-right">
            <div class="form-group">
                <label>Sold By:</label>
                <input type="number" name="sold_by" required>
            </div>
        </div>
    </div>

    <input type="submit" value="Insert">
</form>