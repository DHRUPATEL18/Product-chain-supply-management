<?php
session_start();
header('Content-Type: application/json');

$resp = ['success' => false];

try {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        throw new Exception('Unauthorized');
    }

    $role = $_SESSION['role'];
    if ($role !== 'Retailer' && $role !== 'Distributor' && $role !== 'Manufacture' && $role !== 'Area Sales Manager') {
        throw new Exception('Forbidden');
    }

    $cn = mysqli_connect('localhost', 'root', '', 'pragmanx_onelife_distributor');
    if (!$cn) throw new Exception('DB connection failed');

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if ($id <= 0) throw new Exception('Invalid id');

    // Fetch retailed record
    $q = mysqli_query($cn, "SELECT * FROM retailed_product WHERE id = " . $id . " LIMIT 1");
    $row = $q ? mysqli_fetch_assoc($q) : null;
    if (!$row) throw new Exception('Record not found');

    // Authorization: Retailer can revert ONLY their own retailed items
    if ($role === 'Retailer') {
        $currentUserId = intval($_SESSION['user_id'] ?? 0);
        if (intval($row['retailer_id'] ?? 0) !== $currentUserId) {
            throw new Exception('Forbidden: not your sale');
        }
    }

    $sourceType = (string)($row['source_type'] ?? '');
    $sourceId = intval($row['source_id'] ?? 0);

    // Begin simple transaction
    mysqli_begin_transaction($cn);

    // Restore stock based on source
    if ($sourceType === 'assigned' && $sourceId > 0) {
        $upd = "UPDATE product_assigned_retailer SET quantity = quantity + 1 WHERE id = $sourceId";
        if (!mysqli_query($cn, $upd)) { throw new Exception('Failed restoring assigned stock'); }
    } elseif ($sourceType === 'approved_request' && $sourceId > 0) {
        $upd = "UPDATE requested_products SET quantity = COALESCE(quantity,0) + 1 WHERE id = $sourceId";
        if (!mysqli_query($cn, $upd)) { throw new Exception('Failed restoring requested stock'); }
    }

    // Delete retailed record
    if (!mysqli_query($cn, "DELETE FROM retailed_product WHERE id = $id")) {
        throw new Exception('Failed deleting retailed record');
    }

    mysqli_commit($cn);
    $resp['success'] = true;
} catch (Exception $e) {
    if (isset($cn)) { mysqli_rollback($cn); }
    $resp['error'] = $e->getMessage();
}

echo json_encode($resp);
?>


