
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

// CSRF protection
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Mark message as Read or Unread
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";
    $messageId = filter_input(
        INPUT_POST,
        "message_id",
        FILTER_VALIDATE_INT
    );
    $action = $_POST["action"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["csrf_token"], $token)
    ) {
        exit("Invalid request. Please refresh the page.");
    }

    if (!$messageId || !in_array($action, ["read", "unread"], true)) {
        header("Location: admin_messages.php");
        exit;
    }

    $isRead = ($action === "read") ? 1 : 0;

    $update = $conn->prepare(
        "UPDATE contact_messages SET is_read = ? WHERE id = ?"
    );
    $update->bind_param("ii", $isRead, $messageId);
    $update->execute();
    $update->close();

    header("Location: admin_messages.php");
    exit;
}

// Search and filter contact messages
$search = trim($_GET["search"] ?? "");
$filter = $_GET["filter"] ?? "all";

if (!in_array($filter, ["all", "unread", "read"], true)) {
    $filter = "all";
}

$where = [];

if ($filter === "unread") {
    $where[] = "is_read = 0";
} elseif ($filter === "read") {
    $where[] = "is_read = 1";
}

if ($search !== "") {
    $where[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ?)";
}

$sql = "SELECT id, name, email, subject, message, created_at, is_read
        FROM contact_messages";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY is_read ASC, created_at DESC";

$messageStmt = $conn->prepare($sql);

if ($search !== "") {
    $searchTerm = "%" . $search . "%";
    $messageStmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
}

