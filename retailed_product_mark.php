<?php
session_start();
header('Content-Type: application/json');

$response = [ 'success' => false ];

try {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        throw new Exception('Unauthorized');
    }
    if ($_SESSION['role'] !== 'Retailer') {
        throw new Exception('Only Retailers can mark products as sold');
    }

    $cn = mysqli_connect('localhost', 'root', '', 'pragmanx_onelife_distributor');
    if (!$cn) {
        throw new Exception('DB connection failed');
    }

    // Ensure table exists with price column
    $createSql = "CREATE TABLE IF NOT EXISTS retailed_product (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NULL,
        product_name VARCHAR(255) NOT NULL,
        sku_id VARCHAR(100) NULL,
        retailer_id INT NOT NULL,
        price DECIMAL(10,2) NULL,
        source_type VARCHAR(32) NULL,
        source_id INT NULL,
        sold_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (retailer_id),
        INDEX (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    mysqli_query($cn, $createSql);
    // Add price column if missing (avoid duplicate-column error)
    $__col = mysqli_query($cn, "SHOW COLUMNS FROM retailed_product LIKE 'price'");
    if ($__col && mysqli_num_rows($__col) === 0) {
        mysqli_query($cn, "ALTER TABLE retailed_product ADD COLUMN price DECIMAL(10,2) NULL AFTER retailer_id");
    }
    // Add source_type and source_id if missing
    $__col2 = mysqli_query($cn, "SHOW COLUMNS FROM retailed_product LIKE 'source_type'");
    if ($__col2 && mysqli_num_rows($__col2) === 0) {
        mysqli_query($cn, "ALTER TABLE retailed_product ADD COLUMN source_type VARCHAR(32) NULL AFTER price");
    }
    $__col3 = mysqli_query($cn, "SHOW COLUMNS FROM retailed_product LIKE 'source_id'");
    if ($__col3 && mysqli_num_rows($__col3) === 0) {
        mysqli_query($cn, "ALTER TABLE retailed_product ADD COLUMN source_id INT NULL AFTER source_type");
    }

    $product_id = isset($_POST['product_id']) && $_POST['product_id'] !== '' ? intval($_POST['product_id']) : null;
    $product_name = mysqli_real_escape_string($cn, (string)($_POST['product_name'] ?? ''));
    $sku_id = mysqli_real_escape_string($cn, (string)($_POST['sku_id'] ?? ''));
    $price = isset($_POST['price']) ? floatval($_POST['price']) : null;

    $source_type = mysqli_real_escape_string($cn, (string)($_POST['source_type'] ?? ''));
    $source_id = isset($_POST['source_id']) ? intval($_POST['source_id']) : 0;

    if ($product_name === '') {
        throw new Exception('Missing product name');
    }

    $retailer_id = intval($_SESSION['user_id']);

    // Insert retailed record
    $ins = mysqli_prepare($cn, "INSERT INTO retailed_product (product_id, product_name, sku_id, retailer_id, price, source_type, source_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($ins, 'issidsi', $product_id, $product_name, $sku_id, $retailer_id, $price, $source_type, $source_id);
    if (!mysqli_stmt_execute($ins)) {
        throw new Exception('Insert failed');
    }

    // Decrement stock: if assigned -> reduce par.quantity by 1; if approved_request -> reduce requested_products.quantity by 1
    if ($source_type === 'assigned' && $source_id > 0) {
        $upd = "UPDATE product_assigned_retailer par SET par.quantity = GREATEST(par.quantity - 1, 0) WHERE par.id = $source_id";
        mysqli_query($cn, $upd);
    } elseif ($source_type === 'approved_request' && $source_id > 0) {
        $upd = "UPDATE requested_products rp SET rp.quantity = GREATEST(COALESCE(rp.quantity,0) - 1, 0) WHERE rp.id = $source_id";
        mysqli_query($cn, $upd);
    }

    $response['success'] = true;
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
