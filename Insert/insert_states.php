<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"] ?? '';
    $status = $_POST["status"] ?? '';

    $qr = "CALL add_states(?,?)";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "ss", $name, $status);


    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../tablegrid.php?tn=states");
        exit();
    } else {
        echo "Failed to update record.";
    }
}

?>

<link rel="stylesheet" href="Insert/style.css">

<form method="post" action="Insert/insert_states.php">
    <h2>Insert a New Record in States</h2>

    <div class="form-columns">
        <div class="form-left">
            <div class="form-group">
                <label>State Name:</label>
                <input type="text" name="name" required>
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

