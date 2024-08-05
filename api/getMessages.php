<?php

include 'db.php';

$pdo = getDb();

$lastId = $_GET['lastId'] ?? null;

$query = "SELECT * FROM messages ";
$params = [];


if ($lastId) {
    $query .= "WHERE id > ? ";
    $params[] = $lastId;
}



$query .= "ORDER BY id DESC LIMIT 50";

$stmt = $pdo->prepare($query);
$stmt->execute($params);

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($messages as &$msg) {
    $msg['date'] = (new DateTime($msg['date']))->format('Y-m-d H:i:s');
}

$stmt->closeCursor();
releaseDb($pdo);

header('Content-Type: application/json');
echo json_encode(array_reverse($messages));
?>

