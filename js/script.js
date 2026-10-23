
document.addEventListener("DOMContentLoaded", () => {
    const filterButtons = document.querySelectorAll(".bh-filter");
    const foodCards = document.querySelectorAll(".bh-food-card");
    const searchInput = document.getElementById("food-search");
    const noResults = document.getElementById("no-results");
    const cartCount = document.getElementById("cart-count");

    const params = new URLSearchParams(window.location.search);
    const homeGrid = document.getElementById("food-grid");
    const isHomePage = Boolean(homeGrid);

    let activeCategory = params.get("category") || "All";

    // Show only 6 items when Home page first loads.
    let showHomePreview =
        isHomePage &&
        activeCategory.toLowerCase() === "all" &&
        !params.has("category");

    // FILTER AND SEARCH
    function filterFood() {
        const searchTerm = searchInput
            ? searchInput.value.trim().toLowerCase()
            : "";

        let visibleCount = 0;
        let matchingIndex = 0;

        const showOnlySix =
            isHomePage &&
            showHomePreview &&
            activeCategory.toLowerCase() === "all" &&
            searchTerm === "";

        foodCards.forEach((card) => {
            const category = (card.dataset.category || "")
                .trim()
                .toLowerCase();

            const name = (card.dataset.name || "")
                .trim()
                .toLowerCase();

            const matchesCategory =
                activeCategory.toLowerCase() === "all" ||
                category === activeCategory.toLowerCase();

            const matchesSearch = name.includes(searchTerm);

            let visible = matchesCategory && matchesSearch;

            // Home page preview: display only the first 6 items.
            if (visible && showOnlySix && matchingIndex >= 6) {
                visible = false;
            }

            if (matchesCategory && matchesSearch) {
                matchingIndex++;
            }

            card.hidden = !visible;

            if (visible) {
                visibleCount++;
            }
        });

        if (noResults) {
            noResults.hidden = visibleCount !== 0;
        }
    }

    // CATEGORY FILTER BUTTONS
    filterButtons.forEach((button) => {
        button.addEventListener("click", () => {
            activeCategory = button.dataset.filter || "All";

            // All shows 6 items; other categories show all matching items.
            showHomePreview =
                isHomePage &&
                activeCategory.toLowerCase() === "all";

            filterButtons.forEach((item) => {
                item.classList.toggle("active", item === button);
            });

            filterFood();
        });
    });

    // SEARCH
    if (searchInput) {
        searchInput.addEventListener("input", () => {
            filterFood();
        });
    }

    // CATEGORY LINKS
    document.querySelectorAll("[data-category-link]").forEach((link) => {
        link.addEventListener("click", (event) => {
            const category = link.dataset.categoryLink || "All";

            // Keep normal navigation to menu.php.
            if ((link.getAttribute("href") || "").includes("menu.php")) {
                return;
            }

            event.preventDefault();

            activeCategory = category;
            showHomePreview =
                isHomePage &&
                activeCategory.toLowerCase() === "all";

            filterButtons.forEach((button) => {
                button.classList.toggle(
                    "active",
                    (button.dataset.filter || "").toLowerCase() ===
                    activeCategory.toLowerCase()
                );
            });

            filterFood();

            document.getElementById("menu")?.scrollIntoView({
                behavior: "smooth"
            });
        });
    });

    // APPLY CATEGORY FROM URL ON PAGE LOAD
    filterButtons.forEach((button) => {
        button.classList.toggle(
            "active",
            (button.dataset.filter || "").toLowerCase() ===
            activeCategory.toLowerCase()
        );
    });

    filterFood();

    // CART HELPERS
    function readCart() {
        try {
            const cart = JSON.parse(localStorage.getItem("cart") || "[]");
            return Array.isArray(cart) ? cart : [];
        } catch (error) {
            return [];
        }
    }

    function updateCartCount() {
        const cart = readCart();

        const count = cart.reduce((total, item) => {
            return total + (Number(item.quantity) || 1);
        }, 0);

        if (cartCount) {
            cartCount.textContent = count;
        }
    }

    // ADD FOOD TO CART
    document.querySelectorAll(".bh-add").forEach((button) => {
        button.addEventListener("click", () => {
            const id = button.dataset.id;
            const name = button.dataset.name;
            const price = Number(button.dataset.price);

            const foodCard = button.closest(".bh-food-card");
            const imageElement = foodCard
                ? foodCard.querySelector(".bh-food-image img")
                : null;

            const image = imageElement
                ? imageElement.getAttribute("src") || ""
                : "";

            const cart = readCart();

            const existingItem = cart.find(
                (item) => String(item.id) === String(id)
            );

            if (existingItem) {
                existingItem.quantity =
                    (Number(existingItem.quantity) || 1) + 1;

                if (!existingItem.image && image) {
                    existingItem.image = image;
                }
            } else {
                cart.push({
                    id: id,
                    name: name,
                    price: price,
                    quantity: 1,
                    image: image
                });
            }

            localStorage.setItem("cart", JSON.stringify(cart));

            updateCartCount();
            window.dispatchEvent(new Event("cartUpdated"));

            const originalText = button.textContent;
            button.textContent = "✓ Added!";
            button.disabled = true;

            setTimeout(() => {
                button.textContent = originalText;
                button.disabled = false;
            }, 900);
        });
    });

    // UPDATE CART COUNT
    updateCartCount();

    window.addEventListener("storage", (event) => {
        if (event.key === "cart") {
            updateCartCount();
        }
    });
});
