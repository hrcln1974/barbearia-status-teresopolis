<?php
require __DIR__.'/config.php';
$action=$_GET['action']??'';
$method=$_SERVER['REQUEST_METHOD']??'GET';

if($action==='health' && $method==='GET'){
  json_response(['ok'=>true,'php'=>PHP_VERSION,'data_writable'=>is_writable(DATA_DIR),'gallery_writable'=>is_writable(GALLERY_DIR)]);
}
if($action==='config' && $method==='GET'){ $s=settings(); json_response(['ok'=>true,'barbers'=>array_map(fn($b)=>['id'=>$b['id'],'name'=>$b['name']],$s['barbers']),'services'=>$s['services'],'days'=>$s['days'],'max'=>date('Y-m-d',strtotime('+'.(int)$s['max_days_ahead'].' days'))]); }
if($action==='slots' && $method==='GET'){ $s=settings(); $svc=find_by($s['services'],'name',(string)($_GET['service']??'')); $b=find_by($s['barbers'],'id',(string)($_GET['barber']??''));
  if(!$svc||!$b) json_response(['ok'=>false,'error'=>'Parâmetros inválidos.'],400);
  json_response(['ok'=>true,'slots'=>free_slots($s,$b['id'],(string)($_GET['date']??''),(int)$svc['duration'],appointments())]); }
if($action==='login' && $method==='POST'){
  $fails=read_json(DATA_DIR.'/login_fails.json',[]); $ip=(string)($_SERVER['REMOTE_ADDR']??'?'); $fails[$ip]=array_values(array_filter($fails[$ip]??[],fn($t)=>$t>time()-900));
  if(count($fails[$ip])>=8) json_response(['ok'=>false,'error'=>'Muitas tentativas. Aguarde 15 minutos.'],429);
  $in=json_decode(file_get_contents('php://input'),true)??$_POST;
  if(hash_equals(ADMIN_USER,trim((string)($in['user']??''))) && password_verify((string)($in['pass']??''),admin_hash())){
    session_regenerate_id(true); $_SESSION['admin']=true; json_response(['ok'=>true]);
  }
  $fails[$ip][]=time(); write_json(DATA_DIR.'/login_fails.json',$fails);
  json_response(['ok'=>false,'error'=>'Usuário ou senha inválidos.'],401);
}
if($action==='logout' && $method==='POST'){ $_SESSION=[]; session_destroy(); json_response(['ok'=>true]); }
if($action==='session' && $method==='GET') json_response(['ok'=>true,'authenticated'=>logged()]);
require_admin();

