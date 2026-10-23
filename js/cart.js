
document.addEventListener("DOMContentLoaded", () => {
    const itemsContainer = document.getElementById("bh-cart-items");
    if (!itemsContainer) return;

    const emptyState = document.getElementById("bh-cart-empty");
    const itemCount = document.getElementById("bh-cart-item-count");
    const subtotalElement = document.getElementById("bh-cart-subtotal");
    const deliveryElement = document.getElementById("bh-cart-delivery");
    const totalElement = document.getElementById("bh-cart-total");
    const checkoutButton = document.getElementById("bh-cart-checkout");

    const DELIVERY_FEE = 0;

    function readCart() {
        try {
            const saved = JSON.parse(localStorage.getItem("cart") || "[]");
            return Array.isArray(saved) ? saved : [];
        } catch (error) {
            return [];
        }
    }

    function getName(item) {
        return String(item.name || item.title || item.foodName || "Food Item");
    }

    function getPrice(item) {
        const price = Number(item.price ?? item.amount ?? 0);
        return Number.isFinite(price) && price >= 0 ? price : 0;
    }

    function getQuantity(item) {
        const quantity = Number(item.quantity ?? item.qty ?? 1);
        return Number.isFinite(quantity)
            ? Math.max(1, Math.min(99, Math.floor(quantity)))
            : 1;
    }

    function getImage(item) {
    return (
        item.image ||
        item.img ||
        item.imageUrl ||
        item.image_url ||
        item.foodImage ||
        item.food_image ||
        item.src ||
        item.photo ||
        ""
    );
}

    function money(amount) {
        return "Rs. " + amount.toLocaleString("en-LK", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function saveCart(cart) {
        localStorage.setItem("cart", JSON.stringify(cart));
        window.dispatchEvent(new Event("cartUpdated"));
    }

    function createElement(tag, className, text) {
        const element = document.createElement(tag);
        if (className) element.className = className;
        if (text !== undefined) element.textContent = text;
        return element;
    }

    function renderCart() {
        const cart = readCart();
        itemsContainer.replaceChildren();

        let subtotal = 0;
        let totalQuantity = 0;

        cart.forEach((item, index) => {
            const price = getPrice(item);
            const quantity = getQuantity(item);
            subtotal += price * quantity;
            totalQuantity += quantity;

            const row = createElement("article", "bh-cart-item");

            const imageBox = createElement("div", "bh-cart-item-image");
            const imageUrl = getImage(item);

        if (imageUrl) {
            const image = document.createElement("img");

            image.src = imageUrl;
            image.alt = getName(item);
            image.loading = "lazy";

            image.addEventListener("error", () => {
                console.log("Cart image could not load:", imageUrl);
                imageBox.replaceChildren(
                    createElement("span", "bh-cart-item-placeholder", "♥")
                );
            }, { once: true });

            imageBox.appendChild(image);
        } else {
            imageBox.appendChild(
                createElement("span", "bh-cart-item-placeholder", "♥")
            );
        }
            

            const info = createElement("div", "bh-cart-item-info");
            info.appendChild(createElement("h3", "", getName(item)));
            info.appendChild(
                createElement("p", "bh-cart-item-price", money(price))
            );

            const quantityBox = createElement("div", "bh-cart-quantity");

            const minus = createElement("button", "", "−");
            minus.type = "button";
            minus.setAttribute("aria-label", "Decrease quantity");

            const quantityText = createElement("span", "", String(quantity));

            const plus = createElement("button", "", "+");
            plus.type = "button";
            plus.setAttribute("aria-label", "Increase quantity");

            minus.disabled = quantity <= 1;
            minus.addEventListener("click", () => {
                const updated = readCart();
                if (!updated[index]) return;

                const currentQuantity = getQuantity(updated[index]);
                if (currentQuantity > 1) {
                    updated[index].quantity = currentQuantity - 1;
                    delete updated[index].qty;
                    saveCart(updated);
                    renderCart();
                }
            });

            plus.addEventListener("click", () => {
                const updated = readCart();
                if (!updated[index]) return;

                const currentQuantity = getQuantity(updated[index]);
                if (currentQuantity < 99) {
                    updated[index].quantity = currentQuantity + 1;
                    delete updated[index].qty;
                    saveCart(updated);
                    renderCart();
                }
            });

            quantityBox.append(minus, quantityText, plus);
            info.appendChild(quantityBox);

            const endBox = createElement("div", "bh-cart-item-end");
            endBox.appendChild(
                createElement("strong", "bh-cart-line-total", money(price * quantity))
            );

            const remove = createElement("button", "bh-cart-remove", "Remove");
            remove.type = "button";
            remove.addEventListener("click", () => {
                const updated = readCart();
                updated.splice(index, 1);
                saveCart(updated);
                renderCart();
            });

            endBox.appendChild(remove);
            row.append(imageBox, info, endBox);
            itemsContainer.appendChild(row);
        });

        const isEmpty = cart.length === 0;

        emptyState.hidden = !isEmpty;
        itemsContainer.hidden = isEmpty;

        itemCount.textContent =
            totalQuantity + (totalQuantity === 1 ? " item" : " items");

        subtotalElement.textContent = money(subtotal);
        deliveryElement.textContent = money(DELIVERY_FEE);

        // Campus canteen pickup has no extra fee.
        totalElement.textContent = money(subtotal);

        checkoutButton.classList.toggle("is-disabled", isEmpty);
        checkoutButton.setAttribute("aria-disabled", String(isEmpty));
        checkoutButton.href = isEmpty ? "menu.php" : "checkout.php";
    }

    checkoutButton.addEventListener("click", event => {
        if (readCart().length === 0) {
            event.preventDefault();
            window.location.href = "menu.php";
        }
    });

    window.addEventListener("storage", event => {
        if (event.key === "cart") renderCart();
    });

    window.addEventListener("cartUpdated", renderCart);

    renderCart();
});
