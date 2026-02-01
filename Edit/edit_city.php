<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$id = intval($_COOKIE["edit_id"] ?? 0);

if ($id === 0) {
    echo "Invalid ID.";
    exit();
}

$sql_qr = "SELECT * FROM `states`";
$res = mysqli_query($cn, $sql_qr);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $state_id = $_POST["state_id"] ?? '';
    $city_name = $_POST["city_name"] ?? '';
    $status = $_POST["status"] ?? '';

    $qr = "UPDATE city SET state_id = ?, city_name = ?, status = ? WHERE id = ?";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "issi", $state_id, $city_name, $status, $id);


    if (mysqli_stmt_execute($stmt)) {
        setcookie("edit_id", "", time() - 3600);
        header("Location: ../tablegrid.php?tn=city");
        exit();
    } else {
        echo "Failed to update record.";
    }
}

$qr = "SELECT * FROM city WHERE id = ?";
$stmt = mysqli_prepare($cn, $qr);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
?>

<link rel="stylesheet" href="style.css">

<form method="post">
    <h2>Edit City</h2>
    <div class="form-columns">
        <div class="form-left">
            <div class="form-group">
                <label>State ID:</label>
                <select name="state_id" id="state_id" required>
                <?php
                mysqli_data_seek($res, 0);
                while ($s = mysqli_fetch_array($res)) {
                    if ($s['id'] == $row['state_id']) {
                        echo '<option value="' . $s['id'] . '" selected>' . $s['id'] . ' - ' . $s['name'] . '</option>';
                        break;
                    }
                }

                mysqli_data_seek($res, 0);
                while ($s = mysqli_fetch_array($res)) {
                    if ($s['id'] == $row['state_id'])
                        continue;
                    echo '<option value="' . $s['id'] . '">' . $s['id'] . ' - ' . $s['name'] . '</option>';
                }
                ?>
                </select>
            </div>

            <div class="form-group">
                <label>City Name:</label>
                <input type="text" name="city_name" value="<?= $row['city_name'] ?>">
            </div>

            <div class="form-group">
                <label>Status:</label>
                <input type="text" name="status" value="<?= $row['status'] ?>">
            </div>
        </div>
    </div>
    <input type="submit" value="Update">
</form>