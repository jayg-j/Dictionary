<?php
header('Content-Type: application/json');
require 'db_config.php';

try {
    $raw = file_get_contents('php://input');
    if (!$raw) {
        echo json_encode(['status' => 'error', 'message' => 'No input received']);
        exit;
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
        exit;
    }

    $word = trim($data['word'] ?? '');
    $definition = trim($data['definition'] ?? '');

    if ($word === '' || $definition === '') {
        echo json_encode(['status' => 'error', 'message' => 'Word or definition missing']);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO saved_words (word, definition) 
        VALUES (:word, :definition) 
        ON DUPLICATE KEY UPDATE 
            definition = VALUES(definition), 
            saved_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute(['word' => $word, 'definition' => $definition]);

    echo json_encode(['status' => 'success', 'message' => 'Word saved']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
