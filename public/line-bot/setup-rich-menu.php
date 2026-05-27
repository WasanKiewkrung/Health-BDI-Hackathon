<?php
require_once __DIR__.'/../../backend/config.php';
if(!LINE_CHANNEL_ACCESS_TOKEN){die("กรุณาตั้ง ENV LINE_CHANNEL_ACCESS_TOKEN ก่อน\n");}
$patientUrl = APP_URL . '/patient/login.php';
$doctorUrl  = APP_URL . '/doctor/login.php';
$richMenu = [
  'size'=>['width'=>2500,'height'=>843],
  'selected'=>true,
  'name'=>'USE MED Main Menu',
  'chatBarText'=>'USE MED',
  'areas'=>[
    ['bounds'=>['x'=>0,'y'=>0,'width'=>1250,'height'=>843],'action'=>['type'=>'uri','label'=>'ผู้ป่วย','uri'=>$patientUrl]],
    ['bounds'=>['x'=>1250,'y'=>0,'width'=>1250,'height'=>843],'action'=>['type'=>'uri','label'=>'แพทย์','uri'=>$doctorUrl]],
  ]
];
function call_line($method,$url,$body=null,$contentType='application/json'){
  $headers=['Authorization: Bearer '.LINE_CHANNEL_ACCESS_TOKEN,'Content-Type: '.$contentType];
  $ch=curl_init($url); curl_setopt($ch,CURLOPT_RETURNTRANSFER,true); curl_setopt($ch,CURLOPT_CUSTOMREQUEST,$method); curl_setopt($ch,CURLOPT_HTTPHEADER,$headers); if($body!==null) curl_setopt($ch,CURLOPT_POSTFIELDS,$body); $res=curl_exec($ch); $code=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch); if($code>=300) die("LINE API error $code: $res\n"); return json_decode($res,true) ?: $res;
}
$res=call_line('POST','https://api.line.me/v2/bot/richmenu',json_encode($richMenu,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
$richMenuId=$res['richMenuId'];
$img=file_get_contents(__DIR__.'/richmenu-usemed.png');
call_line('POST','https://api-data.line.me/v2/bot/richmenu/'.$richMenuId.'/content',$img,'image/png');
call_line('POST','https://api.line.me/v2/bot/user/all/richmenu/'.$richMenuId,'');
echo "Created and set default rich menu: $richMenuId\nPatient: $patientUrl\nDoctor: $doctorUrl\n";
?>
