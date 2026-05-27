<?php
require_once __DIR__ . '/../database/connect.php';
function login_staff(string $role,string $username,string $password): bool{
  $st=db()->prepare('SELECT * FROM staff_users WHERE username=? AND role=? AND is_active=1 LIMIT 1');
  $st->execute([$username,$role]); $u=$st->fetch();
  if($u && password_verify($password,$u['password_hash'])){$_SESSION['user']=['id'=>$u['id'],'role'=>$u['role'],'name'=>$u['full_name'],'department'=>$u['department']];return true;}
  return false;
}
function login_patient(string $hn,string $password): bool{
  $st=db()->prepare('SELECT pa.*, p.hn, p.first_name, p.last_name FROM patient_accounts pa JOIN patients p ON p.id=pa.patient_id WHERE p.hn=? AND pa.is_active=1 LIMIT 1');
  $st->execute([$hn]); $u=$st->fetch();
  if($u && password_verify($password,$u['password_hash'])){$_SESSION['user']=['id'=>$u['patient_id'],'role'=>'patient','name'=>$u['first_name'].' '.$u['last_name'],'hn'=>$u['hn']];return true;}
  return false;
}
function require_role(string $role){
  $current = $_SESSION['user']['role'] ?? '';
  if($current !== $role){
    // บังคับแยกสิทธิ์: ผู้ป่วย/แพทย์ต้อง login ผ่านหน้าของตัวเองเท่านั้น
    unset($_SESSION['user']);
    header('Location: '.APP_URL.'/'.$role.'/login.php?required=1');
    exit;
  }
}
function require_any(array $roles){ if(!in_array($_SESSION['user']['role'] ?? '',$roles,true)){ unset($_SESSION['user']); header('Location: '.APP_URL.'/index.php'); exit; } }
function logout_all(){$_SESSION=[]; session_destroy();}
?>
