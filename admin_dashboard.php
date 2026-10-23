
<?php
session_start();
require_once "db.php";

// User login wela nathnam login page ekata yawanawa
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Admin permission check
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

// Total food items
$foodCount = 0;
$result = $conn->query(
    "SELECT COUNT(*) AS total FROM foods"
);

if ($result) {
    $foodCount = (int) $result->fetch_assoc()["total"];
}

// Total customers (admin accounts exclude karanawa)
$customerCount = 0;
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE LOWER(role) <> 'admin'"
);

if ($result) {
    $customerCount = (int) $result->fetch_assoc()["total"];
}

// Total orders
$orderCount = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total FROM orders"
);

if ($result) {
    $orderCount = (int) $result->fetch_assoc()["total"];
}

// Total sales
$totalSales = 0;

$result = $conn->query(
    "SELECT COALESCE(SUM(total), 0) AS sales
     FROM orders
     WHERE LOWER(TRIM(status)) = 'completed'"
);

if ($result) {
    $totalSales = (float) $result->fetch_assoc()["sales"];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BITEHUB Admin Dashboard</title>

    

<style>
    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: var(--blush, #FFF7F8);
        color: var(--text, #333333);
    }

    .dashboard {
        max-width: 1200px;
        margin: auto;
        padding: 35px 25px;
    }

    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 32px;
        padding: 30px;
        background: linear-gradient(135deg, #8B0032, #650024);
        color: white;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(89, 14, 43, 0.15);
    }

    .topbar h1 {
        margin: 0 0 10px;
        color: white;
        font-size: 30px;
        font-weight: 800;
    }

    .topbar p {
        margin: 0;
        color: #FCECEF;
        font-size: 15px;
    }

    .logout {
        display: inline-block;
        background: white;
        color: #8B0032;
        padding: 12px 22px;
        text-decoration: none;
        font-weight: bold;
        border-radius: 9px;
        transition: 0.25s ease;
    }

    .logout:hover {
        background: #FCECEF;
        transform: translateY(-2px);
    }

    .stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        position: relative;
        overflow: hidden;
        background: white;
        padding: 26px 22px;
        border: 1px solid #F0DDE3;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(89, 14, 43, 0.06);
        transition: transform 0.25s ease,
                    box-shadow 0.25s ease;
    }

    .stat-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: #8B0032;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 28px rgba(89, 14, 43, 0.12);
    }

    .stat-card h3 {
        margin: 0 0 18px;
        color: #666666;
        font-size: 14px;
        font-weight: 600;
    }

    .stat-card .number {
        color: #8B0032;
        font-size: 29px;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .section {
        background: white;
        padding: 30px;
        border: 1px solid #F0DDE3;
        border-radius: 16px;
        box-shadow: 0 8px 25px rgba(89, 14, 43, 0.05);
    }

    .section h2 {
        margin: 0 0 22px;
        color: #650024;
        font-size: 22px;
    }

    .actions {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
    }

    .actions a {
        display: inline-block;
        padding: 13px 21px;
        background: #8B0032;
        color: white;
        text-decoration: none;
        font-weight: bold;
        border-radius: 9px;
        transition: 0.25s ease;
    }

    .actions a:hover {
        background: #650024;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(89, 14, 43, 0.18);
    }

    @media (max-width: 900px) {
        .stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 520px) {
        .dashboard {
            padding: 20px 14px;
        }

        .topbar {
            padding: 23px 18px;
        }

        .topbar h1 {
            font-size: 24px;
        }

        .stats {
            grid-template-columns: 1fr;
        }

        .section {
            padding: 22px 18px;
        }

        .actions {
            flex-direction: column;
        }

        .actions a {
            text-align: center;
        }
    }
</style>
 <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
    <script src="js/cart.js" defer></script>
</head>
<body>

<?php include 'includes/header.php'; ?>



    <main class="dashboard">

        <header class="topbar">
            <div>
                <h1>BITEHUB Admin Dashboard</h1>
                <p>Welcome to your admin panel!</p>
            </div>

            <a class="logout" href="logout.php">Logout</a>
        </header>

        <section class="stats">
            <div class="stat-card">
                <h3>Total Food Items</h3>
                <div class="number">
                    <?= $foodCount ?>
                </div>
            </div>

            <div class="stat-card">
                <h3>Total Customers</h3>
                <div class="number">
                    <?= $customerCount ?>
                </div>

            </div>
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="number">
                    <?= $orderCount ?>
                </div>
            </div>

            <div class="stat-card">
                <h3>Total Sales</h3>
                <div class="number">
                    Rs. <?= number_format($totalSales, 2) ?>
                </div>
            </div>


        </section>

        
<section class="section">
    <h2>Manage Your Website</h2>

    <div class="actions">
        <a href="admin_manage_food.php">Manage Food</a>
        <a href="admin_add_food.php">Add New Food</a>
        <a href="admin_orders.php">Manage Orders</a>
        <a href="admin_customers.php">Manage Customers</a>
        <a href="admin_sales.php">Sales Reports</a>
        <a href="admin_messages.php">Contact Messages</a>
        
    </div>
</section>


    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
