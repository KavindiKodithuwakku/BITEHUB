
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("contact-form");
    const feedback = document.getElementById("contact-feedback");

    if (!form || !feedback) return;

    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        feedback.hidden = false;
        feedback.textContent = "Sending your message...";

        try {
            const response = await fetch("contact_submit.php", {
                method: "POST",
                body: new FormData(form)
            });

            const result = await response.json();

            feedback.textContent = result.message;
            feedback.style.color = result.success ? "#26733B" : "#B00020";

            if (result.success) {
                form.reset();
            }
        } catch (error) {
            feedback.textContent =
                "Unable to send your message. Please try again.";
            feedback.style.color = "#B00020";
        } finally {
            button.disabled = false;
        }
    });
});
