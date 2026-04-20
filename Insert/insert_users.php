<?php
require_once '../notification_helper.php';
require_once '../auth_check.php';

$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

// ====================== AJAX: Fetch Cities ======================
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["fill"]) && $_GET["fill"] === "city") {
    $state_id = intval($_GET["state_id"] ?? 0);
    $cities = mysqli_query($cn, "SELECT id, city_name FROM city WHERE state_id = $state_id AND status = 'Active'");

    if (mysqli_num_rows($cities) === 0) {
        echo '<option value="">No cities found</option>';
    } else {
        echo '<option value="">-- Select City --</option>';
        while ($c = mysqli_fetch_assoc($cities)) {
            echo '<option value="' . $c['id'] . '">' . htmlspecialchars($c['city_name']) . '</option>';
        }
    }
    exit();
}

// ====================== INSERT NEW USER ======================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name         = $_POST["name"] ?? '';
    $username     = $_POST["username"] ?? '';
    $contact      = $_POST["contact"] ?? '';
    $company_name = $_POST["company_name"] ?? '';
    $email        = $_POST["email"] ?? '';
    $password     = $_POST["password"] ?? '';
    $state_id     = $_POST["state_id"] ?? '';
    $city_id      = $_POST["city_id"] ?? '';
    $role         = $_POST["role"] ?? '';

    $qr = "INSERT INTO users (name, username, contact, company_name, email, password, state_id, city_id, role, created_at) 
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "ssssssiis", $name, $username, $contact, $company_name, $email, $password, $state_id, $city_id, $role);

    if (mysqli_stmt_execute($stmt)) {
        $insertMessage = "New user " . $name . " (" . $role . ") created by " . $_SESSION['name'];
        createNotification($insertMessage, 'insert', $_SESSION['user_id'], $_SESSION['role'], 'users', mysqli_insert_id($cn));

        header("Location: ../tablegrid.php?tn=users");
        exit();
    } else {
        echo "❌ Failed to insert record. Error: " . mysqli_error($cn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User - OneLife Distributor</title>
    <link rel="stylesheet" href="Insert/style.css">
</head>

<body>
    <form method="post" action="Insert/insert_users.php">
        <h2>Insert Users</h2>

        <div class="form-columns">
            <div class="form-left">
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" required>
                </div>

                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" required>
                </div>

                <div class="form-group">
                    <label>Contact:</label>
                    <input type="text" name="contact" required>
                </div>

                <div class="form-group">
                    <label>Company Name:</label>
                    <input type="text" name="company_name" required>
                </div>

                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
            </div>

            <div class="form-right">
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>

                <div class="form-group">
                    <label>State:</label>
                    <select name="state_id" id="state_id" onchange="loadCities(this.value)" required>
                        <option value="">-- Select State --</option>
                        <?php
                        $states = mysqli_query($cn, "SELECT id, name FROM states WHERE status = 'Active'");
                        while ($s = mysqli_fetch_assoc($states)) {
                            echo '<option value="' . $s['id'] . '">' . htmlspecialchars($s['name']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>City:</label>
                    <select name="city_id" id="city_id" required>
                        <option value="">-- Select City --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Role:</label>
                    <select name="role" required>
                        <option value="">-- Select Role --</option>
                        <option value="Manufacture">Manufacture</option>
                        <option value="Distributor">Distributor</option>
                        <option value="Retailer">Retailer</option>
                        <option value="Area Sales Manager">Area Sales Manager</option>
                    </select>
                </div>
            </div>
        </div>

        <input type="submit" value="Insert">
    </form>

</body>
</html>
