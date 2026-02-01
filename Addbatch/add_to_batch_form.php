<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$role = $_GET['role'] ?? null;
$user = $_SESSION['user_name'] ?? '';
$table = $_GET['table'] ?? null;

$dropdown_label = '';
$users = null;
$products = null;

if ($role === "Manufacture" && $table === "products") {
    $dropdown_label = "Select Distributor";
    $users = mysqli_query($cn, "SELECT id, name FROM users WHERE role = 'Distributor'");
    $products = mysqli_query($cn, "SELECT id, product_name, sku_id FROM products");
    $form_action = "Addbatch/submit_batch.php?flow=manu_to_dist";
} else if ($role === "Distributor" && $table === "product_assigned_dist") {
    $dropdown_label = "Select Retailer";
    $users = mysqli_query($cn, "SELECT id, name FROM users WHERE role = 'Retailer'");
    $name = '';
    $user_sql = "SELECT name FROM users WHERE username = '$user'";
    $user_res = mysqli_query($cn, $user_sql);
    if ($user_row = mysqli_fetch_assoc($user_res)) {
        $name = $user_row['name'];
    }
    $products = mysqli_query($cn, "
        SELECT 
            pad.id,
            pad.product_id,
            p.product_name,
            p.sku_id,
            pad.quantity,
            pad.assigned_at,
            pad.status,
            m.name AS manufacturer_name,
            d.name AS distributor_name
        FROM product_assigned_dist pad
        JOIN sold_products p ON pad.product_id = p.product_id
        JOIN batch_distributor bd ON pad.batch_id = bd.id
        JOIN users m ON bd.assigned_by = m.id
        JOIN users d ON bd.assigned_to = d.id
        WHERE d.name = '$name'
    ");
    $form_action = "Addbatch/submit_batch.php?flow=dist_to_retailer";
} else {
    die("Invalid role or table.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Products to Batch</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        h2 {
            text-align: center;
            margin-top: 30px;
            color: #333;
        }

        form {
            max-width: 900px;
            margin: 20px auto;
            padding: 25px;
            background-color: #f7f9fc;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .dropdown {
            margin-bottom: 20px;
        }

        .dropdown label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }

        .dropdown select {
            width: 100%;
            height: 42px;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            background-color: #fff;
            appearance: none;
            background-image: url("data:image/svg+xml;utf8,<svg fill='gray' height='24' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/></svg>");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
        }

        .dropdown select:focus {
            outline: none;
            border-color: #2196F3;
            box-shadow: 0 0 5px rgba(33, 150, 243, 0.3);
        }

        form table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        form th,
        form td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }

        form th {
            background-color: #e3f2fd;
            font-weight: bold;
            color: #333;
        }

        form input[type="checkbox"] {
            transform: scale(1.2);
        }

        form .btn {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 25px;
            font-size: 16px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        form .btn:hover {
            background-color: #0b7dda;
        }
    </style>
</head>

<body>

    <h2>Add to Batch - <?= htmlspecialchars($dropdown_label) ?></h2>

    <form action="<?= $form_action ?>" method="POST">
        <div class="dropdown">
            <label><strong><?= htmlspecialchars($dropdown_label) ?>:</strong></label>
            <select name="user_id" required>
                <option value=""><?= htmlspecialchars($dropdown_label) ?></option>
                <?php while ($row = mysqli_fetch_assoc($users)) {
                    echo "<option value='{$row['id']}'>{$row['name']}</option>";
                } ?>
            </select>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Product Name</th>
                    <th>SKU ID</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($prod = mysqli_fetch_assoc($products)) {
                    echo "<tr>
                    <td><input type='checkbox' name='product_ids[]' value='{$prod['id']}'></td>
                    <td>{$prod['product_name']}</td>
                    <td>{$prod['sku_id']}</td>
                </tr>";
                } ?>
            </tbody>
        </table>

        <button type="submit" class="btn">Add to Batch</button>
    </form>

</body>

</html>