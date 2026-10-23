<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories | BITEHUB</title>

    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
    
</head>
<?php
$pageTitle = "Food Categories | BITEHUB";
include 'includes/header.php';
?>

<main class="bh-categories-page">

    <section class="bh-categories-hero">
        <span class="bh-categories-tag">EXPLORE OUR MENU</span>
        <h1>Find Your <span>Favourite Food</span></h1>
        <p>Explore our delicious food categories and discover
           something special for your next meal.</p>
    </section>

    <section class="bh-categories-section">
        <div class="bh-categories-heading">
            <h2>Browse Categories</h2>
            <p>Choose a category to explore our menu.</p>
        </div>

        <div class="bh-categories-grid">

            <article class="bh-categories-card">
                <img src="images/ricecurry category.jpeg"
                     alt="ricecurry">
                <div class="bh-categories-content">
                  <h3>Rice &amp; Meals</h3>
                    <p>filling favourites.</p>
                    <div class="bh-explore-more">
                    <a href="menu.php?category=Rice">Explore Menu →</a></div>
                </div>
            </article>

            <article class="bh-categories-card">
                <img src="images/juice catagory.jpeg"
                     alt="juice">
                <div class="bh-categories-content">
                   <h3>Jiuce &amp; Drinks </h3>
                    <p>refresh your day.</p>
                    <div class="bh-explore-more">
                    <a href="menu.php?category=Drinks">Explore Menu →</a></div>
                </div>
            </article>

            <article class="bh-categories-card">
                <img src="images/short eat category.jpeg"
                     alt="short eats">
                <div class="bh-categories-content">
                    <h3>Short Eats</h3>
                    <p>perfect little bites.</p>
                    <div class="bh-explore-more">
                    <a href="menu.php?category=Short Eats">Explore Menu →</a></div>
                </div>
            </article>

            <article class="bh-categories-card">
                <img src="images/fast eat category.jpeg"
                     alt="fast food">
                <div class="bh-categories-content">
                    <h3>Fast Food</h3>
                    <p>Crispy and tasty chicken favourites.</p>
                    <div class="bh-explore-more">
                    <a href="menu.php?category=Fast Food">Explore Menu →</a></div>

                </div>
            </article>

            <article class="bh-categories-card">
                <img src="images/sweet category.jpeg"
                     alt="Pasta dish">
                <div class="bh-categories-content">
                    <h3>Sweet &amp; Desserts</h3>
                    <p>A little sweetness</p>
                    <div class="bh-explore-more">
                    <a href="menu.php?category=Desserts">Explore Menu →</a></div>
                </div>
            </article>

            

            
        </div>
    </section>

</main>

<?php include 'includes/footer.php'; ?>