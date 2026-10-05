const nav=document.querySelector('#nav'),menu=document.querySelector('.menu');menu?.addEventListener('click',()=>nav?.classList.toggle('open'));document.querySelectorAll('.nav nav a').forEach(a=>a.addEventListener('click',()=>nav?.classList.remove('open')));
const form=document.querySelector('#bookingForm'),msg=document.querySelector('#bookingMsg'),box=document.querySelector('#slots');
const today=()=>{const d=new Date();return new Date(d-d.getTimezoneOffset()*6e4).toISOString().slice(0,10)};
async function loadSlots(){if(!form)return;form.time.value='';const q=new URLSearchParams({barber:form.barber.value,service:form.service.value,date:form.date.value});
 if(!form.date.value){box.innerHTML='<small>Escolha a data para ver os horários livres.</small>';return}
 box.innerHTML='<small>Buscando horários…</small>';
 try{const j=await (await fetch('api.php?action=slots&'+q)).json();
  box.innerHTML=j.slots&&j.slots.length?j.slots.map(t=>`<button type="button" class="slot" data-t="${t}">${t}</button>`).join(''):'<small>Sem horários livres neste dia para este barbeiro. Tente outra data.</small>'}
 catch{box.innerHTML='<small>Não foi possível carregar os horários.</small>'}}
box?.addEventListener('click',e=>{const b=e.target.closest('.slot');if(!b)return;form.time.value=b.dataset.t;box.querySelectorAll('.slot').forEach(x=>x.classList.toggle('on',x===b))});
['barber','service','date'].forEach(n=>form?.querySelectorAll(`[name=${n}]`).forEach(x=>x.addEventListener('change',loadSlots)));
form?.addEventListener('submit',async e=>{e.preventDefault();const btn=e.submitter;msg.className='form-msg';
 if(!form.time.value){msg.textContent='Escolha um horário livre.';msg.classList.add('err');return}
 btn.disabled=true;msg.textContent='Reservando seu horário...';
 try{const data=Object.fromEntries(new FormData(form));const r=await fetch('booking.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});const j=await r.json();
  if(!r.ok||!j.ok){if(r.status===409)loadSlots();throw Error(j.error||'Não foi possível agendar.')}
  const a=j.appointment||{};msg.classList.add('ok');msg.innerHTML=`✔ Reservado com ${a.barber} em ${a.date} às ${a.time}. <a href="${j.whatsapp}" target="_blank" rel="noopener">Abrir WhatsApp para confirmar</a>`;
  if(j.whatsapp)window.open(j.whatsapp,'_blank','noopener');form.reset();form.date.min=today();loadSlots()}
 catch(err){msg.textContent=err.message;msg.classList.add('err')}finally{btn.disabled=false}});
document.querySelectorAll('input[type=date]').forEach(x=>{x.min=today()});
