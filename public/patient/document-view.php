<?php
require_once __DIR__.'/../../backend/shared/auth.php';
require_role('patient');
$id=(int)($_GET['id']??0);
$pid=(int)($_SESSION['user']['id']??0);
$st=db()->prepare('SELECT d.*,p.hn,p.first_name,p.last_name,p.sex,p.age,v.visit_date,v.department,v.ward,v.doctor_name FROM documents d JOIN patients p ON p.id=d.patient_id LEFT JOIN visits v ON v.id=d.visit_id WHERE d.id=? AND d.patient_id=?');
$st->execute([$id,$pid]);
$d=$st->fetch();
if(!$d){ http_response_code(404); exit('ไม่พบเอกสารหรือไม่มีสิทธิ์เข้าถึง'); }
$typeMap=[
  'medical_certificate'=>'ใบรับรองแพทย์',
  'symptom_report'=>'รายงานอาการ',
  'lab_request'=>'ใบส่งตรวจ Lab',
  'referral'=>'ใบส่งต่อ / Consult',
  'operation_report'=>'รายงานผ่าตัด / วิสัญญี'
];
?>
<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?=e($typeMap[$d['doc_type']]??'เอกสาร')?> | USE MED</title><link rel="stylesheet" href="<?=APP_URL?>/assets/usemed.css"><script src="<?=APP_URL?>/assets/app.js" defer></script></head><body>
<div class="actions no-print" style="max-width:850px;margin:18px auto"><a class="btn secondary" href="documents.php">← กลับเอกสารของฉัน</a><button class="btn" onclick="printDoc()">พิมพ์ / Save as PDF</button></div>
<div class="doc-page"><h1><?=e($typeMap[$d['doc_type']]??'เอกสารทางการแพทย์')?></h1><p style="text-align:center">USE MED Hospital</p><hr>
<p><b>HN:</b> <?=e($d['hn'])?> <b>ชื่อ:</b> <?=e($d['first_name'].' '.$d['last_name'])?> <b>เพศ/อายุ:</b> <?=e($d['sex'].' / '.$d['age'])?></p>
<p><b>วันที่รับบริการ:</b> <?=e($d['visit_date'])?> <b>แผนก:</b> <?=e($d['department'])?> <b>Ward:</b> <?=e($d['ward'])?></p>
<?php if($d['doc_type']==='referral'):?><p><b>ส่งต่อไปแผนก:</b> <?=e($d['department_to'])?> <b>แพทย์ปลายทาง:</b> <?=e($d['doctor_to'])?></p><?php endif;?>
<h3><?=e($d['title'])?></h3><div style="white-space:pre-wrap;line-height:1.9"><?=e($d['content'])?></div><br><br>
<p style="text-align:right">ลงชื่อแพทย์ ___________________________<br><?=e($d['doctor_name'] ?: '')?></p></div></body></html>
