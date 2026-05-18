<?php
require_once __DIR__ . '/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $errors[] = 'All fields are required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }
    if (get_user_by_email($email)) {
        $errors[] = 'Email already registered.';
    }

    if (empty($errors)) {
        create_user($name, $email, $password);
        flash('Account created. Please login.', 'success');
        redirect('login.php');
    }
}

require_once __DIR__ . '/header.php';
?>
<section class="auth-card">
    <h1>Register</h1>
    <?php if ($errors): ?>
        <div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?= sanitize($error); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" action="register.php" class="auth-form">
        <label>Name<input type="text" name="name" value="<?= sanitize($_POST['name'] ?? ''); ?>" required></label>
        <label>Email<input type="email" name="email" value="<?= sanitize($_POST['email'] ?? ''); ?>" required></label>
        <label>Password<input type="password" name="password" required></label>
        <label>Confirm Password<input type="password" name="confirm_password" required></label>
        <button class="button button-primary" type="submit">Create account</button>
    </form>
    <p class="small-text">Already have an account? <a href="login.php">Login here</a>.</p>
</section>
<?php require_once __DIR__ . '/footer.php'; ?>