<?php
// Database connection with error handling
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");
if (!$cn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Delete record function
function delete_row()
{
    global $cn;

    $table = $_GET['tn'] ?? '';
    $id = intval($_GET['del'] ?? 0);

    if (!$table) {
        echo "Invalid table name or ID.";
        return;
    }

    $sql_qr = "CALL delete_record(?, ?)";
    $stmt = mysqli_prepare($cn, $sql_qr);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $table, $id);
        if (mysqli_stmt_execute($stmt)) {
            showgrid();
        } else {
            echo "Error executing delete: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing delete: " . mysqli_error($cn);
    }
}

function showgrid()
{
    session_start();
    global $cn;

    // Validate session
    if (!isset($_SESSION['role']) || !isset($_SESSION['user_name'])) {
        echo "Session not valid. Please login again.";
        return;
    }

    $role = $_SESSION['role'];
    $user = $_SESSION['user_name'];
    $user_id = $_SESSION['user_id'];
    $tn = $_POST['tn'] ?? $_GET['tn'] ?? '';

    if (!$tn) {
        echo "No valid table name provided.";
        return;
    }

    // Get user name using prepared statement
    $name = '';
    $user_sql = "SELECT name FROM users WHERE username = ?";
    $stmt = mysqli_prepare($cn, $user_sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $user);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($user_row = mysqli_fetch_assoc($result)) {
            $name = $user_row['name'];
        }
        mysqli_stmt_close($stmt);
    }

    // Get manufacturer names using prepared statement
    $mnames = [];
    $muser_sql = "SELECT name FROM users WHERE role = ?";
    $stmt = mysqli_prepare($cn, $muser_sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $role);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $mnames[] = $row['name'];
        }
        mysqli_stmt_close($stmt);
    }

    $syntheticTables = ['stock', 'retailed_product'];

    if ($tn === "sold_products" && $role === "Distributor") {
        $sql = "SELECT * FROM sold_products WHERE sold_by = ?";
        $stmt = mysqli_prepare($cn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $name);
    } elseif ($tn === "sold_products" && $role === "Manufacture") {
        if (!empty($mnames)) {
            $placeholders = str_repeat('?,', count($mnames) - 1) . '?';
            $sql = "SELECT * FROM sold_products WHERE sold_by IN ($placeholders)";
            $stmt = mysqli_prepare($cn, $sql);
            $types = str_repeat('s', count($mnames));
            mysqli_stmt_bind_param($stmt, $types, ...$mnames);
        } else {
            $sql = "SELECT * FROM sold_products WHERE 1=0";
            $stmt = mysqli_prepare($cn, $sql);
        }
    } elseif (!in_array($tn, $syntheticTables, true)) {
        $sql = "SELECT * FROM $tn";
        $stmt = mysqli_prepare($cn, $sql);
    } else {
        // Synthetic table/view will be handled later; skip default SELECT
        $stmt = null;
    }

    if ($stmt) {
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
    } elseif (!in_array($tn, $syntheticTables, true)) {
        echo "Error preparing query: " . mysqli_error($cn);
        return;
    }

    $label = ucwords(str_replace('_', ' ', $tn));

    echo "<h3>Table Data: $label</h3>";
    echo '<div class="table-wrapper" style="overflow-x: auto; max-width: 100%;">';

    // 1. user_relations table
    if ($tn === "user_relations") {
        $sql = "
        SELECT 
            u2.id, 
            u1.name AS parent_name, 
            u3.name AS child_name,
            u2.relation
        FROM user_relations u2
        JOIN users u1 ON u1.id = u2.parent_id
        JOIN users u3 ON u3.id = u2.child_id
        ";

        $res = mysqli_query($cn, $sql);

        if ($res && mysqli_num_rows($res) > 0) {
            echo "<table style='width: 100%; min-width: 600px;'>
                <tr>
                    <th>Id</th>
                    <th>Parent Name</th>
                    <th>Child Name</th>
                    <th>Relation</th>
                    <th>Actions</th>
                </tr>";

            while ($row = mysqli_fetch_assoc($res)) {
                $id = htmlspecialchars($row['id']);
                $parent_name = htmlspecialchars($row['parent_name']);
                $child_name = htmlspecialchars($row['child_name']);
                $relation = htmlspecialchars($row['relation']);

                echo "<tr>
                    <td>{$id}</td>
                    <td>{$parent_name}</td>
                    <td>{$child_name}</td>
                    <td>{$relation}</td>
                    <td>
                        <button class='btna btn-edit' onclick=\"dbed($id, '$tn')\"><i class='fas fa-edit'></i></button>
                        <button class='btna btn-delete' onclick=\"dbdel($id, '$tn')\"><i class='fas fa-trash-alt'></i></button>
                        <button class='btna btn-view' onclick=\"dbview($id, '$tn')\"><i class='fas fa-eye'></i></button>
                    </td>
                  </tr>";
            }

            echo "</table>";
        } else {
            echo "No data found in user_relations.";
        }

        echo "<br><button class='btna btn-insert' onclick=\"dbinsert('$tn')\">Add On</button>";
        echo "</div>";
        return;
    }

    // 2. city table
    elseif ($tn === "city") {
        $sql = "
        SELECT 
            city.id,
            states.name AS state_name,
            city.city_name,
            city.status
        FROM city
        JOIN states ON city.state_id = states.id
        ";

        $res = mysqli_query($cn, $sql);

        if ($res && mysqli_num_rows($res) > 0) {
            echo "<table style='width: 100%; min-width: 600px;'>
                <tr>
                    <th>Id</th>
                    <th>State Name</th>
                    <th>City Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>";

            while ($row = mysqli_fetch_assoc($res)) {
                $id = htmlspecialchars($row['id']);
                $state_name = htmlspecialchars($row['state_name']);
                $city_name = htmlspecialchars($row['city_name']);
                $status = htmlspecialchars($row['status']);

                echo "<tr>
                    <td>{$id}</td>
                    <td>{$state_name}</td>
                    <td>{$city_name}</td>
                    <td>{$status}</td>
                    <td>
                        <button class='btna btn-edit' onclick=\"dbed($id, '$tn')\"><i class='fas fa-edit'></i></button>
                        <button class='btna btn-delete' onclick=\"dbdel($id, '$tn')\"><i class='fas fa-trash-alt'></i></button>
                        <button class='btna btn-view' onclick=\"dbview($id, '$tn')\"><i class='fas fa-eye'></i></button>
                    </td>
                  </tr>";
            }

            echo "</table>";
        } else {
            echo "No cities found.";
        }

        echo "<br><button class='btna btn-insert' onclick=\"dbinsert('$tn')\">Add City</button>";
        echo "</div>";
        return;
    }

    // 3. batch_distributor table and batch_retailer
    elseif ($tn === "batch_distributor" || $tn === "batch_retailer") {
        $sql = "
        SELECT 
            bd.id,
            ub.name AS assigned_by_name,
            ut.name AS assigned_to_name,
            bd.assigned_at,
            bd.status
        FROM $tn bd
        LEFT JOIN users ub ON bd.assigned_by = ub.id
        LEFT JOIN users ut ON bd.assigned_to = ut.id
        ";

        if ($role === 'Distributor' && $tn === "batch_retailer") {
            // Show only batches created by this distributor (assigned_by = distributor id)
            $sql .= " WHERE bd.assigned_by = " . intval($user_id);
        }

        if ($role === 'Distributor' && $tn === "batch_distributor") {
            // Show only batches assigned to this distributor (assigned_to = distributor id)
            $sql .= (strpos($sql, 'WHERE') === false ? ' WHERE ' : ' AND ') . " bd.assigned_to = " . intval($user_id);
        }

        $res = mysqli_query($cn, $sql);

        if ($res && mysqli_num_rows($res) > 0) {
            echo "<table style='width: 100%; min-width: 700px;'>
                <tr>
                    <th>Id</th>
                    <th>Assigned By</th>
                    <th>Assigned To</th>
                    <th>Assigned At</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>";

            while ($row = mysqli_fetch_assoc($res)) {
                $id = htmlspecialchars($row['id']);
                $assigned_by = htmlspecialchars($row['assigned_by_name']);
                $assigned_to = htmlspecialchars($row['assigned_to_name']);
                $assigned_at = htmlspecialchars($row['assigned_at']);
                $status = htmlspecialchars($row['status']);

                echo "<tr>
                    <td>{$id}</td>
                    <td>{$assigned_by}</td>
                    <td>{$assigned_to}</td>
                    <td>{$assigned_at}</td>
                    <td>{$status}</td>
                    <td>
                        <button class='btna btn-edit' onclick=\"dbed($id, '$tn')\"><i class='fas fa-edit'></i></button>
                        <button class='btna btn-delete' onclick=\"dbdel($id, '$tn')\"><i class='fas fa-trash-alt'></i></button>
                        <button class='btna btn-view' onclick=\"dbview($id, '$tn')\"><i class='fas fa-eye'></i></button>
                    </td>
                  </tr>";
            }

            echo "</table>";
            echo "<br> <button class='btna btn-insert' onclick=\"dbinsert('$tn')\">Add On</button>";
        } else {
            echo "No users found.";
        }

        echo "</div>";
        return;
    }

    // 4. product_assigned_dist table
    elseif ($tn === "product_assigned_dist") {
        $sql = "
        SELECT 
            pad.id,
            pad.batch_id,
            pad.product_id,
            COALESCE(p.product_name, sp.product_name, CONCAT('Product #', pad.product_id)) AS product_name,
            pad.quantity,
            pad.assigned_at,
            pad.status,
            bd.assigned_by,
            bd.assigned_to,
            m.name AS manufacturer_name,
            d.name AS distributor_name
        FROM product_assigned_dist pad
        LEFT JOIN products p ON pad.product_id = p.id
        LEFT JOIN (
            SELECT product_id, MAX(product_name) AS product_name
            FROM sold_products
            GROUP BY product_id
        ) sp ON sp.product_id = pad.product_id
        LEFT JOIN batch_distributor bd ON pad.batch_id = bd.id
        LEFT JOIN users m ON bd.assigned_by = m.id
        LEFT JOIN users d ON bd.assigned_to = d.id
        ";
        $order_by = " ORDER BY pad.batch_id ASC, pad.product_id ASC, pad.id ASC, pad.assigned_at ASC";
        if ($role === "Distributor") {
            $sql .= " WHERE bd.assigned_to = ?";
            $stmt = mysqli_prepare($cn, $sql . $order_by);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
        } else {
            $stmt = mysqli_prepare($cn, $sql . $order_by);
        }
        if ($stmt) {
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
        }
        if ($res && mysqli_num_rows($res) > 0) {
            $current_batch_id = null;
            while ($row = mysqli_fetch_assoc($res)) {
                $id = htmlspecialchars($row['id']);
                if ($row['batch_id'] !== $current_batch_id) {
                    if ($current_batch_id !== null) {
                        echo "</tbody></table><br>";
                    }
                    echo "<h3 style='color: #1f1f1f;'>📦 Batch ID: " . htmlspecialchars($row['batch_id']) .
                        " <span style='color: #555;'>(Manufacturer: " . htmlspecialchars($row['manufacturer_name']) .
                        ", Distributor: " . htmlspecialchars($row['distributor_name']) . ")</span></h3><br>";
                    echo "<table style='width: 100%; min-width: 800px;'>
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Assigned At</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>";
                    $current_batch_id = $row['batch_id'];
                }
                echo "<tr>
                    <td>{$id}</td>
                    <td>" . htmlspecialchars((string)($row['product_id'] ?? '')) . "</td>
                    <td>" . htmlspecialchars((string)($row['product_name'] ?? '')) . "</td>
                    <td>" . htmlspecialchars((string)($row['quantity'] ?? '')) . "</td>
                    <td>" . htmlspecialchars((string)($row['assigned_at'] ?? '')) . "</td>
                    <td>" . htmlspecialchars((string)($row['status'] ?? '')) . "</td>
                    <td>
                        <button class='btna btn-edit' onclick=\"dbed($id, '$tn')\"><i class='fas fa-edit'></i></button>
                        <button class='btna btn-delete' onclick=\"dbdel($id, '$tn')\"><i class='fas fa-trash-alt'></i></button>
                        <button class='btna btn-view' onclick=\"dbview($id, '$tn')\"><i class='fas fa-eye'></i></button>
                    </td>
                  </tr>";
            }
            echo "</tbody></table>";
            if ($role === "Distributor") {
                echo "<br>
                <button class='btna btn-insert' onclick=\"addtobatch('Distributor', 'product_assigned_dist')\">
                    <i class='fas fa-briefcase'></i> Add Batch
                </button>";
            }
        } else {
            echo "No assigned products found.";
        }
        echo "</div>";
        return;
    }

    // 5. product_assigned_retailer table
    elseif ($tn === "product_assigned_retailer") {
        if ($role === "Retailer") {
            $sql = "
        SELECT 
            par.id,
            par.batch_id,
            par.product_id,
            COALESCE(p.product_name, sp.product_name, CONCAT('Product #', par.product_id)) AS product_name,
            par.quantity,
            par.assigned_at,
            par.status,
            br.assigned_by,
            br.assigned_to,
            d.name AS distributor_name,
            r.name AS retailer_name
        FROM product_assigned_retailer par
        LEFT JOIN products p ON par.product_id = p.id
        LEFT JOIN (
            SELECT product_id, MAX(product_name) AS product_name
            FROM sold_products
            GROUP BY product_id
        ) sp ON sp.product_id = par.product_id
        LEFT JOIN batch_retailer br ON par.batch_id = br.id
        LEFT JOIN users d ON br.assigned_by = d.id
        LEFT JOIN users r ON br.assigned_to = r.id
        WHERE br.assigned_to = ?
        ORDER BY par.batch_id ASC, par.assigned_at ASC";
            $stmt = mysqli_prepare($cn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
        } elseif ($role === "Distributor") {
            $sql = "
        SELECT 
            par.id,
            par.batch_id,
            par.product_id,
            COALESCE(p.product_name, sp.product_name, CONCAT('Product #', par.product_id)) AS product_name,
            par.quantity,
            par.assigned_at,
            par.status,
            br.assigned_by,
            br.assigned_to,
            d.name AS distributor_name,
            r.name AS retailer_name
        FROM product_assigned_retailer par
        LEFT JOIN products p ON par.product_id = p.id
        LEFT JOIN (
            SELECT product_id, MAX(product_name) AS product_name
            FROM sold_products
            GROUP BY product_id
        ) sp ON sp.product_id = par.product_id
        LEFT JOIN batch_retailer br ON par.batch_id = br.id
        LEFT JOIN users d ON br.assigned_by = d.id
        LEFT JOIN users r ON br.assigned_to = r.id
        WHERE br.assigned_by = ?
        ORDER BY par.batch_id ASC, par.assigned_at ASC";
            $stmt = mysqli_prepare($cn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
        } else {
            // For Manufacture / Admin – see all assigned retailer products
            $sql = "
        SELECT 
            par.id,
            par.batch_id,
            par.product_id,
            COALESCE(p.product_name, sp.product_name, CONCAT('Product #', par.product_id)) AS product_name,
            par.quantity,
            par.assigned_at,
            par.status,
            br.assigned_by,
            br.assigned_to,
            d.name AS distributor_name,
            r.name AS retailer_name
        FROM product_assigned_retailer par
        LEFT JOIN products p ON par.product_id = p.id
        LEFT JOIN (
            SELECT product_id, MAX(product_name) AS product_name
            FROM sold_products
            GROUP BY product_id
        ) sp ON sp.product_id = par.product_id
        LEFT JOIN batch_retailer br ON par.batch_id = br.id
        LEFT JOIN users d ON br.assigned_by = d.id
        LEFT JOIN users r ON br.assigned_to = r.id
        ORDER BY par.batch_id ASC, par.assigned_at ASC";
            $stmt = mysqli_prepare($cn, $sql);
        }

        if ($stmt) {
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
        }

        if ($res && mysqli_num_rows($res) > 0) {
            $current_batch_id = null;
            while ($row = mysqli_fetch_assoc($res)) {
                $id = htmlspecialchars($row['id']);

                // Group by batch
                if ($row['batch_id'] !== $current_batch_id) {
                    if ($current_batch_id !== null) {
                        echo "</tbody></table><br>";
                    }
                    echo "<h3 style='color:#1f1f1f;'>📦 Batch ID: " . htmlspecialchars($row['batch_id']) .
                        " <span style='color:#555;'>(Distributor: " . htmlspecialchars($row['distributor_name'] ?? '') .
                        ", Retailer: " . htmlspecialchars($row['retailer_name'] ?? '') . ")</span></h3><br>";
                    echo "<table style='width:100%; min-width:800px;'>
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Assigned At</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>";
                    $current_batch_id = $row['batch_id'];
                }

                echo "<tr>
                <td>{$id}</td>
                <td>" . htmlspecialchars((string)($row['product_id'] ?? '')) . "</td>
                <td>" . htmlspecialchars((string)($row['product_name'] ?? '')) . "</td>
                <td>" . htmlspecialchars((string)($row['quantity'] ?? '')) . "</td>
                <td>" . htmlspecialchars((string)($row['assigned_at'] ?? '')) . "</td>
                <td>" . htmlspecialchars((string)($row['status'] ?? '')) . "</td>
                <td>
                    <button class='btna btn-edit' onclick=\"dbed($id, '$tn')\"><i class='fas fa-edit'></i></button>
                    <button class='btna btn-delete' onclick=\"dbdel($id, '$tn')\"><i class='fas fa-trash-alt'></i></button>
                    <button class='btna btn-view' onclick=\"dbview($id, '$tn')\"><i class='fas fa-eye'></i></button>
                </td>
            </tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "No assigned products found.";
        }
        echo "</div>";
        return;
    }

    // Synthetic: Retailer stock view (Assigned items + Approved requests)
    else if ($tn === 'stock') {
        if ($role !== 'Retailer') {
            echo "Only Retailers can view stock.";
            echo "</div>";
            return;
        }

        $retailer_id = intval($_SESSION['user_id'] ?? 0);

        // Assigned items to this retailer (from product_assigned_retailer)
        $assigned = mysqli_query($cn, "
            SELECT 
                par.id AS source_id,
                'assigned' AS source_type,
                par.product_id,
                p.product_name,
                par.quantity,
                par.assigned_at AS date_time,
                par.status,
                '' AS sku_id
            FROM product_assigned_retailer par
        JOIN products p ON par.product_id = p.id
            JOIN batch_retailer br ON par.batch_id = br.id
            WHERE br.assigned_to = '$retailer_id' AND par.quantity > 0
        ");

        // Approved requests for this retailer
        $approved = mysqli_query($cn, "
            SELECT 
                rp.id AS source_id,
                'approved_request' AS source_type,
                NULL AS product_id,
                rp.name AS product_name,
                rp.quantity,
                rp.date_time AS date_time,
                rp.status,
                '' AS sku_id
            FROM requested_products rp
            WHERE rp.retailer_id = '$retailer_id' AND rp.status = 'Approved' AND COALESCE(rp.quantity,0) > 0
        ");

        // Collect rows
        $rows = [];
        if ($assigned) { while ($r = mysqli_fetch_assoc($assigned)) { $rows[] = $r; } }
        if ($approved) { while ($r = mysqli_fetch_assoc($approved)) { $rows[] = $r; } }

        echo "<table style='width: 100%; min-width: 700px;'>
                <tr>
                    <th>Id</th>
                    <th>Source</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Date / Time</th>
                    <th>Action</th>
                </tr>";

        // Sort ascending by source_id
        usort($rows, function($a, $b) {
            $ai = intval($a['source_id'] ?? 0);
            $bi = intval($b['source_id'] ?? 0);
            if ($ai === $bi) return 0;
            return ($ai < $bi) ? -1 : 1;
        });

        $seq = 1;
        foreach ($rows as $row) {
            $displayId = intval($row['source_id'] ?? 0);
            if ($displayId <= 0) { $displayId = $seq; }
            $sourceRaw = strtolower((string)($row['source_type'] ?? ''));
            $sourceLabel = $sourceRaw === 'assigned' ? 'Assigned product' : ($sourceRaw === 'approved_request' ? 'Approved product' : ucfirst($sourceRaw));

            $safeName = htmlspecialchars($row['product_name'], ENT_QUOTES);
            $safeSourceType = htmlspecialchars($row['source_type'], ENT_QUOTES);
            $sourceId = intval($row['source_id'] ?? 0);

            echo "<tr>";
            echo "<td>" . htmlspecialchars($displayId) . "</td>";
            echo "<td>" . htmlspecialchars($sourceLabel) . "</td>";
            echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
            echo "<td><span style=\"color: #f44336; font-weight:700;\">Unsold</span></td>";
            echo "<td>" . htmlspecialchars($row['date_time']) . "</td>";
            echo "<td>
                <button onclick=\"markAsRetailed(" . intval($row['product_id'] ?? 0) . ", '{$safeName}', '', '{$safeSourceType}', {$sourceId})\" title='Mark as Sold' style=\"background-color:#455f96; color:white; border:none; padding:6px 10px; border-radius:4px; cursor:pointer;\"><i class='fas fa-check-circle'></i> Mark as Sold</button>
            </td>";
            echo "</tr>";
            $seq++;
        }

        echo "</table>";
        echo "</div>";
        return;
    }

    // Synthetic: Retailed products (market sales) with revert option
    else if ($tn === 'retailed_product') {
        // Ensure table exists (idempotent)
        mysqli_query($cn, "CREATE TABLE IF NOT EXISTS retailed_product (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NULL,
            product_name VARCHAR(255) NOT NULL,
            sku_id VARCHAR(100) NULL,
            retailer_id INT NOT NULL,
            price DECIMAL(10,2) NULL,
            sold_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX (retailer_id),
            INDEX (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Restrict visibility: Retailers see only their own retailed items
        $retailerWhere = '';
        if ($role === 'Retailer') {
            $safeUserId = intval($user_id);
            $retailerWhere = " WHERE rp.retailer_id = " . $safeUserId . " ";
        }

        $resRp = mysqli_query($cn, "
            SELECT rp.id, rp.product_id, rp.product_name, rp.price, u.name AS retailer_name, rp.sold_at
            FROM retailed_product rp
            LEFT JOIN users u ON rp.retailer_id = u.id
            " . $retailerWhere . "
            ORDER BY rp.id ASC
        ");

        echo "<table style='width: 100%; min-width: 700px;'>
                <tr>
                    <th>Id</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Retailer Name</th>
                    <th>Status</th>
                    <th>Sold At</th>
                    <th>Action</th>
                </tr>";

        while ($row = mysqli_fetch_assoc($resRp)) {
            $id = intval($row['id']);
            $pid = intval($row['product_id'] ?? 0);
            $pname = htmlspecialchars($row['product_name'], ENT_QUOTES);
            echo "<tr>";
            echo "<td>" . htmlspecialchars($id) . "</td>";
            echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
            echo "<td>" . htmlspecialchars(number_format((float)($row['price'] ?? 0), 2)) . "</td>";
            echo "<td>" . htmlspecialchars($row['retailer_name'] ?? '') . "</td>";
            echo "<td><span style=\"color: #4CAF50; font-weight:700;\">Sold</span></td>";
            echo "<td>" . htmlspecialchars($row['sold_at']) . "</td>";
            echo "<td>
                <button onclick=\"revertRetailed(" . $id . ", '" . $pid . "', '{$pname}', '', 0)\" title='Revert Sale' style=\"background-color:#795548; color:white; border:none; padding:6px 10px; border-radius:4px; cursor:pointer;\"><i class='fas fa-undo'></i> Revert</button>
            </td>";
            echo "</tr>";
        }

        echo "</table>";
        echo "</div>";
        return;
    }

    // 6. ASM Attendance Table
    else if ($tn === "asm_attendance") {
        $res = mysqli_query($cn, "SELECT * FROM $tn ORDER BY id ASC");
        $fields = mysqli_fetch_fields($res);

        echo "<table border='1' cellpadding='8'><tr>";
        foreach ($fields as $field) {
            $label = ucwords(str_replace('_', ' ', $field->name));
            echo "<th>{$label}</th>";
        }
        echo "<th>View in Map</th>";

        while ($row = mysqli_fetch_assoc($res)) {
            echo "<tr>";
            foreach ($fields as $field) {
                echo "<td>" . htmlspecialchars($row[$field->name]) . "</td>";
            }

            // Add View in Map button
            $location = urlencode($row['location']);
            echo "<td>
                <a href=\"View/view_single_asm_map.php?id={$row['id']}\" target=\"_blank\" title='View Single ASM Location' style=\"background-color:rgb(81, 78, 78); color: white; border: none; padding: 6px 10px; margin-right: 5px; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block;\">
                <i class='fas fa-map-marker-alt'></i> View Location
                </a>
                <a href=\"https://www.google.com/maps/search/?api=1&query={$location}\" target=\"_blank\" title='View in Google Maps' style=\"background-color: #cacaca; color: white; border: none; padding: 6px 10px; margin-right: 5px; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block;\">
                <i class='fas fa-external-link-alt'></i> Google Maps
                </a>
            </td>";

            echo "</tr>";
        }

        echo "</table>";

        // Add a button to view all ASM locations on the interactive map
        echo "<br><button onclick=\"window.open('View/view_asm_map.php', '_blank')\" title='View All ASM Locations' style='padding:10px 20px; background-color:#9C27B0; color:white; border:none; border-radius:4px; cursor:pointer; margin-right:10px;'>
        <i class='fas fa-map'></i> View All ASM Locations on Map
        </button>";
    }

    // (no custom handlers for inventory tables)

    // 9. Products
    else if ($tn === "products") {
        $sql = "
        SELECT 
            p.id, 
            pc.category_name AS product_category_name, 
            p.product_name, 
            p.sku_id, 
            p.added_by, 
            p.status, 
            p.date_of_creation 
        FROM products p
        JOIN product_category pc ON p.product_category_id = pc.id
        ORDER BY p.id ASC
        ";

        $res = mysqli_query($cn, $sql);

        if ($res && mysqli_num_rows($res) > 0) {
            echo "<table style='width: 100%; min-width: 800px;'>
        <tr>
            <th>Id</th>
            <th>Product Category</th>
            <th>Product Name</th>
            <th>SKU ID</th>
            <th>Added By</th>
            <th>Status</th>
            <th>Date of Creation</th>
            <th>Actions</th>
        </tr>";

            while ($row = mysqli_fetch_assoc($res)) {
                $id = htmlspecialchars($row['id']);
                $category = htmlspecialchars($row['product_category_name']);
                $name = htmlspecialchars($row['product_name']);
                $sku = htmlspecialchars($row['sku_id']);
                $added_by = htmlspecialchars($row['added_by']);
                $status = htmlspecialchars($row['status']);
                $date = htmlspecialchars($row['date_of_creation']);

            echo "<tr>
            <td>{$id}</td>
            <td>{$category}</td>
            <td>{$name}</td>
            <td>{$sku}</td>
            <td>{$added_by}</td>
            <td>{$status}</td>
            <td>{$date}</td>
            <td>
                <button class='btna btn-edit' onclick=\"dbed($id, '$tn')\"><i class='fas fa-edit'></i></button>
                <button class='btna btn-delete' onclick=\"dbdel($id, '$tn')\"><i class='fas fa-trash-alt'></i></button>
                <button class='btna btn-view' onclick=\"dbview($id, '$tn')\"><i class='fas fa-eye'></i></button>
                ";
                if ($role === 'Retailer') {
                    $safeName = htmlspecialchars($row['product_name'], ENT_QUOTES);
                    echo "<button class='btna' style=\"margin-left:6px; background:#ff9800; color:#fff;\" onclick=\"requestProduct($id, '$safeName')\"><i class='fas fa-paper-plane'></i> Request</button>";
                }
                echo "
            </td>
          </tr>";
            }

            echo "</table>";
        } else {
            echo "No products found.";
        }

        if ($role === "Manufacture") {
            echo "<br><button class='btna btn-insert' onclick=\"dbinsert('$tn')\">Add On</button>";

            echo "<button class='btna btn-insert' onclick=\"addtobatch('Manufacture', 'products')\">
                <i class='fas fa-briefcase'></i> Add Batch </button>";

            echo "<button onclick='openImportProducts()' title='Import from Excel' style='padding:10px 20px; background-color:#3f51b5; color:white; border:none; border-radius:4px; cursor:pointer; margin-left:10px;'><i class='fas fa-file-import'></i> Import from Excel
            </button>";
        }
        echo "</div>";
        return;
    }

    // 9. Requested Products (custom rendering with role actions)
    else if ($tn === 'requested_products') {
        if ($role === 'Retailer') {
            $res = mysqli_query($cn, "SELECT rp.id, rp.name, rp.category, rp.specifications, rp.quantity, rp.status, rp.retailer_id, ur.name AS retailer_name, rp.distributor_id, ud.name AS distributor_name, rp.date_time FROM requested_products rp LEFT JOIN users ur ON ur.id = rp.retailer_id LEFT JOIN users ud ON ud.id = rp.distributor_id WHERE rp.retailer_id = '" . mysqli_real_escape_string($cn, (string)$user_id) . "' ORDER BY rp.id DESC");
        } else if ($role === 'Distributor') {
            $res = mysqli_query($cn, "SELECT rp.id, rp.name, rp.category, rp.specifications, rp.quantity, rp.status, rp.retailer_id, ur.name AS retailer_name, rp.distributor_id, ud.name AS distributor_name, rp.date_time FROM requested_products rp LEFT JOIN users ur ON ur.id = rp.retailer_id LEFT JOIN users ud ON ud.id = rp.distributor_id WHERE rp.distributor_id = '" . mysqli_real_escape_string($cn, (string)$user_id) . "' ORDER BY rp.id DESC");
        } else {
            $res = mysqli_query($cn, "SELECT rp.id, rp.name, rp.category, rp.specifications, rp.quantity, rp.status, rp.retailer_id, ur.name AS retailer_name, rp.distributor_id, ud.name AS distributor_name, rp.date_time FROM requested_products rp LEFT JOIN users ur ON ur.id = rp.retailer_id LEFT JOIN users ud ON ud.id = rp.distributor_id ORDER BY rp.id DESC");
        }

        echo "<table style='width: 100%; min-width: 900px;'>";
        echo "<tr>
                <th>Id</th>
                <th>Name</th>
                <th>Category</th>
                <th>Specifications</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Retailer Name</th>
                <th>Distributor Name</th>
                <th>Date Time</th>
                <th>Actions</th>
            </tr>";

        while ($row = mysqli_fetch_assoc($res)) {
            $id = intval($row['id']);
            $status = (string)($row['status'] ?? '');
            $reqColor = '#FFC107';
            if (strcasecmp($status, 'Approved') === 0) $reqColor = '#4CAF50';
            elseif (strcasecmp($status, 'Forwarded to Manufacturer') === 0) $reqColor = '#9C27B0';
            elseif (strcasecmp($status, 'Manufacturer Approved') === 0) $reqColor = '#3F51B5';

            echo "<tr>";
            echo "<td>" . htmlspecialchars($id) . "</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['category']) . "</td>";
            echo "<td>" . htmlspecialchars($row['specifications']) . "</td>";
            echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
            echo "<td><span style=\"color: $reqColor; font-weight:700;\">" . htmlspecialchars($status) . "</span></td>";
            echo "<td>" . htmlspecialchars($row['retailer_name'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['distributor_name'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['date_time']) . "</td>";
            echo "<td>";
            echo "<button class='btna btn-edit' onclick=\"dbed($id, 'requested_products')\"><i class='fas fa-edit'></i></button>
                  <button class='btna btn-delete' onclick=\"dbdel($id, 'requested_products')\"><i class='fas fa-trash-alt'></i></button>
                  <button class='btna btn-view' onclick=\"dbview($id, 'requested_products')\"><i class='fas fa-eye'></i></button>";

            // Distributor actions for own requests
            if ($role === 'Distributor' && intval($row['distributor_id']) === intval($user_id)) {
                if (!in_array($status, ['Forwarded to Manufacturer', 'Approved'], true)) {
                    echo "<button class='btna' style=\"margin-left:6px; background:#3f51b5; color:#fff;\" onclick=\"sendToRetailer($id)\"><i class='fas fa-paper-plane'></i> Send to Retailer</button>";
                    echo "<button class='btna' style=\"margin-left:6px; background:#ff9800; color:#fff;\" onclick=\"checkAndForwardRequest($id)\"><i class='fas fa-share'></i> Check/Forward</button>";
                }
            }
            // Manufacturer action when forwarded
            if ($role === 'Manufacture' && ($status === 'Forwarded to Manufacturer' || $status === 'Pending Manufacturer Approval')) {
                echo "<button class='btna' style=\"margin-left:6px; background:#4CAF50; color:#fff;\" onclick=\"approveRequestByManufacturer($id)\"><i class='fas fa-check'></i> Approve</button>";
            }

            echo "</td>";
            echo "</tr>";
        }

        echo "</table>";
        echo "</div>";
        return;
    }

    // 8. Default fallback
    else {
        if ($res && mysqli_num_rows($res) > 0) {
            echo "<table style='width: 100%; min-width: 600px;'><tr>";

            $fields = mysqli_fetch_fields($res);
            foreach ($fields as $field) {
                $label = ucwords(str_replace('_', ' ', $field->name));
                echo "<th>{$label}</th>";
            }
            echo "<th>Actions</th></tr>";

            while ($row = mysqli_fetch_assoc($res)) {
                $id = htmlspecialchars($row['id']);
                echo "<tr>";
                foreach ($row as $cell) {
                    echo "<td>" . htmlspecialchars($cell) . "</td>";
                }
                echo "<td>";
                // Role-based access control for offers table
                if ($tn === "offers") {
                    if ($role === "Manufacture") {
                        echo "<button class='btna btn-edit' onclick=\"dbed($id, '$tn')\"><i class='fas fa-edit'></i></button>
                              <button class='btna btn-delete' onclick=\"dbdel($id, '$tn')\"><i class='fas fa-trash-alt'></i></button>";
                    }
                    // View button is always available for offers
                    echo "<button class='btna btn-view' onclick=\"dbview($id, '$tn')\"><i class='fas fa-eye'></i></button>";
                } else {
                    // For other tables, show all buttons
                    echo "<button class='btna btn-edit' onclick=\"dbed($id, '$tn')\"><i class='fas fa-edit'></i></button>
                          <button class='btna btn-delete' onclick=\"dbdel($id, '$tn')\"><i class='fas fa-trash-alt'></i></button>
                          <button class='btna btn-view' onclick=\"dbview($id, '$tn')\"><i class='fas fa-eye'></i></button>";
                }
                echo "</td>
                </tr>";
            }

            echo "</table>";

            // Role-based access control for offers table
            if ($tn === "offers") {
                if ($role === "Manufacture") {
                    echo "<br><button class='btna btn-insert' onclick=\"dbinsert('$tn')\">Add On</button>";
                }
            } else {
                echo "<br><button class='btna btn-insert' onclick=\"dbinsert('$tn')\">Add On</button>";
            }

            echo "</div>";
        } else {
            echo "<br><button class='btna btn-insert' onclick=\"dbinsert('$tn')\">Add On</button>";
            echo "No data found or table doesn't exist.";
            echo "</div>";
        }
    }
}

// Execution entry point
if (isset($_POST['tn'])) {
    showgrid();
} elseif (isset($_GET['del']) && isset($_GET['tn'])) {
    delete_row();
}
?>