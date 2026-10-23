
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Cart | BITEHUB</title>

    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
    <script src="js/cart.js" defer></script>
</head>
<body>

<?php include 'includes/header.php'; ?>

<main class="bh-cart-page">

    <section class="bh-cart-heading">
        <span class="bh-cart-eyebrow">YOUR BITEHUB ORDER</span>
        <h1>My <span>Shopping Cart</span></h1>
        <p>Review your favourite meals before checking out.</p>

        <div class="bh-cart-steps">
            <span class="bh-cart-step bh-cart-step-active">
                <b>1</b> Cart
            </span>
            <span class="bh-cart-step-line"></span>
            <span class="bh-cart-step">
                <b>2</b> Checkout
            </span>
            <span class="bh-cart-step-line"></span>
            <span class="bh-cart-step">
                <b>3</b> Confirmation
            </span>
        </div>
    </section>

    <section class="bh-cart-layout">

        <div class="bh-cart-items-panel">
            <div class="bh-cart-panel-heading">
                <h2>Your Items</h2>
                <span id="bh-cart-item-count">0 items</span>
            </div>

            <div id="bh-cart-items" class="bh-cart-items"></div>

            <div id="bh-cart-empty" class="bh-cart-empty" hidden>
                <div class="bh-cart-empty-icon">♡</div>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any delicious food yet.</p>
                <a href="menu.php" class="bh-cart-primary-button">
                    Explore Our Menu →
                </a>
            </div>

            <a href="menu.php" class="bh-cart-continue">
                <span>←</span> Continue Shopping
            </a>
        </div>

        <aside class="bh-cart-summary">
            <h2>Order Summary</h2>

            <div class="bh-cart-summary-row">
                <span>Subtotal</span>
                <strong id="bh-cart-subtotal">Rs. 0.00</strong>
            </div>

            <div class="bh-cart-summary-row">
                <span>Pickup Fee</span>
                <strong id="bh-cart-delivery">Rs. 0.00</strong>
            </div>

            <div class="bh-cart-delivery-note">
                <span>✓</span>
                Free campus canteen pickup
            </div>

            <div class="bh-cart-summary-total">
                <span>Total</span>
                <strong id="bh-cart-total">Rs. 0.00</strong>
            </div>

            <a
                href="checkout.php"
                id="bh-cart-checkout"
                class="bh-cart-checkout-button"
            >
                Proceed to Checkout <span>→</span>
            </a>

            <div class="bh-cart-payment-note">
                <span>♡</span>
                <div>
                    <strong>Secure Checkout</strong>
                    <p>Review your order before placing it.</p>
                </div>
            </div>
        </aside>

    </section>

</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
