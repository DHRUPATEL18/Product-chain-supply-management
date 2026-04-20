<?php
require_once '../auth_check.php';

$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category_name = $_POST["category_name"] ?? '';
    $status = $_POST["status"] ?? '';
    $added_by = $_POST["manufacture_id"] ?? '';

    $qr = "CALL add_product_category(?,?,?)";
    $stmt = mysqli_prepare($cn, $qr);
    
    if (!$stmt) {
        echo "Failed to prepare statement: " . mysqli_error($cn);
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "sss", $category_name, $status, $added_by);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../tablegrid.php?tn=product_category");
        exit();
    } else {
        echo "Failed to insert product category record.";
    }
    
    mysqli_stmt_close($stmt);
}

mysqli_close($cn);
?>

<link rel="stylesheet" href="Insert/style.css">

<form method="post" action="Insert/insert_product_category.php">
    <h2>Insert a New Record in Product Category</h2>

    <div class="form-columns">
        <div class="form-left">
            <div class="form-group">
                <label>Category Name:</label>
                <input type="text" name="category_name" required>
            </div>

            <div class="form-group">
                <label>Added By:</label>
                <select name="manufacture_id" required>
                    <option value="">Select Manufacture</option>
                    <?php
                    $Manufacture = mysqli_query($cn, "SELECT id, name FROM users WHERE role = 'Manufacture'");
                    while ($row = mysqli_fetch_assoc($Manufacture)) {
                        echo '<option value="'.$row['id'].'">' . $row['name'] . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="form-right">
            <div class="form-group">
                <label>Status:</label>
                <input type="text" name="status" required>
            </div>
        </div>
    </div>

    <input type="submit" value="Insert">
</form>