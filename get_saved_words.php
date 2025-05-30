<?php
header('Content-Type: application/json');
require 'db_config.php';

try {
    $stmt = $pdo->query("SELECT word, definition, saved_at FROM saved_words ORDER BY saved_at DESC");
    $words = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($words);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
