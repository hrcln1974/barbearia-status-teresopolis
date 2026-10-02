<?php require __DIR__.'/config.php'; $s=settings(); $g=gallery(); ?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="Barbearia STATUS em Teresópolis - RJ. Cortes, barba e estilo.">
<title><?=e($s['name'])?> • <?=e($s['city'])?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/site.css">
</head>
<body>
<header class="header"><div class="wrap nav">
<a class="brand" href="#inicio"><span class="brand-mark">S</span><span>STATUS<small>BARBEARIA</small></span></a>
<button class="menu" aria-label="Abrir menu">☰</button>
<nav class="nav-links"><a href="#inicio">Início</a><a href="#servicos">Serviços</a><a href="#galeria">Galeria</a><a href="#contato">Contato</a></nav>
<a class="nav-wa" href="<?=e(whatsapp_url($s['phone'],$s['whatsapp_message']))?>" target="_blank" rel="noopener">WhatsApp</a>
</div></header>
<main id="inicio">
<section class="hero"><div class="hero-overlay"></div><div class="wrap hero-content">
<span class="eyebrow">TERESÓPOLIS • RIO DE JANEIRO</span><h1>Seu estilo.<br><strong>Seu STATUS.</strong></h1>
<p>Barbearia masculina com atendimento, precisão e personalidade.</p>
<div class="actions"><a class="btn gold" href="<?=e(whatsapp_url($s['phone'],$s['whatsapp_message']))?>" target="_blank" rel="noopener">Agendar pelo WhatsApp</a><a class="btn ghost" href="#galeria">Ver galeria</a></div>
</div></section>
<section class="intro" id="servicos"><div class="wrap"><span class="eyebrow dark">SERVIÇOS</span><h2>Experiência completa para você.</h2><p class="lead">Corte, barba e acabamento feitos para valorizar o seu estilo.</p>
<div class="cards">
<article><img src="assets/corte-maquina.png" alt="Corte masculino"><h3>Corte Máquina</h3><p>Acabamento limpo e preciso.</p></article>
<article><img src="assets/corte-tesoura.png" alt="Corte tesoura"><h3>Corte Tesoura</h3><p>Técnica e personalização para cada rosto.</p></article>
<article><img src="assets/corte5.png" alt="Barba e acabamento"><h3>Barba & Acabamento</h3><p>Detalhes que fazem a diferença.</p></article>
</div></div></section>
<section class="gallery-section" id="galeria"><div class="wrap"><span class="eyebrow">GALERIA</span><h2>Trabalhos da STATUS</h2><p class="lead">Fotos atualizadas pelo painel administrativo.</p>
<div class="gallery"><?php if (!$g): ?><div class="empty">A galeria será atualizada em breve.</div><?php else: foreach($g as $item): ?><figure><img loading="lazy" src="<?=e($item['url'])?>" alt="<?=e($item['title'] ?? 'Trabalho Barbearia STATUS')?>"><figcaption><?=e($item['title'] ?? '')?></figcaption></figure><?php endforeach; endif; ?></div>
</div></section>
<section class="contact" id="contato"><div class="wrap contact-grid"><div><span class="eyebrow">FALE CONOSCO</span><h2>Pronto para renovar o visual?</h2><p>Entre em contato e consulte horários.</p><a class="btn gold" href="<?=e(whatsapp_url($s['phone'],$s['whatsapp_message']))?>" target="_blank" rel="noopener">Chamar no WhatsApp</a></div>
<div class="contact-box"><p><b>📍</b> <?=e($s['address'])?></p><p><b>☎</b> <?=e($s['phone'])?></p><p><b>◷</b> <?=e($s['hours'])?></p><div class="socials"><?php if($s['instagram']): ?><a href="<?=e($s['instagram'])?>" target="_blank" rel="noopener">Instagram</a><?php endif; ?><?php if($s['facebook']): ?><a href="<?=e($s['facebook'])?>" target="_blank" rel="noopener">Facebook</a><?php endif; ?><a href="<?=e(whatsapp_url($s['phone'],$s['whatsapp_message']))?>" target="_blank" rel="noopener">WhatsApp</a></div></div></div></section>
</main>
<footer><div class="wrap"><span>© <?=date('Y')?> <?=e($s['name'])?></span><span><?=e($s['city'])?></span></div></footer>
<a class="floating-wa" aria-label="WhatsApp" href="<?=e(whatsapp_url($s['phone'],$s['whatsapp_message']))?>" target="_blank" rel="noopener">☏</a>
<script src="assets/site.js"></script>
</body></html>
