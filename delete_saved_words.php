<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "saved_words");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$word = $data['word'] ?? null;

if ($word === null) {
    // No word provided — delete ALL words
    $sql = "DELETE FROM saved_words";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "All words deleted"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
} else {
    // Delete specific word with prepared statement
    $stmt = $conn->prepare("DELETE FROM saved_words WHERE words = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Prepare statement failed: " . $conn->error]);
        exit;
    }
    $stmt->bind_param("s", $word);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["status" => "success", "message" => "Word deleted"]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Word not found"]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
    $stmt->close();
}

$conn->close();
?>
