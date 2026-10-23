
<?php
require_once "db.php";

$databaseFoods = [];

$foodsResult = $conn->query(
    "SELECT id, name, price, category, image, description
     FROM foods
     WHERE is_available = 1
     ORDER BY created_at DESC"
);

if ($foodsResult) {
    while ($food = $foodsResult->fetch_assoc()) {
        $databaseFoods[] = $food;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Our Menu | BITEHUB</title>

    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>
<body>

<?php include 'includes/header.php'; ?>

<!-- HERO SECTION -->
<section class="bh-menu-hero">
    <div class="bh-menu-hero-content">
        <span class="bh-menu-tag">FRESH • TASTY • DELICIOUS</span>

        <h1>Explore Our <span>Menu</span></h1>

        <p>
            Discover your favourites, enjoy every bite,
            and order delicious food with BITEHUB.
        </p>

        <a href="#menu-items" class="bh-menu-explore">
            Explore Food ↓
        </a>
    </div>

    <div class="bh-menu-hero-image">
        <img src="images/menu image.jpeg" alt="Delicious burger">
    </div>
</section>

<!-- MENU SECTION -->
<main class="bh-menu-main" id="menu-items">

    <div class="bh-menu-heading">
        <span class="bh-menu-tag">MADE FOR YOUR CRAVINGS</span>
        <h2>Our Delicious Menu</h2>
        <p>Find something delicious for your next meal.</p>
    </div>

    <!-- SEARCH -->
    <div class="bh-menu-search">
        <span>⌕</span>
        <input
            type="search"
            id="food-search"
            placeholder="Search your favourite food..."
            aria-label="Search food"
        >
    </div>

    <!-- FILTERS -->
    <div class="bh-menu-filters">
        <button type="button" class="bh-filter active" data-filter="All">All Foods</button>
        <button type="button" class="bh-filter" data-filter="rice">Rice &amp; Meals</button>
        <button type="button" class="bh-filter" data-filter="drinks">Juice &amp; Drinks</button>
        <button type="button" class="bh-filter" data-filter="short eats">Short Eats</button>
        <button type="button" class="bh-filter" data-filter="fast food">Fast Food</button>
        <button type="button" class="bh-filter" data-filter="desserts">Sweets &amp; Desserts</button>
    </div>

    <!-- DATABASE FOOD CARDS -->
    <div class="bh-menu-grid">

        <?php foreach ($databaseFoods as $food): ?>

            <?php
                $foodCategory = strtolower(trim($food["category"] ?? ""));

                if (in_array($foodCategory, ["rice", "rice & meals", "meals"], true)) {
                    $filterCategory = "rice";
                } elseif (in_array($foodCategory, ["drinks", "juice & drinks"], true)) {
                    $filterCategory = "drinks";
                } elseif ($foodCategory === "short eats") {
                    $filterCategory = "short eats";
                } elseif ($foodCategory === "fast food") {
                    $filterCategory = "fast food";
                } elseif (in_array($foodCategory, ["desserts", "sweets & desserts"], true)) {
                    $filterCategory = "desserts";
                } else {
                    $filterCategory = $foodCategory;
                }

                $foodName = $food["name"] ?? "";
                $foodImage = trim($food["image"] ?? "");
                $foodDescription = trim($food["description"] ?? "");
            ?>

            <article
                class="bh-food-card"
                data-category="<?= htmlspecialchars($filterCategory, ENT_QUOTES, 'UTF-8') ?>"
                data-name="<?= htmlspecialchars($foodName, ENT_QUOTES, 'UTF-8') ?>"
            >
                <div class="bh-food-image">
                    <img
                        src="<?= htmlspecialchars($foodImage !== "" ? $foodImage : "images/menu image.jpeg", ENT_QUOTES, 'UTF-8') ?>"
                        alt="<?= htmlspecialchars($foodName, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="bh-food-info">

                    <span class="bh-food-category">
                        <?= htmlspecialchars($food["category"] ?? "", ENT_QUOTES, 'UTF-8') ?>
                    </span>

                    <h3>
                        <?= htmlspecialchars($foodName, ENT_QUOTES, 'UTF-8') ?>
                    </h3>

                    <p>
                        <?= htmlspecialchars(
                            $foodDescription !== ""
                                ? $foodDescription
                                : "Freshly prepared and ready to enjoy.",
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>

                    <div class="bh-food-bottom">

                        <strong>
                            Rs. <?= number_format((float)$food["price"], 2) ?>
                        </strong>

                        <button
                            class="bh-add"
                            type="button"
                            data-id="<?= htmlspecialchars($food["id"], ENT_QUOTES, 'UTF-8') ?>"
                            data-name="<?= htmlspecialchars($foodName, ENT_QUOTES, 'UTF-8') ?>"
                            data-price="<?= htmlspecialchars((string)$food["price"], ENT_QUOTES, 'UTF-8') ?>"
                        >+ Add</button>

                    </div>
                </div>
            </article>

        <?php endforeach; ?>

    </div>

    <p id="no-results" hidden>No food found. Try another search.</p>

</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
