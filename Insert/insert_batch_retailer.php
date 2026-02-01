<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $assigned_by = $_POST["assigned_by"] ?? '';
    $assigned_to = $_POST["assigned_to"] ?? '';
    $assigned_at = $_POST["assigned_at"] ?? '';
    $status	 = $_POST["status"] ?? '';

    $qr = "CALL add_batch_retailer(?,?,?)";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "iis", $assigned_by, $assigned_to, $status);


    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../tablegrid.php?tn=batch_retailer");
        exit();
    } else {
        echo "Failed to update record.";
    }
}
?>

<link rel="stylesheet" href="Insert/style.css">

<form method="post" action="Insert/insert_batch_retailer.php">
    <h2>Insert a New Record in Batch Retailer</h2>

    <div class="form-columns">
        <div class="form-left">
            <div class="form-group">
                <label>Assigned By:</label>
                <input type="number" name="assigned_by" required>
            </div>
            
            <div class="form-group">
                <label>Assigned To:</label>
                <input type="number" name="assigned_to" required>
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

