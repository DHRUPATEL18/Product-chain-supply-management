<?php
session_start();
require_once '../notification_helper.php';
require_once '../auth_check.php';
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_category_id = $_POST["product_category_id"] ?? '';
    $product_name = $_POST["product_name"] ?? '';
    $sku_id = $_POST["sku_id"] ?? '';
    $added_by = $_POST["Manufacture"] ?? '';
    $status = $_POST["status"] ?? '';

    // Check for duplicate SKU ID in both products and sold_products
    $checkSku = mysqli_query(
        $cn,
        "SELECT id FROM products WHERE sku_id = '$sku_id'
         UNION
         SELECT id FROM sold_products WHERE sku_id = '$sku_id'"
    );

    if (mysqli_num_rows($checkSku) > 0) {
        echo "<script>
            alert('❌ SKU ID already exists. Please use a unique SKU.');
            window.location.href = '../tablegrid.php?tn=products';
        </script>";
        exit();
    } else {

        $qr = "INSERT INTO products(product_category_id, product_name, sku_id, added_by, status, date_of_creation) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($cn, $qr);
        mysqli_stmt_bind_param($stmt, "issss", $product_category_id, $product_name, $sku_id, $added_by, $status);

        if (mysqli_stmt_execute($stmt)) {

            // Create product insert notification
            $productMessage = "New product " . $product_name . " (SKU: " . $sku_id . ") added by " . $_SESSION['name'];
            createNotification($productMessage, 'product', $_SESSION['user_id'], $_SESSION['role'], 'products', mysqli_insert_id($cn));

            header("Location: ../tablegrid.php?tn=products");
            exit();
        } else {
            echo "Failed to insert product.";
        }
    }
}
?>

<link rel="stylesheet" href="Insert/style.css">

<form method="post" action="Insert/insert_products.php">
    <h2>Insert a New Record in Products</h2>

    <div class="form-columns">
        <div class="form-left">
            <div class="form-group">
                <label>Product Category ID:</label>
                <select name="product_category_id" required>
                    <option value="">Select Category</option>
                    <?php
                    $product = mysqli_query($cn, "SELECT id, category_name FROM product_category");
                    while ($row = mysqli_fetch_assoc($product)) {
                        echo '<option value="' . $row['id'] . '">' . $row['id'] . ' - ' . $row['category_name'] . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>SKU ID:</label>
                <input type="text" name="sku_id" id="sku_id" required>
                <!-- <div id="skuWarning" style="color:red; font-weight:bold;"></div> -->
            </div>

            <div class="form-group">
                <label>Status:</label>
                <input type="text" name="status" required>
            </div>
        </div>

        <div class="form-right">
            <div class="form-group">
                <label>Product Name:</label>
                <input type="text" name="product_name" required>
            </div>

            <div class="form-group">
                <label>Added By:</label>
                <select name="Manufacture" required>
                    <option value="">Select Manufacture</option>
                    <?php
                    $Manufacture = mysqli_query($cn, "SELECT id, name FROM users WHERE role = 'Manufacture'");
                    while ($row = mysqli_fetch_assoc($Manufacture)) {
                        echo '<option value="' . $row['name'] . '">' . $row['name'] . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>

    <input type="submit" value="Insert">
</form>