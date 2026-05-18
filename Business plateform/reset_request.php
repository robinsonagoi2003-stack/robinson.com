<?php
require_once __DIR__ . '/functions.php';

$errors = [];
$success = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    } else {
        $user = get_user_by_email($email);
        if ($user) {
            $token = create_reset_token($email);
            $sent = send_password_reset_email($email, $token);
            if ($sent) {
                $success = 'A password reset link has been sent to your email address. Check your inbox or spam folder.';
            } else {
                $resetLink = site_url('reset_password.php?token=' . urlencode($token));
                $success = "Email sending is not configured. Use this reset link instead:<br><code><a href=\"$resetLink\">$resetLink</a></code>";
            }
        } else {
            $errors[] = 'No account found with that email.';
        }
    }
}

require_once __DIR__ . '/header.php';
?>
<section class="auth-card">
    <h1>Reset Password</h1>
    <?php if ($errors): ?>
        <div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?= sanitize($error); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success; ?></div>
    <?php endif; ?>
    <form method="post" action="reset_request.php" class="auth-form">
        <label>Email<input type="email" name="email" value="<?= sanitize($_POST['email'] ?? ''); ?>" required></label>
        <button class="button button-primary" type="submit">Create Reset Link</button>
    </form>
    <p class="small-text"><a href="login.php">Back to login</a></p>
</section>
<?php require_once __DIR__ . '/footer.php'; ?>