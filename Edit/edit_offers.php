<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

// Check if user is logged in and has Manufacture role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manufacture') {
    echo "Access denied. Only Manufacture role can edit offers.";
    exit();
}

$id = intval($_COOKIE["edit_id"] ?? 0);

if ($id === 0) {
    echo "Invalid ID.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST["title"] ?? '';
    $description = $_POST["description"] ?? '';
    $status = $_POST["status"] ?? '';
    $fn = $_FILES['f']['name'] ?? '';

    foreach ($fn as $key => $fnm) {
        $src = $_FILES['f']['tmp_name'][$key];

        if (file_exists("Uploads/$fnm")) {
            echo "Sorry, file '$fnm' already exists.<br>";
        } else {
            if (move_uploaded_file($src, "../Uploads/$fnm")) {
                $qr = "UPDATE offers SET title = ?, img = ?, description = ?, status = ?, date_time = NOW() WHERE id = ?";
                $stmt = mysqli_prepare($cn, $qr);
                mysqli_stmt_bind_param($stmt, "sssss", $title, $fnm, $description, $status, $id);

                if (mysqli_stmt_execute($stmt)) {
                    header("Location: ../tablegrid.php?tn=offers");
                    exit();
                } else {
                    echo "Failed to update offers record.";
                }
            } else {
                echo "There was an issue uploading $fnm. Try again later";
            }
        }
    }
}

$qr = "SELECT * FROM offers WHERE id = ?";
$stmt = mysqli_prepare($cn, $qr);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
?>

<link rel="stylesheet" href="style.css">

<form method="post" enctype="multipart/form-data">
    <h2>Edit Offers</h2>
    <div class="form-columns">
        <div class="form-left">
            <div class="form-group">
                <label>Title:</label>
                <input type="text" name="title" value="<?= $row['title'] ?>" required>
            </div>

            <div class="form-group">
                <label>Image:</label>
                <input type="file" name="f[]" value="<?= $row['img'] ?>" required>
            </div>

            <div class="form-group">
                <label>Description:</label>
                <input type="text" name="description" value="<?= $row['description'] ?>" required>
            </div>

            <div class="form-group">
                <label>Status:</label>
                <input type="text" name="status" value="<?= $row['status'] ?>" required>
            </div>
        </div>
    </div>
    <input type="submit" value="Update">
</form>