if($action==='dashboard' && $method==='GET'){
  $a=appointments(); $t=date('Y-m-d'); $act=fn($x)=>!in_array($x['status']??'pending',['cancelled','blocked'],true);
  $pending=count(array_filter($a,fn($x)=>($x['status']??'pending')==='pending'));
  $today=count(array_filter($a,fn($x)=>$act($x)&&($x['date']??'')===$t));
  json_response(['ok'=>true,'stats'=>['photos'=>count(gallery()),'appointments'=>count(array_filter($a,$act)),'pending'=>$pending,'today'=>$today]]);
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
if($action==='appointments' && $method==='GET'){ $a=appointments(); $d=(string)($_GET['date']??''); if($d!=='')$a=array_values(array_filter($a,fn($x)=>($x['date']??'')===$d));
  usort($a,fn($x,$y)=>[$x['date'],$x['time']]<=>[$y['date'],$y['time']]); json_response(['ok'=>true,'items'=>$a]); }
if($action==='appointment_create' && $method==='POST'){ $in=json_decode(file_get_contents('php://input'),true)??[]; [$ok,$r,$c]=create_appt($in,true); json_response($ok?['ok'=>true,'item'=>$r]:['ok'=>false,'error'=>$r],$c); }
if($action==='appointment_status' && $method==='POST'){
  $in=json_decode(file_get_contents('php://input'),true)??[]; $st=(string)($in['status']??'');
  if(!in_array($st,['pending','confirmed','done','cancelled'],true))json_response(['ok'=>false,'error'=>'Status inválido.'],400);
  $ok=with_lock(function() use($in,$st){$items=appointments();$f=false;foreach($items as &$it){if(($it['id']??'')===($in['id']??'')){$it['status']=$st;$f=true;break;}}unset($it);return $f&&write_json(DATA_DIR.'/appointments.json',$items);});
  json_response(['ok'=>$ok,'error'=>$ok?null:'Agendamento não encontrado.'],$ok?200:404);
}
if($action==='appointment_delete' && $method==='POST'){
  $in=json_decode(file_get_contents('php://input'),true)??[];
  $ok=with_lock(function() use($in){$items=appointments();$n=array_values(array_filter($items,fn($x)=>($x['id']??'')!==($in['id']??'')));return count($n)<count($items)&&write_json(DATA_DIR.'/appointments.json',$n);});
  json_response(['ok'=>$ok],$ok?200:404);
}
if($action==='password' && $method==='POST'){
  $in=json_decode(file_get_contents('php://input'),true)??[];
  if(!password_verify((string)($in['current']??''),admin_hash()))json_response(['ok'=>false,'error'=>'Senha atual incorreta.'],400);
  if(mb_strlen((string)($in['new']??''))<8)json_response(['ok'=>false,'error'=>'A nova senha precisa ter 8+ caracteres.'],400);
  json_response(['ok'=>write_json(DATA_DIR.'/auth.json',['hash'=>password_hash((string)$in['new'],PASSWORD_DEFAULT)])]);
}
function settings_view(array $s): array { foreach($s['barbers'] as $b)$s['phone_'.$b['id']]=$b['phone']; $s['days']=implode(',',$s['days']);
  $s['services_text']=implode("\n",array_map(fn($v)=>$v['name'].'|'.$v['duration'].'|'.$v['price'],$s['services'])); unset($s['barbers'],$s['services']); return $s; }
if($action==='settings' && $method==='GET') json_response(['ok'=>true,'settings'=>settings_view(settings())]);
if($action==='settings' && $method==='POST'){
  $in=json_decode(file_get_contents('php://input'),true)??[];$s=settings();
  foreach(['name','city','phone','instagram','facebook','address','hours','about'] as $k)if(array_key_exists($k,$in))$s[$k]=trim((string)$in[$k]);
  foreach(['open','close'] as $k)if(isset($in[$k])){ if(!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/',(string)$in[$k]))json_response(['ok'=>false,'error'=>'Horário inválido.'],400); $s[$k]=$in[$k]; }
  if(to_min($s['open'])>=to_min($s['close']))json_response(['ok'=>false,'error'=>'Abertura deve ser antes do fechamento.'],400);
  if(isset($in['slot'])){$s['slot']=max(10,min(120,(int)$in['slot']));}
  if(isset($in['days'])){$d=array_values(array_unique(array_filter(array_map('intval',explode(',',(string)$in['days'])),fn($x)=>$x>=0&&$x<=6)));if(!$d)json_response(['ok'=>false,'error'=>'Informe ao menos um dia aberto.'],400);$s['days']=$d;}
  foreach($s['barbers'] as &$b)if(array_key_exists('phone_'.$b['id'],$in))$b['phone']=norm_phone((string)$in['phone_'.$b['id']]);unset($b);
  if(isset($in['services_text'])){$sv=[];foreach(preg_split('/\R/',(string)$in['services_text']) as $ln){$p=array_map('trim',explode('|',$ln));if($p[0]==='')continue;$sv[]=['name'=>mb_substr($p[0],0,60),'duration'=>max(10,(int)($p[1]??30)),'price'=>mb_substr($p[2]??'',0,20)];}
    if(!$sv)json_response(['ok'=>false,'error'=>'Cadastre ao menos um serviço.'],400);$s['services']=$sv;}
  if(!write_json(DATA_DIR.'/settings.json',$s))json_response(['ok'=>false,'error'=>'Não foi possível salvar.'],500);
  json_response(['ok'=>true,'settings'=>settings_view($s)]);
}
json_response(['ok'=>false,'error'=>'Ação inválida.'],404);
