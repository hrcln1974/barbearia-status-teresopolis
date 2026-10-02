<?php require __DIR__.'/config.php';
if (isset($_GET['logout'])) { session_destroy(); header('Location: admin.php'); exit; }
$loginError='';
if (!is_admin() && $_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='login') {
  $user=trim($_POST['user']??''); $pass=$_POST['pass']??'';
  // Credenciais iniciais: admin / status123 (troque em produção no arquivo admin.php).
  if (hash_equals('admin',$user) && hash_equals('status123',$pass)) { $_SESSION['status_admin']=true; header('Location: admin.php'); exit; }
  $loginError='Usuário ou senha inválidos.';
}
if (!is_admin()):
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin • STATUS</title><link rel="stylesheet" href="assets/admin.css"></head><body class="login"><form method="post" class="login-card"><div class="mark">S</div><h1>STATUS</h1><p>Painel administrativo</p><input type="hidden" name="action" value="login"><label>Usuário</label><input name="user" autocomplete="username" required><label>Senha</label><input name="pass" type="password" autocomplete="current-password" required><?php if($loginError):?><div class="error"><?=e($loginError)?></div><?php endif;?><button>Entrar</button><small>Após publicar, altere as credenciais no arquivo <b>admin.php</b>.</small></form></body></html>
<?php exit; endif;
$msg=''; $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 $action=$_POST['action']??'';
 if($action==='settings'){
   $s=settings();
   foreach(['name','city','phone','instagram','facebook','hours','address','whatsapp_message'] as $k) $s[$k]=trim($_POST[$k]??'');
   json_write(SETTINGS_FILE,$s); $msg='Configurações salvas.';
 }
 if($action==='upload'){
   if(empty($_FILES['photo']) || $_FILES['photo']['error']!==UPLOAD_ERR_OK) $error='Selecione uma imagem válida.';
   else {
    $f=$_FILES['photo']; $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if(!isset($allowed[$mime]) || $f['size']>8*1024*1024) $error='Formato não permitido ou arquivo maior que 8 MB.';
    else {
      $name=bin2hex(random_bytes(8)).'.'.$allowed[$mime]; $dest=GALLERY_DIR.'/'.$name;
      if(move_uploaded_file($f['tmp_name'],$dest)){
        $g=gallery(); $g[]= ['id'=>bin2hex(random_bytes(6)),'url'=>'uploads/gallery/'.$name,'title'=>trim($_POST['title']??''),'created_at'=>date('c')]; json_write(GALLERY_FILE,$g); $msg='Foto adicionada à galeria.';
      } else $error='Não foi possível salvar o arquivo.';
    }
   }
 }
 if($action==='delete'){
   $id=$_POST['id']??''; $g=gallery(); $new=[];
   foreach($g as $item){ if(($item['id']??'')===$id){$file=BASE_DIR.'/'.$item['url']; if(is_file($file)) @unlink($file);} else $new[]=$item; }
   json_write(GALLERY_FILE,$new); $msg='Foto removida.';
 }
}
$s=settings(); $g=gallery();
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Painel • STATUS</title><link rel="stylesheet" href="assets/admin.css"></head><body>
<header class="top"><div><b>STATUS</b><span> Painel administrativo</span></div><a href="index.php" target="_blank">Ver site ↗</a><a href="?logout=1">Sair</a></header>
<main class="dashboard"><?php if($msg):?><div class="notice ok"><?=e($msg)?></div><?php endif;?><?php if($error):?><div class="notice error"><?=e($error)?></div><?php endif;?>
<section class="panel"><h2>Galeria de fotos</h2><p>Envie fotos dos cortes e trabalhos. Elas aparecem automaticamente no site.</p>
<form method="post" enctype="multipart/form-data" class="upload"><input type="hidden" name="action" value="upload"><input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp,.gif" required><input name="title" placeholder="Título opcional"><button>Adicionar foto</button></form>
<div class="gallery-admin"><?php foreach($g as $item):?><div><img src="<?=e($item['url'])?>" alt=""><form method="post" onsubmit="return confirm('Remover esta foto?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=e($item['id'])?>"><span><?=e($item['title']??'')?></span><button class="danger">Excluir</button></form></div><?php endforeach; if(!$g):?><p class="muted">Nenhuma foto cadastrada.</p><?php endif;?></div></section>
<section class="panel"><h2>Configurações do site</h2><form method="post" class="settings"><input type="hidden" name="action" value="settings">
<label>Nome<input name="name" value="<?=e($s['name'])?>" required></label><label>Cidade<input name="city" value="<?=e($s['city'])?>"></label><label>Telefone<input name="phone" value="<?=e($s['phone'])?>"></label><label>Instagram<input name="instagram" type="url" value="<?=e($s['instagram'])?>" placeholder="https://instagram.com/..."></label><label>Facebook<input name="facebook" type="url" value="<?=e($s['facebook'])?>" placeholder="https://facebook.com/..."></label><label>Horário<input name="hours" value="<?=e($s['hours'])?>"></label><label>Endereço<input name="address" value="<?=e($s['address'])?>"></label><label>Mensagem WhatsApp<textarea name="whatsapp_message"><?=e($s['whatsapp_message'])?></textarea></label><button>Salvar configurações</button></form></section>
<section class="panel security"><h2>Segurança</h2><p>Antes de colocar o site no ar, altere o usuário e senha iniciais diretamente no <code>admin.php</code>. Recomenda-se também renomear o painel posteriormente.</p></section>
</main></body></html>
