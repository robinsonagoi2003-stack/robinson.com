<?php
require_once __DIR__ . '/config.php';

function sanitize($value)
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function site_url($path = '')
{
    global $base_url;
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return rtrim($scheme . '://' . $host . $base_url, '/') . '/' . ltrim($path, '/');
}

function require_login()
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function get_user_by_email($email)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function get_user_by_id($id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function create_user($name, $email, $password)
{
    global $pdo;
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())');
    return $stmt->execute([$name, $email, $hash]);
}

function login_user($email, $password)
{
    $user = get_user_by_email($email);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ];
        return true;
    }
    return false;
}

function create_reset_token($email)
{
    global $pdo;
    $token = bin2hex(random_bytes(16));
    $stmt = $pdo->prepare('INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())');
    $stmt->execute([$email, $token]);
    return $token;
}

function get_reset_request($token)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()');
    $stmt->execute([$token]);
    return $stmt->fetch();
}

function reset_password($email, $password)
{
    global $pdo;
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
    return $stmt->execute([$hash, $email]);
}

function clear_reset_tokens($email)
{
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM password_resets WHERE email = ?');
    $stmt->execute([$email]);
}

function smtp_send_email($to, $subject, $message)
{
    global $smtp_enabled, $smtp_host, $smtp_port, $smtp_user, $smtp_pass, $smtp_secure, $mail_from;
    if (!$smtp_enabled || empty($smtp_host) || empty($smtp_port)) {
        return false;
    }

    $remoteHost = ($smtp_secure === 'ssl') ? 'ssl://' . $smtp_host : $smtp_host;
    $errno = 0;
    $errstr = '';
    $socket = stream_socket_client($remoteHost . ':' . $smtp_port, $errno, $errstr, 15, STREAM_CLIENT_CONNECT);
    if (!$socket) {
        return false;
    }

    stream_set_timeout($socket, 15);
    $response = fgets($socket, 515);
    if (strpos($response, '220') !== 0) {
        fclose($socket);
        return false;
    }

    fwrite($socket, "EHLO localhost\r\n");
    $response = fgets($socket, 515);

    if ($smtp_secure === 'tls') {
        fwrite($socket, "STARTTLS\r\n");
        $response = fgets($socket, 515);
        if (strpos($response, '220') !== 0) {
            fclose($socket);
            return false;
        }
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        fwrite($socket, "EHLO localhost\r\n");
        $response = fgets($socket, 515);
    }

    if (!empty($smtp_user) && !empty($smtp_pass)) {
        fwrite($socket, "AUTH LOGIN\r\n");
        fgets($socket, 515);
        fwrite($socket, base64_encode($smtp_user) . "\r\n");
        fgets($socket, 515);
        fwrite($socket, base64_encode($smtp_pass) . "\r\n");
        fgets($socket, 515);
    }

    fwrite($socket, "MAIL FROM:<{$mail_from}>\r\n");
    fgets($socket, 515);
    fwrite($socket, "RCPT TO:<{$to}>\r\n");
    fgets($socket, 515);
    fwrite($socket, "DATA\r\n");
    fgets($socket, 515);

    $headers = "From: {$mail_from}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $emailMessage = "Subject: {$subject}\r\n" . $headers . "\r\n" . $message . "\r\n.\r\n";
    fwrite($socket, $emailMessage);
    fgets($socket, 515);
    fwrite($socket, "QUIT\r\n");
    fclose($socket);
    return true;
}

function send_password_reset_email($email, $token)
{
    global $mail_from;
    $resetLink = site_url('reset_password.php?token=' . urlencode($token));
    $subject = 'Password reset request for your business platform account';
    $message = "<p>Hello,</p><p>You requested a password reset. Click the link below to set a new password:</p><p><a href=\"{$resetLink}\">{$resetLink}</a></p><p>If you did not request this, please ignore this email.</p>";

    if (smtp_send_email($email, $subject, $message)) {
        return true;
    }

    if (function_exists('mail')) {
        $headers = "From: {$mail_from}\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        return mail($email, $subject, $message, $headers);
    }

    return false;
}

