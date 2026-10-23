
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("checkout-form");

    if (!form) return;

    const itemsContainer = document.getElementById("checkout-items");
    const itemCount = document.getElementById("checkout-item-count");
    const subtotalElement = document.getElementById("checkout-subtotal");
    const deliveryElement = document.getElementById("checkout-delivery");
    const totalElement = document.getElementById("checkout-total");
    const errorElement = document.getElementById("checkout-error");
    const placeOrderBtn = document.getElementById("place-order-btn");
    const paymentMessage = document.getElementById("payment-message");

    const DELIVERY_FEE = 0;

    function getCart() {
        try {
            const cart = JSON.parse(localStorage.getItem("cart") || "[]");
            return Array.isArray(cart) ? cart : [];
        } catch {
            return [];
        }
    }

    function money(amount) {
        return "Rs. " + Number(amount).toLocaleString("en-LK", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function escapeHTML(value) {
        return String(value).replace(/[&<>"']/g, char => ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#39;"
        })[char]);
    }

    function renderCart() {
        const cart = getCart().filter(item =>
            item &&
            item.id &&
            Number.isInteger(Number(item.quantity)) &&
            Number(item.quantity) > 0
        );

        if (cart.length === 0) {
            itemsContainer.innerHTML =
                '<p class="bh-checkout-loading">Your cart is empty. Please add food from the menu.</p>';

            itemCount.textContent = "0 items";
            subtotalElement.textContent = money(0);
            deliveryElement.textContent = money(0);
            totalElement.textContent = money(0);
            placeOrderBtn.disabled = true;
            return;
        }

        placeOrderBtn.disabled = false;

        let subtotal = 0;
        let quantityTotal = 0;

        itemsContainer.innerHTML = cart.map(item => {
            const price = Number(item.price) || 0;
            const quantity = Number(item.quantity);
            const lineTotal = price * quantity;

            subtotal += lineTotal;
            quantityTotal += quantity;

            const imageHTML = item.image
                ? `<img src="${escapeHTML(item.image)}" alt="" class="bh-checkout-item-image">`
                : "";

            return `
                <div class="bh-checkout-item">
                    ${imageHTML}
                    <div class="bh-checkout-item-info">
                        <strong>${escapeHTML(item.name || "")}</strong>
                        <span>${quantity} × ${money(price)}</span>
                    </div>
                    <strong class="bh-checkout-item-price">${money(lineTotal)}</strong>
                </div>
            `;
        }).join("");

        itemCount.textContent =
            `${quantityTotal} ${quantityTotal === 1 ? "item" : "items"}`;

        subtotalElement.textContent = money(subtotal);
        deliveryElement.textContent = money(DELIVERY_FEE);
        totalElement.textContent = money(subtotal + DELIVERY_FEE);
    }

    form.querySelectorAll('input[name="payment"]').forEach(input => {
        input.addEventListener("change", () => {
            if (paymentMessage) {
                paymentMessage.textContent = input.value === "cod"
                    ? "Please pay in cash when collecting your food at the canteen."
                    : "Online payment is a demo option. No real payment will be processed.";
            }
        });
    });

    form.addEventListener("submit", async event => {
        event.preventDefault();

        errorElement.hidden = true;
        errorElement.textContent = "";

        const cart = getCart().filter(item =>
            item &&
            item.id &&
            Number.isInteger(Number(item.quantity)) &&
            Number(item.quantity) > 0
        );

        if (cart.length === 0) {
            errorElement.textContent = "Your cart is empty.";
            errorElement.hidden = false;
            renderCart();
            return;
        }

        if (!form.reportValidity()) return;

        const payment = form.querySelector('input[name="payment"]:checked');

        if (!payment) {
            errorElement.textContent = "Please select a payment method.";
            errorElement.hidden = false;
            return;
        }

        
const csrfToken = document.getElementById("csrf-token")?.value;

if (!csrfToken) {
    errorElement.textContent =
        "Security token missing. Please refresh the page.";
    errorElement.hidden = false;
    return;
}

if (payment.value !== "cod") {
    errorElement.textContent =
        "Please select Pay at Canteen (Cash).";
    errorElement.hidden = false;
    return;
}

const orderData = {
    note: document.getElementById("delivery-note")?.value.trim() || "",
    paymentMethod: "cod",
    csrfToken: csrfToken,
    items: cart.map(item => ({
        id: item.id,
        quantity: Number(item.quantity)
    }))
};


        placeOrderBtn.disabled = true;
        const originalText = placeOrderBtn.textContent;
        placeOrderBtn.textContent = "Placing order...";

        try {
            const response = await fetch("place_order.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(orderData)
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || "Could not place your order.");
            }

           
            localStorage.setItem(
                "bitehubLastOrder",
                JSON.stringify({
                    orderNumber: result.orderNumber,
                    total: Number(result.total),
                    subtotal: cart.reduce(
                        (sum, item) =>
                            sum + Number(item.price || 0) * Number(item.quantity || 0),
                        0
                    ),
                    deliveryFee: 0,
                    paymentMethod: payment.value,
                    items: cart.map(item => ({
                        name: item.name,
                        price: Number(item.price),
                        quantity: Number(item.quantity)
                    })),
                    createdAt: new Date().toISOString()
                })
            );


            localStorage.removeItem("cart");

            window.location.href =
                "confirmation.php?order=" +
                encodeURIComponent(result.orderNumber);

        } catch (error) {
            errorElement.textContent =
                error.message || "Something went wrong. Please try again.";
            errorElement.hidden = false;
            placeOrderBtn.disabled = false;
            placeOrderBtn.textContent = originalText;
        }
    });

    renderCart();
});
