<?php
require __DIR__.'/config.php';
if($_SERVER['REQUEST_METHOD']!=='POST')json_response(['ok'=>false,'error'=>'Método inválido.'],405);
$d=json_decode(file_get_contents('php://input'),true)??[];
foreach(['name','phone','date','time','service'] as $k)if(!trim((string)($d[$k]??'')))json_response(['ok'=>false,'error'=>'Preencha nome, WhatsApp, data, horário e serviço.'],400);
$s=settings();$wa=preg_replace('/\D+/','',(string)$s['phone']);$items=appointments();
$item=['id'=>bin2hex(random_bytes(8)),'name'=>trim((string)$d['name']),'phone'=>trim((string)$d['phone']),'date'=>trim((string)$d['date']),'time'=>trim((string)$d['time']),'service'=>trim((string)$d['service']),'message'=>trim((string)($d['message']??'')),'status'=>'pending','created_at'=>date('c')];
$items[]=$item;if(!write_json(DATA_DIR.'/appointments.json',$items))json_response(['ok'=>false,'error'=>'Não foi possível registrar. Verifique a pasta data/.'],500);
$text="Olá! Quero agendar na Barbearia STATUS.%0A%0ANome: ".rawurlencode($item['name'])."%0AWhatsApp: ".rawurlencode($item['phone'])."%0AServiço: ".rawurlencode($item['service'])."%0AData: ".rawurlencode($item['date'])."%0AHorário: ".rawurlencode($item['time'])."%0AObservação: ".rawurlencode($item['message']);
json_response(['ok'=>true,'whatsapp'=>'https://wa.me/55'.$wa.'?text='.$text]);
