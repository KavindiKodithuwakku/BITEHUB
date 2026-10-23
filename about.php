
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>About Us | BITEHUB</title>

    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>
<body>

<?php include 'includes/header.php'; ?>

<main class="bh-about-page">

    <!-- HERO SECTION -->
    <section class="bh-about-hero">
        <div class="bh-about-hero-content">
            <span class="bh-about-label">OUR STORY</span>

            <h1>Good Food.<br>
                <span>Good Mood.</span>
            </h1>

            <p>
                At BITEHUB, we believe great food brings people together.
                We make ordering your favourite meals simple, convenient
                and enjoyable — one delicious bite at a time.
            </p>

            <a href="menu.php" class="bh-about-button">
                Explore Our Menu <span>→</span>
            </a>
        </div>

        <div class="bh-about-hero-image">
            <img
                src="images/about us.avif"
                alt="Fresh and delicious food served in a bowl"
            >

            <div class="bh-about-image-badge">
                <span>♥</span>
                <div>
                    <strong>Made with love</strong>
                    <small>Served with happiness</small>
                </div>
            </div>
        </div>
    </section>

    <!-- OUR STORY -->
    <section class="bh-about-story">
        <div class="bh-about-story-image">
            <img
                src="images/aboutpage.avif"
                alt="A delicious meal ready to be enjoyed"
                loading="lazy"
            >
        </div>

        <div class="bh-about-story-content">
            <span class="bh-about-label">WHO WE ARE</span>

            <h2>Bringing Your Favourite Flavours <span>Closer.</span></h2>

            <p>
                BITEHUB was created to make enjoying good food easier.
                We bring a variety of delicious meal options together
                in one convenient place.
            </p>

            <p>
                Whether you're taking a quick break, sharing a meal
                with friends or enjoying a quiet moment, we want every
                order to feel special.
            </p>

            <div class="bh-about-points">
                <div class="bh-about-point">
                    <span class="bh-about-point-icon">✓</span>
                    <div>
                        <strong>Easy Ordering</strong>
                        <p>A simple and convenient ordering experience.</p>
                    </div>
                </div>

                <div class="bh-about-point">
                    <span class="bh-about-point-icon">♥</span>
                    <div>
                        <strong>Food You'll Love</strong>
                        <p>A menu made to help you find your favourites.</p>
                    </div>
                </div>

                <div class="bh-about-point">
                    <span class="bh-about-point-icon">✦</span>
                    <div>
                        <strong>Customer First</strong>
                        <p>Your satisfaction is always important to us.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MISSION & VISION -->
    <section class="bh-about-values">
        <div class="bh-about-section-heading">
            <span class="bh-about-label">WHAT DRIVES US</span>
            <h2>Our Mission & <span>Vision</span></h2>
            <p>More than food — an experience worth coming back for.</p>
        </div>

        <div class="bh-about-value-grid">
            <article class="bh-about-value-card">
                <div class="bh-about-value-icon">♡</div>
                <h3>Our Mission</h3>
                <p>
                    To make ordering delicious food simple, accessible
                    and enjoyable while putting our customers first.
                </p>
            </article>

            <article class="bh-about-value-card">
                <div class="bh-about-value-icon">✧</div>
                <h3>Our Vision</h3>
                <p>
                    To become a favourite destination for people who
                    appreciate great food and a smooth ordering experience.
                </p>
            </article>

            <article class="bh-about-value-card">
                <div class="bh-about-value-icon">♥</div>
                <h3>Our Promise</h3>
                <p>
                    To keep improving the way you discover, choose
                    and order your favourite meals.
                </p>
            </article>
        </div>
    </section>

    <!-- CTA SECTION -->
    <section class="bh-about-cta">
        <div>
            <span class="bh-about-cta-heart">♥</span>
            <h2>Ready for Something Delicious?</h2>
            <p>Your next favourite meal could be just a click away.</p>
            <a href="menu.php" class="bh-about-cta-button">
                Order Your Favourites →
            </a>
        </div>
    </section>

</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
