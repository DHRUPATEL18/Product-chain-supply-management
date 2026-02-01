<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

if (isset($_GET['batch_id'])) {
    $batch_id = $_GET['batch_id'];

    $sql = "
    SELECT 
        sp.id,
        sp.product_id,
        sp.sku_id,
        sp.sold_by,
        sp.sold_at,
        pad.batch_id,
        u.name AS distributor_name
    FROM sold_products sp
    LEFT JOIN product_assigned_dist pad 
        ON sp.product_id = pad.product_id
    LEFT JOIN batch_distributor bd 
        ON pad.batch_id = bd.id
    LEFT JOIN users u 
        ON bd.assigned_to = u.id
    WHERE pad.batch_id = '$batch_id'
    ORDER BY sp.id ASC;
    ";

    $res = mysqli_query($cn, $sql);

    if ($res && mysqli_num_rows($res) > 0) {
        $firstRow = mysqli_fetch_assoc($res);

        echo "<h3>Products report for Batch: <strong>$batch_id</strong> - Distributor: <strong>" . $firstRow['distributor_name'] . "</strong></h3>";
        echo "<table border='1' cellpadding='6' cellspacing='0'>
                <tr>
                    <th>ID</th>
                    <th>Product ID</th>
                    <th>SKU ID</th>
                    <th>Sold By</th>
                    <th>Sold At</th>
                </tr>";

        echo "<tr>";
        echo "<td>" . $firstRow['id'] . "</td>";
        echo "<td>" . $firstRow['product_id'] . "</td>";
        echo "<td>" . $firstRow['sku_id'] . "</td>";
        echo "<td>" . $firstRow['sold_by'] . "</td>";
        echo "<td>" . $firstRow['sold_at'] . "</td>";
        echo "</tr>";

        while ($row = mysqli_fetch_assoc($res)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['product_id'] . "</td>";
            echo "<td>" . $row['sku_id'] . "</td>";
            echo "<td>" . $row['sold_by'] . "</td>";
            echo "<td>" . $row['sold_at'] . "</td>";
            echo "</tr>";
        }

        echo "</table><br>";


        echo '<div style="display: flex; gap: 20px; align-items: center; margin-top: 20px;">';

        echo "
        <a href='Getreport/download_csv.php?batch_id=$batch_id' class='btn-report' style='padding:8px 14px; background:#007BFF; color:white; text-decoration:none; border-radius:5px;'>
            <i class='bx bx-download'></i> Get Report
        </a>";

        echo '
        <a href="../tablegrid.php" onclick="window.parent.generateReport(); return false;" style="padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;">Back to Report</a>';

        echo '</div>';

    } else {
        echo "<p>No sold products found for this batch.</p>";
    }
} else {
    echo "<p>No batch selected.</p>";
}
