<?php
declare(strict_types=1);
date_default_timezone_set('America/Sao_Paulo');

if (session_status() === PHP_SESSION_NONE) {
    session_name('status_admin');
    session_set_cookie_params([
        'httponly'=>true,
        'samesite'=>'Lax',
        'secure'=>(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    ]);
    session_start();
}

const ADMIN_USER = 'admin';
const DEFAULT_PASS_HASH = '$2y$12$gQSkQsB4n7cFlfL.58QbT.Va1WgQ4LrBbFQ3EkrLGSrc5x3nUS2D6';
const DATA_DIR = __DIR__ . '/data';
const GALLERY_DIR = __DIR__ . '/uploads/gallery';

function ensure_dirs(): void {
    foreach ([DATA_DIR,GALLERY_DIR] as $dir) if (!is_dir($dir)) @mkdir($dir,0755,true);
}
function read_json(string $file,array $default=[]): array {
    ensure_dirs();
    if (!is_file($file)) { write_json($file,$default); return $default; }
    $d=json_decode((string)@file_get_contents($file),true);
    return is_array($d)?$d:$default;
}
function write_json(string $file,array $data): bool {
    ensure_dirs();
    $j=json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    return $j!==false && @file_put_contents($file,$j,LOCK_EX)!==false;
}
function default_settings(): array {
    return ['name'=>'Barbearia STATUS','city'=>'Teresópolis - RJ','phone'=>'21991525359',
      'instagram'=>'','facebook'=>'','address'=>'Teresópolis - RJ',
      'hours'=>'Segunda a sábado, 09h às 19h',
      'about'=>'Cortes modernos, acabamento preciso e atendimento de qualidade em Teresópolis.',
      'open'=>'09:00','close'=>'19:00','slot'=>30,'days'=>[1,2,3,4,5,6],'max_days_ahead'=>45,
      'barbers'=>[['id'=>'jose-luis','name'=>'José Luís','phone'=>''],['id'=>'carlinhos','name'=>'Carlinhos','phone'=>'']],
      'services'=>[['name'=>'Corte na máquina','duration'=>30,'price'=>''],['name'=>'Corte na tesoura','duration'=>45,'price'=>''],
                   ['name'=>'Barba & acabamento','duration'=>30,'price'=>''],['name'=>'Corte + barba','duration'=>60,'price'=>'']]];
}
function settings(): array { return array_replace(default_settings(), read_json(DATA_DIR.'/settings.json',default_settings())); }
function gallery(): array { return read_json(DATA_DIR.'/gallery.json',[]); }
function appointments(): array { return read_json(DATA_DIR.'/appointments.json',[]); }
function logged(): bool { return !empty($_SESSION['admin']); }
function json_response(array $data,int $code=200): never {
    http_response_code($code); header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
}
function require_admin(): void { if (!logged()) json_response(['ok'=>false,'error'=>'Não autenticado.'],401); }
ensure_dirs();

function admin_hash(): string { $a=read_json(DATA_DIR.'/auth.json',[]); return (string)($a['hash']??DEFAULT_PASS_HASH); }
function with_lock(callable $fn){ ensure_dirs(); $h=fopen(DATA_DIR.'/.lock','c'); flock($h,LOCK_EX); try{return $fn();}finally{flock($h,LOCK_UN);fclose($h);} }
function only_digits(string $v): string { return preg_replace('/\D+/','',$v); }
function norm_phone(string $v): string { $d=only_digits($v); if(strlen($d)>11&&str_starts_with($d,'55'))$d=substr($d,2); return $d; }
function to_min(string $t): int { [$h,$m]=array_map('intval',explode(':',$t)); return $h*60+$m; }
function fmt_min(int $m): string { return sprintf('%02d:%02d',intdiv($m,60),$m%60); }
function find_by(array $list,string $key,string $val): ?array { foreach($list as $x) if(($x[$key]??null)===$val) return $x; return null; }
function valid_date(string $d): bool { $x=DateTime::createFromFormat('Y-m-d',$d); return $x&&$x->format('Y-m-d')===$d; }
function barber_phone(array $s,array $b): string { $p=norm_phone((string)($b['phone']??'')); return $p!==''?$p:norm_phone((string)$s['phone']); }
function free_slots(array $s,string $barber,string $date,int $dur,array $appts): array {
    if(!valid_date($date)||$date<date('Y-m-d')||$date>date('Y-m-d',strtotime('+'.(int)$s['max_days_ahead'].' days'))) return [];
    if(!in_array((int)date('w',strtotime($date)),array_map('intval',$s['days']),true)) return [];
    $open=to_min($s['open']);$close=to_min($s['close']);$step=max(10,(int)$s['slot']);$busy=[];
    foreach($appts as $a) if(($a['barber']??'')===$barber&&($a['date']??'')===$date&&($a['status']??'pending')!=='cancelled'){$t=to_min($a['time']);$busy[]=[$t,$t+(int)($a['duration']??$step)];}
    $now=date('Y-m-d')===$date?to_min(date('H:i')):-1; $out=[];
    for($t=$open;$t+$dur<=$close;$t+=$step){
        if($t<=$now) continue; $ok=true;
        foreach($busy as [$b0,$b1]) if($t<$b1&&$t+$dur>$b0){$ok=false;break;}
        if($ok)$out[]=fmt_min($t);
    } return $out;
}
/** Cria agendamento de forma atômica. $admin=true ignora grade/antecedência mas nunca permite choque de horário. */
function create_appt(array $d,bool $admin=false): array {
    return with_lock(function() use($d,$admin){
        $s=settings(); $b=find_by($s['barbers'],'id',(string)($d['barber']??''));
        if(!$b) return [false,'Escolha o barbeiro.',400];
        $block=$admin&&($d['service']??'')==='__bloqueio';
        $svc=$block?['name'=>'Bloqueio de horário','duration'=>max(10,(int)($d['duration']??60))]:find_by($s['services'],'name',(string)($d['service']??''));
        if(!$svc) return [false,'Escolha o serviço.',400];
        $date=trim((string)($d['date']??''));$time=trim((string)($d['time']??''));
        if(!valid_date($date)||!preg_match('/^\d{2}:\d{2}$/',$time)) return [false,'Data ou horário inválido.',400];
        $name=trim((string)($d['name']??''));$phone=norm_phone((string)($d['phone']??''));
        if(!$block){ if($name===''||mb_strlen($name)>80) return [false,'Informe o nome.',400]; if(!preg_match('/^\d{10,11}$/',$phone)) return [false,'WhatsApp inválido. Use DDD + número.',400]; }
        $all=appointments(); $dur=(int)$svc['duration'];
        if(!$admin){
            if(!in_array($time,free_slots($s,$b['id'],$date,$dur,$all),true)) return [false,'Esse horário não está mais disponível. Escolha outro.',409];
            $n=0; foreach($all as $a) if(($a['phone']??'')===$phone&&($a['status']??'')!=='cancelled'&&($a['status']??'')!=='done'&&($a['date']??'')>=date('Y-m-d'))$n++;
            if($n>=3) return [false,'Você já possui 3 agendamentos ativos. Fale com a barbearia pelo WhatsApp.',429];
        } else {
            $t0=to_min($time);
            foreach($all as $a) if(($a['barber']??'')===$b['id']&&($a['date']??'')===$date&&($a['status']??'')!=='cancelled'){$a0=to_min($a['time']);$a1=$a0+(int)$a['duration'];if($t0<$a1&&$t0+$dur>$a0)return [false,'Choque com outro horário deste barbeiro.',409];}
        }
        $item=['id'=>bin2hex(random_bytes(8)),'barber'=>$b['id'],'barber_name'=>$b['name'],'name'=>$block?'—':$name,'phone'=>$phone,'date'=>$date,'time'=>$time,
               'service'=>$svc['name'],'duration'=>$dur,'message'=>mb_substr(trim((string)($d['message']??'')),0,300),'status'=>$block?'blocked':($admin?'confirmed':'pending'),
               'source'=>$admin?'painel':'site','created_at'=>date('c')];
        $all[]=$item; if(!write_json(DATA_DIR.'/appointments.json',$all)) return [false,'Não foi possível gravar. Verifique a pasta data/.',500];
        return [true,$item,200];
    });
}
