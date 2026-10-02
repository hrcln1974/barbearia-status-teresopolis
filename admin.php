<?php
require __DIR__ . '/config.php';
$s = settings();
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Painel | STATUS Barbearia</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div id="login" class="login">
  <div class="box">
    <a class="back" href="index.php">← Voltar ao site</a>
    <div class="mark">S</div>
    <p class="eyebrow">ÁREA RESTRITA</p>
    <h1>STATUS ADMIN</h1>
    <p>Gerencie galeria, agendamentos e informações da barbearia.</p>
    <form id="loginForm">
      <label>Usuário<input id="user" autocomplete="username" value="admin"></label>
      <label>Senha<input id="pass" type="password" autocomplete="current-password" placeholder="Digite sua senha"></label>
      <button class="primary" type="submit">Entrar no painel</button>
      <small id="loginMsg" class="msg"></small>
    </form>
  </div>
</div>

<div id="app" class="app hidden">
  <aside>
    <div class="sidebrand"><b>STATUS</b><small>BARBEARIA • ADMIN</small></div>
    <button class="active" data-tab="dash">Dashboard</button>
    <button data-tab="appointments">Agendamentos <span id="navPending" class="badge">0</span></button>
    <button data-tab="gallery">Galeria</button>
    <button data-tab="settings">Configurações</button>
    <a href="index.php" target="_blank" class="side-site">↗ Abrir site</a>
    <button id="logout" class="logout">Sair</button>
  </aside>

  <main>
    <header class="mobile-head"><b>STATUS ADMIN</b><a href="index.php">Site</a></header>

    <section id="dash" class="tab">
      <div class="page-head"><div><p class="eyebrow">VISÃO GERAL</p><h1>Dashboard</h1></div></div>
      <div class="stats">
        <div><b id="statPhotos">0</b><span>Fotos na galeria</span></div>
        <div><b id="statAppointments">0</b><span>Agendamentos</span></div>
        <div><b id="statPending">0</b><span>Pendentes</span></div>
      </div>
      <div class="panel welcome"><h2>Painel conectado</h2><p>O site, o painel e os dados estão no mesmo servidor. Esta versão usa JSON, portanto não depende de MySQL/Firebase.</p><div class="quick"><button data-tab="appointments">Ver agendamentos</button><button data-tab="gallery">Gerenciar galeria</button><button data-tab="settings">Editar informações</button></div></div>
    </section>

    <section id="appointments" class="tab hidden">
      <div class="page-head"><div><p class="eyebrow">CLIENTES</p><h1>Agendamentos</h1></div></div>
      <div id="appointmentsList" class="appointments"></div>
    </section>

    <section id="gallery" class="tab hidden">
      <div class="page-head"><div><p class="eyebrow">MÍDIA</p><h1>Galeria</h1></div></div>
      <form id="uploadForm" class="panel upload">
        <div><label>Imagem<input type="file" name="photo" accept="image/jpeg,image/png,image/webp,image/gif" required></label><small>JPG, PNG, WEBP ou GIF • máximo 6 MB.</small></div>
        <label>Título (opcional)<input name="title" maxlength="120" placeholder="Ex.: Corte degradê"></label>
        <button class="primary">Adicionar foto</button>
      </form>
      <div id="galleryGrid" class="admin-gallery"></div>
    </section>

    <section id="settings" class="tab hidden">
      <div class="page-head"><div><p class="eyebrow">NEGÓCIO</p><h1>Configurações</h1></div></div>
      <form id="settingsForm" class="panel settings">
        <label>Nome<input name="name"></label><label>Cidade<input name="city"></label><label>Telefone<input name="phone"></label>
        <label>Instagram<input name="instagram" placeholder="https://instagram.com/..."></label><label>Facebook<input name="facebook" placeholder="https://facebook.com/..."></label>
        <label>Endereço<input name="address"></label><label>Horário<input name="hours"></label><label>Descrição<textarea name="about"></textarea></label>
        <button class="primary">Salvar configurações</button><small id="saveMsg" class="msg"></small>
      </form>
    </section>
  </main>
</div>
<script src="assets/admin.js"></script>
</body>
</html>
