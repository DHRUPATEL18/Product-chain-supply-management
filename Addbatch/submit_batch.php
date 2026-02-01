<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");
require_once '../notification_helper.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer-6.10.0/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer-6.10.0/src/SMTP.php';
require __DIR__ . '/../PHPMailer-6.10.0/src/Exception.php';
require __DIR__ . '/../TCPDF-main/tcpdf.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    die("User not logged in");
}

$assigned_by = $_SESSION['user_id'];
$role = $_SESSION['role'];
$flow = $_GET['flow'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["user_id"], $_POST["product_ids"])) {
    $assigned_to = $_POST["user_id"];
    $products = $_POST["product_ids"];

    if ($flow === "manu_to_dist") {
        $batch_table = "batch_distributor";
        $assign_table = "product_assigned_dist";
        $trigger_table = "products";
    } elseif ($flow === "dist_to_retailer") {
        $batch_table = "batch_retailer";
        $assign_table = "product_assigned_retailer";
        $trigger_table = "product_assigned_dist";
    } else {
        die("Invalid flow");
    }

    // Insert batch record
    $query = "INSERT INTO `$batch_table` (assigned_by, assigned_to, assigned_at, status)
              VALUES ('$assigned_by', '$assigned_to', NOW(), 'Ongoing')";
    if (mysqli_query($cn, $query)) {
        $batch_id = mysqli_insert_id($cn);

        $product_ids_str = implode(',', array_map('intval', $products));
        $product_list_html = '';
        $product_list_text = '';

        if ($role === "Manufacture") {
            // Manufacture → Distributor
            $product_list_res = mysqli_query($cn, "SELECT product_name, sku_id FROM products WHERE id IN ($product_ids_str)");
            while ($prod = mysqli_fetch_assoc($product_list_res)) {
                $product_list_html .= "<tr><td>{$prod['product_name']}</td><td>{$prod['sku_id']}</td></tr>";
                $product_list_text .= "{$prod['product_name']} (SKU: {$prod['sku_id']})\n";
            }
        } else {
            // Distributor → Retailer
            $pad_ids = implode(',', array_map('intval', $products));
            $res = mysqli_query($cn, "
                SELECT p.id AS product_id, p.product_name, p.sku_id 
                FROM product_assigned_dist pad
                JOIN products p ON pad.product_id = p.id
                WHERE pad.id IN ($pad_ids)
            ");
            while ($prod = mysqli_fetch_assoc($res)) {
                $product_list_html .= "<tr><td>{$prod['product_name']}</td><td>{$prod['sku_id']}</td></tr>";
                $product_list_text .= "{$prod['product_name']} (SKU: {$prod['sku_id']})\n";
            }
        }

        // Insert products into assigned table and delete from source
        if ($role === "Manufacture") {
            foreach ($products as $product_id) {
                $product_id = intval($product_id);
                mysqli_query($cn, "INSERT INTO `$assign_table` (batch_id, product_id, quantity, assigned_at, status)
                                   VALUES ('$batch_id', '$product_id', 1, NOW(), 'Ongoing')");
                mysqli_query($cn, "DELETE FROM `$trigger_table` WHERE id = '$product_id'");
            }
        } else {
            foreach ($products as $pad_id) {
                $pad_id = intval($pad_id);
                $res = mysqli_query($cn, "SELECT product_id FROM product_assigned_dist WHERE id = '$pad_id'");
                if ($row = mysqli_fetch_assoc($res)) {
                    $real_product_id = intval($row['product_id']);
                    mysqli_query($cn, "INSERT INTO `$assign_table` (batch_id, product_id, quantity, assigned_at, status)
                                       VALUES ('$batch_id', '$real_product_id', 1, NOW(), 'Ongoing')");
                    mysqli_query($cn, "DELETE FROM `$trigger_table` WHERE id = '$pad_id'");
                }
            }
        }

        // -------- Email & WhatsApp Notifications --------
        if ($role === "Manufacture") {
            $distributor_res = mysqli_query($cn, "SELECT name, email, contact FROM users WHERE id = '$assigned_to'");
            $distributor = mysqli_fetch_assoc($distributor_res);
            if (!empty($distributor['email'])) {
                $pdf = new TCPDF();
                $pdf->AddPage();
                $pdf->SetFont('helvetica', '', 12);
                $html = "<h3>Batch ID: $batch_id</h3><p>Distributor: {$distributor['name']}</p>
                         <table border='1' cellpadding='4'><tr><th>Product Name</th><th>SKU ID</th></tr>$product_list_html</table>";
                $pdf_path = __DIR__ . "/batch_$batch_id.pdf";
                $pdf->writeHTML($html, true, false, true, false, '');
                $pdf->Output($pdf_path, 'F');

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'dhrupatel090@gmail.com';
                    $mail->Password = 'choo uqeu ousu foqj'; // App Password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->setFrom('dhrupatel090@gmail.com', 'Pragma Distributor');
                    $mail->addAddress($distributor['email'], $distributor['name']);
                    $mail->isHTML(true);
                    $mail->Subject = "New Batch Assigned (Batch ID: $batch_id)";
                    $mail->Body = "Hi <strong>{$distributor['name']}</strong>,<br><br>
                                   A new batch has been assigned to you. Please find the attached PDF.<br><br>Regards,<br>Pragma Team";
                    $mail->addAttachment($pdf_path);
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Email error: {$mail->ErrorInfo}");
                }
            }

            $distributorMessage = "A new batch (ID: {$batch_id}) with " . count($products) . " products has been assigned to you by " . $_SESSION['name'] . " (Manufacturer)";
            createNotification($distributorMessage, 'batch', $distributor_id, 'Distributor', 'batch_distributor', $batch_id);

            // Also create a general notification for the manufacturer (optional)
            $manufacturerMessage = "Batch " . $batch_id . " assigned to " . $distributor['name'] . " with " . count($products) . " products";
            createNotification($manufacturerMessage, 'batch', $_SESSION['user_id'], $_SESSION['role'], 'batch_distributor', $batch_id);

            if (!empty($distributor['contact'])) {
                $phone = '91' . preg_replace('/\D/', '', $distributor['contact']);
                $encoded_msg = urlencode("Hi {$distributor['name']},\n\nNew Batch Assigned (ID: $batch_id)\n\n$product_list_text\n\n-Pragma Team");
                echo "<script>
                        window.open('https://web.whatsapp.com/send/?phone=$phone&text=$encoded_msg&type=phone_number&app_absent=0', '_blank');
                        setTimeout(() => { window.location.href = '../tablegrid.php?success=1'; }, 8000);
                      </script>";
                exit;
            }
        }

        if ($role === "Distributor") {
            $retailer_res = mysqli_query($cn, "SELECT name, email, contact FROM users WHERE id = '$assigned_to'");
            $retailer = mysqli_fetch_assoc($retailer_res);
            if (!empty($retailer['email'])) {
                $pdf = new TCPDF();
                $pdf->AddPage();
                $pdf->SetFont('helvetica', '', 12);
                $html = "<h3>Batch ID: $batch_id</h3><p>Retailer: {$retailer['name']}</p>
                         <table border='1' cellpadding='4'><tr><th>Product Name</th><th>SKU ID</th></tr>$product_list_html</table>";
                $pdf_path = __DIR__ . "/batch_$batch_id.pdf";
                $pdf->writeHTML($html, true, false, true, false, '');
                $pdf->Output($pdf_path, 'F');

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'dhrupatel090@gmail.com';
                    $mail->Password = 'choo uqeu ousu foqj'; // App Password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->setFrom('dhrupatel090@gmail.com', 'Pragma Distributor');
                    $mail->addAddress($retailer['email'], $retailer['name']);
                    $mail->isHTML(true);
                    $mail->Subject = "New Batch Assigned (Batch ID: $batch_id)";
                    $mail->Body = "Hi <strong>{$retailer['name']}</strong>,<br><br>
                                   A new batch has been assigned to you. Please find the attached PDF.<br><br>Regards,<br>Pragma Team";
                    $mail->addAttachment($pdf_path);
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Email error: {$mail->ErrorInfo}");
                }
            }
            if (!empty($retailer['contact'])) {
                $phone = '91' . preg_replace('/\D/', '', $retailer['contact']);
                $encoded_msg = urlencode("Hi {$retailer['name']},\n\nNew Batch Assigned (ID: $batch_id)\n\n$product_list_text\n\n-Pragma Team");
                echo "<script>
                        window.open('https://web.whatsapp.com/send/?phone=$phone&text=$encoded_msg&type=phone_number&app_absent=0', '_blank');
                        setTimeout(() => { window.location.href = '../tablegrid.php?success=1'; }, 8000);
                      </script>";
                exit;
            }
        }

        header("Location: ../tablegrid.php?success=1");
        exit;
    } else {
        echo "Error inserting batch: " . mysqli_error($cn);
    }
} else {
    echo "Invalid form submission.";
}
?>