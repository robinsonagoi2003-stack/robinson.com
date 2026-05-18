<?php
require_once __DIR__ . '/functions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$prompt = sanitize($data['prompt'] ?? '');
if (empty($prompt)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a prompt.']);
    exit;
}

$response = ai_insight_response($prompt);
if (strpos(strtolower($prompt), 'task') !== false || strpos(strtolower($prompt), 'automation') !== false) {
    $response .= "\n\nAI Task Automation Tip: Use the task manager to add suggested items, then update statuses as the team completes them.";
}

echo json_encode(['success' => true, 'response' => $response]);
