<?php
require __DIR__.'/config.php';
$action=$_GET['action']??'';
$method=$_SERVER['REQUEST_METHOD']??'GET';

if($action==='health' && $method==='GET'){
  json_response(['ok'=>true,'php'=>PHP_VERSION,'data_writable'=>is_writable(DATA_DIR),'gallery_writable'=>is_writable(GALLERY_DIR)]);
}
if($action==='login' && $method==='POST'){
  $in=json_decode(file_get_contents('php://input'),true)??$_POST;
  if(hash_equals(ADMIN_USER,trim((string)($in['user']??''))) && password_verify((string)($in['pass']??''),ADMIN_PASS_HASH)){
    session_regenerate_id(true); $_SESSION['admin']=true; json_response(['ok'=>true]);
  }
  json_response(['ok'=>false,'error'=>'Usuário ou senha inválidos.'],401);
}
if($action==='logout' && $method==='POST'){ $_SESSION=[]; session_destroy(); json_response(['ok'=>true]); }
if($action==='session' && $method==='GET') json_response(['ok'=>true,'authenticated'=>logged()]);
require_admin();

if($action==='dashboard' && $method==='GET'){
  $a=appointments(); $pending=count(array_filter($a,fn($x)=>($x['status']??'pending')==='pending'));
  json_response(['ok'=>true,'stats'=>['photos'=>count(gallery()),'appointments'=>count($a),'pending'=>$pending]]);
}
if($action==='gallery' && $method==='GET') json_response(['ok'=>true,'items'=>array_reverse(gallery())]);
if($action==='gallery' && $method==='POST'){
  if(!is_writable(GALLERY_DIR)||!is_writable(DATA_DIR)) json_response(['ok'=>false,'error'=>'Sem permissão de escrita em data/ ou uploads/gallery/.'],500);
  if(empty($_FILES['photo'])||$_FILES['photo']['error']!==UPLOAD_ERR_OK) json_response(['ok'=>false,'error'=>'Selecione uma imagem válida.'],400);
  $f=$_FILES['photo'];
  if($f['size']>6*1024*1024) json_response(['ok'=>false,'error'=>'A imagem deve ter no máximo 6 MB.'],400);
  $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
  $map=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
  if(!isset($map[$mime])) json_response(['ok'=>false,'error'=>'Use JPG, PNG, WEBP ou GIF.'],400);
  $name=bin2hex(random_bytes(10)).'.'.$map[$mime]; $target=GALLERY_DIR.'/'.$name;
  if(!move_uploaded_file($f['tmp_name'],$target)) json_response(['ok'=>false,'error'=>'Falha ao salvar a imagem.'],500);
  $items=gallery(); $item=['id'=>bin2hex(random_bytes(8)),'url'=>'uploads/gallery/'.$name,'title'=>trim((string)($_POST['title']??'')),'created_at'=>date('c')];
  $items[]=$item;
  if(!write_json(DATA_DIR.'/gallery.json',$items)){@unlink($target);json_response(['ok'=>false,'error'=>'Falha ao atualizar a galeria.'],500);}
  json_response(['ok'=>true,'item'=>$item]);
}
if($action==='gallery_delete' && $method==='POST'){
  $in=json_decode(file_get_contents('php://input'),true)??[]; $items=gallery();$new=[];$found=false;
  foreach($items as $item){ if(($item['id']??'')===($in['id']??'')){ $p=__DIR__.'/'.ltrim((string)($item['url']??''),'/');if(is_file($p))@unlink($p);$found=true;}else$new[]=$item;}
  if(!write_json(DATA_DIR.'/gallery.json',$new))json_response(['ok'=>false,'error'=>'Falha ao salvar a galeria.'],500);
  json_response(['ok'=>$found]);
}
if($action==='appointments' && $method==='GET') json_response(['ok'=>true,'items'=>array_reverse(appointments())]);
if($action==='appointment_status' && $method==='POST'){
  $in=json_decode(file_get_contents('php://input'),true)??[];$items=appointments();$ok=false;
  foreach($items as &$item){if(($item['id']??'')===($in['id']??'')){ $st=(string)($in['status']??'pending'); if(!in_array($st,['pending','confirmed','done','cancelled'],true))json_response(['ok'=>false,'error'=>'Status inválido.'],400);$item['status']=$st;$ok=true;break;}}unset($item);
  if(!$ok||!write_json(DATA_DIR.'/appointments.json',$items))json_response(['ok'=>false,'error'=>'Agendamento não encontrado.'],404);
  json_response(['ok'=>true]);
}
if($action==='settings' && $method==='GET') json_response(['ok'=>true,'settings'=>settings()]);
if($action==='settings' && $method==='POST'){
  $in=json_decode(file_get_contents('php://input'),true)??[];$s=settings();
  foreach(['name','city','phone','instagram','facebook','address','hours','about'] as $k)if(array_key_exists($k,$in))$s[$k]=trim((string)$in[$k]);
  if(!write_json(DATA_DIR.'/settings.json',$s))json_response(['ok'=>false,'error'=>'Não foi possível salvar.'],500);
  json_response(['ok'=>true,'settings'=>$s]);
}
json_response(['ok'=>false,'error'=>'Ação inválida.'],404);
