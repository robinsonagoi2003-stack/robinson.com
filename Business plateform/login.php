<?php
require_once __DIR__ . '/functions.php';
if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (login_user($email, $password)) {
        flash('Welcome back!', 'success');
        redirect('dashboard.php');
    }
    $errors[] = 'Invalid email or password.';
}

require_once __DIR__ . '/header.php';
?>
<section class="auth-card">
    <h1>Login</h1>
    <?php if ($errors): ?>
        <div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?= sanitize($error); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" action="login.php" class="auth-form">
        <label>Email<input type="email" name="email" value="<?= sanitize($_POST['email'] ?? ''); ?>" required></label>
        <label>Password<input type="password" name="password" required></label>
        <button class="button button-primary" type="submit">Login</button>
    </form>
    <p class="small-text"><a href="reset_request.php">Forgot password?</a></p>
    <p class="small-text">New user? <a href="register.php">Create an account</a>.</p>
</section>
<?php require_once __DIR__ . '/footer.php'; ?>