<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$id = intval($_COOKIE["view_id"] ?? 0);

if ($id === 0) {
    echo "Invalid ID.";
    exit();
}

$sql = "
    SELECT 
        u.id,
        u.name,
        u.contact,
        u.company_name,
        u.email,
        u.username,
        u.role,
        u.state_id,
        u.city_id,
        s.name AS statename,
        c.city_name AS cityname
    FROM 
        users u
    LEFT JOIN 
        states s ON s.id = u.state_id
    LEFT JOIN 
        city c ON c.id = u.city_id
    WHERE 
        u.id = ?
";

$stmt = mysqli_prepare($cn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    echo "User not found.";
    exit();
}

mysqli_stmt_close($stmt);
mysqli_close($cn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    <link rel="stylesheet" href="View/style.css">
</head>
<body>

<h2 style="text-align:center;">User Details</h2>

<form method="post" class="two-column-form">

    <div class="form-group">
        <label>Name:</label>
        <input type="text" name="name" value="<?=$row['name'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Contact:</label>
        <input type="text" name="contact" value="<?=$row['contact'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Company Name:</label>
        <input type="text" name="company_name" value="<?= $row['company_name'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Email:</label>
        <input type="email" name="email" value="<?= $row['email'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>State:</label>
        <input type="text" name="state_name" value="<?= $row['state_id']." - ".$row['statename'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>City:</label>
        <input type="text" name="city_name" value="<?= $row['city_id']." - ".$row['cityname'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Role:</label>
        <input type="text" name="role" value="<?= $row['role'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Username:</label>
        <input type="text" name="username" value="<?= $row['username'] ?>" readonly>
    </div>

</form>

</body>
</html>