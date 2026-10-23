
<?php
session_start();
require_once "db.php";

// Only logged-in admin users can access this page.
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
    exit("Access denied. Admins only.");
}

$sql = "SELECT id, order_number, created_at, total, status
        FROM orders
        ORDER BY created_at DESC";

$result = $conn->query($sql);

if (!$result) {
    exit("Unable to load orders. Please check the orders table.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders | BITEHUB</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include "includes/header.php"; ?>

<main class="bh-my-orders">
    <div class="bh-my-orders-container">
       
    <h1>Manage Orders</h1>
    <p>View customer orders and their current status.</p>

    
        <div class="bh-admin-food-link">
            <div class="bh-admin-food-info">
                <span class="bh-admin-food-icon">🍔</span>
                <div>
                    <h3>Manage Foods</h3>
                    <p>Add new foods, edit prices and manage your menu.</p>
                </div>
            </div>

            <a href="admin_manage_food.php" class="bh-admin-food-button">
                Manage Menu <span>→</span>
            </a>
        </div>



        <?php if ($result->num_rows === 0): ?>
            <p>No orders found.</p>
        <?php else: ?>
            <?php while ($order = $result->fetch_assoc()): ?>
                <section class="bh-order-card">
                    <div class="bh-order-card-header">
                        <h2>
                            Order #<?= htmlspecialchars($order["order_number"]) ?>
                        </h2>
                        <span class="bh-order-status">
                            <?= htmlspecialchars(ucfirst(strtolower($order["status"]))) ?>
                        </span>
                    </div>

                    <p>
                        Date:
                        <?= htmlspecialchars($order["created_at"]) ?>
                    </p>

                    <p>
                        Total:
                        Rs. <?= number_format((float)$order["total"], 2) ?>
                    </p>

                    <a href="admin_order_details.php?id=<?= (int)$order["id"] ?>">
                        View / Update Order
                    </a>
                </section>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</main>

<?php include "includes/footer.php"; ?>

</body>
</html>
