<?php
require __DIR__ . '/config.php';
$s = settings();
$g = array_reverse(gallery());
$wa = preg_replace('/\D+/', '', (string)$s['phone']);
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=htmlspecialchars($s['name'])?> | <?=htmlspecialchars($s['city'])?></title>
<meta name="description" content="<?=htmlspecialchars($s['about'])?>">
<link rel="stylesheet" href="assets/site.css">
</head>
<body>
<header class="top">
  <a class="brand" href="#inicio"><span>STATUS</span><small>BARBEARIA</small></a>
  <button class="menu" aria-label="Abrir menu">☰</button>
  <nav>
    <a href="#inicio">Início</a><a href="#servicos">Serviços</a><a href="#galeria">Galeria</a><a href="#agendar">Agendar</a><a href="#contato">Contato</a>
  </nav>
  <a class="admin-link" href="admin.php" title="Área administrativa">🔒 Painel</a>
</header>

<main>
<section id="inicio" class="hero">
  <div class="hero-copy">
    <p class="eyebrow">TERESÓPOLIS • RIO DE JANEIRO</p>
    <h1>Seu estilo.<br><strong>Sua STATUS.</strong></h1>
    <p><?=htmlspecialchars($s['about'])?></p>
    <div class="actions">
      <a class="btn primary" href="#agendar">Agendar horário</a>
      <a class="btn ghost" target="_blank" rel="noopener" href="https://wa.me/55<?=$wa?>">WhatsApp</a>
    </div>
  </div>
  <div class="hero-card">
    <div class="seal">S</div><b>STATUS</b><span>BARBEARIA</span><small>ESTILO • PRECISÃO • PRESENÇA</small>
  </div>
</section>

<section id="servicos" class="section">
  <p class="eyebrow">SERVIÇOS</p><h2>Experiência completa para você.</h2>
  <div class="cards">
    <article><span>01</span><h3>Corte na máquina</h3><p>Praticidade, acabamento limpo e visual alinhado.</p></article>
    <article><span>02</span><h3>Corte na tesoura</h3><p>Precisão e personalização para o seu estilo.</p></article>
    <article><span>03</span><h3>Barba & acabamento</h3><p>Contorno, alinhamento e detalhes que fazem diferença.</p></article>
  </div>
</section>

<section id="galeria" class="section dark">
  <p class="eyebrow">GALERIA</p><h2>Trabalhos da STATUS</h2>
  <div class="gallery">
    <?php if (!$g): ?><div class="empty">As fotos dos trabalhos serão adicionadas pelo painel administrativo.</div>
    <?php else: foreach ($g as $item): ?>
      <figure><img src="<?=htmlspecialchars((string)$item['url'])?>" alt="<?=htmlspecialchars((string)($item['title'] ?? 'Trabalho Barbearia STATUS'))?>" loading="lazy"><figcaption><?=htmlspecialchars((string)($item['title'] ?? ''))?></figcaption></figure>
    <?php endforeach; endif; ?>
  </div>
</section>

<section id="agendar" class="section booking">
  <div>
    <p class="eyebrow">AGENDAMENTO</p><h2>Reserve seu horário.</h2>
    <p>Preencha os dados. O pedido fica registrado no painel e também é encaminhado pelo WhatsApp.</p>
    <div class="contact-line"><b>Telefone</b><a href="tel:+55<?=$wa?>"><?=$s['phone']?></a></div>
    <div class="contact-line"><b>Horário</b><span><?=htmlspecialchars($s['hours'])?></span></div>
  </div>
  <form id="bookingForm">
    <input name="name" required placeholder="Seu nome">
    <input name="phone" required placeholder="Seu WhatsApp">
    <input name="date" required type="date">
    <input name="time" required type="time">
    <select name="service" required><option value="">Escolha o serviço</option><option>Corte na máquina</option><option>Corte na tesoura</option><option>Barba & acabamento</option><option>Corte + barba</option></select>
    <textarea name="message" placeholder="Observação (opcional)"></textarea>
    <button class="btn primary" type="submit">Enviar pelo WhatsApp</button>
    <p id="bookingMsg" class="form-msg"></p>
  </form>
</section>

<section id="contato" class="section contact">
  <p class="eyebrow">CONTATO</p><h2>STATUS BARBEARIA</h2><p><?=htmlspecialchars($s['address'])?></p>
  <div class="socials">
    <?php if ($s['instagram']): ?><a target="_blank" rel="noopener" href="<?=htmlspecialchars($s['instagram'])?>">Instagram</a><?php endif; ?>
    <?php if ($s['facebook']): ?><a target="_blank" rel="noopener" href="<?=htmlspecialchars($s['facebook'])?>">Facebook</a><?php endif; ?>
    <a target="_blank" rel="noopener" href="https://wa.me/55<?=$wa?>">WhatsApp</a>
  </div>
</section>
</main>

<footer><span>© <?=date('Y')?> <?=htmlspecialchars($s['name'])?> • Teresópolis/RJ</span><a href="admin.php">🔒 Área do administrador</a></footer>
<a class="float" target="_blank" rel="noopener" href="https://wa.me/55<?=$wa?>">WhatsApp</a>
<script src="assets/site.js"></script>
</body>
</html>
