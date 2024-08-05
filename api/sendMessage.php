<?php

include 'cors.php';  

// Connect to the SQLite database
$pdo = new PDO('sqlite:chat.db');

// // Check if the table exists
// $tableCheckQuery = "SELECT name FROM sqlite_master WHERE type='table' AND name='messages';";
// $tableCheck = $pdo->query($tableCheckQuery);

// if ($tableCheck->rowCount() === 0) {
//     // Table does not exist, so create it
//     $createTableQuery = "CREATE TABLE messages (
//         id INTEGER PRIMARY KEY AUTOINCREMENT,
//         name TEXT NOT NULL,
//         message TEXT NOT NULL,
//         date DATETIME DEFAULT CURRENT_TIMESTAMP
//     );";
//     $pdo->exec($createTableQuery);
// }

// Retrieve input data
$name = $_POST['name'] ?? '';
$message = $_POST['message'] ?? '';

// Validate and sanitize input data
$name = htmlspecialchars(strip_tags($name));
$message = htmlspecialchars(strip_tags($message));

// Insert the message into the table
$stmt = $pdo->prepare('INSERT INTO messages (name, message, date) VALUES (?, ?, datetime("now"))');
$stmt->execute([$name, $message]);

// Optionally, send a response
header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
?>