$messageStmt->execute();
$messages = $messageStmt->get_result();
$messageCount = $messages ? $messages->num_rows : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Messages | BITEHUB</title>
    <link rel="stylesheet" href="css/style.css">

    <style>
        .messages-wrap {
            max-width: 1100px;
            margin: 35px auto;
            padding: 20px;
        }

        .messages-heading {
            background: linear-gradient(135deg, #8B0032, #650024);
            color: white;
            padding: 28px;
            border-radius: 16px;
            margin-bottom: 25px;
        }

        .messages-heading h1 {
            color: white;
            margin: 0 0 8px;
        }

        .messages-heading p {
            color: #FCECEF;
            margin: 0;
        }

        .messages-summary {
            background: white;
            border: 1px solid #F0DDE3;
            border-radius: 13px;
            padding: 22px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(89, 14, 43, 0.06);
        }

        .messages-summary h3 {
            color: #666;
            margin: 0 0 10px;
            font-size: 15px;
        }

        .messages-number {
            color: #8B0032;
            font-size: 28px;
            font-weight: bold;
        }

        .messages-list {
            display: grid;
            gap: 18px;
        }

        .message-card {
            background: white;
            border: 1px solid #F0DDE3;
            border-radius: 14px;
            padding: 23px;
            box-shadow: 0 8px 25px rgba(89, 14, 43, 0.05);
            overflow-wrap: anywhere;
        }

        .message-card.unread {
            border-left: 5px solid #8B0032;
            background: #FFFAFB;
        }

        .message-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .message-card h2 {
            color: #650024;
            margin: 0;
            font-size: 20px;
        }

        .message-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-unread {
            color: #8B0032;
            background: #FCECEF;
        }

        .status-read {
            color: #26733B;
            background: #E8F5E9;
        }

        .message-detail {
            margin: 9px 0;
            color: #444;
        }

        .message-detail strong {
            color: #650024;
        }

        .message-body {
            background: #FFF7F8;
            border-radius: 9px;
            padding: 15px;
            margin-top: 15px;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .message-date {
            color: #777;
            font-size: 13px;
            margin-top: 15px;
        }

        .messages-btn,
        .message-action {
            display: inline-block;
            padding: 10px 15px;
            background: #8B0032;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }

        .messages-btn {
            margin-bottom: 20px;
        }

        .messages-btn:hover,
        .message-action:hover,
        .reply-email-btn:hover {
            background: #650024;
        }

        .reply-email-btn {
            display: inline-block;
            padding: 10px 15px;
            background: #8B0032;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            margin-right: 8px;
            margin-bottom: 8px;
        }

        .message-actions {
            margin-top: 16px;
        }

        .no-messages {
            background: white;
            border: 1px solid #F0DDE3;
            border-radius: 13px;
            padding: 30px;
            text-align: center;
            color: #666;
        }

        .message-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            background: white;
            border: 1px solid #F0DDE3;
            border-radius: 13px;
            padding: 18px;
            margin-bottom: 22px;
            box-shadow: 0 8px 25px rgba(89, 14, 43, 0.05);
        }

        .message-filters input,
        .message-filters select {
            min-width: 180px;
            flex: 1;
            padding: 11px 12px;
            border: 1px solid #E5C8D1;
            border-radius: 8px;
            background: white;
            color: #333;
            font: inherit;
        }

        .message-filters button,
        .message-filters .clear-filter {
            display: inline-block;
            padding: 11px 16px;
            background: #8B0032;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }

        .message-filters button:hover,
        .message-filters .clear-filter:hover {
            background: #650024;
        }

        @media (max-width: 600px) {
            .messages-wrap {
                padding: 14px;
            }

            .message-card {
                padding: 16px;
            }
        }
    </style>
</head>

<body>

<?php include "includes/header.php"; ?>

<main class="messages-wrap">

    <a class="messages-btn" href="admin_dashboard.php">
        &larr; Admin Dashboard
    </a>

    <header class="messages-heading">
        <h1>Contact Messages</h1>
        <p>Messages received from BITEHUB customers</p>
    </header>

    <section class="messages-summary">
        <h3>Messages Found</h3>
        <div class="messages-number">
            <?= $messageCount ?>
        </div>
    </section>

    <form method="GET" class="message-filters">
        <input
            type="search"
            name="search"
            placeholder="Search name, email, or subject..."
            value="<?= htmlspecialchars($search, ENT_QUOTES, "UTF-8") ?>"
            aria-label="Search messages"
        >

        <select name="filter" aria-label="Filter messages by read status">
            <option value="all" <?= $filter === "all" ? "selected" : "" ?>>
                All Messages
            </option>
            <option value="unread" <?= $filter === "unread" ? "selected" : "" ?>>
                Unread Only
            </option>
            <option value="read" <?= $filter === "read" ? "selected" : "" ?>>
                Read Only
            </option>
        </select>

        <button type="submit">Search / Filter</button>
        <a class="clear-filter" href="admin_messages.php">Clear</a>
    </form>

    <section class="messages-list">

        <?php if ($messages && $messageCount > 0): ?>

            <?php while ($item = $messages->fetch_assoc()): ?>

                <?php $isRead = (int) $item["is_read"] === 1; ?>

                <article class="message-card <?= $isRead ? "" : "unread" ?>">

                    <div class="message-top">
                        <h2><?= htmlspecialchars(
                            $item["subject"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?></h2>

                        <span class="message-status <?= $isRead ? "status-read" : "status-unread" ?>">
                            <?= $isRead ? "Read" : "Unread" ?>
                        </span>
                    </div>

                    <p class="message-detail">
                        <strong>Customer:</strong>
                        <?= htmlspecialchars(
                            $item["name"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>
                    </p>

                    <p class="message-detail">
                        <strong>Email:</strong>
                        <a href="mailto:<?= htmlspecialchars(
                            $item["email"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>">
                            <?= htmlspecialchars(
                                $item["email"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </a>
                    </p>

                    <div class="message-body"><?= htmlspecialchars(
                        $item["message"],
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?></div>

                    <p class="message-date">
                        Received:
                        <?= htmlspecialchars(
                            $item["created_at"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>
                    </p>

                    <?php
                        $replySubject = "Re: " . $item["subject"];
                        $replyBody = "Hi " . $item["name"] . ",\r\n\r\n";
                    ?>
                    <div class="message-actions">
                        <a
                            class="reply-email-btn"
                            href="mailto:<?= htmlspecialchars($item["email"], ENT_QUOTES, "UTF-8") ?>?subject=<?= rawurlencode($replySubject) ?>&body=<?= rawurlencode($replyBody) ?>"
                        >
                            Reply by Email
                        </a>
                    </div>

                    <form method="POST" class="message-actions">
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars(
                                $_SESSION["csrf_token"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>"
                        >

                        <input
                            type="hidden"
                            name="message_id"
                            value="<?= (int) $item["id"] ?>"
                        >

                        <?php if ($isRead): ?>
                            <button
                                type="submit"
                                name="action"
                                value="unread"
                                class="message-action"
                            >
                                Mark as Unread
                            </button>
                        <?php else: ?>
                            <button
                                type="submit"
                                name="action"
                                value="read"
                                class="message-action"
                            >
                                Mark as Read
                            </button>
                        <?php endif; ?>
                    </form>

                </article>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="no-messages">
                <h2>No Messages Found</h2>
                <p>Try another search term or choose a different filter.</p>
            </div>

        <?php endif; ?>

    </section>

</main>

<?php include "includes/footer.php"; ?>

</body>
</html>
