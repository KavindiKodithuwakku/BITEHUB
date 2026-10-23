
<?php
session_start();
require_once "db.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Check admin role
$userId = (int) $_SESSION["user_id"];

$stmt = $conn->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($role);

$isAdmin = $stmt->fetch() && $role === "admin";
$stmt->close();

if (!$isAdmin) {
    http_response_code(403);
    exit("Access denied. Admins only.");
}

$orderId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$orderId || $orderId < 1) {
    exit("Invalid order ID.");
}

$allowedStatuses = [
    "Pending",
    "Preparing",
    "Prepared",
    "Completed",
    "Cancelled"
];

$message = "";

// Create CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Update status
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";
    $newStatus = $_POST["status"] ?? "";

    if (!hash_equals($_SESSION["csrf_token"], $token)) {
        $message = "Invalid request. Please try again.";
    } elseif (!in_array($newStatus, $allowedStatuses, true)) {
        $message = "Invalid order status.";
    } else {
        $update = $conn->prepare(
            "UPDATE orders SET status = ? WHERE id = ?"
        );
        $update->bind_param("si", $newStatus, $orderId);

        if ($update->execute()) {
            $message = "Order status updated successfully!";
        } else {
            $message = "Unable to update order status.";
        }

        $update->close();
    }
}

// Get order
$stmt = $conn->prepare(
    "SELECT id, order_number, created_at, total,
            status, payment_method
     FROM orders
     WHERE id = ?
     LIMIT 1"
);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$stmt->bind_result(
    $id,
    $orderNumber,
    $createdAt,
    $total,
    $status,
    $paymentMethod
);

$found = $stmt->fetch();
$stmt->close();

if (!$found) {
    exit("Order not found.");
}

// Get order items
$itemStmt = $conn->prepare(
    "SELECT food_name, unit_price, quantity, line_total
     FROM order_items
     WHERE order_id = ?"
);
$itemStmt->bind_param("i", $orderId);
$itemStmt->execute();
$itemStmt->bind_result(
    $foodName,
    $unitPrice,
    $quantity,
    $lineTotal
);

$items = [];

while ($itemStmt->fetch()) {
    $items[] = [
        "food_name" => $foodName,
        "unit_price" => $unitPrice,
        "quantity" => $quantity,
        "line_total" => $lineTotal
    ];
}
$itemStmt->close();

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order | BITEHUB</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include "includes/header.php"; ?>

<main class="bh-admin-order-details">
    <div class="bh-admin-order-container">

        <h1>Order Details</h1>

        <?php if ($message !== ""): ?>
            <div
                class="bh-admin-order-message <?= strpos($message, 'successfully') !== false ? 'success' : 'error' ?>"
                role="status"
            >
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <section class="bh-admin-order-card">
            <h2>Order #<?= e($orderNumber) ?></h2>

            <p>Date: <?= e($createdAt) ?></p>

            <p>
                Payment Method:
                <?= $paymentMethod === "cod"
                    ? "Pay at Canteen (Cash)"
                    : e($paymentMethod) ?>
            </p>

            <h3>Food Items</h3>

            <?php if (empty($items)): ?>
                <p>No items found for this order.</p>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <p>
                        <?= e($item["food_name"]) ?>
                        × <?= (int)$item["quantity"] ?>
                        — Rs. <?= number_format((float)$item["line_total"], 2) ?>
                    </p>
                <?php endforeach; ?>
            <?php endif; ?>

            <h3>
                Total: Rs. <?= number_format((float)$total, 2) ?>
            </h3>

            <p>
                Current Status: <strong><?= e($status) ?></strong>
            </p>

            <form method="POST"
                  action="admin_order_details.php?id=<?= (int)$id ?>">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($_SESSION["csrf_token"]) ?>"
                >

                <label for="status">Update Order Status</label>

                <select name="status" id="status" required>
                    <?php foreach ($allowedStatuses as $option): ?>
                        <option
                            value="<?= e($option) ?>"
                            <?= $status === $option ? "selected" : "" ?>
                        >
                            <?= e($option) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br><br><br>

                <button type="submit" class="bh-login-button">
                    Save Status
                </button>
            </form>
            <br><br>

            <p>
    <a href="admin_orders.php"
       style="color: #8B0035;
              text-decoration: none;
              font-weight: bold;">
        ← Back to All Orders
    </a>
</p>
        </section>
    </div>
</main>

<?php include "includes/footer.php"; ?>

</body>
</html>