function record_activity($message, $type = 'system')
{
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO activity (message, type, created_at) VALUES (?, ?, NOW())');
    $stmt->execute([$message, $type]);
}

function fetch_dashboard_metrics()
{
    global $pdo;
    $metrics = [];
    $metrics['total_revenue'] = $pdo->query('SELECT SUM(amount) FROM orders')->fetchColumn() ?: 0;
    $metrics['total_orders'] = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn() ?: 0;
    $metrics['total_customers'] = $pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn() ?: 0;
    $currentRevenue = $pdo->query('SELECT SUM(amount) FROM orders WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())')->fetchColumn() ?: 0;
    $previousRevenue = $pdo->query('SELECT SUM(amount) FROM orders WHERE MONTH(created_at) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))')->fetchColumn() ?: 0;
    $metrics['growth_rate'] = $previousRevenue > 0 ? round((($currentRevenue - $previousRevenue) / $previousRevenue) * 100, 1) : ($currentRevenue > 0 ? 100.0 : 0.0);
    $metrics['active_tasks'] = $pdo->query('SELECT COUNT(*) FROM tasks WHERE status != "completed"')->fetchColumn() ?: 0;
    return $metrics;
}

function fetch_recent_activity()
{
    global $pdo;
    $stmt = $pdo->query('SELECT * FROM activity ORDER BY created_at DESC LIMIT 8');
    return $stmt->fetchAll();
}

function fetch_task_list()
{
    global $pdo;
    $stmt = $pdo->query('SELECT * FROM tasks ORDER BY created_at DESC');
    return $stmt->fetchAll();
}

function add_task($title, $notes)
{
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO tasks (title, notes, status, created_at) VALUES (?, ?, "pending", NOW())');
    return $stmt->execute([$title, $notes]);
}

function get_task_by_id($id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function update_task($id, $title, $notes, $status)
{
    global $pdo;
    $stmt = $pdo->prepare('UPDATE tasks SET title = ?, notes = ?, status = ? WHERE id = ?');
    return $stmt->execute([$title, $notes, $status, $id]);
}

function delete_task($id)
{
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ?');
    return $stmt->execute([$id]);
}

function update_task_status($id, $status)
{
    global $pdo;
    $stmt = $pdo->prepare('UPDATE tasks SET status = ? WHERE id = ?');
    return $stmt->execute([$status, $id]);
}

function ai_insight_response($prompt)
{
    global $openai_api_key;

    if (!empty($openai_api_key) && function_exists('curl_init')) {
        $payload = json_encode([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a business dashboard assistant.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => 300,
        ]);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $openai_api_key,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $result = json_decode($response, true);
            if (!empty($result['choices'][0]['message']['content'])) {
                return trim($result['choices'][0]['message']['content']);
            }
        }
    }

    $prompt = strtolower($prompt);
    if (strpos($prompt, 'forecast') !== false) {
        return "AI Insight: Your revenue trend shows stable growth. Focus on raising prices for best-selling services and reduce costs in operations. Plan a marketing campaign targeted at repeat customers for the next quarter.";
    }
    if (strpos($prompt, 'optimize') !== false || strpos($prompt, 'efficiency') !== false) {
        return "AI Insight: Automate recurring tasks and standardize approval flows. Use task templates for common projects, and centralize expense tracking to avoid overspending.";
    }
    if (strpos($prompt, 'sales') !== false || strpos($prompt, 'growth') !== false) {
        return "AI Insight: Cross-sell existing clients by bundling services and offer a loyalty discount. Track sales by source to double down on high-performing channels.";
    }
    if (strpos($prompt, 'task') !== false || strpos($prompt, 'automation') !== false) {
        return "AI Task Assistant: Create tasks for improved workflow such as 'Review unpaid orders', 'Follow up with high-value customers', and 'Create monthly revenue forecast report'. Add task notes for clear execution and assign deadlines for each item.";
    }

    return "AI Assistant: Review your current revenue, orders, customers, and tasks regularly. Set weekly goals, keep customer follow-up tight, and prioritize high-value clients to maximize margins.";
}
