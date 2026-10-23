
<?php
session_start();
require_once "db.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$registerMessage = "";
$registerSuccess = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $submittedToken = $_POST["csrf_token"] ?? "";

    if (
        !is_string($submittedToken) ||
        !hash_equals($_SESSION["csrf_token"], $submittedToken)
    ) {
        http_response_code(403);
        exit("Invalid security token. Please refresh the page and try again.");
    }

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";
    $terms = $_POST["terms"] ?? "";

    if (
        $name === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
!checkdnsrr(substr(strrchr($email, "@"), 1), "MX") ||
        !preg_match('/^0[0-9]{9}$/', $phone) ||
        strlen($password) < 8 ||
        $terms !== "on"
    ) {
        $registerMessage = "Please enter valid details.";
    } elseif ($password !== $confirmPassword) {
        $registerMessage = "Passwords do not match.";
    } else {
        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $registerMessage = "This email is already registered.";
        } else {
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare(
                "INSERT INTO users
                (full_name, email, phone, password)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssss",
                $name,
                $email,
                $phone,
                $hashedPassword
            );

           
            if ($stmt->execute()) {
                header("Location: login.php?registered=1");
                exit;
            } 
            else {
                $registerMessage =
                    "Registration failed. Please try again.";
            }


            $stmt->close();
        }

        $check->close();
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account | BITEHUB</title>

    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
    <script src="js/register.js" defer></script>
</head>
<body>

<?php include 'includes/header.php'; ?>

<main class="bh-register-page">

    <section class="bh-register-card">

        <div class="bh-register-intro">
            <span class="bh-register-heart">♥</span>
            <p class="bh-register-eyebrow">WELCOME TO BITEHUB</p>
            <h1>Create Your <span>Account</span></h1>
            <p>Join us and make every meal a little more special.</p>
        </div>

       <form id="register-form" class="bh-register-form" method="POST" action="register.php">
        <input
    type="hidden"
    name="csrf_token"
    value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

            <div class="bh-register-field">
                <label for="register-name">Full Name</label>
                <input
                    type="text"
                    id="register-name"
                    name="name"
                    placeholder="Enter your full name"
                    autocomplete="name"
                    required
                >
            </div>

            <div class="bh-register-field">
                <label for="register-email">Email Address</label>
                <input
                    type="email"
                    id="register-email"
                    name="email"
                    placeholder="you@example.com"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="bh-register-field">
                <label for="register-phone">Phone Number</label>
                <input
                    type="tel"
                    id="register-phone"
                    name="phone"
                    placeholder="07X XXX XXXX"
                    pattern="0[0-9]{9}"
                    title="Enter a 10-digit phone number starting with 0"
                    autocomplete="tel"
                    required
                >
            </div>

            <div class="bh-register-field">
                <label for="register-password">Password</label>
                <input
                    type="password"
                    id="register-password"
                    name="password"
                    placeholder="At least 8 characters"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >
            </div>

            <div class="bh-register-field">
                <label for="register-confirm">Confirm Password</label>
                <input
                    type="password"
                    id="register-confirm"
                    name="confirm_password"
                    placeholder="Re-enter your password"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >
            </div>

            <label class="bh-register-terms">
                <input type="checkbox" id="register-terms" name="terms" required>
                <span>I agree to the terms and conditions.</span>
            </label>

            <p id="register-message" class="bh-register-message" role="status"
            <?php if ($registerMessage === "") echo "hidden"; ?>>
                <?= htmlspecialchars($registerMessage, ENT_QUOTES, 'UTF-8') ?>
            </p>

            <button type="submit" class="bh-register-button">
                Create Account <span>→</span>
            </button>

            <p class="bh-register-login">
                Already have an account?
                <a href="login.php">Login</a>
            </p>

        </form>

    </section>

</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
