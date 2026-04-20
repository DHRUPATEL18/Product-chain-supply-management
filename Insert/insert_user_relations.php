<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["fill"], $_GET["type"])) {
    $relation = $_GET["type"];

    if ($_GET["fill"] === "parent") {
        if ($relation === "Manufacture-Distributor") {
            $res = mysqli_query($cn, "SELECT id, name FROM users WHERE role = 'Manufacture'");
        } elseif ($relation === "Distributor-Retailer") {
            $res = mysqli_query($cn, "SELECT id, name FROM users WHERE role = 'Distributor'");
        } elseif ($relation === "ASM-Distributor") {
            $res = mysqli_query($cn, "SELECT id, name FROM users WHERE role =          'Area Sales Manager'");
        } elseif ($relation === "Manufacture-ASM") {
            $res = mysqli_query($cn, "SELECT id, name FROM users WHERE role = 'Manufacture'");
        }
    } elseif ($_GET["fill"] === "child") {
        if ($relation === "Manufacture-Distributor") {
            $res = mysqli_query($cn, "SELECT id, name FROM users WHERE role = 'Distributor'");
        } elseif ($relation === "Distributor-Retailer") {
            $res = mysqli_query($cn, "SELECT id, name FROM users WHERE role =    'Retailer'");
        } elseif ($relation === "ASM-Distributor") {
            $res = mysqli_query($cn, "SELECT id, name FROM users WHERE role = 'Distributor'");
        } elseif ($relation === "Manufacture-ASM") {
            $res = mysqli_query($cn, "SELECT id, name FROM users WHERE role = 'Area Sales Manager'");
        }
    }

    while ($row = mysqli_fetch_assoc($res)) {
        echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
    }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $relation = $_POST["relation"] ?? '';

    $parent_id = $_POST["parent_id"] ?? '';
    $child_id = $_POST["child_id"] ?? '';

    $qr = "CALL add_user_relation(?, ?, ?)";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "iis", $parent_id, $child_id, $relation);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../tablegrid.php?tn=user_relations");
        exit();
    } else {
        echo "Failed to update user record.";
    }
}   
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Insert/style.css">
</head>

<body>
    <form method="post" action="Insert/insert_user_relations.php">
        <h2>Insert a New Record in User Relations</h2>

        <div class="form-columns">
            <div class="form-left">
                <div class="form-group">
                    <label>Relation Type:</label>
                    <select name="relation" onchange="fillp(this.value); fillc(this.value);" required>
                        <option value="">Select Relation</option>
                        <option value="Manufacture-Distributor">Manufacture-Distributor</option>
                        <option value="Manufacture-ASM">Manufacture-ASM</option>
                        <option value="ASM-Distributor">ASM-Distributor</option>
                        <option value="Distributor-Retailer">Distributor-Retailer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Child Name:</label>
                    <select name="child_id" id="child_id" required>
                        <option value="">Select child ID</option>
                    </select>

                </div>
            </div>

            <div class="form-right">
                <div class="form-group">
                    <label>Parent Name:</label>
                    <select name="parent_id" id="parent_id" required>
                        <option value="">Select parent ID</option>
                    </select>
                </div>
            </div>
        </div>

        <input type="submit" value="Insert">
    </form>

</body>

</html>