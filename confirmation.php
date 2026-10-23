
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];
$orderNumber = trim($_GET["order"] ?? "");

$order = null;
$orderItems = [];

function bhMoney($amount) {
    return "Rs. " . number_format((float) $amount, 2);
}

if ($orderNumber !== "") {
    $stmt = $conn->prepare(
        "SELECT id, order_number, subtotal, delivery_fee,
                total, status, payment_method
         FROM orders
         WHERE order_number = ? AND user_id = ?
         LIMIT 1"
    );

    $stmt->bind_param("si", $orderNumber, $userId);
    $stmt->execute();

    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();

    if ($order) {
        $stmt = $conn->prepare(
            "SELECT food_name, unit_price, quantity, line_total
             FROM order_items
             WHERE order_id = ?"
        );

        $orderId = (int) $order["id"];
        $stmt->bind_param("i", $orderId);
        $stmt->execute();

        $result = $stmt->get_result();

        while ($item = $result->fetch_assoc()) {
            $orderItems[] = $item;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Order Confirmation | BITEHUB</title>

    <link rel="stylesheet" href="css/style.css">

    <script src="js/script.js" defer></script>
    
</head>
<body>

<?php include 'includes/header.php'; ?>

<main class="bh-confirmation-page">

    <section class="bh-confirmation-card">

        <div class="bh-success-icon">
            <span>✓</span>
        </div>

        <span class="bh-confirmation-label">
            BITEHUB ORDER UPDATE
        </span>

        <h1>Thank You for <span>Your Order!</span></h1>

        <p class="bh-confirmation-message">
            Thank you for choosing BITEHUB.
            Your order details are shown below.
        </p>

        <div class="bh-order-status">
            <span class="bh-status-dot"></span>
           <span id="bh-order-status-text"><?=
                $order
                    ? htmlspecialchars($order["status"]) . " · Pay at Canteen (Cash)"
                    : "Order Not Found"
            ?></span>
        </div>

        <div class="bh-order-number-box">
            <div>
                <span class="bh-small-label">ORDER NUMBER</span>
                <h3 id="bh-order-number"><?= $order
                    ? htmlspecialchars($order["order_number"])
                    : "—"
                ?></h3>
            </div>

            <div class="bh-order-number-icon">♡</div>
        </div>

        <div class="bh-confirmation-divider"></div>

        <div class="bh-confirmation-details">
            <h2>Order Summary</h2>

           <div id="bh-confirmation-items">
    <?php if ($order && !empty($orderItems)): ?>
        <?php foreach ($orderItems as $item): ?>
            <div class="bh-confirmation-item">
                <div>
                    <div class="bh-confirmation-item-name">
                        <?= htmlspecialchars($item["food_name"]) ?>
                    </div>

                    <div class="bh-confirmation-item-quantity">
                        Quantity: <?= (int) $item["quantity"] ?>
                    </div>
                </div>

                <strong class="bh-confirmation-item-price">
                    <?= bhMoney($item["line_total"]) ?>
                </strong>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="bh-confirmation-empty">
            <?= $order
                ? "No items found for this order."
                : "Order details could not be found."
            ?>
        </p>
    <?php endif; ?>
</div>

            
<div class="bh-confirmation-total-row">
    <span>Subtotal</span>
    <strong id="bh-confirmation-subtotal">
        <?= bhMoney($order["subtotal"] ?? 0) ?>
    </strong>
</div>

<div class="bh-confirmation-total-row">
    <span>Pickup Fee</span>
    <strong id="bh-confirmation-delivery">
        <?= bhMoney(0) ?>
    </strong>
</div>

<div class="bh-confirmation-grand-total">
    <span>Total</span>
    <strong id="bh-confirmation-total">
        <?= bhMoney($order["total"] ?? 0) ?>
    </strong>
</div>

        </div>

        <div class="bh-confirmation-info">
            <div class="bh-confirmation-info-icon">♡</div>
            <div>
                <h3>What's Next?</h3>
                <p>
                    The canteen will prepare your order.
                    Once the status is Prepared, please collect your food
                    and pay in cash at the canteen.
                </p>
            </div>
        </div>

        <div class="bh-confirmation-actions">
            <a href="menu.php" class="bh-confirmation-primary">
                Continue Shopping <span>→</span>
            </a>

            <a href="index.php" class="bh-confirmation-secondary">
                Back to Home
            </a>
        </div>

        <p class="bh-confirmation-footer-note">
            Made with ♡ by BITEHUB
        </p>

    </section>

</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
