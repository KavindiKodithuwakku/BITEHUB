

<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php?redirect=checkout.php");
    exit;
}

// CSRF security token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

require_once "db.php";

$userId = (int) $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT full_name, email, phone
     FROM users
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($customerName, $customerEmail, $customerPhone);

if (!$stmt->fetch()) {
    $stmt->close();
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

$stmt->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Checkout | BITEHUB</title>

    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
    <script src="js/checkout.js" defer></script>
</head>

<body>

<?php include 'includes/header.php'; ?>

<main class="bh-checkout-page">

    <section class="bh-checkout-heading">
        <p class="bh-checkout-eyebrow">ALMOST THERE!</p>
        <h1>Complete Your <span>Order</span></h1>
        <p>Order online. Pick up fresh food at your campus canteen.</p>

        <div class="bh-checkout-steps">
            <span class="bh-step-done">1. Cart</span>
            <span class="bh-step-active">2. Checkout</span>
            <span>3. Confirmation</span>
        </div>
    </section>

    <form id="checkout-form" class="bh-checkout-layout">
        <input
    type="hidden"
    id="csrf-token"
    value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

        <section class="bh-checkout-details">

            <!-- CONTACT INFORMATION -->
            <div class="bh-checkout-card">

                <div class="bh-checkout-card-title">
                    <span class="bh-checkout-icon">01</span>
                    <div>
                        <h2>Contact Information</h2>
                        <p>How can we reach you?</p>
                    </div>
                </div>

                <div class="bh-form-grid">

                    <div class="bh-form-field bh-field-full">
                        <label for="customer-name">Full Name</label>
                        <input
                            type="text"
                            id="customer-name"
                            name="customer_name"
                            value="<?= htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Enter your full name"
                            autocomplete="name"
                            required
                        >
                    </div>

                    <div class="bh-form-field">
                        <label for="customer-email">Email Address</label>
                        <input
                            type="email"
                            id="customer-email"
                            name="email"
                            value="<?= htmlspecialchars($customerEmail, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="you@example.com"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div class="bh-form-field">
                        <label for="customer-phone">Phone Number</label>
                        <input
                            type="tel"
                            id="customer-phone"
                            name="phone"
                            value="<?= htmlspecialchars($customerPhone, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="07X XXX XXXX"
                            autocomplete="tel"
                            pattern="0[0-9]{9}"
                            title="Enter a 10-digit phone number starting with 0"
                            required
                        >
                    </div>

                </div>
            </div>

            <!-- CANTEEN PICKUP -->
            <div class="bh-checkout-card">

                <div class="bh-checkout-card-title">
                    <span class="bh-checkout-icon">02</span>
                    <div>
                        <h2>Canteen Pickup</h2>
                        <p>Collect your order from the campus canteen.</p>
                    </div>
                </div>

                <div class="bh-form-grid">

                    <div class="bh-form-field bh-field-full">
                        <label>Pickup Location</label>
                        <p>🏫 Campus Canteen</p>
                        <p>Place your order online and collect it when the status is Prepared.</p>
                    </div>

                    <div class="bh-form-field bh-field-full">
                        <label for="delivery-note">Special Instructions (Optional)</label>
                        <input
                            type="text"
                            id="delivery-note"
                            name="note"
                            placeholder="Any special requests for your food?"
                        >
                    </div>

                </div>
            </div>

            <!-- PAYMENT METHOD -->
            <div class="bh-checkout-card">

                <div class="bh-checkout-card-title">
                    <span class="bh-checkout-icon">03</span>
                    <div>
                        <h2>Payment Method</h2>
                        <p>Pay at the canteen when collecting your food.</p>
                    </div>
                </div>

                <div class="bh-payment-methods">

                    <label class="bh-payment-option">

                        <input
                            type="radio"
                            name="payment"
                            value="cod"
                            checked
                        >

                        <span class="bh-payment-symbol">💵</span>

                        <span class="bh-payment-text">
                            <strong>Pay at Canteen (Cash)</strong>
                            <small>Pay when you collect your order.</small>
                        </span>

                        <span class="bh-payment-check">✓</span>

                    </label>

                </div>

                <p id="payment-message" class="bh-payment-note">
                    Please pay in cash when collecting your food.
                </p>

            </div>

        </section>

        <!-- ORDER SUMMARY -->
        <aside class="bh-checkout-summary">

            <div class="bh-summary-heading">
                <h2>Your Order</h2>
                <span id="checkout-item-count">0 items</span>
            </div>

            <div id="checkout-items" class="bh-checkout-items">
                <p class="bh-checkout-loading">
                    Checking your cart...
                </p>
            </div>

            <div class="bh-summary-divider"></div>

            <div class="bh-summary-row">
                <span>Subtotal</span>
                <strong id="checkout-subtotal">Rs. 0.00</strong>
            </div>

            <div class="bh-summary-row">
                <span>Pickup Fee</span>
                <strong id="checkout-delivery">Rs. 0.00</strong>
            </div>

            <div class="bh-summary-total">
                <span>Total</span>
                <strong id="checkout-total">Rs. 0.00</strong>
            </div>

            <p id="checkout-error" class="bh-checkout-error" hidden></p>

            <button
                type="submit"
                id="place-order-btn"
                class="bh-place-order"
            >
                Place Order <span>→</span>
            </button>

            <p class="bh-checkout-secure">
                ♥ Made with love by BITEHUB
            </p>

            <a href="menu.php" class="bh-back-menu">
                ← Continue Shopping
            </a>

        </aside>

    </form>

    <section id="order-success" class="bh-order-success" hidden>
        <div class="bh-success-icon">✓</div>
        <p class="bh-checkout-eyebrow">ORDER RECEIVED</p>
        <h2>Thank You for Your Order!</h2>
        <p id="order-success-message">
            Your order has been received.
        </p>
        <a href="menu.php" class="bh-success-button">
            Order More Food
        </a>
    </section>

</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>