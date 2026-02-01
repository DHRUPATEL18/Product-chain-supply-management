    <?php
    session_start();
    $cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

    if (isset($_GET['batch_id'])) {
        $batch_id = $_GET['batch_id'];

        $info_sql = "
            SELECT u.name AS distributor_name
            FROM product_assigned_dist pad
            LEFT JOIN batch_distributor bd ON pad.batch_id = bd.id
            LEFT JOIN users u ON bd.assigned_to = u.id
            WHERE pad.batch_id = '$batch_id'
            LIMIT 1
        ";
        $info_res = mysqli_query($cn, $info_sql);
        $distributor_name = "N/A";
        if ($info_res && $row = mysqli_fetch_assoc($info_res)) {
            $distributor_name = $row['distributor_name'];
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="sold_products_batch_' . $batch_id . '.csv"');

        $output = fopen('php://output', 'w');

        fputcsv($output, ["Report for Batch: $batch_id - Distributor: $distributor_name"]);
        fputcsv($output, []);
        fputcsv($output, ['ID', 'Product ID', 'SKU ID', 'Sold By', 'Sold At']);

        $sql = "
            SELECT 
                sp.id,
                sp.product_id,
                sp.sku_id,
                sp.sold_by,
                sp.sold_at
            FROM sold_products sp
            LEFT JOIN product_assigned_dist pad ON sp.product_id = pad.product_id
            LEFT JOIN batch_distributor bd ON pad.batch_id = bd.id
            LEFT JOIN users u ON bd.assigned_to = u.id
            WHERE pad.batch_id = '$batch_id'
            ORDER BY sp.id ASC;
        ";

        $res = mysqli_query($cn, $sql);
        while ($row = mysqli_fetch_assoc($res)) {
            fputcsv($output, [$row['id'], $row['product_id'], $row['sku_id'], $row['sold_by'], $row['sold_at']]);
        }

        fclose($output);
        mysqli_close($cn);
        exit;
    } else {
        echo "No batch ID provided.";
    }
    ?>
