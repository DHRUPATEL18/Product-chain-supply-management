<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$id = intval($_COOKIE["edit_id"] ?? 0);

if ($id === 0) {
    echo "Invalid ID.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category_name = $_POST["category_name"] ?? '';
    $status = $_POST["status"] ?? '';
    $added_by = $_POST["added_by"] ?? '';

    $qr = "UPDATE product_category SET category_name = ?, status = ?, added_by = ?, date_of_creation = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "sssi", $category_name, $status, $added_by, $id);

    if (mysqli_stmt_execute($stmt)) {
        setcookie("edit_id", "", time() - 3600);
        header("Location: ../tablegrid.php?tn=product_category");
        exit();
    } else {
        echo "Failed to update user record.";
    }
}

$qr = "SELECT * FROM product_category WHERE id = ?";
$stmt = mysqli_prepare($cn, $qr);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

?>

<link rel="stylesheet" href="style.css">

<form method="post">
    <h2>Edit Product Category</h2>
    <div class="form-columns">
        <div class="form-left">
            <div class="form-group">
                <label>Category Name:</label>
                <input type="text" name="category_name" value="<?= $row['category_name'] ?>" required>
            </div>

            <div class="form-group">
                <label>Status:</label>
                <input type="text" name="status" value="<?= $row['status'] ?>" required>
            </div>

            <div class="form-group">
                <label>Added By:</label>
                <select name="added_by" required>
                    <option value="">Select Manufacture</option>
                    <?php
                    $added_by = $row['added_by'];
                    $manufactures = mysqli_query($cn, "SELECT id, name FROM users WHERE role = 'Manufacture'");
                    while ($man = mysqli_fetch_assoc($manufactures)) {
                        $selected = ($man['name'] == $added_by) ? 'selected' : '';
                        echo '<option value="' . $man['name'] . '" ' . $selected . '>' . $man['name'] . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>
    <input type="submit" value="Update">
</form>