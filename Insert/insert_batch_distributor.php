
<?php
session_start();
require_once '../notification_helper.php';

$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $assigned_by = $_POST["assigned_by"] ?? '';
    $assigned_to = $_POST["assigned_to"] ?? '';
    $status = $_POST["status"] ?? '';

    $qr = "CALL add_batch_distributor(?,?,?)";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "iis", $assigned_by, $assigned_to, $status);

    if (mysqli_stmt_execute($stmt)) {
        $batch_id = mysqli_insert_id($cn);
        
        // Get manufacturer name for better notification
        $manufacturer_query = "SELECT name FROM users WHERE id = ?";
        $manufacturer_stmt = mysqli_prepare($cn, $manufacturer_query);
        mysqli_stmt_bind_param($manufacturer_stmt, "i", $assigned_by);
        mysqli_stmt_execute($manufacturer_stmt);
        $manufacturer_result = mysqli_stmt_get_result($manufacturer_stmt);
        $manufacturer_name = "Manufacturer ID " . $assigned_by;
        if ($manufacturer_row = mysqli_fetch_assoc($manufacturer_result)) {
            $manufacturer_name = $manufacturer_row['name'];
        }
        
        // Create notification for the distributor
        $distributorMessage = "A new batch (ID: {$batch_id}) has been assigned to you by {$manufacturer_name} (Manufacturer)";
        createNotification($distributorMessage, 'batch', $assigned_to, 'Distributor', 'batch_distributor', $batch_id);
        
        // Also create a general notification for the manufacturer (optional)
        $manufacturerMessage = "New batch (ID: {$batch_id}) assigned to Distributor ID {$assigned_to}";
        createNotification($manufacturerMessage, 'batch', $_SESSION['user_id'], $_SESSION['role'], 'batch_distributor', $batch_id);
        
        header("Location: ../tablegrid.php?table=batch_distributor");
        exit();
    } else {
        echo "❌ Failed to insert record. Error: " . mysqli_error($cn);
    }
}
?>