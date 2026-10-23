
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("register-form");

    if (!form) return;

    const password = document.getElementById("register-password");
    const confirmPassword = document.getElementById("register-confirm");
    const message = document.getElementById("register-message");

    function showMessage(text) {
        message.textContent = text;
        message.hidden = false;
    }

    function clearMessage() {
        message.textContent = "";
        message.hidden = true;
    }

    confirmPassword.addEventListener("input", () => {
        clearMessage();

        if (
            confirmPassword.value &&
            password.value !== confirmPassword.value
        ) {
            confirmPassword.setCustomValidity("Passwords do not match.");
        } else {
            confirmPassword.setCustomValidity("");
        }
    });

    password.addEventListener("input", () => {
        clearMessage();

        if (
            confirmPassword.value &&
            password.value !== confirmPassword.value
        ) {
            confirmPassword.setCustomValidity("Passwords do not match.");
        } else {
            confirmPassword.setCustomValidity("");
        }
    });

    form.addEventListener("submit", event => {
        clearMessage();

        if (password.value.length < 8) {
            event.preventDefault();
            showMessage("Password must contain at least 8 characters.");
            password.focus();
            return;
        }

        if (password.value !== confirmPassword.value) {
            event.preventDefault();
            showMessage("Passwords do not match. Please try again.");
            confirmPassword.focus();
            return;
        }

        // Valid form: allow normal POST submission to register.php
    });
});
