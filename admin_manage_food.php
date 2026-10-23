
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($role);
$isAdmin = $stmt->fetch() && $role === "admin";
$stmt->close();

if (!$isAdmin) {
    http_response_code(403);
    exit("Access denied.");
}

$categories = ["Rice", "Fast Food", "Short Eats", "Drinks", "Desserts"];
$message = "";
$editingFood = null;

if (empty($_SESSION["food_csrf"])) {
    $_SESSION["food_csrf"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";

    if (!is_string($token) || !hash_equals($_SESSION["food_csrf"], $token)) {
        $message = "Security check failed. Please refresh and try again.";
    } else {
        $action = $_POST["action"] ?? "";

        if ($action === "delete") {
            $id = trim($_POST["id"] ?? "");

            if ($id === "") {
                $message = "Food item not found.";
            } else {
                $stmt = $conn->prepare("DELETE FROM foods WHERE id = ?");
                $stmt->bind_param("s", $id);
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    $message = "Food deleted successfully.";
                } else {
                    $message = "Food item not found or could not be deleted.";
                }
                $stmt->close();
            }
        } elseif ($action === "update") {
            $id = trim($_POST["id"] ?? "");
            $name = trim($_POST["name"] ?? "");
            $price = filter_var($_POST["price"] ?? "", FILTER_VALIDATE_FLOAT);
            $category = $_POST["category"] ?? "";
            $imageInput = $_POST["image"] ?? "";

            if (!is_string($imageInput)) {
                $image = "";
            } else {
                $image = trim($imageInput);
            }
                if ($image !== "") {
    $image = trim($image);

    if (
        !preg_match(
            '/^images\/[a-zA-Z0-9 _.-]+\.(jpg|jpeg|png|gif|webp)$/i',
            $image
        ) ||
        strpos($image, '..') !== false
    ) {
        $image = "";
    }
}
            $description = trim($_POST["description"] ?? "");
            $available = isset($_POST["is_available"]) ? 1 : 0;

            
            if (
                $id === "" ||
                $name === "" ||
                strlen($name) > 150 ||
                strlen($description) > 2000 ||
                $price === false ||
                !is_numeric($_POST["price"] ?? null) ||
                $price <= 0 ||
                $price > 1000000 ||
                !in_array($category, $categories, true) ||
                strlen($image) > 255
            ) {

                $message = "Please enter a valid name, price and category.";
            } else {
                $stmt = $conn->prepare(
                    "UPDATE foods
                     SET name = ?, price = ?, category = ?, image = ?,
                         description = ?, is_available = ?
                     WHERE id = ?"
                );
                $stmt->bind_param(
                    "sdsssis",
                    $name,
                    $price,
                    $category,
                    $image,
                    $description,
                    $available,
                    $id
                );

                if ($stmt->execute() && $stmt->affected_rows >= 0) {
                    $message = "Food updated successfully.";
                } else {
                    $message = "Could not update food. Please try again.";
                }
                $stmt->close();
            }
        }
    }
}

if (isset($_GET["edit"])) {
    $editId = trim($_GET["edit"]);
    $stmt = $conn->prepare(
        "SELECT id, name, price, category, image, description, is_available
         FROM foods WHERE id = ? LIMIT 1"
    );
    $stmt->bind_param("s", $editId);
    $stmt->execute();
    $result = $stmt->get_result();
    $editingFood = $result->fetch_assoc();
    $stmt->close();

    if (!$editingFood) {
        $message = "Food item not found.";
    }
}

