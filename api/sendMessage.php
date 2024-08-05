<?php

include 'db.php';

$pdo = getDb();

// Retrieve input data
$name = $_POST['name'] ?? '';
$message = $_POST['message'] ?? '';

// Validate and sanitize input data
$name = htmlspecialchars(strip_tags($name));
$message = htmlspecialchars(strip_tags($message));

if (!$name || !$message) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Name and message are required.']);
    exit;
}
// Insert the message into the table
$stmt = $pdo->prepare('INSERT INTO messages (name, message, date) VALUES (?, ?, datetime("now"))');
$stmt->execute([$name, $message]);

releaseDb($pdo);

// Optionally, send a response
header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
?>
