<?php
require 'db_config.php';

$stmt = $pdo->query("SELECT * FROM saved_words ORDER BY saved_at DESC");
$words = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Saved Words</title>
</head>
<body>
  <h1>Saved Words</h1>
  <ul>
    <?php foreach ($words as $w): ?>
      <li>
        <strong><?=htmlspecialchars($w['word'])?></strong>: <?=htmlspecialchars($w['definition'])?><br />
        <small>Saved at: <?=$w['saved_at']?></small>
      </li>
    <?php endforeach; ?>
  </ul>
  <a href="index.html">Back</a>
</body>
</html>
