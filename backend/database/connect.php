<?php
require_once __DIR__ . '/../config.php';
function db(): PDO {
  static $pdo = null;
  if($pdo) return $pdo;
  $host = envv('DB_HOST','127.0.0.1');
  $name = envv('DB_NAME','usemed');
  $user = envv('DB_USER','root');
  $pass = envv('DB_PASS','');
  $charset = 'utf8mb4';
  $dsn = "mysql:host=$host;dbname=$name;charset=$charset";
  $pdo = new PDO($dsn,$user,$pass,[
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES=>false,
  ]);
  return $pdo;
}
function e($v){return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');}
?>
