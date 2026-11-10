<?php
// Test-only CLI bridge, enabling the same HTTP suite against PDO MySQL.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/bootstrap.php';
$input=json_decode(stream_get_contents(STDIN),true,512,JSON_THROW_ON_ERROR);
$db=App\Core\Database::getInstance()->getConnection();
$statement=$db->prepare($input['sql']); $statement->execute($input['params'] ?? []);
echo json_encode($statement->columnCount() ? $statement->fetchAll() : [],JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);
