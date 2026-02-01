<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$rtable = "";
$restable = false;

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['ret'])) {
    $ret_name = $_GET['ret'] ?? '';
    $s_date = $_GET['s_date'] ?? '';
    $e_date = $_GET['e_date'] ?? '';

    if (!empty($s_date) && !empty($e_date)) {
        $rtable = "
        SELECT 
            bd.id AS batch_id,
            u.name AS retailer_name,
            bd.status,
            bd.assigned_at AS assigned_date
        FROM batch_retailer bd
        LEFT JOIN users u ON bd.assigned_to = u.id
        WHERE bd.assigned_to = '$ret_name'
        AND DATE(bd.assigned_at) BETWEEN '$s_date' AND '$e_date'
        ORDER BY bd.id ASC
        ";
    } else {
        $rtable = "
        SELECT 
            bd.id AS batch_id,
            u.name AS retailer_name,
            bd.status,
            bd.assigned_at AS assigned_date
        FROM batch_retailer bd
        LEFT JOIN users u ON bd.assigned_to = u.id
        WHERE bd.assigned_to = '$ret_name'
        ORDER BY bd.id ASC
        ";
    }

    $restable = mysqli_query($cn, $rtable);

    if ($restable && mysqli_num_rows($restable) > 0) {
        echo "<table border='1' cellpadding='8' cellspacing='0'>
            <tr>
                <th>Batch ID</th>
                <th>Retailer Name</th>
                <th>Status</th>
                <th>Assigned Date</th>
                <th>Action</th>
            </tr>";

        while ($row = mysqli_fetch_assoc($restable)) {
            echo "<tr>";
            echo "<td>" . $row['batch_id'] . "</td>";
            echo "<td>" . $row['retailer_name'] . "</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "<td>" . $row['assigned_date'] . "</td>";
            echo "<td><button class='btna btn-view' onclick='dbloadrdis(" . $row['batch_id'] . ")'><i class='fas fa-eye'></i></button></td>";
            echo "</tr>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<p>No data found.</p>";
    }
}
