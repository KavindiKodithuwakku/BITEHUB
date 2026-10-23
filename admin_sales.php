
<?php
session_start();
require_once "db.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Check admin permission
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

// Overall sales summary
$completedSales = 0;
$completedOrders = 0;
$pendingOrders = 0;
$totalOrders = 0;

$result = $conn->query(
    "SELECT
        COUNT(*) AS total_orders,
        COALESCE(SUM(CASE
            WHEN LOWER(TRIM(status)) = 'completed'
            THEN 1 ELSE 0
        END), 0) AS delivered_orders,
        COALESCE(SUM(CASE
            WHEN LOWER(TRIM(status)) = 'pending'
            THEN 1 ELSE 0
        END), 0) AS pending_orders,
        COALESCE(SUM(CASE
            WHEN LOWER(TRIM(status)) = 'completed'
            THEN total ELSE 0
        END), 0) AS delivered_sales
     FROM orders"
);

if ($result) {
    $summary = $result->fetch_assoc();

    $totalOrders = (int) $summary["total_orders"];
    $completedOrders = (int) $summary["delivered_orders"];
    $completedSales = (float) $summary["delivered_sales"];
    $pendingOrders = (int) $summary["pending_orders"];
}

// Monthly sales for the current year
$monthlySales = $conn->query(
    "SELECT
        MONTH(created_at) AS month_number,
        COALESCE(SUM(total), 0) AS sales,
        COUNT(*) AS order_count
     FROM orders
     WHERE LOWER(TRIM(status)) = 'completed'
       AND YEAR(created_at) = YEAR(CURDATE())
     GROUP BY MONTH(created_at)
     ORDER BY MONTH(created_at) DESC"
);

$monthNames = [
    1 => "January", 2 => "February", 3 => "March",
    4 => "April", 5 => "May", 6 => "June",
    7 => "July", 8 => "August", 9 => "September",
    10 => "October", 11 => "November", 12 => "December"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sales Reports | BITEHUB</title>
    <link rel="stylesheet" href="css/style.css">

    <style>
        .sales-wrap {
            max-width: 1100px;
            margin: 35px auto;
            padding: 20px;
        }

        .sales-heading {
            background: linear-gradient(135deg, #8B0032, #650024);
            color: white;
            padding: 28px;
            border-radius: 16px;
            margin-bottom: 25px;
        }

        .sales-heading h1 {
            margin: 0 0 8px;
            color: white;
        }

        .sales-heading p {
            margin: 0;
            color: #FCECEF;
        }

        .sales-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }

        .sales-card {
            background: white;
            padding: 23px;
            border: 1px solid #F0DDE3;
            border-radius: 13px;
            box-shadow: 0 8px 25px rgba(89, 14, 43, 0.06);
        }

        .sales-card h3 {
            margin: 0 0 12px;
            color: #666;
            font-size: 15px;
        }

        .sales-number {
            color: #8B0032;
            font-size: 25px;
            font-weight: bold;
            overflow-wrap: anywhere;
        }

        .sales-section {
            background: white;
            padding: 25px;
            border: 1px solid #F0DDE3;
            border-radius: 15px;
            margin-bottom: 25px;
            overflow-x: auto;
        }

        .sales-section h2 {
            color: #650024;
            margin-top: 0;
        }

        .sales-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sales-table th,
        .sales-table td {
            padding: 13px;
            border-bottom: 1px solid #F0DDE3;
            text-align: left;
        }

        .sales-table th {
            background: #FFF7F8;
            color: #650024;
        }

        .sales-btn {
            display: inline-block;
            padding: 10px 15px;
            margin-bottom: 20px;
            background: #8B0032;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }

        .sales-btn:hover {
            background: #650024;
        }

        @media (max-width: 600px) {
            .sales-stats {
                grid-template-columns: 1fr;
            }

            .sales-wrap {
                padding: 14px;
            }
        }
    </style>
</head>

<body>

<?php include "includes/header.php"; ?>

<main class="sales-wrap">

    <a class="sales-btn" href="admin_dashboard.php">
        &larr; Admin Dashboard
    </a>

    <header class="sales-heading">
        <h1>Sales Reports</h1>
        <p>BITEHUB sales and order summary</p>
    </header>

    <section class="sales-stats">

        <div class="sales-card">
            <h3>Total Sales (Completed Orders)</h3>
            <div class="sales-number">
                Rs. <?= number_format($completedSales, 2) ?>
            </div>
        </div>

        <div class="sales-card">
            <h3>Total Orders</h3>
            <div class="sales-number">
                <?= $totalOrders ?>
            </div>
        </div>

        <div class="sales-card">
            <h3>Completed Orders</h3>
            <div class="sales-number">
                <?= $completedOrders ?>
            </div>
        </div>

        <div class="sales-card">
            <h3>Pending Orders</h3>
            <div class="sales-number">
                <?= $pendingOrders ?>
            </div>
        </div>

    </section>

    <section class="sales-section">
        <h2>Monthly Sales — <?= date("Y") ?></h2>

        <table class="sales-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Completed Orders</th>
                    <th>Sales (Rs.)</th>
                </tr>
            </thead>

            <tbody>
            <?php if ($monthlySales && $monthlySales->num_rows > 0): ?>
                <?php while ($month = $monthlySales->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?= $monthNames[(int) $month["month_number"]] ?>
                        </td>
                        <td>
                            <?= (int) $month["order_count"] ?>
                        </td>
                        <td>
                            <?= number_format((float) $month["sales"], 2) ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">
                        No completed sales recorded this year yet.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>

</main>

<?php include "includes/footer.php"; ?>

</body>
</html>