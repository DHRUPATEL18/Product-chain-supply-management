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
    $username = $_POST["username"] ?? '';
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';
    $contact = $_POST["contact"] ?? '';
    $status = $_POST["status"] ?? '';

    $qr = "UPDATE admin SET name = ?, username = ?, email = ?, password = ?, contact = ?, status = ?, date_time = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "ssssssi", $name, $username, $email, $password, $contact, $status, $id);


    if (mysqli_stmt_execute($stmt)) {
        setcookie("edit_id", "", time() - 3600);
        header("Location: ../tablegrid.php?tn=admin");
        exit();
    } else {
        echo "Failed to update record.";
    }
}

$qr = "SELECT * FROM admin WHERE id = ?";
$stmt = mysqli_prepare($cn, $qr);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
?>

<link rel="stylesheet" href="style.css">

<form method="post">
    <h2>Edit Admin</h2>

    <div class="form-columns">
        <div class="form-left">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" value="<?= $row['name'] ?>">
            </div>

            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" value="<?= $row['username'] ?>" readonly>
            </div>

            <div class="form-group">
                <label>Email:</label>
                <input type="text" name="email" value="<?= $row['email'] ?>">
            </div>
        </div>

        <div class="form-right">
            <div class="form-group">
                <label>Password:</label>
                <input type="text" name="password" value="<?= $row['password'] ?>">
            </div>

            <div class="form-group">
                <label>Contact:</label>
                <input type="number" name="contact" value="<?= $row['contact'] ?>">
            </div>

            <div class="form-group">
                <label>Status:</label>
                <input type="text" name="status" value="<?= $row['status'] ?>">
            </div>
        </div>
    </div>

    <input type="submit" value="Update">
</form>