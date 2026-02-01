<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$sql_qr = "SELECT * FROM `states`";
$res = mysqli_query($cn, $sql_qr);


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $state_id = $_POST["state_id"] ?? '';
    $city_name = $_POST["city_name"] ?? '';
    $status = $_POST["status"] ?? '';

    $qr = "CALL add_city(?,?,?)";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "iss", $state_id, $city_name, $status);


    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../tablegrid.php?tn=city");
        exit();
    } else {
        echo "Failed to update record.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>State-City Dropdown</title>
    <link rel="stylesheet" href="Insert/style.css">
</head>

<body>
    <form method="post" action="Insert/insert_city.php">
        <h2>Insert a New Record in City</h2>

        <div class="form-columns">
            <div class="form-left">
                <div class="form-group">
                    <label>State ID:</label>
                    <select name="state_id" id="state_id" required>
                        <option value="">Select State</option>
                        <?php
                        while ($row = mysqli_fetch_array($res)) {
                            echo '<option value="'.$row['id'].'">'.$row['id']." - ".$row['name'].'</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status:</label>
                    <input type="text" name="status" required>
                </div>
            </div>

            <div class="form-right">
                <div class="form-group">
                    <label>City Name:</label>
                    <input type="text" name="city_name" required>
                </div>
            </div>
        </div>

        <input type="submit" value="Insert">
    </form>
</body>

</html>