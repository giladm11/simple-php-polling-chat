<?php

include 'cors.php';  
include 'db.php';

$pdo = getDb();

// Retrieve input data
$name = $_POST['name'] ?? '';
$message = $_POST['message'] ?? '';

// Validate and sanitize input data
$name = htmlspecialchars(strip_tags($name));
$message = htmlspecialchars(strip_tags($message));

// Insert the message into the table
$stmt = $pdo->prepare('INSERT INTO messages (name, message, date) VALUES (?, ?, datetime("now"))');
$stmt->execute([$name, $message]);

releaseDb($pdo);

// Optionally, send a response
header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
?>
