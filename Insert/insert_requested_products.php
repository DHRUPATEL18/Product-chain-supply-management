<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"] ?? '';
    $category = $_POST["category"] ?? '';
    $color = $_POST["color"] ?? '';
    $specifications = $_POST["specifications"] ?? '';
    $quantity = $_POST["quantity"] ?? '';
    $status = $_POST["status"] ?? '';
    $retailer_id = $_POST["retailer_id"] ?? '';
    $distributor_id = $_POST["distributor_id"] ?? '';

    $qr = "CALL add_requested_products(?,?,?,?,?,?,?,?)";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "sissssii", $name, $category, $color, $specifications, $quantity, $status,$retailer_id, $distributor_id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../tablegrid.php?tn=requested_products");
        exit();
    } else {
        echo "Failed to update record.";
    }
}
?>

<link rel="stylesheet" href="Insert/style.css">

<form method="post" action="Insert/insert_requested_products.php">
    <h2>Insert a New Record in Requested Product</h2>

    <div class="form-columns">
        <div class="form-left">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>Color:</label>
                <input type="text" name="color" required>
            </div>

            <div class="form-group">
                <label>Quantity:</label>
                <input type="number" name="quantity" required>
            </div>

            <div class="form-group">
                <label>Retailer ID:</label>
                <input type="number" name="retailer_id" required>
            </div>
        </div>

        <div class="form-right">
            <div class="form-group">
                <label>Category:</label>
                <input type="number" name="category" required>
            </div>

            <div class="form-group">
                <label>Specifications:</label>
                <input type="text" name="specifications" required>
            </div>

            <div class="form-group">
                <label>Status:</label>
                <input type="text" name="status" required>
            </div>

            <div class="form-group">
                <label>Distributor ID:</label>
                <input type="number" name="distributor_id" required>
            </div>
        </div>
    </div>

    <input type="submit" value="Insert">
</form>
