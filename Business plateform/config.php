<?php
session_start();

// Database connection settings
$db_host = 'localhost';
$db_name = 'business_management';
$db_user = 'root';
$db_pass = '';

$base_url = '/Business plateform';

// Optional OpenAI API key for external AI integration
$openai_api_key = ''; // Put your OpenAI API key here if you want external AI support.

// SMTP settings for real messaging
$smtp_enabled = false;
$smtp_secure = 'tls';
$smtp_host = 'smtp.example.com';
$smtp_port = 587;
$smtp_user = '';
$smtp_pass = '';
$mail_from = 'no-reply@businessplatform.local';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    try {
        $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE $db_name");
    } catch (PDOException $inner) {
        $pdo = null;
    }
}

// Optional OpenAI API key for external AI integration
$openai_api_key = ''; // Put your OpenAI API key here if you want external AI support.

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function is_logged_in()
{
    return !empty($_SESSION['user']);
}

function flash($message, $type = 'info')
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function get_flash()
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
