<?php

include 'cors.php';

$pdo = new PDO('sqlite:chat.db');

$lastId = $_GET['lastId'] ?? null;

$query = "SELECT * FROM messages ";
$params = [];

if ($lastId) {
    $query .= "WHERE id > ? ";
    $params[] = $lastId;
    echo json_encode();
    break;
}

$query .= "ORDER BY id DESC LIMIT 50";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$stmt = $pdo->query('SELECT * FROM messages ORDER BY id DESC LIMIT 50');
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($messages as &$msg) {
    $msg['date'] = (new DateTime($msg['date']))->format('Y-m-d H:i:s');
}

header('Content-Type: application/json');
echo json_encode(array_reverse($messages));
?>

