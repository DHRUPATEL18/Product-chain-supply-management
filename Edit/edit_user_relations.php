<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$id = intval($_COOKIE["edit_id"] ?? 0);
if ($id === 0) {
    echo "Invalid ID.";
    exit();
}

function getRoleByRelation($relation, $fill)
{
    if ($fill === "parent") {
        if ($relation === "Manufacture-Distributor" || $relation === "Manufacture-ASM") return "Manufacture";
        if ($relation === "Distributor-Retailer") return "Distributor";
        if ($relation === "ASM-Distributor") return "Area Sales Manager";
    } elseif ($fill === "child") {
        if ($relation === "Manufacture-Distributor" || $relation === "ASM-Distributor") return "Distributor";
        if ($relation === "Distributor-Retailer") return "Retailer";
        if ($relation === "Manufacture-ASM") return "Area Sales Manager";
    }
    return "";
}

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["role_type"], $_GET["relation"])) {
    $fill = $_GET["role_type"];
    $relation = $_GET["relation"];
    $currentId = intval($_GET["current"] ?? 0);
    $role = getRoleByRelation($relation, $fill);

    $res = mysqli_query($cn, "SELECT id, name FROM users WHERE role = '$role'");
    while ($row = mysqli_fetch_assoc($res)) {
        $selected = $row['id'] == $currentId ? 'selected' : '';
        echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
    }
    exit();
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $relation = $_POST["relation"] ?? '';
    $parent_id = intval($_POST["parent_id"] ?? 0);
    $child_id = intval($_POST["child_id"] ?? 0);

    $qr = "UPDATE user_relations SET parent_id = ?, child_id = ?, relation = ? WHERE id = ?";
    $stmt = mysqli_prepare($cn, $qr);
    mysqli_stmt_bind_param($stmt, "iisi", $parent_id, $child_id, $relation, $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../tablegrid.php?tn=user_relations");
        exit();
    } else {
        echo "Failed to update user record.";
    }
}

$sql = "
    SELECT 
        u2.id, 
        u2.parent_id,
        u2.child_id,
        u2.relation
    FROM user_relations u2
    WHERE u2.id = $id
";
$data = mysqli_fetch_assoc(mysqli_query($cn, $sql));

$parentRole = getRoleByRelation($data['relation'], "parent");
$childRole = getRoleByRelation($data['relation'], "child");
$parents = mysqli_query($cn, "SELECT id, name FROM users WHERE role = '$parentRole'");
$children = mysqli_query($cn, "SELECT id, name FROM users WHERE role = '$childRole'");

$allRelations = [
    "Manufacture-Distributor",
    "Manufacture-ASM",
    "ASM-Distributor",
    "Distributor-Retailer"
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User Relation</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<form method="post">
    <h2>Edit User Relation</h2>

    <div class="form-group">
        <label>Relation Type:</label>
        <select name="relation" id="relation" onchange="updateDropdowns()" required>
            <?php foreach ($allRelations as $rel): ?>
                <option value="<?= $rel ?>" <?= $rel === $data['relation'] ? 'selected' : '' ?>><?= $rel ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Parent Name:</label>
        <select name="parent_id" id="parent_id" required>
            <?php while ($p = mysqli_fetch_assoc($parents)): ?>
                <option value="<?= $p['id'] ?>" <?= $p['id'] == $data['parent_id'] ? 'selected' : '' ?>>
                    <?= $p['name'] ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Child Name:</label>
        <select name="child_id" id="child_id" required>
            <?php while ($c = mysqli_fetch_assoc($children)): ?>
                <option value="<?= $c['id'] ?>" <?= $c['id'] == $data['child_id'] ? 'selected' : '' ?>>
                    <?= $c['name'] ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <input type="submit" value="Update">
</form>

<script>
function updateDropdowns() {
    const relation = document.getElementById("relation").value;

    fetch("edit_user_relations.php?role_type=parent&relation=" + encodeURIComponent(relation))
        .then(res => res.text())
        .then(html => {
            document.getElementById("parent_id").innerHTML = html;
        });

    fetch("edit_user_relations.php?role_type=child&relation=" + encodeURIComponent(relation))
        .then(res => res.text())
        .then(html => {
            document.getElementById("child_id").innerHTML = html;
        });
}
</script>
</body>
</html>
