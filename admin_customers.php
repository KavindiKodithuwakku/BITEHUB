
<?php
session_start();
require_once "db.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Check admin permission from database
$userId = (int) $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT role FROM users WHERE id = ? LIMIT 1"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || strtolower($user["role"]) !== "admin") {
    http_response_code(403);
    exit("Access denied. Admin only.");
}

// Get customers (exclude admins)
$customers = $conn->query(
    "SELECT id, full_name, email, role
     FROM users
     WHERE LOWER(role) <> 'admin'
     ORDER BY id DESC"
);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Customers | BITEHUB</title>
    <link rel="stylesheet" href="css/style.css">

    <style>
        .customers-wrap {
            max-width: 1100px;
            margin: 35px auto;
            padding: 20px;
        }

        .customers-box {
            background: #fff;
            padding: 25px;
            border: 1px solid #F0DDE3;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(89, 14, 43, 0.06);
        }

        .customers-box h1 {
            color: #8B0032;
            margin-top: 0;
        }

        .customers-table-wrap {
            overflow-x: auto;
        }

        .customers-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }

        .customers-table th,
        .customers-table td {
            padding: 13px;
            text-align: left;
            border-bottom: 1px solid #F0DDE3;
        }

        .customers-table th {
            background: #FFF7F8;
            color: #650024;
        }

        .customers-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background: #8B0032;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }

        .customers-btn:hover {
            background: #650024;
        }
    </style>
</head>
<body>

<?php include "includes/header.php"; ?>

<main class="customers-wrap">
    <a class="customers-btn" href="admin_dashboard.php">
        &larr; Admin Dashboard
    </a>

    <section class="customers-box">
        <h1>Manage Customers</h1>
        <p>Registered BITEHUB customers.</p>

        <div class="customers-table-wrap">
            <table class="customers-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer Name</th>
                        <th>Email</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($customers && $customers->num_rows > 0): ?>
                    <?php while ($customer = $customers->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int) $customer["id"] ?></td>
                            <td>
                                <?= htmlspecialchars(
                                    $customer["full_name"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(
                                    $customer["email"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(
                                    $customer["role"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No customers found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php include "includes/footer.php"; ?>

</body>
</html>
