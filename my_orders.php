

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If customer is not logged in, remember this page and go to login.
if (!isset($_SESSION["user_id"])) {
    $_SESSION["redirect_after_login"] = "my_orders.php";
    header("Location: login.php");
    exit;
}

require_once "db.php";

$userId = (int) $_SESSION["user_id"];

// Get only the logged-in customer's orders.
$stmt = $conn->prepare(
    "SELECT id, order_number, created_at, subtotal,
            delivery_fee, total, status, payment_method
     FROM orders
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();

function orderEscape($value) {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Orders | BITEHUB</title>

    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>
<body>

<?php include "includes/header.php"; ?>

<main class="bh-my-orders">
    <section class="bh-my-orders-container">

        <h1>My <span>Orders</span></h1>

        <p class="bh-my-orders-intro">
            View your previous orders and order details.
        </p>

        <?php if (count($orders) === 0): ?>

            <div class="bh-no-orders">
                <h2>No orders yet</h2>
                <p>
                    Your orders will appear here after you place an order.
                </p>
                <a href="menu.php">Explore Menu</a>
            </div>

        <?php else: ?>

            <?php foreach ($orders as $order): ?>

                <article class="bh-order-card">

                    <div class="bh-order-card-header">
                        <div>
                            <span class="bh-order-label">ORDER NUMBER</span>
                            <h2>
                                <?= orderEscape($order["order_number"]) ?>
                            </h2>
                        </div>

                        <span class="bh-order-status">
                            <?= orderEscape($order["status"]) ?>
                        </span>
                    </div>

                    <p class="bh-order-date">
                        Date:
                        <?= orderEscape(
                            date(
                                "d M Y, h:i A",
                                strtotime($order["created_at"])
                            )
                        ) ?>
                    </p>

                    <?php
                    $itemStmt = $conn->prepare(
                        "SELECT food_name, unit_price, quantity, line_total
                         FROM order_items
                         WHERE order_id = ?
                         ORDER BY id ASC"
                    );

                    $itemStmt->bind_param("i", $order["id"]);
                    $itemStmt->execute();

                    $itemResult = $itemStmt->get_result();
                    ?>

                    <div class="bh-order-items">

                        <?php while ($item = $itemResult->fetch_assoc()): ?>

                            <div class="bh-order-item">
                                <span>
                                    <?= orderEscape($item["food_name"]) ?>
                                    × <?= (int) $item["quantity"] ?>
                                </span>

                                <strong>
                                    Rs. <?= number_format(
                                        (float) $item["line_total"],
                                        2
                                    ) ?>
                                </strong>
                            </div>

                        <?php endwhile; ?>

                    </div>

                    <?php $itemStmt->close(); ?>

                    <div class="bh-order-total">
                        <span>Total</span>
                        <strong>
                            Rs. <?= number_format(
                                (float) $order["total"],
                                2
                            ) ?>
                        </strong>
                    </div>

                    <p class="bh-order-payment">
                        Payment:
                        <?= orderEscape(
                            strtoupper($order["payment_method"])
                        ) ?>
                    </p>

                </article>

            <?php endforeach; ?>

        <?php endif; ?>

        <div class="bh-orders-back">
            <a href="menu.php">← Continue Shopping</a>
        </div>

    </section>
</main>

<?php include "includes/footer.php"; ?>

</body>
</html>



 