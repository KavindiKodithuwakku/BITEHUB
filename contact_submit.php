
<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Invalid request."
    ]);
    exit;
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$subject = trim($_POST["subject"] ?? "");
$message = trim($_POST["message"] ?? "");

if (
    $name === "" ||
    $email === "" ||
    $subject === "" ||
    $message === ""
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Please fill in all fields."
    ]);
    exit;
}

if (
    mb_strlen($name) > 100 ||
    mb_strlen($email) > 150 ||
    mb_strlen($subject) > 100 ||
    mb_strlen($message) > 2000 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Please check your details and try again."
    ]);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO contact_messages (name, email, subject, message)
     VALUES (?, ?, ?, ?)"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Unable to save your message right now."
    ]);
    exit;
}

$stmt->bind_param("ssss", $name, $email, $subject, $message);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Thank you! Your message has been sent successfully."
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Unable to save your message. Please try again."
    ]);
}

$stmt->close();
$conn->close();
?>
