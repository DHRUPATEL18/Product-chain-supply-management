<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$id = intval($_COOKIE["edit_id"] ?? 0);
if ($id === 0) {
    echo "Invalid ID.";
    exit();
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["check_only"])) {
    $sku_id = $_POST["sku_id"] ?? '';
    $sku_id = mysqli_real_escape_string($cn, $sku_id);
    $check = mysqli_query($cn, 
    " SELECT sku_id FROM products WHERE sku_id = '$sku_id' UNION SELECT sku_id FROM sold_products WHERE sku_id = '$sku_id'");
    echo (mysqli_num_rows($check) > 0) ? "exists" : "ok";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST["check_only"])) {
    $product_category_id = $_POST["product_category_id"] ?? '';
    $product_name = $_POST["product_name"] ?? '';
    $sku_id = $_POST["sku_id"] ?? '';
    $added_by = $_POST["added_by"] ?? '';
    $status = $_POST["status"] ?? '';

    $qr = "UPDATE products SET product_category_id = ?, product_name = ?, sku_id = ?, added_by = ?, status = ?, date_of_creation = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "issssi", $product_category_id, $product_name, $sku_id, $added_by, $status, $id);

    if (mysqli_stmt_execute($stmt)) {
        setcookie("edit_id", "", time() - 3600);
        header("Location: ../tablegrid.php?tn=products");
        exit();
    } else {
        echo "Failed to update product.";
    }
}

$stmt = mysqli_prepare($cn, "SELECT * FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
?>

<link rel="stylesheet" href="style.css">

<form method="post">
    <h2>Edit Product</h2>
    <div class="form-columns">
        <div class="form-left">

            <div class="form-group">
                <label>Product Name:</label>
                <input type="text" name="product_name" value="<?= htmlspecialchars($row['product_name']) ?>" required>
            </div>

            <div class="form-group">
                <label>SKU ID:</label>
                <input type="text" name="sku_id" id="sku_id" value="<?= htmlspecialchars($row['sku_id']) ?>" required>
                <div id="skuWarning" style="color:red; font-weight:bold;"></div>
            </div>

            <div class="form-group">
                <label>Added By:</label>
                <select name="added_by" required>
                    <option value="">Select Manufacturer</option>
                    <?php
                    $addby = $row['added_by'];
                    $manufactures = mysqli_query($cn, "SELECT id, name FROM users WHERE role = 'Manufacture'");
                    while ($man = mysqli_fetch_assoc($manufactures)) {
                        $selected = ($man['name'] == $addby) ? 'selected' : '';
                        echo '<option value="' . $man['name'] . '" ' . $selected . '>' . $man['name'] . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>Status:</label>
                <input type="text" name="status" value="<?= htmlspecialchars($row['status']) ?>" required>
            </div>
        </div>
    </div>
    <input type="submit" id="submitBtn" value="Update">
</form>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const skuInput = document.getElementById("sku_id");
    const warningDiv = document.getElementById("skuWarning");
    const submitBtn = document.getElementById("submitBtn");

    skuInput.addEventListener("input", function () {
        const sku = skuInput.value.trim();
        if (sku === "") {
            warningDiv.textContent = "";
            submitBtn.disabled = false;
            return;
        }

        const formData = new URLSearchParams();
        formData.append("sku_id", sku);
        formData.append("check_only", "1");

        fetch("", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: formData.toString()
        })
        .then(res => res.text())
        .then(data => {
            if (data === "exists") {
                warningDiv.textContent = "SKU ID already exists!";
                submitBtn.disabled = true;
            } else {
                warningDiv.textContent = "";
                submitBtn.disabled = false;
            }
        });
    });
});
</script>
