<?php
header('Content-Type: application/json');

// Get the word from JSON input
$input = json_decode(file_get_contents('php://input'), true);
$word = $input['word'] ?? '';

if (!$word) {
    echo json_encode(['status' => 'error', 'message' => 'No word provided']);
    exit;
}

try {
    // Connect to MySQL (adjust `root` and '' as needed)
    $pdo = new PDO('mysql:host=localhost;dbname=saved_words;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Delete word
    $stmt = $pdo->prepare('DELETE FROM saved_words WHERE word = :word');
    $stmt->execute([':word' => $word]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Word not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
