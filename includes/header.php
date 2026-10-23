
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header class="bh-header">

    <a href="index.php" class="bh-logo">♥ BITEHUB</a>

    <nav class="bh-nav">
        <a href="index.php">Home</a>
        <a href="categories.php">Categories</a>
        <a href="menu.php">Our Menu</a>
        <a href="about.php">About Us</a>
        <a href="contact.php">Contact Us</a>
        <a href="my_orders.php">My Orders</a>

        
<?php if (isset($_SESSION["user_id"]) && ($_SESSION["user_role"] ?? "") === "admin"): ?>
    <a href="admin_dashboard.php">Admin Dashbord</a>
<?php endif; ?>


    </nav>

    <div class="bh-actions">

        <?php if (isset($_SESSION["user_id"])): ?>

            <span class="bh-welcome">
                Hi, <?= htmlspecialchars($_SESSION["user_name"] ?? "User", ENT_QUOTES, "UTF-8") ?>
            </span>

            <a href="logout.php" class="bh-login">Logout</a>

        <?php else: ?>

            <a href="login.php" class="bh-login">Login</a>
             <a href="register.php" class="bh-login">Register</a>
            

        <?php endif; ?>

        <a href="cart.php" class="bh-cart">
            Cart <span id="cart-count">0</span>
        </a>

    </div>

</header>