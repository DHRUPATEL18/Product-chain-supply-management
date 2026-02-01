<?php
session_start();
require_once '../notification_helper.php';

$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");
if (!$cn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $inserted = 0;
    $skipped = 0;

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'No file uploaded or upload error.';
    } else {
        $tmpName = $_FILES['file']['tmp_name'];

        // --- Skip mime_content_type check because Windows often marks CSV wrong ---
        if (($handle = fopen($tmpName, 'r')) !== false) {
            // Read header
            $header = fgetcsv($handle);
            if (!$header) {
                $errors[] = 'Empty CSV.';
            } else {
                // Normalize header names and add debug info
                $normalized = array_map(function($h){return strtolower(trim($h));}, $header);
                $expected = ['product category', 'product name', 'sku id', 'added by', 'status'];
                
                echo "<div style='color:#666; font-family:monospace; margin:10px 0;'>";
                echo "Found headers: " . implode(", ", $header) . "<br>";
                echo "Normalized headers: " . implode(", ", $normalized) . "<br>";
                echo "</div>";

                $pos = [];
                foreach ($expected as $col) {
                    $idx = array_search($col, $normalized);
                    if ($idx === false) {
                        $errors[] = "Missing column: $col";
                    } else {
                        $pos[$col] = $idx;
                    }
                }

                if (empty($errors)) {
                    $categoryCache = [];
                    $userCache = [];

                    while (($row = fgetcsv($handle)) !== false) {
                        // Skip blank lines
                        $isEmpty = true;
                        foreach ($row as $cell) {
                            if (trim($cell) !== '') { $isEmpty = false; break; }
                        }
                        if ($isEmpty) { continue; }

                        $categoryName = trim($row[$pos['product category']] ?? '');
                        $productName = trim($row[$pos['product name']] ?? '');
                        $skuId = trim($row[$pos['sku id']] ?? '');
                        $addedByNameOrId = trim($row[$pos['added by']] ?? '');
                        $status = trim($row[$pos['status']] ?? '');

                        if ($categoryName === '' || $productName === '' || $skuId === '' || $addedByNameOrId === '') {
                            echo "<div style='color:#f44336;'>Skipped row - Empty required field: Category='".htmlspecialchars($categoryName)."', Product='".htmlspecialchars($productName)."', SKU='".htmlspecialchars($skuId)."', AddedBy='".htmlspecialchars($addedByNameOrId)."'</div>";
                            $skipped++;
                            continue;
                        }

                        // --- Resolve category ---
                        $categoryId = $categoryCache[$categoryName] ?? null;
                        if ($categoryId === null) {
                            $stmt = mysqli_prepare($cn, "SELECT id FROM product_category WHERE category_name = ? LIMIT 1");
                            mysqli_stmt_bind_param($stmt, 's', $categoryName);
                            mysqli_stmt_execute($stmt);
                            $res = mysqli_stmt_get_result($stmt);
                            if ($cat = mysqli_fetch_assoc($res)) {
                                $categoryId = (int)$cat['id'];
                                $categoryCache[$categoryName] = $categoryId;
                            } else {
                                $addedByUserId = $_SESSION['user_id'] ?? null;
                                $create = mysqli_prepare($cn, "INSERT INTO product_category(category_name, status, added_by, date_of_creation) VALUES(?, 'Ongoing', ?, NOW())");
                                mysqli_stmt_bind_param($create, 'si', $categoryName, $addedByUserId);
                                if (mysqli_stmt_execute($create)) {
                                    $categoryId = mysqli_insert_id($cn);
                                    $categoryCache[$categoryName] = $categoryId;
                                } else {
                                    $skipped++;
                                    continue;
                                }
                            }
                        }

                        // --- Resolve Added By ---
                        $addedById = null;
                        if (ctype_digit($addedByNameOrId)) {
                            $addedById = (int)$addedByNameOrId;
                        } else {
                            $addedById = $userCache[$addedByNameOrId] ?? null;
                            if ($addedById === null) {
                                $stmt = mysqli_prepare($cn, "SELECT id FROM users WHERE name = ? LIMIT 1");
                                mysqli_stmt_bind_param($stmt, 's', $addedByNameOrId);
                                mysqli_stmt_execute($stmt);
                                $res = mysqli_stmt_get_result($stmt);
                                if ($u = mysqli_fetch_assoc($res)) {
                                    $addedById = (int)$u['id'];
                                    $userCache[$addedByNameOrId] = $addedById;
                                } else {
                                    $skipped++;
                                    continue;
                                }
                            }
                        }

                        // Default status
                        if ($status === '') { $status = 'Ongoing'; }

                        // --- Duplicate SKU check ---
                        $skuStmt = mysqli_prepare($cn, "SELECT id FROM products WHERE sku_id = ? UNION SELECT id FROM sold_products WHERE sku_id = ? LIMIT 1");
                        mysqli_stmt_bind_param($skuStmt, 'ss', $skuId, $skuId);
                        mysqli_stmt_execute($skuStmt);
                        $skuRes = mysqli_stmt_get_result($skuStmt);
                        if (mysqli_fetch_assoc($skuRes)) {
                            echo "<div style='color:#f44336;'>Skipped row - Duplicate SKU ID: ".htmlspecialchars($skuId)."</div>";
                            $skipped++;
                            continue;
                        }

                        // --- Insert product ---
                        $ins = mysqli_prepare($cn, "INSERT INTO products(product_category_id, product_name, sku_id, added_by, status, date_of_creation) VALUES(?, ?, ?, ?, ?, NOW())");
                        mysqli_stmt_bind_param($ins, 'issis', $categoryId, $productName, $skuId, $addedById, $status);
                        if (mysqli_stmt_execute($ins)) {
                            $inserted++;
                        } else {
                            $skipped++;
                        }
                    }
                    fclose($handle);
                }
            }
        } else {
            $errors[] = 'Unable to read uploaded file.';
        }
    }

    // --- Summary Output ---
    $summary = "Imported: $inserted, Skipped: $skipped";
    if ($inserted > 0) {
        $message = $inserted . " product(s) imported by " . ($_SESSION['name'] ?? 'Unknown');
        createNotification($message, 'product', $_SESSION['user_id'] ?? null, $_SESSION['role'] ?? null, 'products', null);
    }

    echo "<div>";
    if (!empty($errors)) {
        echo "<div style='color:#f44336; margin-bottom:10px;'><strong>Errors:</strong><br>" . htmlspecialchars(implode(' | ', $errors)) . "</div>";
    }
    echo "<div style='color:#1f1f1f; margin-bottom:10px;'><strong>Result:</strong> " . htmlspecialchars($summary) . "</div>";
    echo "<div style='text-align:right;'>
            <button onclick=\"closeModal(); dbload('products');\" style='padding:8px 14px; background:#1f1f1f; color:#fff; border:none; border-radius:4px; cursor:pointer;'>Close</button>
        </div>";
    echo "</div>";
    exit;
}
?>

<!-- Upload form -->
<h2 style="text-align:center; margin-top:0;">Import Products from Excel (CSV)</h2>
<form method="post" action="Insert/import_products.php" enctype="multipart/form-data" class="two-column-form" onsubmit="return importProductsSubmit(event, this)">
    <div class="form-group" style="grid-column: span 2;">
        <label>CSV File:</label>
        <input type="file" name="file" accept=".csv" required>
        <small>Columns required: Product Category, Product Name, SKU ID, Added By, Status.<br>ID and Date are auto-generated. Save Excel as CSV (UTF-8).</small>
    </div>
    <div style="grid-column: span 2; text-align: center; margin-top: 20px;">
        <button type="submit" style="padding:10px 20px; background-color:#1f1f1f; color:white; border:none; border-radius:6px;">Upload & Import</button>
    </div>
</form>

<link rel="stylesheet" href="Insert/style.css">
