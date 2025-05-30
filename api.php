<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if (!isset($_GET['word'])) {
    http_response_code(400);
    echo json_encode(["error" => "No word provided"]);
    exit;
}

$word = urlencode($_GET['word']);
$url = "https://api.dictionaryapi.dev/api/v2/entries/en/{$word}";

$response = file_get_contents($url);
if ($response === FALSE) {
    http_response_code(404);
    echo json_encode(["error" => "Word not found"]);
    exit;
}

echo $response;