$foodsResult = $conn->query(
    "SELECT id, name, price, category, image, description, is_available
     FROM foods ORDER BY created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Foods | BITEHUB</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .food-admin-wrap { max-width: 1100px; margin: 35px auto; padding: 20px; }
        .food-admin-message { padding: 12px 15px; margin: 15px 0; background: #fff0f5; border-radius: 8px; }
        .food-admin-form { background: #fff; border: 1px solid #eee; border-radius: 12px; padding: 20px; margin: 20px 0; }
        .food-admin-form input, .food-admin-form select, .food-admin-form textarea { box-sizing: border-box; width: 100%; padding: 11px; margin-top: 5px; }
        .food-admin-form label { display: block; margin: 12px 0; }
        .food-admin-table { width: 100%; border-collapse: collapse; background: #fff; }
        .food-admin-table th, .food-admin-table td { border-bottom: 1px solid #eee; padding: 12px; text-align: left; vertical-align: top; }
        .food-admin-actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .food-admin-btn { display: inline-block; border: 0; border-radius: 7px; padding: 9px 13px; cursor: pointer; text-decoration: none; background: #8B0035; color: #fff; }
        .food-admin-delete { background: #b42335; }
        .food-admin-muted { color: #666; font-size: .9rem; }
        @media (max-width: 700px) { .food-admin-table { font-size: 13px; } .food-admin-table th, .food-admin-table td { padding: 7px; } }
    </style>
</head>
<body>
<?php include "includes/header.php"; ?>

<main class="food-admin-wrap">
    <h1>Manage Food Items</h1>
    <p>Add, edit, or remove food items from the BITEHUB database.</p>

    <?php if ($message !== ""): ?>
        <p class="food-admin-message"><?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?></p>
    <?php endif; ?>

    
<p>
    <a class="food-admin-btn" href="admin_dashboard.php">← Admin Dashboard</a>
    <a class="food-admin-btn" href="admin_add_food.php">+ Add New Food</a>
    <a class="food-admin-btn" href="admin_orders.php">Manage Orders</a>
</p>


    <?php if ($editingFood): ?>
        <section class="food-admin-form">
            <h2>Edit Food</h2>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["food_csrf"], ENT_QUOTES, "UTF-8") ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= htmlspecialchars($editingFood["id"], ENT_QUOTES, "UTF-8") ?>">

                <label>Food Name
                    <input name="name" type="text" maxlength="150" required value="<?= htmlspecialchars($editingFood["name"], ENT_QUOTES, "UTF-8") ?>">
                </label>
                <label>Price (Rs.)
                    <input name="price" type="number" min="0.01" step="0.01" required value="<?= htmlspecialchars((string)$editingFood["price"], ENT_QUOTES, "UTF-8") ?>">
                </label>
                <label>Category
                    <select name="category" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat, ENT_QUOTES, "UTF-8") ?>" <?= $editingFood["category"] === $cat ? "selected" : "" ?>>
                                <?= htmlspecialchars($cat, ENT_QUOTES, "UTF-8") ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Image path or URL
                    <input name="image" type="text" maxlength="255" value="<?= htmlspecialchars($editingFood["image"] ?? "", ENT_QUOTES, "UTF-8") ?>">
                </label>
                <label>Description
                    <textarea name="description" rows="3"><?= htmlspecialchars($editingFood["description"] ?? "", ENT_QUOTES, "UTF-8") ?></textarea>
                </label>
                <label>
                    <input type="checkbox" name="is_available" value="1" style="width:auto" <?= (int)$editingFood["is_available"] === 1 ? "checked" : "" ?>>
                    Available on menu
                </label>
                <button class="food-admin-btn" type="submit">Save Changes</button>
                <a class="food-admin-btn" href="admin_manage_food.php">Cancel</a>
            </form>
        </section>
    <?php endif; ?>

    <h2>Food Items</h2>
    <?php if ($foodsResult && $foodsResult->num_rows > 0): ?>
        <div style="overflow-x:auto">
        <table class="food-admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Availability</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($food = $foodsResult->fetch_assoc()): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($food["name"], ENT_QUOTES, "UTF-8") ?></strong>
                        <?php if (!empty($food["description"])): ?>
                            <div class="food-admin-muted"><?= htmlspecialchars($food["description"], ENT_QUOTES, "UTF-8") ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($food["category"], ENT_QUOTES, "UTF-8") ?></td>
                    <td>Rs. <?= number_format((float)$food["price"], 2) ?></td>
                    <td><?= (int)$food["is_available"] === 1 ? "Available" : "Unavailable" ?></td>
                    <td>
                        <div class="food-admin-actions">
                            <a class="food-admin-btn" href="admin_manage_food.php?edit=<?= rawurlencode($food["id"]) ?>">Edit</a>
                            <form method="post" onsubmit="return confirm('Are you sure you want to delete this food item?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["food_csrf"], ENT_QUOTES, "UTF-8") ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($food["id"], ENT_QUOTES, "UTF-8") ?>">
                                <button class="food-admin-btn food-admin-delete" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    <?php else: ?>
        <p>No food items have been added to the database yet.</p>
    <?php endif; ?>
</main>

<?php include "includes/footer.php"; ?>
</body>
</html>
