<?php


function createTable($pdo) {
    $query = "CREATE TABLE IF NOT EXISTS messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        message TEXT NOT NULL,
        date DATETIME DEFAULT CURRENT_TIMESTAMP
    );";
    $pdo->exec($query);
}

function releaseDb($pdo) {
    unset($pdo);
}


function getDb() {
    return new PDO('sqlite:' . __DIR__ . '/db/chat.db');
}
