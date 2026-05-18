<?php require_once __DIR__ . '/header.php'; ?>
<section class="hero">
    <div class="hero-copy">
        <h1>Business Management Dashboard</h1>
        <p>Manage clients, projects, sales, tasks, and AI-powered insights in one platform.</p>
        <?php if (!is_logged_in()): ?>
            <div class="hero-actions">
                <a class="button button-primary" href="register.php">Create Account</a>
                <a class="button button-secondary" href="login.php">Login</a>
            </div>
        <?php else: ?>
            <div class="hero-actions">
                <a class="button button-primary" href="dashboard.php">Go to Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
    <div class="hero-image">
        <img src="assets/img/dashboard-illustration.svg" alt="Business dashboard illustration" />
    </div>
</section>
<section class="feature-grid">
    <article>
        <h2>Real Business Visibility</h2>
        <p>Track revenue, expenses, projects and team tasks from a single dashboard.</p>
    </article>
    <article>
        <h2>Secure Authentication</h2>
        <p>Register, login, and reset your password securely using modern hashing.</p>
    </article>
    <article>
        <h2>AI Business Assistant</h2>
        <p>Use an AI module to generate insights, forecasts, and recommendations for your business.</p>
    </article>
</section>
<?php require_once __DIR__ . '/footer.php'; ?>