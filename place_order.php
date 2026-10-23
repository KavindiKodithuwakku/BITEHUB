<?php
session_start();
require_once "db.php";

header("Content-Type: application/json; charset=UTF-8");

function respond($success, $message, $status = 400, $extra = []) {
    http_response_code($status);
    echo json_encode(array_merge([
        "success" => $success,
        "message" => $message
    ], $extra));
    exit;
}

if (!isset($_SESSION["user_id"])) {
    respond(false, "Please login first.", 401);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(false, "Invalid request method.");
}


$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {
    respond(false, "Invalid order request.");
}

$submittedToken = $data["csrfToken"] ?? "";

if (
    !is_string($submittedToken) ||
    !isset($_SESSION["csrf_token"]) ||
    !is_string($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $submittedToken)
) {
    respond(
        false,
        "Security check failed. Please refresh and try again.",
        403
    );
}

if (empty($data["items"]) || !is_array($data["items"])) {
    respond(false, "Your cart is empty.");
}


$address = "Campus Canteen Pickup";
$city = "Campus";

$note = $data["note"] ?? "";

if (!is_string($note)) {
    respond(false, "Invalid order note.");
}

$note = trim($note);

if (strlen($note) > 500) {
    respond(false, "Order note is too long.");
}

$payment = $data["paymentMethod"] ?? "";

if (!is_string($payment) || $payment !== "cod") {
    respond(false, "Please select Pay at Canteen (Cash).");
}

// Existing menu items stay supported until they are moved into the foods table.
$legacyFoods = [
    "menu-rice-01"       => ["Chicken Fried Rice", 850],
    "menu-fast-food-01"  => ["Chicken Burger", 450],
    "menu-fast-food-02"  => ["Cheese Pizza", 1200],
    "menu-rice-02"       => ["Rice and Curry", 250],
    "menu-rice-03"       => ["Egg Fried Rice", 250],
    "menu-short-eats-01" => ["Vegitable Roti", 100],
    "menu-short-eats-02" => ["Fish Bun", 150],
    "menu-drink-01"      => ["Chocolate Milkshake", 250],
    "menu-drink-02"      => ["Orange Juice", 120],
    "menu-drink-03"      => ["Mango Juice", 120],
    "menu-drink-04"      => ["Watermelon Juice", 120],
    "menu-drink-05"      => ["Avocado Juice", 130],
    "menu-drink-06"      => ["Milk Tea", 80],
    "menu-drink-07"      => ["Strawberry Milk Tea", 100],
    "menu-drink-08"      => ["Apple Juice", 100],
    "menu-fast-food-03"  => ["Veggie Pizza", 950],
    "menu-fast-food-04"  => ["Cheese Burger", 180],
    "menu-fast-food-05"  => ["French Fries", 150],
    "menu-short-eat-03"  => ["Chicken Pastry", 150],
    "menu-short-eats-04" => ["Fish Roll", 100],
    "menu-dessert-01"    => ["Ice Cream", 180],
    "menu-dessert-02"    => ["Doughnut", 150],
    "menu-dessert-03"    => ["Chocolate Cake", 80]
];


if (count($data["items"]) > 20) {
    respond(false, "Your cart has too many items. Please reduce your cart.");
}

$mergedItems = [];

foreach ($data["items"] as $item) {
    if (!is_array($item)) {
        respond(false, "Invalid cart item.");
    }

    $id = $item["id"] ?? "";
    $quantity = filter_var(
        $item["quantity"] ?? null,
        FILTER_VALIDATE_INT
    );

    if (
        !is_string($id) ||
        $id === "" ||
        $quantity === false ||
        $quantity < 1 ||
        $quantity > 100
    ) {
        respond(false, "Invalid food item or quantity.");
    }

    if (isset($mergedItems[$id])) {
        $mergedItems[$id] += $quantity;
    } else {
        $mergedItems[$id] = $quantity;
    }

    if ($mergedItems[$id] > 100) {
        respond(false, "Maximum quantity per food item is 100.");
    }
}

