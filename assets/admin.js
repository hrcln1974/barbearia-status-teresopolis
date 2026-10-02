const $ = s => document.querySelector(s);
const $$ = s => [...document.querySelectorAll(s)];
let gallery = [], appointments = [], settings = {};

function esc(v='') {
  return String(v).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
}
async function api(action, options={}) {
  const r = await fetch(`api.php?action=${encodeURIComponent(action)}`, options);
  let j = {};
  try { j = await r.json(); } catch {}
  if (!r.ok) throw new Error(j.error || `Erro HTTP ${r.status}`);
  return j;
}
function showTab(name) {
  $$('.tab').forEach(t => t.classList.toggle('hidden', t.id !== name));
  $$('aside button[data-tab]').forEach(b => b.classList.toggle('active', b.dataset.tab === name));
  if (name === 'dash') loadDashboard();
  if (name === 'appointments') loadAppointments();
  if (name === 'gallery') loadGallery();
  if (name === 'settings') loadSettings();
}
async function loadDashboard() {
  const j = await api('dashboard');
  $('#statPhotos').textContent = j.stats.photos;
  $('#statAppointments').textContent = j.stats.appointments;
  $('#statPending').textContent = j.stats.pending;
  $('#navPending').textContent = j.stats.pending;
}
async function loadGallery() {
  const j = await api('gallery'); gallery = j.items || [];
  $('#galleryGrid').innerHTML = gallery.map(x => `
    <article><img src="${esc(x.url)}" alt="${esc(x.title || 'Foto da galeria')}" loading="lazy">
      <div><span>${esc(x.title || 'Sem título')}</span><button class="danger" data-del="${esc(x.id)}">Excluir</button></div>
    </article>`).join('') || '<div class="panel empty">Nenhuma foto cadastrada.</div>';
}
async function loadAppointments() {
  const j = await api('appointments'); appointments = j.items || [];
  $('#appointmentsList').innerHTML = appointments.map(x => `
    <article class="appointment">
      <div class="appt-main"><strong>${esc(x.name)}</strong><span>${esc(x.service)}</span><small>${esc(x.date)} às ${esc(x.time)} • ${esc(x.phone)}</small>${x.message ? `<p>${esc(x.message)}</p>`:''}</div>
      <div class="appt-actions"><span class="status ${esc(x.status||'pending')}">${esc(x.status||'pending')}</span>
      <select data-status="${esc(x.id)}"><option value="pending">Pendente</option><option value="confirmed">Confirmado</option><option value="done">Concluído</option><option value="cancelled">Cancelado</option></select></div>
    </article>`).join('') || '<div class="panel empty">Nenhum agendamento registrado.</div>';
  appointments.forEach(x => { const el = document.querySelector(`select[data-status="${CSS.escape(x.id)}"]`); if(el) el.value=x.status||'pending'; });
}
async function loadSettings() {
  const j = await api('settings'); settings = j.settings || {};
  Object.entries(settings).forEach(([k,v]) => { const el = document.querySelector(`[name="${CSS.escape(k)}"]`); if(el) el.value=v ?? ''; });
}
$('#loginForm').addEventListener('submit', async e => {
  e.preventDefault(); $('#loginMsg').textContent = 'Entrando...';
  try {
    await api('login',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({user:$('#user').value,pass:$('#pass').value})});
    $('#login').classList.add('hidden'); $('#app').classList.remove('hidden'); showTab('dash');
  } catch(err) { $('#loginMsg').textContent = err.message; }
});
$$('aside button[data-tab], .quick button[data-tab]').forEach(b => b.addEventListener('click', () => showTab(b.dataset.tab)));
$('#logout').addEventListener('click', async () => { try { await api('logout',{method:'POST'}); } finally { location.reload(); } });
$('#uploadForm').addEventListener('submit', async e => {
  e.preventDefault(); const btn=e.submitter; btn.disabled=true;
  try { const j=await api('gallery',{method:'POST',body:new FormData(e.target)}); if(j.ok){e.target.reset();await loadGallery();await loadDashboard();} }
  catch(err){ alert(err.message); } finally { btn.disabled=false; }
});
$('#galleryGrid').addEventListener('click', async e => {
  const id=e.target.dataset.del; if(!id)return; if(!confirm('Excluir esta foto?'))return;
  try { await api('gallery_delete',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})}); await loadGallery(); await loadDashboard(); }
  catch(err){alert(err.message);}
});
$('#settingsForm').addEventListener('submit', async e => {
  e.preventDefault(); $('#saveMsg').textContent='Salvando...';
  try { await api('settings',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(Object.fromEntries(new FormData(e.target)))}); $('#saveMsg').textContent='Configurações salvas com sucesso.'; }
  catch(err){$('#saveMsg').textContent=err.message;}
});
$('#appointmentsList').addEventListener('change', async e => {
  const id=e.target.dataset.status; if(!id)return;
  try { await api('appointment_status',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,status:e.target.value})}); await loadAppointments(); await loadDashboard(); }
  catch(err){alert(err.message);}
});
(async function init(){
  try { const j=await api('session'); if(j.authenticated){$('#login').classList.add('hidden');$('#app').classList.remove('hidden');showTab('dash');} } catch {}
})();
