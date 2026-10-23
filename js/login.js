

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("login-form");

    if (!form) return;

    const passwordInput = document.getElementById("login-password");
    const toggleButton = document.getElementById("toggle-password");
    const eyeIcon = document.getElementById("eye-icon");

    if (toggleButton && passwordInput && eyeIcon) {
        toggleButton.addEventListener("click", () => {
            const isPassword = passwordInput.type === "password";

            passwordInput.type = isPassword ? "text" : "password";

            toggleButton.setAttribute(
                "aria-label",
                isPassword ? "Hide password" : "Show password"
            );

            toggleButton.setAttribute(
                "aria-pressed",
                String(isPassword)
            );

            eyeIcon.innerHTML = isPassword
                ? `
                    <path d="M3 3l18 18"/>
                    <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
                    <path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a15.8 15.8 0 0 1-4 4.8"/>
                    <path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7a10.8 10.8 0 0 0 4-.8"/>
                  `
                : `
                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/>
                    <circle cx="12" cy="12" r="3"/>
                  `;
        });
    }

    form.addEventListener("submit", event => {
        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
            return;
        }

        // Valid form: allow PHP to process the login.
    });
});
