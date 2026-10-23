
document.addEventListener("DOMContentLoaded", () => {
    const itemsContainer = document.getElementById(
        "bh-confirmation-items"
    );

    const subtotalElement = document.getElementById(
        "bh-confirmation-subtotal"
    );

    const deliveryElement = document.getElementById(
        "bh-confirmation-delivery"
    );

    const totalElement = document.getElementById(
        "bh-confirmation-total"
    );

    const orderNumberElement = document.getElementById(
        "bh-order-number"
    );

    const statusElement = document.getElementById(
        "bh-order-status-text"
    );

    const DELIVERY_FEE = 0;

    function money(amount) {
        return "Rs. " + amount.toLocaleString("en-LK", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function getLastOrder() {
        try {
            return JSON.parse(
                localStorage.getItem("bitehubLastOrder") || "null"
            );
        } catch (error) {
            return null;
        }
    }

    function renderConfirmation() {
        const order = getLastOrder();

        // CHECK WHETHER ORDER DETAILS EXIST
        if (!order || !Array.isArray(order.items)) {
            itemsContainer.replaceChildren();

            const message = document.createElement("p");
            message.className = "bh-confirmation-empty";
            message.textContent =
                "No saved order details found. Please place a demo order first.";

            itemsContainer.appendChild(message);

            subtotalElement.textContent = money(0);
            deliveryElement.textContent = money(0);
            totalElement.textContent = money(0);
            orderNumberElement.textContent = "BH-DEMO";
            statusElement.textContent = "No Order Saved";

            return;
        }

        // DISPLAY PAYMENT STATUS
        if (order.paymentMethod === "cod") {
            statusElement.textContent =
                "Demo Order · Cash on Delivery";
        } else {
            statusElement.textContent =
                "Order Placed · Pending — Pay at Canteen (Cash)";
        }

        // DISPLAY ORDER ITEMS
        itemsContainer.replaceChildren();

        let subtotal = 0;

        order.items.forEach((item) => {
            const name = String(
                item.name || item.title || "Food Item"
            );

            const price = Math.max(
                0,
                Number(item.price ?? item.amount ?? 0) || 0
            );

            const quantity = Math.max(
                1,
                Number(item.quantity ?? item.qty ?? 1) || 1
            );

            subtotal += price * quantity;

            const row = document.createElement("div");
            row.className = "bh-confirmation-item";

            const info = document.createElement("div");

            const itemName = document.createElement("div");
            itemName.className = "bh-confirmation-item-name";
            itemName.textContent = name;

            const itemQuantity = document.createElement("div");
            itemQuantity.className = "bh-confirmation-item-quantity";
            itemQuantity.textContent = "Quantity: " + quantity;

            info.append(itemName, itemQuantity);

            const itemPrice = document.createElement("strong");
            itemPrice.className = "bh-confirmation-item-price";
            itemPrice.textContent = money(price * quantity);

            row.append(info, itemPrice);
            itemsContainer.appendChild(row);
        });

        // CALCULATE TOTAL
        const delivery = subtotal > 0
            ? Math.max(
                0,
                Number(order.deliveryFee ?? DELIVERY_FEE) || 0
            )
            : 0;

        const total = subtotal + delivery;

        subtotalElement.textContent = money(subtotal);
        deliveryElement.textContent = money(delivery);
        totalElement.textContent = money(total);

        // DISPLAY ORDER NUMBER
        orderNumberElement.textContent = String(
            order.orderNumber || "BH-DEMO"
        );
    }

    renderConfirmation();
});
