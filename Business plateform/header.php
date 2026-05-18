<?php
require_once __DIR__ . '/functions.php';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Management Platform</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header class="topbar">
    <div class="container nav-wrap">
        <a class="brand" href="index.php">Business Platform</a>
        <nav>
            <a href="index.php">Home</a>
            <?php if (is_logged_in()): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="tasks.php">Tasks</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<?php if ($flash): ?>
    <div class="alert alert-<?= sanitize($flash['type']); ?>"><?= sanitize($flash['message']); ?></div>
<?php endif; ?>
<main class="container">
