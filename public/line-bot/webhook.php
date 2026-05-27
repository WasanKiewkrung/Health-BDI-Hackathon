<?php
require_once __DIR__.'/../../backend/config.php';
$body = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'] ?? '';
$hash = base64_encode(hash_hmac('sha256', $body, LINE_CHANNEL_SECRET, true));
if(LINE_CHANNEL_SECRET && !hash_equals($hash, $signature)){ http_response_code(400); echo 'Invalid signature'; exit; }
$data=json_decode($body,true); if(!$data){echo 'OK'; exit;}
function line_reply($replyToken,$text){
  if(!LINE_CHANNEL_ACCESS_TOKEN) return;
  $payload=json_encode(['replyToken'=>$replyToken,'messages'=>[['type'=>'text','text'=>$text]]],JSON_UNESCAPED_UNICODE);
  $ch=curl_init('https://api.line.me/v2/bot/message/reply');
  curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.LINE_CHANNEL_ACCESS_TOKEN],CURLOPT_POSTFIELDS=>$payload,CURLOPT_RETURNTRANSFER=>true]);
  curl_exec($ch); curl_close($ch);
}
foreach(($data['events']??[]) as $ev){
  $token=$ev['replyToken']??'';
  if(($ev['type']??'')==='follow') line_reply($token,"ยินดีต้อนรับสู่ USE MED กด Rich Menu ด้านล่างเพื่อเลือก ผู้ป่วย หรือ แพทย์");
  if(($ev['type']??'')==='message') line_reply($token,"กดเมนูด้านล่าง: ผู้ป่วย หรือ แพทย์ เพื่อเข้าใช้งานระบบ USE MED");
}
echo 'OK';
?>
