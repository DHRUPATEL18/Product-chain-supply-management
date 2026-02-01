<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$id = intval($_COOKIE["edit_id"] ?? 0);
if ($id === 0) {
    echo "Invalid ID.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["state_id"])) {
    $sid = intval($_GET["state_id"]);
    $stmt = mysqli_prepare($cn, "SELECT id, city_name FROM city WHERE state_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $sid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['city_name']) . '</option>';
    }
    mysqli_stmt_close($stmt);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"] ?? '';
    $contact = $_POST["contact"] ?? '';
    $company_name = $_POST["company_name"] ?? '';
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';
    $state_id = intval($_POST["state_id"] ?? 0);
    $city_id = intval($_POST["city_id"] ?? 0);
    $role = $_POST["role"] ?? '';
    $username = $_POST["username"] ?? '';

    $qr = "UPDATE users SET name = ?, contact = ?, company_name = ?, email = ?, password = ?, state_id = ?, city_id = ?, role = ?, username = ? WHERE id = ?";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "sssssiissi", $name, $contact, $company_name, $email, $password, $state_id, $city_id, $role, $username, $id);

    if (mysqli_stmt_execute($stmt)) {
        setcookie("edit_id", "", time() - 3600);
        mysqli_stmt_close($stmt);
        header("Location: ../tablegrid.php?tn=users");
        exit();
    } else {
        echo "Failed to update user record: " . mysqli_error($cn);
    }
    mysqli_stmt_close($stmt);
}

$sql = "
    SELECT 
        u.*, 
        s.name AS state_name, 
        c.city_name AS city_name
    FROM 
        users u
    LEFT JOIN 
        states s ON u.state_id = s.id
    LEFT JOIN 
        city c ON u.city_id = c.id
    WHERE 
        u.id = ?
";
$stmt = mysqli_prepare($cn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "User not found.";
    exit();
}
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <form method="post">
        <h2>Edit User</h2>
        <div class="form-columns">
            <div class="form-left">
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($data['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Contact:</label>
                    <input type="text" name="contact" value="<?= htmlspecialchars($data['contact']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Company Name:</label>
                    <input type="text" name="company_name" value="<?= htmlspecialchars($data['company_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($data['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" value="<?= htmlspecialchars($data['password']) ?>" required>
                </div>

                <div class="form-group">
                    <label>State:</label>
                    <select name="state_id" id="state_id" required>
                        <option value="">Select State</option>
                        <?php
                        $states = mysqli_query($cn, "SELECT id, name FROM states ORDER BY name");
                        while ($s = mysqli_fetch_assoc($states)) {
                            $selected = $s['id'] == $data['state_id'] ? 'selected' : '';
                            echo "<option value='{$s['id']}' $selected>" . htmlspecialchars($s['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>City:</label>
                    <select name="city_id" id="city_id" required>
                        <option value="">Select City</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Role:</label>
                    <?php
                    $roles = ["Manufacture", "Distributor", "Retailer", "Area Sales Manager"];
                    $currentRole = $data['role'] ?? '';
                    ?>
                    <select name="role" required>
                        <option value="">Select Role</option>
                        <?php foreach ($roles as $role) { ?>
                            <option value="<?= $role ?>" <?= $currentRole === $role ? 'selected' : '' ?>>
                                <?= $role ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($data['username']) ?>" required>
                </div>
            </div>
        </div>
        <input type="submit" value="Update">
    </form>

    <script>
        const originalStateId = "<?= $data['state_id'] ?>";
        const originalCityId = "<?= $data['city_id'] ?>";

        function fillcity(stateId, callback = null) {
            const citySelect = document.getElementById("city_id");
            citySelect.innerHTML = '<option value="">Loading...</option>';

            const xhr = new XMLHttpRequest();
            xhr.open("GET", "edit_users.php?state_id=" + encodeURIComponent(stateId), true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    citySelect.innerHTML = xhr.responseText;
                    if (callback) callback();
                } else {
                    console.error("Failed to load cities.");
                }
            };
            xhr.send();
        }

        document.addEventListener("DOMContentLoaded", function () {
            const stateSelect = document.getElementById("state_id");
            if (originalStateId) {
                fillcity(originalStateId, function () {
                    document.getElementById("city_id").value = originalCityId;
                });
            }

            stateSelect.addEventListener("change", function () {
                fillcity(this.value);
            });
        });
    </script>
</body>
</html>
