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
    $status = $_POST["status"] ?? '';

    $qr = "UPDATE states SET name = ?, status = ? WHERE id = ?";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "ssi", $name, $status, $id);


    if (mysqli_stmt_execute($stmt)) {
        setcookie("edit_id", "", time() - 3600);
        header("Location: ../tablegrid.php?tn=states");
        exit();
    } else {
        echo "Failed to update record.";
    }
}

$qr = "SELECT * FROM states WHERE id = ?";
$stmt = mysqli_prepare($cn, $qr);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
?>

<link rel="stylesheet" href="style.css">

<form method="post">
    <h2>Edit States</h2>
    <div class="form-columns">
        <div class="form-left">
            <div class="form-group">
                <label>State Name:</label>
                <input type="text" name="name" value="<?= $row['name'] ?>">
            </div>

            <div class="form-group">
                <label>Status:</label>
                <input type="text" name="status" value="<?= $row['status'] ?>">
            </div>
        </div>
    </div>
    <input type="submit" value="Update">
</form>


