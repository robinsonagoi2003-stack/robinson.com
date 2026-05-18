<?php
require_once __DIR__ . '/functions.php';

$token = sanitize($_GET['token'] ?? '');
$resetRequest = $token ? get_reset_request($token) : null;
$errors = [];
$success = false;

if (!$resetRequest) {
    $errors[] = 'This password reset link is invalid or has expired.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $resetRequest) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (empty($password) || empty($confirm)) {
        $errors[] = 'Both password fields are required.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }
    if (empty($errors)) {
        reset_password($resetRequest['email'], $password);
        clear_reset_tokens($resetRequest['email']);
        $success = true;
    }
}

require_once __DIR__ . '/header.php';
?>
<section class="auth-card">
    <h1>Choose a New Password</h1>
    <?php if ($errors): ?>
        <div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?= sanitize($error); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success">Your password has been reset successfully. <a href="login.php">Login now</a>.</div>
    <?php else: ?>
        <form method="post" action="reset_password.php?token=<?= sanitize($token); ?>" class="auth-form">
            <label>New Password<input type="password" name="password" required></label>
            <label>Confirm Password<input type="password" name="confirm_password" required></label>
            <button class="button button-primary" type="submit">Save new password</button>
        </form>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/footer.php'; ?>