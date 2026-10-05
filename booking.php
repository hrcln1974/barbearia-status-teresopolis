<?php
require __DIR__.'/config.php';
if($_SERVER['REQUEST_METHOD']!=='POST')json_response(['ok'=>false,'error'=>'Método inválido.'],405);
$d=json_decode(file_get_contents('php://input'),true); if(!is_array($d))$d=[];
if(trim((string)($d['website']??''))!=='')json_response(['ok'=>true,'whatsapp'=>'']); // honeypot anti-robô
[$ok,$r,$code]=create_appt($d,false);
if(!$ok)json_response(['ok'=>false,'error'=>$r],$code);
$s=settings(); $b=find_by($s['barbers'],'id',$r['barber']);
$dt=date('d/m/Y',strtotime($r['date']));
$msg="Olá, {$b['name']}! Quero agendar na {$s['name']}.\n\nNome: {$r['name']}\nServiço: {$r['service']}\nData: {$dt}\nHorário: {$r['time']}".($r['message']!==''?"\nObs: {$r['message']}":'');
json_response(['ok'=>true,'whatsapp'=>'https://wa.me/55'.barber_phone($s,$b).'?text='.rawurlencode($msg),'appointment'=>['date'=>$dt,'time'=>$r['time'],'barber'=>$b['name'],'service'=>$r['service']]]);
