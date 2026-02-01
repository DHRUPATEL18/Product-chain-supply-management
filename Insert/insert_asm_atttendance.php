<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $asm_id = $_POST["asm_id"] ?? '';
    $attendance = $_POST["attendance"] ?? '';
    $location = $_POST["location"] ?? '';

    $qr = "INSERT INTO asm_attendance(asm_id, attendance, location, date_time) 
           VALUES (?, ?, ?,NOW())";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "iss", $asm_id, $attendance, $location);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../tablegrid.php?table=asm_attendance");
        exit();
    } else {
        echo "❌ Failed to insert record. Error: " . mysqli_error($cn);
    }
}
?>


<h2 style="text-align:center;">Asm Attendance</h2>
<form method="post" action="Insert/insert_asm_attendance.php" class="two-column-form">
    <div class="form-group">
        <label>Asm Id:</label>
        <input type="number" name="asm_id" required>
    </div>

    <div class="form-group">
        <label>Attendance:</label>
        <input type="text" name="attendance" required>
    </div>

    <div class="form-group">
        <label>Location:</label>
        <input type="text" name="location" required>
    </div>

     
    <div style="grid-column: span 2; text-align: center; margin-top: 20px;">
        <button type="submit" style="padding:10px 20px; background-color:green; color:white; border:none; border-radius:6px;">Submit</button>
    </div>
</form>
<link rel="stylesheet" href="Insert/style.css">