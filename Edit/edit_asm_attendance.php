<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$id = intval($_COOKIE["edit_id"] ?? 0);

if ($id === 0) {
    echo "Invalid ID.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $asm_id = $_POST["asm_id"] ?? '';
    $attendance = $_POST["attendance"] ?? '';
    $location = $_POST["location"] ?? '';
    $date_time = $_POST["date_time"] ?? '';

    $qr = "UPDATE asm_attendance SET asm_id = ?, attendance = ?, location = ?, date_time = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "issi", $asm_id, $attendance, $location, $id);


    if (mysqli_stmt_execute($stmt)) {
        setcookie("edit_id", "", time() - 3600);
        header(header: "Location: ../tablegrid.php?tn=asm_attendance");
        exit();
    } else {
        echo "Failed to update record.";
    }
}

$qr = "SELECT * FROM asm_attendance  WHERE id = ?";
$stmt = mysqli_prepare($cn, $qr);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
?>

<link rel="stylesheet" href="style.css">

<form method="post">
    <h2>Edit ASM Attendance</h2>
    <div class="form-columns">
        <div class="form-left">
            <div class="form-group">
                <label>ASM ID:</label>
                <input type="number" name="asm_id" value="<?= $row['asm_id'] ?>">
            </div>

            <div class="form-group">
                <label>Attendance:</label>
                <input type="text" name="attendance" value="<?= $row['attendance'] ?>">
            </div>
        </div>
        <div class="form-right">
            <div class="form-group">
                <label>Location:</label>
                <input type="text" name="location" value="<?= $row['location'] ?>">
            </div>
        </div>
    </div>

    <input type="submit" value="Update">
</form>