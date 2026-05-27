<?php
require_once __DIR__.'/../database/connect.php';
function score_level($score){ if($score>=75) return 'High'; if($score>=45) return 'Moderate'; return 'Low'; }
function assess_patient_risk(int $patient_id): array{
  $pdo=db();
  $p=$pdo->prepare('SELECT * FROM patients WHERE id=?'); $p->execute([$patient_id]); $patient=$p->fetch();
  $d=$pdo->prepare('SELECT * FROM diabetes_followups WHERE patient_id=? ORDER BY followup_date DESC,id DESC LIMIT 1'); $d->execute([$patient_id]); $df=$d->fetch() ?: [];
  $v=$pdo->prepare('SELECT * FROM visits WHERE patient_id=? ORDER BY visit_date DESC,id DESC LIMIT 1'); $v->execute([$patient_id]); $visit=$v->fetch() ?: [];
  $score=0; $reason=[];
  $age=(int)($patient['age']??0); if($age>=60){$score+=8;$reason[]='อายุ ≥ 60 ปี';}
  $bmi=(float)($patient['bmi'] ?? ($df['bmi']??0)); if($bmi>=30){$score+=14;$reason[]='BMI สูงมาก';} elseif($bmi>=25){$score+=8;$reason[]='BMI เกินเกณฑ์';}
  $hba1c=(float)($df['hba1c']??0); if($hba1c>=9){$score+=25;$reason[]='HbA1c ≥ 9%';} elseif($hba1c>=7){$score+=15;$reason[]='HbA1c คุมไม่ได้';}
  $fpg=(float)($df['fpg']??0); if($fpg>=180){$score+=16;$reason[]='FPG สูงมาก';} elseif($fpg>=126){$score+=10;$reason[]='FPG สูง';}
  $sbp=(float)($df['sbp'] ?? ($visit['sbp']??0)); $dbp=(float)($df['dbp'] ?? ($visit['dbp']??0));
  if($sbp>=160 || $dbp>=100){$score+=18;$reason[]='ความดันสูงระดับเสี่ยง';} elseif($sbp>=140 || $dbp>=90){$score+=10;$reason[]='ความดันสูง';}
  $egfr=(float)($df['egfr']??0); if($egfr>0 && $egfr<60){$score+=14;$reason[]='eGFR ต่ำ/เสี่ยงไต';}
  $uacr=(float)($df['uacr']??0); if($uacr>=300){$score+=16;$reason[]='UACR สูงมาก';} elseif($uacr>=30){$score+=8;$reason[]='เริ่มมี albuminuria';}
  $ldl=(float)($df['ldl']??0); if($ldl>=130){$score+=8;$reason[]='LDL สูง';}
  $hasHTN=$pdo->prepare("SELECT COUNT(*) FROM patient_conditions WHERE patient_id=? AND condition_name LIKE '%ความดัน%' "); $hasHTN->execute([$patient_id]); if($hasHTN->fetchColumn()>0){$score+=8;$reason[]='มีโรคความดันร่วม';}
  $score=min(100,$score); $level=score_level($score);
  $recommend = $level==='High' ? 'นัดติดตามเร็ว ตรวจ HbA1c/eGFR/UACR และทบทวนยา/insulin' : ($level==='Moderate' ? 'ติดตามตามนัด ปรับพฤติกรรมและทบทวนยา' : 'ควบคุมต่อเนื่อง ตรวจตามรอบ');
  return ['score'=>$score,'level'=>$level,'reasons'=>$reason,'recommendation'=>$recommend];
}
function save_ai_risk(int $patient_id,string $type='diabetes_htn'){
  $r=assess_patient_risk($patient_id);
  $st=db()->prepare('INSERT INTO ai_risk_assessments(patient_id,risk_type,score,level,reasons,recommendation,assessed_at) VALUES(?,?,?,?,?,?,NOW())');
  $st->execute([$patient_id,$type,$r['score'],$r['level'],json_encode($r['reasons'],JSON_UNESCAPED_UNICODE),$r['recommendation']]);
  return $r;
}
function assess_ventilator(array $case): array{
  $score=0; $reason=[];
  if(($case['pip']??0)>35){$score+=25;$reason[]='PIP สูง เสี่ยง barotrauma';}
  if(($case['peep']??0)>12){$score+=16;$reason[]='PEEP สูง ต้องประเมิน oxygenation';}
  if(($case['vte']??0)<250){$score+=18;$reason[]='Vte ต่ำ อาจ ventilation ไม่พอ';}
  if(($case['rr']??0)>30){$score+=14;$reason[]='RR สูง';}
  if(($case['flow']??0)>70){$score+=8;$reason[]='Flow สูงผิดปกติ';}
  $score=min(100,$score); return ['score'=>$score,'level'=>score_level($score),'reasons'=>$reason,'recommendation'=>$score>=75?'แจ้ง ICU team และประเมิน ventilator synchrony':'ติดตาม waveform และ vital signs ต่อเนื่อง'];
}
?>
