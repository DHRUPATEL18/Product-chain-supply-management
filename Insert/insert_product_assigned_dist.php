<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $batch_id = $_POST["batch_id"] ?? '';
    $product_id = $_POST["product_id"] ?? '';
    $quantity = $_POST["quantity"] ?? '';
    $status = $_POST["status"] ?? '';

    $qr = "CALL add_product_assigned_dist(?,?,?,?)";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "iiis", $batch_id, $product_id, $quantity, $status);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../tablegrid.php?tn=product_assigned_dist");
        exit();
    } else {
        echo "Failed to update record.";
    }
}

?>

<link rel="stylesheet" href="Insert/style.css">

<form method="post" action="Insert/insert_product_assigned_dist.php">
    <h2>Insert a New Record in Product Assigned Dist</h2>

    <div class="form-columns">
        <div class="form-left">
            <div class="form-group">
                <label>Batch ID:</label>
                <input type="number" name="batch_id" required>
            </div>

            <div class="form-group">
                <label>Quantity:</label>
                <input type="number" name="quantity" required>
            </div>
        </div>

        <div class="form-right">
            <div class="form-group">
                <label>Product ID:</label>
                <input type="number" name="product_id" required>
            </div>

            <div class="form-group">
                <label>Status:</label>
                <input type="text" name="status" required>
            </div>
        </div>
    </div>

    <input type="submit" value="Insert">
</form>