<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

$id = intval($_COOKIE["view_id"] ?? 0);

if ($id === 0) {
    echo "Invalid ID.";
    exit();
}

$sql = "
    SELECT 
        u2.id, 
        u2.parent_id,
        u2.child_id,
        u1.name AS parent_name, 
        u3.name AS child_name,
        u2.relation
    FROM 
        user_relations u2
    JOIN 
        users u1 ON u1.id = u2.parent_id
    JOIN 
        users u3 ON u3.id = u2.child_id
    WHERE 
        u2.id = $id
";
$res = mysqli_query($cn, $sql);
$data = mysqli_fetch_assoc($res);

if (!$data) {
    echo "No record found.";
    exit();
}
?>

<h2 style="text-align:center;">User Relations</h2>
<form method="post" class="two-column-form">
    <div class="form-group">
        <label>Parent Name:</label>
        <input type="text" name="parent_id" value="<?= $data['parent_id'] ." - ". $data['parent_name'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Child Name:</label>
        <input type="text" name="child_id" value="<?= $data['child_id'] ." - ". $data['child_name'] ?>" readonly>
    </div>

    <div class="form-group">
        <label>Relation Type:</label>
        <input type="text" value="<?= $data['relation'] ?>" readonly>
    </div>
</form>

<link rel="stylesheet" href="View/style.css">
