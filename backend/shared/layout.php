<?php require_once __DIR__.'/../database/connect.php'; ?>
<?php function app_header(string $title,string $role='doctor',string $active='dashboard'){ ?>
<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?=e($title)?> | USE MED</title><link rel="stylesheet" href="<?=APP_URL?>/assets/usemed.css"><script src="<?=APP_URL?>/assets/app.js" defer></script></head><body><div class="app-shell"><aside class="sidebar"><div class="brand"><div class="brand-mark">UM</div><div><h1>USE MED</h1><p>Connected Hospital Care</p></div></div><nav class="nav">
<?php if($role==='doctor'): ?>
<a class="<?=$active==='dashboard'?'active':''?>" href="<?=APP_URL?>/doctor/dashboard.php">🏥 Dashboard</a>
<a class="<?=$active==='register'?'active':''?>" href="<?=APP_URL?>/doctor/register-patient.php">➕ ลงทะเบียนคนไข้</a>
<a class="<?=$active==='ai'?'active':''?>" href="<?=APP_URL?>/doctor/ai-risk.php">🤖 AI Risk</a>
<a class="<?=$active==='icu'?'active':''?>" href="<?=APP_URL?>/doctor/icu.php">🫁 ICU Ventilator</a>
<a class="<?=$active==='docs'?'active':''?>" href="<?=APP_URL?>/doctor/documents.php">📄 เอกสาร/PDF</a>
<a href="<?=APP_URL?>/doctor/logout.php">ออกจากระบบ</a>
<?php elseif($role==='patient'): ?>
<a class="<?=$active==='portal'?'active':''?>" href="<?=APP_URL?>/patient/portal.php">🧾 ข้อมูลของฉัน</a>
<a class="<?=$active==='docs'?'active':''?>" href="<?=APP_URL?>/patient/documents.php">📄 เอกสารของฉัน</a>
<a class="<?=$active==='support'?'active':''?>" href="<?=APP_URL?>/support.php">🛠 แจ้งปัญหา</a>
<a href="<?=APP_URL?>/patient/logout.php">ออกจากระบบ</a>
<?php else: ?>
<a class="<?=$active==='admin'?'active':''?>" href="<?=APP_URL?>/admin/dashboard.php">Admin</a><a href="<?=APP_URL?>/admin/logout.php">ออกจากระบบ</a>
<?php endif; ?>
</nav></aside><main class="main"><div class="topbar"><div><h2><?=e($title)?></h2><p>ระบบเชื่อมข้อมูลผู้ป่วย แพทย์ LINE Rich Menu, PDF และ AI risk engine</p></div><div class="top-actions"><button type="button" class="btn secondary mini" onclick="history.length>1?history.back():location.href='<?=APP_URL?>/index.php'">← กลับ</button><span class="pill">👤 <?=e($_SESSION['user']['name'] ?? 'Guest')?></span></div></div>
<?php } function app_footer(){ ?></main></div></body></html><?php } ?>
