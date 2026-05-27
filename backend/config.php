<?php
session_start();
function envv(string $key, $default=null){
  $v = getenv($key);
  if($v !== false && $v !== '') return $v;
  return $_ENV[$key] ?? $default;
}
define('APP_NAME','USE MED');
define('APP_URL', rtrim(envv('USEMED_PUBLIC_URL','http://localhost/usemed'),'/')); // URL ของโฟลเดอร์ public ที่วางใน htdocs เช่น http://localhost/usemed
define('LINE_CHANNEL_ACCESS_TOKEN', envv('LINE_CHANNEL_ACCESS_TOKEN',''));
define('LINE_CHANNEL_SECRET', envv('LINE_CHANNEL_SECRET',''));
?>
