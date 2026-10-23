
<?php
require_once "db.php";

$homeFoods = [];

$foodsResult = $conn->query(
    "SELECT id, name, price, category, image, description
     FROM foods
     WHERE is_available = 1
     ORDER BY created_at DESC"
);

if ($foodsResult) {
    while ($food = $foodsResult->fetch_assoc()) {
        $homeFoods[] = $food;
    }
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BITEHUB | University Canteen</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>
<body>

<!-- HEADER -->
<?php include 'includes/header.php'; ?>

<main>

<!-- HERO SECTION -->
<section class="bh-hero">
    <div class="bh-hero-content">
        <span class="bh-pill">♥ WELCOME TO BITEHUB</span>

        <h1>Delicious Food,<br>Just a <span>Click Away!</span></h1>

        <p>
            Discover delicious meals, refreshing drinks and tasty snacks. Find your favourites and order easily.
        </p>

        <a href="#menu" class="bh-button">
            Explore Our Menu <span>→</span>
        </a>

        <div class="bh-trust">
            <span>✓ Fresh meals</span>
            <span>✓ Student-friendly prices</span>
        </div>
    </div>

    <div class="bh-hero-image">
        <img
            src="images/homepage.jpeg"
            alt="Fresh and delicious meal">
        <div class="bh-image-note">
            <span class="bh-note-icon">♥</span>
            <div>
                <strong>Made with care</strong>
                <small>Your campus food favourites</small>
            </div>
        </div>
    </div>
</section>

<!-- CATEGORIES -->
<section class="bh-section" id="categories">
    <div class="bh-section-heading">
        <span class="bh-pill">EXPLORE</span>
        <h2>What Are You <span>Craving?</span></h2>
        <p>Choose your favourite food category.</p>
    </div>

    <div class="bh-category-grid">
        <a href="#menu" class="bh-category-card" data-category-link="Rice & Meals">
            <img src="images/ricecurry category.jpeg">
            <h3>Rice &amp; Meals</h3>
            <p>Filling favourites</p>
        </a>

        <a href="#menu" class="bh-category-card" data-category-link="Drinks">
            <img src="images/juice catagory.jpeg">
            <h3>Juices &amp; Drinks</h3>
            <p>Refresh your day</p>
        </a>

        <a href="#menu" class="bh-category-card" data-category-link="Short Eats">
            <img src="images/short eat category.jpeg" alt="Short eats and snacks">
            <h3>Short Eats</h3>
            <p>Perfect little bites</p>
        </a>

        <a href="#menu" class="bh-category-card" data-category-link="Fast Food">
            <img src="images/fast eat category.jpeg" alt="Burger">
            <h3>Fast Food</h3>
            <p>Big flavour, quick bites</p>
        </a>

        <a href="#menu" class="bh-category-card" data-category-link="Desserts">
            <img src="images/sweet category.jpeg" alt="Sweet dessert">
            <h3>Sweets &amp; Desserts</h3>
            <p>A little sweetness</p>
        </a>
    </div>
</section>

<!-- POPULAR MENU -->
<section class="bh-section bh-menu-section" id="menu">
    <div class="bh-section-heading">
        <span class="bh-pill">OUR MENU</span>
        <h2>Discover Our <span>Delicious Food</span></h2>
        <p>Find something tasty for your next campus break.</p>
    </div>

    <div class="bh-menu-tools">
        <div class="bh-filters">
            <button class="bh-filter active" data-filter="All">All</button>
            <button class="bh-filter" data-filter="Rice & Meals">Rice &amp; Meals</button>
            <button class="bh-filter" data-filter="Drinks">Drinks</button>
            <button class="bh-filter" data-filter="Short Eats">Short Eats</button>
            <button class="bh-filter" data-filter="Fast Food">Fast Food</button>
            <button class="bh-filter" data-filter="Desserts">Desserts</button>
        </div>

        <label class="bh-search">
            <input id="food-search" type="search" placeholder="Search food...">
            <span>⌕</span>
        </label>
    </div>

<div class="bh-food-grid" id="food-grid">

    <?php foreach ($homeFoods as $food): ?>

        <?php
        $category = trim($food['category'] ?? '');

        $categoryMap = [
            'Rice' => 'Rice & Meals',
            'Rice & Meals' => 'Rice & Meals',
            'Juice & Drinks' => 'Drinks',
            'Drinks' => 'Drinks',
            'Short Eats' => 'Short Eats',
            'Fast Food' => 'Fast Food',
            'Desserts' => 'Desserts',
            'Sweets & Desserts' => 'Desserts'
        ];

        $displayCategory = $categoryMap[$category] ?? $category;
        $foodImage = trim($food['image'] ?? '');
        $foodName = $food['name'] ?? '';
        $foodDescription = trim($food['description'] ?? '');
        $foodPrice = (float) $food['price'];
        ?>

        <article
            class="bh-food-card"
            data-category="<?= htmlspecialchars($displayCategory, ENT_QUOTES, 'UTF-8') ?>"
            data-name="<?= htmlspecialchars($foodName, ENT_QUOTES, 'UTF-8') ?>"
        >
            <div class="bh-food-image">
                <?php if ($foodImage !== ''): ?>
                    <img
                        src="<?= htmlspecialchars($foodImage, ENT_QUOTES, 'UTF-8') ?>"
                        alt="<?= htmlspecialchars($foodName, ENT_QUOTES, 'UTF-8') ?>"
                    >
                <?php else: ?>
                    <img src="images/homepage.jpeg" alt="Food">
                <?php endif; ?>

                <span class="bh-food-tag">Popular</span>
            </div>

            <div class="bh-food-info">
                <h3><?= htmlspecialchars($foodName, ENT_QUOTES, 'UTF-8') ?></h3>

                <p><?= htmlspecialchars(
                    $foodDescription !== '' ? $foodDescription : 'A delicious campus favourite.',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?></p>

                <div class="bh-food-bottom">
                    <strong>Rs. <?= number_format($foodPrice, 2) ?></strong>

                    <button
                        class="bh-add"
                        data-id="<?= htmlspecialchars($food['id'], ENT_QUOTES, 'UTF-8') ?>"
                        data-name="<?= htmlspecialchars($foodName, ENT_QUOTES, 'UTF-8') ?>"
                        data-price="<?= htmlspecialchars((string) $foodPrice, ENT_QUOTES, 'UTF-8') ?>"
                    >+ Add to Cart</button>
                </div>
            </div>
        </article>

    <?php endforeach; ?>

</div>

    
    <p class="bh-no-results" id="no-results" hidden>No matching food found. Try another search.</p>

    <div class="bh-center">
        <a href="menu.php" class="bh-button">View Full Menu →</a>
    </div>
</section>

<!-- ABOUT -->
<section class="bh-about" id="about">
    <div class="bh-about-image">
        <img src="images/about us.avif" alt="Freshly prepared food">
        <div class="bh-about-sticker">Good food.<br>Good mood. ♥</div>
    </div>

    <div class="bh-about-content">
        <span class="bh-pill">A LITTLE ABOUT US</span>
        <h2>Every Bite Makes <span>You Smile.</span></h2>
        <p>
            BITEHUB makes campus dining simpler. Explore a variety
            of food, find your favourites, and prepare your order
            with an easy online experience.
        </p>

        <ul class="bh-checklist">
            <li>✓ A variety of campus food favourites</li>
            <li>✓ Easy browsing and food discovery</li>
            <li>✓ A simple, convenient cart</li>
            <li>✓ Designed for busy university days</li>
        </ul>

        <a href="about.php" class="bh-button">Learn More →</a>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="bh-section bh-how">
    <div class="bh-section-heading">
        <span class="bh-pill">SIMPLE &amp; EASY</span>
        <h2>How It <span>Works</span></h2>
        <p>Your next meal is just a few simple steps away.</p>
    </div>

    <div class="bh-steps-grid">
        <article class="bh-step-card">
            <span class="bh-step-number">01</span>
            <h3>Choose Your Food</h3>
            <p>Explore our menu and pick something delicious.</p>
        </article>
        <article class="bh-step-card">
            <span class="bh-step-number">02</span>
            <h3>Place Your Order</h3>
            <p>Add your favourites to the cart and review your order.</p>
        </article>
        <article class="bh-step-card">
            <span class="bh-step-number">03</span>
            <h3>Pick It Up</h3>
            <p>Follow your order details and enjoy your meal.</p>
        </article>
    </div>
</section>

<!-- WHY CHOOSE US -->
<section class="bh-section bh-why">
    <div class="bh-section-heading">
        <span class="bh-pill">WHY BITEHUB?</span>
        <h2>A Better Way to <span>Eat on Campus</span></h2>
    </div>

    <div class="bh-feature-grid">
        <article class="bh-feature-card">
            <div class="bh-feature-icon">⚡</div>
            <h3>Quick &amp; Easy</h3>
            <p>Browse your choices without the hassle.</p>
        </article>
        <article class="bh-feature-card">
            <div class="bh-feature-icon">♧</div>
            <h3>Student Friendly</h3>
            <p>Find convenient meal options for campus life.</p>
        </article>
        <article class="bh-feature-card">
            <div class="bh-feature-icon">♨</div>
            <h3>Fresh Favourites</h3>
            <p>Discover meals and snacks for every break.</p>
        </article>
        <article class="bh-feature-card">
            <div class="bh-feature-icon">♡</div>
            <h3>Made for You</h3>
            <p>A simple ordering experience made for students.</p>
        </article>
    </div>
</section>

<!-- CALL TO ACTION -->
<section class="bh-cta">
    <div>
        <span class="bh-cta-label">YOUR NEXT FAVOURITE MEAL AWAITS</span>
        <h2>Hungry Between Lectures?</h2>
        <p>Take a little break. Find something delicious.</p>
    </div>
    <div class="bh-cta-actions">
        <a href="register.php" class="bh-cta-light">Create Account</a>
        <a href="menu.php" class="bh-cta-outline">View Menu</a>
    </div>
</section>

<!-- CONTACT -->
<section class="bh-section bh-contact" id="contact">
    <div class="bh-section-heading">
        <span class="bh-pill">GET IN TOUCH</span>
        <h2>We're Here to <span>Help</span></h2>
        <p>Questions or feedback? We'd love to hear from you.</p>
    </div>

    <div class="bh-contact-grid">
        <article class="bh-contact-card">
            <div class="bh-contact-icon">📍</div>
            <h3>Our Location</h3>
            <p>University of Kelaniya,<br>Dalugama, Sri Lanka</p>
        </article>
        <article class="bh-contact-card">
            <div class="bh-contact-icon">✉️</div>
            <h3>Email Us</h3>
            <p>canteen@kln.ac.lk</p>
        </article>
        <article class="bh-contact-card">
            <div class="bh-contact-icon">📞</div>
            <h3>Call Us</h3>
            <p>+94 11 290 3903</p>
        </article>
    </div>

    <div class="bh-center">
        <a href="contact.php" class="bh-button">Contact Us →</a>
    </div>
</section>

</main>

<!-- FOOTER -->
<?php include 'includes/footer.php'; ?>

</body>
</html>
