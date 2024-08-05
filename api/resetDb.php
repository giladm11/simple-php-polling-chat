<?php

include 'db.php';

if (isset($_GET['pass']) && $_GET['pass'] == '123') {
    $dbFolder = __DIR__ . '/db';
    
    if (!file_exists($dbFolder)) {
        mkdir($dbFolder);
    }

    $currentDbFile = $dbFolder . '/chat.db';
    $newDbFile = $dbFolder . '/chat_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.db';

    $pdo = null;

    if (file_exists($currentDbFile)) {
        try {
            $pdo = new PDO('sqlite:' . $currentDbFile);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check if there are tables in the database
            $stmt = $pdo->query('SELECT name FROM sqlite_master WHERE type="table"');
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $stmt->closeCursor();

            if (count($tables) > 0) {
                // Check if there are records in the 'messages' table
                $stmt = $pdo->query('SELECT COUNT(*) AS cnt FROM messages');
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $stmt->closeCursor();
                $count = intval($row['cnt']);

                // Copy the database file if there are records
                if ($count > 0) {
                    copy($currentDbFile, $newDbFile);
                }
            }

            // Begin a new transaction
            $pdo->beginTransaction();

            // Drop and recreate the table
            $pdo->exec('DROP TABLE IF EXISTS messages');
            createTable($pdo);

            // Commit the transaction
            $pdo->commit();

        } catch (PDOException $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // Log the error message for debugging
            error_log($e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'An error occurred - ' . $e->getMessage()]);
            exit;
        } finally {
            // Ensure the PDO connection is closed
            releaseDb($pdo);
            $pdo = null;
        }
    } else {
        try {
            $pdo = new PDO('sqlite:' . $currentDbFile);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Begin a new transaction
            $pdo->beginTransaction();

            // Create the table
            createTable($pdo);

            // Commit the transaction
            $pdo->commit();

        } catch (PDOException $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // Log the error message for debugging
            error_log($e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'An error occurred']);
            exit;
        } finally {
            // Ensure the PDO connection is closed
            releaseDb($pdo);
            $pdo = null;
        }
    }

    $response = [
        'success' => true,
        'newDbFile' => $newDbFile,
        'currentDbFile' => $currentDbFile
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} else {
    http_response_code(404);
    exit;
}
