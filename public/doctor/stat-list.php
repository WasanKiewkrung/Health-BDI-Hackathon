<?php require_once __DIR__.'/../../backend/shared/auth.php'; require_role('doctor'); require_once __DIR__.'/../../backend/shared/layout.php';
$pdo=db();
$type=$_GET['type'] ?? 'patients';
$allowed=['patients','visits','high','icu']; if(!in_array($type,$allowed,true)) $type='patients';
$titles=['patients'=>'รายชื่อผู้ป่วยทั้งหมด','visits'=>'รายการ Visit ทั้งหมด','high'=>'ผู้ป่วย AI High Risk','icu'=>'ผู้ป่วย ICU / Ventilator'];
$title=$titles[$type];
if($type==='patients'){
  $rows=$pdo->query('SELECT p.*, GROUP_CONCAT(c.condition_name SEPARATOR ", ") conditions FROM patients p LEFT JOIN patient_conditions c ON c.patient_id=p.id GROUP BY p.id ORDER BY p.hn LIMIT 300')->fetchAll();
} elseif($type==='visits'){
  $rows=$pdo->query('SELECT v.*,p.hn,p.first_name,p.last_name FROM visits v JOIN patients p ON p.id=v.patient_id ORDER BY v.visit_date DESC,v.id DESC LIMIT 400')->fetchAll();
} elseif($type==='high'){
  $rows=$pdo->query("SELECT p.hn,p.first_name,p.last_name,p.sex,p.age,p.bmi,a.score,a.level,a.reasons,a.recommendation,a.assessed_at FROM ai_risk_assessments a JOIN patients p ON p.id=a.patient_id JOIN (SELECT patient_id,MAX(id) mid FROM ai_risk_assessments GROUP BY patient_id) x ON x.mid=a.id WHERE a.level='High' ORDER BY a.score DESC,p.hn LIMIT 300")->fetchAll();
} else {
  $rows=$pdo->query('SELECT vc.*,p.hn,p.first_name,p.last_name,v.visit_date,v.department,v.ward FROM ventilator_cases vc JOIN patients p ON p.id=vc.patient_id LEFT JOIN visits v ON v.id=vc.visit_id ORDER BY vc.recorded_at DESC,vc.id DESC LIMIT 300')->fetchAll();
}
app_header($title,'doctor','dashboard'); ?>
<a class="back" href="dashboard.php">← กลับ Dashboard</a>
<div class="card">
  <div class="stat-filter-title"><div><h3><?=e($title)?></h3><p class="tl-meta">กด HN เพื่อเปิดข้อมูลผู้ป่วย และดู Timeline/เพิ่มการรักษาต่อได้</p></div><span class="badge blue"><?=count($rows)?> รายการ</span></div>
  <div class="table-wrap"><table class="table">
<?php if($type==='patients'): ?>
<tr><th>HN</th><th>ชื่อ</th><th>เพศ/อายุ</th><th>BMI</th><th>โรค/กลุ่มเสี่ยง</th><th>โทร</th><th></th></tr>
<?php foreach($rows as $r): ?><tr><td class="nowrap"><b><?=e($r['hn'])?></b></td><td><?=e($r['first_name'].' '.$r['last_name'])?></td><td><?=e($r['sex'])?> / <?=e($r['age'])?></td><td><?=e($r['bmi'])?></td><td><?=e($r['conditions'] ?: '-')?></td><td><?=e($r['phone'])?></td><td><a class="btn secondary" href="patient-profile.php?hn=<?=urlencode($r['hn'])?>">เปิดข้อมูล</a></td></tr><?php endforeach; ?>
<?php elseif($type==='visits'): ?>
<tr><th>วันที่</th><th>HN</th><th>ชื่อ</th><th>ประเภท</th><th>แผนก/Ward</th><th>แพทย์</th><th>มาเพราะ</th><th></th></tr>
<?php foreach($rows as $r): ?><tr><td class="nowrap"><?=e($r['visit_date'])?></td><td><b><?=e($r['hn'])?></b></td><td><?=e($r['first_name'].' '.$r['last_name'])?></td><td><?=e($r['visit_type'])?></td><td><?=e($r['department'].' / '.$r['ward'])?></td><td><?=e($r['doctor_name'])?></td><td><?=e($r['chief_complaint'])?></td><td><a class="btn secondary" href="visit-detail.php?id=<?=$r['id']?>">ดู Visit</a></td></tr><?php endforeach; ?>
<?php elseif($type==='high'): ?>
<tr><th>HN</th><th>ชื่อ</th><th>เพศ/อายุ</th><th>BMI</th><th>Score</th><th>เหตุผล</th><th>คำแนะนำ</th><th></th></tr>
<?php foreach($rows as $r): ?><tr><td><b><?=e($r['hn'])?></b></td><td><?=e($r['first_name'].' '.$r['last_name'])?></td><td><?=e($r['sex'])?> / <?=e($r['age'])?></td><td><?=e($r['bmi'])?></td><td><span class="badge red"><?=e($r['score'])?> High</span></td><td><?=e(str_replace(['[',']','"'],'',$r['reasons']))?></td><td><?=e($r['recommendation'])?></td><td><a class="btn secondary" href="patient-profile.php?hn=<?=urlencode($r['hn'])?>">เปิดข้อมูล</a></td></tr><?php endforeach; ?>
<?php else: ?>
<tr><th>เวลา</th><th>HN</th><th>ชื่อ</th><th>Ventilator</th><th>Mode</th><th>PIP/PEEP/Vte</th><th>RR</th><th>AI</th><th></th></tr>
<?php foreach($rows as $r): ?><tr><td class="nowrap"><?=e($r['recorded_at'])?></td><td><b><?=e($r['hn'])?></b></td><td><?=e($r['first_name'].' '.$r['last_name'])?></td><td><?=e($r['ventilator_id'])?></td><td><?=e($r['mode_name'])?></td><td><?=e($r['pip'].' / '.$r['peep'].' / '.$r['vte'])?></td><td><?=e($r['rr'])?></td><td><span class="badge <?=$r['ai_level']==='High'?'red':($r['ai_level']==='Moderate'?'warn':'')?>"><?=e($r['ai_level'])?></span></td><td><a class="btn secondary" href="patient-profile.php?hn=<?=urlencode($r['hn'])?>">เปิดข้อมูล</a></td></tr><?php endforeach; ?>
<?php endif; ?>
  </table></div>
  <?php if(!count($rows)): ?><div class="empty">ยังไม่มีข้อมูลในหมวดนี้</div><?php endif; ?>
</div>
<?php app_footer(); ?>