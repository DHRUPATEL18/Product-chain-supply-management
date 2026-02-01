<?php
require_once '../auth_check.php';

// Connection and data fetch
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");
$sql_qr = "SELECT * FROM `users` WHERE role = 'Distributor'";
$res = mysqli_query($cn, $sql_qr);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>GET - Report</title>
    <link rel="stylesheet" href="Getreport/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <div id="rf">
        <form method="GET">
            <h3>Report</h3>
            <label for="dist">Select Distributor</label>
            <select name="dist" id="dist">
                <option value="">Select Distributor</option>
                <?php
                while ($row = mysqli_fetch_array($res)) {
                    $selected = (isset($_GET['dist']) && $_GET['dist'] == $row['id']) ? 'selected' : '';
                    echo '<option value="' . $row['id'] . '" ' . $selected . '>' . $row['id'] . " - " . $row['name'] . '</option>';
                }
                ?>
            </select>

            <label for="s_date">Start Date</label>
            <input type="date" name="s_date" id="s_date">

            <label for="e_date">End Date</label>
            <input type="date" name="e_date" id="e_date">

            <button type="button" onclick="rload()">Submit</button>
        </form>

        <div class="rgrid" id="rg"></div>
    </div>
</body>

</html>