$data["items"] = [];

foreach ($mergedItems as $id => $quantity) {
    $data["items"][] = [
        "id" => $id,
        "quantity" => $quantity
    ];
}


$items = [];
$subtotal = 0;
$foodStmt = $conn->prepare(
    "SELECT name, price FROM foods WHERE id = ? AND is_available = 1 LIMIT 1"
);
if (!$foodStmt) {
    error_log("BITEHUB food lookup prepare error: " . $conn->error);
    respond(false, "Unable to process the order right now.", 500);
}

foreach ($data["items"] as $item) {
    $foodId = $item["id"] ?? "";
    $quantity = filter_var($item["quantity"] ?? null, FILTER_VALIDATE_INT);

    if (!is_string($foodId) || $foodId === "" || $quantity === false || $quantity < 1 || $quantity > 100) {
        $foodStmt->close();
        respond(false, "Invalid food item or quantity.");
    }

    $name = null;
    $price = null;

    // Newly added foods use their database IDs (for example, food-a1b2...).
    $foodStmt->bind_param("s", $foodId);
    if (!$foodStmt->execute()) {
        $foodStmt->close();
        respond(false, "Unable to check food availability.", 500);
    }
    $foodStmt->bind_result($dbName, $dbPrice);
    if ($foodStmt->fetch()) {
        $name = $dbName;
        $price = (float) $dbPrice;
    }
    $foodStmt->free_result();

    // Keep the original hardcoded menu items working too.
    if ($name === null && isset($legacyFoods[$foodId])) {
        $name = $legacyFoods[$foodId][0];
        $price = (float) $legacyFoods[$foodId][1];
    }

    if ($name === null || $price === null || $price <= 0) {
        $foodStmt->close();
        respond(false, "A food item in your cart is unavailable. Please refresh the menu.");
    }

    $lineTotal = $price * $quantity;
    $items[] = [
        "name" => $name,
        "price" => $price,
        "quantity" => $quantity,
        "line_total" => $lineTotal
    ];
    $subtotal += $lineTotal;
}
$foodStmt->close();

$deliveryFee = 0;
$total = $subtotal + $deliveryFee;
$userId = (int) $_SESSION["user_id"];
$orderNumber = "BH" . date("ymdHis") . random_int(100, 999);

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare(
        "SELECT full_name, email, phone FROM users WHERE id = ? LIMIT 1"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($customerName, $email, $phone);
    if (!$stmt->fetch()) {
        $stmt->close();
        throw new Exception("Customer account not found.");
    }
    $stmt->close();

    $stmt = $conn->prepare(
        "INSERT INTO orders
        (order_number, user_id, customer_name, email, phone,
         address, city, note, payment_method, subtotal,
         delivery_fee, total, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')"
    );
    $stmt->bind_param(
        "sisssssssddd",
        $orderNumber, $userId, $customerName, $email, $phone,
        $address, $city, $note, $payment, $subtotal, $deliveryFee, $total
    );
    $stmt->execute();
    $orderId = $conn->insert_id;
    $stmt->close();

    $stmt = $conn->prepare(
        "INSERT INTO order_items
        (order_id, food_name, unit_price, quantity, line_total)
        VALUES (?, ?, ?, ?, ?)"
    );
    foreach ($items as $item) {
        $name = $item["name"];
        $price = $item["price"];
        $quantity = $item["quantity"];
        $lineTotal = $item["line_total"];
        $stmt->bind_param("isdid", $orderId, $name, $price, $quantity, $lineTotal);
        $stmt->execute();
    }
    $stmt->close();
    $conn->commit();

    respond(true, "Order saved successfully.", 200, [
        "orderNumber" => $orderNumber,
        "total" => $total
    ]);
} catch (Throwable $e) {
    $conn->rollback();
    error_log("BITEHUB order error: " . $e->getMessage());
    respond(false, "There was a problem saving your order. Please try again.", 500);
}
