<?php
$sqlite = new PDO('sqlite:storage/preview.sqlite');
$tables = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);
echo "Tables in sqlite:\n" . implode("\n", $tables) . "\n";

