<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

// Check if user is logged in and has Manufacture role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manufacture') {
    echo "Access denied. Only Manufacture role can add offers.";
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
                $qr = "CALL add_offers(?,?,?,?)";
                $stmt = mysqli_prepare($cn, $qr);
                mysqli_stmt_bind_param($stmt, "ssss", $title, $fnm, $description, $status);

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
?>

<link rel="stylesheet" href="Insert/style.css">

<form method="post" action="Insert/insert_offers.php" enctype="multipart/form-data">
    <h2>Insert a New Record in Offers</h2>

    <div class="form-columns">
        <div class="form-left">
            <div class="form-group">
                <label>Title:</label>
                <input type="text" name="title" required>
            </div>

            <div class="form-group">
                <label>Description:</label>
                <input type="text" name="description" required>
            </div>
        </div>

        <div class="form-right">
            <div class="form-group">
                <label>Select Photo:</label>
                <input type="file" name="f[]" required>
            </div>

            <div class="form-group">
                <label>Status:</label>
                <input type="text" name="status" required>
            </div>
        </div>
    </div>

    <input type="submit" value="Insert">
</form>