
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Check admin permission using the database.
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

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$categories = [
    "Rice",
    "Fast Food",
    "Short Eats",
    "Drinks",
    "Desserts"
];


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Validate CSRF token before processing the form.
    $submittedToken = $_POST["csrf_token"] ?? "";

    if (
        !is_string($submittedToken) ||
        !hash_equals($_SESSION["csrf_token"], $submittedToken)
    ) {
        http_response_code(403);
        exit("Invalid security token. Please refresh the page and try again.");
    }

$nameInput = $_POST["name"] ?? "";
$priceInput = $_POST["price"] ?? "";
$categoryInput = $_POST["category"] ?? "";
$imageInput = $_POST["image"] ?? "";
$descriptionInput = $_POST["description"] ?? "";

$name = is_string($nameInput) ? trim($nameInput) : "";
$price = filter_var($priceInput, FILTER_VALIDATE_FLOAT);
$category = is_string($categoryInput) ? $categoryInput : "";
$image = is_string($imageInput) ? trim($imageInput) : "";
$description = is_string($descriptionInput) ? trim($descriptionInput) : "";


if ($image !== "") {
    $isLocalImage = preg_match(
        '/^images\/[a-zA-Z0-9 _.-]+\.(jpg|jpeg|png|gif|webp)$/i',
        $image
    ) && strpos($image, '..') === false;

    $isImageUrl = filter_var($image, FILTER_VALIDATE_URL)
        && in_array(
            strtolower((string) parse_url($image, PHP_URL_SCHEME)),
            ["https"],
            true
        );

    if (!$isLocalImage && !$isImageUrl) {
        $message = "Please enter a valid image path or HTTPS image URL.";
    }
}



   
if (
    $name === "" ||
    strlen($name) > 150 ||
    strlen($description) > 2000 ||
    $price === false ||
    !is_numeric($priceInput) ||
    $price <= 0 ||
    $price > 1000000 ||
    !in_array($category, $categories, true) ||
    strlen($image) > 255 ||
    (
        $image !== "" &&
        !(
            (
                preg_match(
                    '/^images\/[a-zA-Z0-9 _.-]+\.(jpg|jpeg|png|gif|webp)$/i',
                    $image
                ) &&
                strpos($image, '..') === false
            ) ||
            (
                filter_var($image, FILTER_VALIDATE_URL) &&
                strtolower((string) parse_url($image, PHP_URL_SCHEME)) === "https"
            )
        )
    )
) {
    $message = "Please enter a valid name, price, category and image path or HTTPS URL.";
} else {

        $id = "food-" . bin2hex(random_bytes(8));

        $stmt = $conn->prepare(
            "INSERT INTO foods
             (id, name, price, category, image, description)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssdsss",
            $id,
            $name,
            $price,
            $category,
            $image,
            $description
        );

        if ($stmt->execute()) {
            $message = "Food added successfully!";

            // Rotate the token after a successful submission.
            $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
        } else {
            $message = "Could not add food. Please try again.";
        }

        $stmt->close();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Food | BITEHUB</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include "includes/header.php"; ?>

<main class="bh-add-food-page">
    <div class="bh-add-food-heading">
    <span class="bh-add-food-icon">👨‍🍳</span>
    <p class="bh-add-food-subtitle">BITEHUB ADMIN PANEL</p>
    <h1>Add New Food</h1>
    <p>Add a delicious new item to your menu.</p>
</div>

    <?php if ($message !== ""): ?>
        <p><?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?></p>
    <?php endif; ?>

    <form method="POST" class="bh-add-food-form">
        <input
    type="hidden"
    name="csrf_token"
    value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
        
           <div class="bh-add-food-field">
                <label for="name">Food Name</label>
                <input id="name" name="name" type="text"
                    maxlength="150" required
                    placeholder="Enter food name">
            </div>
        

        
           <div class="bh-add-food-field">
                <label for="price">Price (Rs.)</label>
                <input id="price" name="price" type="number"
                    min="0.01" step="0.01" required
                    placeholder="Enter price">
            </div>
        

        
           <div class="bh-add-food-field">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="">Choose category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat, ENT_QUOTES, "UTF-8") ?>">
                            <?= htmlspecialchars($cat, ENT_QUOTES, "UTF-8") ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        

        
            <div class="bh-add-food-field">
                <label for="image">Image Path or URL <span>(Optional)</span></label>
                <input id="image" name="image" type="text"
                    maxlength="255"
                    placeholder="images/food/chicken-burger.jpg">
                <small>Enter the image path or image URL.</small>
            </div>
        

        
            <div class="bh-add-food-field">
                <label for="description">Description <span>(Optional)</span></label>
                <textarea id="description" name="description"
                        rows="4"
                        placeholder="Write a short food description..."></textarea>
            </div>
        

                <button type="submit" class="bh-add-food-submit">
                    + Add Food to Menu
                </button>
    </form>

   <div class="bh-add-food-back-wrap">
        <a href="admin_orders.php" class="bh-add-food-back">
            <span>←</span> Back to Admin Panel
        </a>
    </div>
</main>

<?php include "includes/footer.php"; ?>

</body>
</html>
