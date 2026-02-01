<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"] ?? '';
    $username = $_POST["username"] ?? '';
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';
    $contact = $_POST["contact"] ?? '';
    $status = $_POST["status"] ?? '';

    $qr = "CALL add_admin(?,?,?,?,?,?)";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "ssssss", $name, $username, $email, $password, $contact, $status);


    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../tablegrid.php?tn=admin");
        exit();
    } else {
        echo "Failed to update record.";
    }
}
?>

<link rel="stylesheet" href="Insert/style.css">

<form method="post" action="Insert/insert_admin.php">
    <h2>Insert a New Record in Admin</h2>

    <div class="form-columns">
        <div class="form-left">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>Email:</label>
                <input type="text" name="email" required>
            </div>

            <div class="form-group">
                <label>Contact:</label>
                <input type="number" name="contact" required>
            </div>
        </div>

        <div class="form-right">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Password:</label>
                <input type="text" name="password" required>
            </div>

            <div class="form-group">
                <label>Status:</label>
                <input type="text" name="status" required>
            </div>
        </div>
    </div>

    <input type="submit" value="Insert">
</form>