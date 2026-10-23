
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contact Us | BITEHUB</title>

    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
    <script src="js/contact.js" defer></script>
</head>
<body>

<?php include 'includes/header.php'; ?>

<main class="bh-contact-page">

    <!-- HERO -->
    <section class="bh-contact-hero">
        <span class="bh-contact-eyebrow">WE'RE HERE FOR YOU</span>
        <h1>Let's Talk About <span>Food.</span></h1>
        <p>
            Have a question, suggestion or need a little help?
            We'd love to hear from you.
        </p>
    </section>

    <!-- CONTACT CONTENT -->
    <section class="bh-contact-container">

        <!-- CONTACT INFORMATION -->
        <div class="bh-contact-info">
            <span class="bh-contact-label">GET IN TOUCH</span>
            <h2>We'd Love to<br>Hear From You!</h2>

            <p class="bh-contact-description">
                Reach out to us with your questions and feedback.
                We're always happy to help make your BITEHUB
                experience better.
            </p>

            <div class="bh-contact-info-list">

                <div class="bh-contact-info-item">
                    <div class="bh-contact-icon">✉</div>
                    <div>
                        <h3>Email Us</h3>
                        <a href="mailto:hello@bitehub.com">
                            hello@bitehub.com
                        </a>
                    </div>
                </div>

                <div class="bh-contact-info-item">
                    <div class="bh-contact-icon">☎</div>
                    <div>
                        <h3>Call Us</h3>
                        <a href="tel:+94112345678">
                            +94 11 234 5678
                        </a>
                    </div>
                </div>

                <div class="bh-contact-info-item">
                    <div class="bh-contact-icon">⌖</div>
                    <div>
                        <h3>Our Location</h3>
                        <p>Sri Lanka</p>
                    </div>
                </div>

                <div class="bh-contact-info-item">
                    <div class="bh-contact-icon">◷</div>
                    <div>
                        <h3>Opening Hours</h3>
                        <p>Monday – Saturday</p>
                        <p>9:00 AM – 9:00 PM</p>
                    </div>
                </div>

            </div>

            <div class="bh-contact-note">
                <span>♥</span>
                <p>Good food starts with a good conversation.</p>
            </div>
        </div>

        <!-- CONTACT FORM -->
        <div class="bh-contact-form-card">
            <div class="bh-contact-form-heading">
                <h2>Send Us a Message</h2>
                <p>Fill out the form below and say hello!</p>
            </div>

            <form id="contact-form" class="bh-contact-form">

                <div class="bh-contact-field">
                    <label for="contact-name">Your Name</label>
                    <input
                        type="text"
                        id="contact-name"
                        name="name"
                        placeholder="Enter your full name"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="bh-contact-field">
                    <label for="contact-email">Email Address</label>
                    <input
                        type="email"
                        id="contact-email"
                        name="email"
                        placeholder="you@example.com"
                        maxlength="150"
                        required
                    >
                </div>

                <div class="bh-contact-field">
                    <label for="contact-subject">Subject</label>
                    <select id="contact-subject" name="subject" required>
                        <option value="">Choose a subject</option>
                        <option value="Order Inquiry">Order Inquiry</option>
                        <option value="Menu Inquiry">Menu Inquiry</option>
                        <option value="Payment Issue">Payment Issue</option>
                        <option value="Feedback">Feedback</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="bh-contact-field">
                    <label for="contact-message">Your Message</label>
                    <textarea
                        id="contact-message"
                        name="message"
                        rows="5"
                        maxlength="2000"
                        placeholder="How can we help you?"
                        required
                    ></textarea>
                </div>

                <p id="contact-feedback" class="bh-contact-feedback" hidden></p>

                <button type="submit" class="bh-contact-submit">
                    Send Message <span>→</span>
                </button>

                <p class="bh-contact-privacy">
                    ♥ Thank you for getting in touch with BITEHUB.
                </p>

            </form>
        </div>

    </section>

    <!-- BOTTOM BANNER -->
    <section class="bh-contact-bottom">
        <span>MADE WITH LOVE ♥</span>
        <h2>Good Food. Great People. Happy Moments.</h2>
        <a href="menu.php">Explore Our Menu →</a>
    </section>

</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
