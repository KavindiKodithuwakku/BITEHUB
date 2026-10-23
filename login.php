

<?php
session_start();
require_once "db.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$loginMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $submittedToken = $_POST["csrf_token"] ?? "";

    if (
        !is_string($submittedToken) ||
        !hash_equals($_SESSION["csrf_token"], $submittedToken)
    ) {
        http_response_code(403);
        exit("Invalid security token. Please refresh the page and try again.");
    }


    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $loginMessage = "Please enter a valid email address.";
    } else {

        $stmt = $conn->prepare(
            "SELECT id, full_name, password, role
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $stmt->bind_result(
            $userId,
            $fullName,
            $hashedPassword,
            $role
        );

        if ($stmt->fetch() && password_verify($password, $hashedPassword)) {

            $stmt->close();

            session_regenerate_id(true);

            $_SESSION["user_id"] = $userId;
            $_SESSION["user_name"] = $fullName;
            $_SESSION["user_role"] = $role;

            if (strtolower($role) === "admin") {
                unset($_SESSION["redirect_after_login"]);
                header("Location: admin_orders.php");
                exit;
            }

            $redirect = $_SESSION["redirect_after_login"]
                ?? ($_POST["redirect"] ?? ($_GET["redirect"] ?? ""));

            unset($_SESSION["redirect_after_login"]);

            $allowedRedirects = [
                "my_orders.php",
                "checkout.php",
                "menu.php",
                "index.php"
            ];

            if (!in_array($redirect, $allowedRedirects, true)) {
                $redirect = "menu.php";
            }

            header("Location: " . $redirect);
            exit;

        } else {
            $stmt->close();
            $loginMessage = "Invalid email or password.";
        }
    }
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | BITEHUB</title>

    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
    <script src="js/login.js" defer></script>
</head>
<body>

<?php include 'includes/header.php'; ?>

<main class="bh-login-page">

    <section class="bh-login-card">

        <div class="bh-login-intro">
            <span class="bh-login-heart">♥</span>

            <p class="bh-login-eyebrow">WELCOME BACK</p>

            <h1>Login to <span>BITEHUB</span></h1>

            <p>Your favourite food is just a few clicks away.</p>
        </div>

        <form id="login-form"
                    class="bh-login-form"
                    method="POST"
                    action="login.php">
                <input
    type="hidden"
    name="csrf_token"
    value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

            <div class="bh-login-field">
                <label for="login-email">Email Address</label>

                <input
                    type="email"
                    id="login-email"
                    name="email"
                    placeholder="you@example.com"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="bh-login-field">
                <div class="bh-login-password-heading">
                    <label for="login-password">Password</label>
 <!--
                    <a href="forgot-password.php">
                        Forgot Password?
                    </a>
-->
                </div>

            
<div class="bh-login-password-box">
    <input
        type="password"
        id="login-password"
        name="password"
        placeholder="Enter your password"
        autocomplete="current-password"
        required
    >

    <button
        type="button"
        id="toggle-password"
        class="bh-show-password"
        aria-label="Show password"
        aria-pressed="false"
    >
        <svg
            id="eye-icon"
            xmlns="http://www.w3.org/2000/svg"
            width="21"
            height="21"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/>
            <circle cx="12" cy="12" r="3"/>
        </svg>
    </button>
</div>

            </div>

            <label class="bh-login-remember">
                <input
                    type="checkbox"
                    id="remember-me"
                    name="remember"
                >
                <span>Remember me</span>
            </label>

            <p id="login-message"
                class="bh-login-message"
                role="status"
                <?php if ($loginMessage === "") echo "hidden"; ?>>
                    <?= htmlspecialchars($loginMessage, ENT_QUOTES, 'UTF-8') ?>
            </p>

            <button type="submit" class="bh-login-button">
                Login <span>→</span>
            </button>

            <p class="bh-login-register">
                Don't have an account?
                <a href="register.php">Create Account</a>
            </p>

            <input
                type="hidden"
                name="redirect"
                value="<?= htmlspecialchars($_GET["redirect"] ?? "", ENT_QUOTES, "UTF-8") ?>">


        </form>

        <div class="bh-login-bottom">
            <span>♥</span> Good food, good mood, every day.
        </div>

    </section>

</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
