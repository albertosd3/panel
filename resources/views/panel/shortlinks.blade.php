<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Shortlinks</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root{ --bg:#0b1020; --panel:#0e1726; --muted:#8aa0b3; --text:#e6eef6; --border:rgba(255,255,255,.08); --chip:#0b1322; --primary:#4f8cff; }
        *{ box-sizing:border-box }
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; background:var(--bg); color:var(--text); min-height:100vh; margin:0; }
        .wrap{ display:grid; grid-template-columns: 320px 1fr; min-height:100vh; }
        .sidebar{ background:linear-gradient(180deg, rgba(255,255,255,.02), rgba(255,255,255,.005)); border-right:1px solid var(--border); padding:18px; }
        .main{ padding:22px; }
        .card { background:linear-gradient(180deg, rgba(255,255,255,.02), rgba(255,255,255,.005)); border:1px solid var(--border); border-radius:16px; padding:16px; box-shadow:0 10px 30px rgba(0,0,0,.35); margin-bottom:16px; }
        input,button{ padding:10px 12px; border-radius:10px; border:1px solid var(--border); background:#0b1220; color:#e5e7eb; }
        button{ background:linear-gradient(90deg, #4f8cff, #3b82f6); border:0; cursor:pointer; color:#fff; font-weight:700 }
        .list{ max-height:calc(100vh - 160px); overflow:auto; }
        .item{ padding:10px; border-radius:10px; border:1px solid transparent; cursor:pointer; }
        .item:hover{ background:#0b1322; }
        .item.active{ background:#0b1322; border-color:#1e293b; }
        .muted{ color:var(--muted); font-size:12px; }
        table{ width:100%; border-collapse: collapse; }
        th,td{ border-bottom:1px solid #1f2937; padding:10px; text-align:left; white-space:nowrap; }
        .grid{ display:grid; grid-template-columns: repeat(3, 1fr); gap:12px; }
        .stat{ background:#0b1220; padding:14px; border-radius:12px; }
        .agg{ display:flex; gap:10px; flex-wrap:wrap; font-size:13px; }
        .agg div{ background:#0b1220; padding:6px 10px; border-radius:12px; }
        .topbar{ display:flex; justify-content:space-between; align-items:center; margin-bottom:10px }
        a{ color:#9ec1ff; text-decoration:none }
    </style>
</head>
<body>
<div class="wrap">
    <aside class="sidebar">
        <h3 style="margin:0 0 8px">Shortlinks</h3>
        <div class="card">
            <form id="createForm">
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <input style="flex:2" name="destination" placeholder="https://tujuan.com/path" required>
                    <input style="flex:1" name="slug" placeholder="custom-slug (opsional)">
                </div>
                <button style="width:100%; margin-top:10px" type="submit">Buat</button>
                <p id="createError" class="muted" style="color:#fda4af; display:none"></p>
                <p id="createdUrl" class="muted" style="margin-top:8px;"></p>
            </form>
        </div>
        <div class="card list" id="list"></div>
        <form method="POST" action="{{ route('panel.logout') }}">
            @csrf
            <button style="width:100%; margin-top:10px; background:#ef4444">Logout</button>
        </form>
    </aside>

    <main class="main">
        <div class="topbar">
            <div>
                <div class="muted">Slug</div>
                <div id="statSlug" style="font-weight:800; font-size:20px">-</div>
            </div>
            <div>
                <div class="muted">Total Klik</div>
                <div id="statClicks" style="font-weight:800; font-size:20px">0</div>
            </div>
            <div>
                <div class="muted">Last Update</div>
                <div id="statLast" style="font-weight:800; font-size:20px">-</div>
            </div>
        </div>
        <div class="card">
            <h3 style="margin:4px 0 10px">Agregasi</h3>
            <div id="aggregates" class="agg"></div>
        </div>
        <div class="card" style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Waktu</th><th>IP</th><th>Negara</th><th>Kota</th><th>ASN</th><th>Org/ISP</th><th>Device</th><th>Platform</th><th>Browser</th><th>Referrer</th><th>Bot?</th>
                    </tr>
                </thead>
                <tbody id="eventsBody"></tbody>
            </table>
        </div>
    </main>
</div>
<script>
const createForm = document.getElementById('createForm');
const createError = document.getElementById('createError');
const createdUrl = document.getElementById('createdUrl');
const statClicks = document.getElementById('statClicks');
const statSlug = document.getElementById('statSlug');
const statLast = document.getElementById('statLast');
const eventsBody = document.getElementById('eventsBody');
const aggregates = document.getElementById('aggregates');
const listEl = document.getElementById('list');
let currentSlug = null;

createForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    createError.style.display = 'none';
    createdUrl.textContent = '';
    const formData = new FormData(createForm);
    const payload = Object.fromEntries(formData.entries());
    try {
        const res = await fetch('{{ route('panel.shortlinks.store') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: new URLSearchParams(payload)
        });
        const data = await res.json();
        if (!data.ok) throw new Error('Gagal membuat link');
        createdUrl.innerHTML = 'Short URL: <a target="_blank" href="' + data.short_url + '">' + data.short_url + '</a>';
        await loadList();
        selectSlug(data.data.slug);
    } catch (err) {
        createError.textContent = err.message;
        createError.style.display = 'block';
    }
});

async function loadList(){
    const res = await fetch('{{ route('panel.shortlinks.list') }}');
    const json = await res.json();
    listEl.innerHTML = '';
    if (!json.ok) return;
    json.data.forEach(item => {
        const d = document.createElement('div');
        d.className = 'item' + (item.slug===currentSlug?' active':'');
        d.innerHTML = `<div style="display:flex; justify-content:space-between; gap:8px;">
            <div>
              <div style="font-weight:700">/${item.slug}</div>
              <div class="muted" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:220px">${item.destination}</div>
            </div>
            <div class="muted" title="Klik">${item.clicks}</div>
        </div>`;
        d.onclick = () => selectSlug(item.slug);
        listEl.appendChild(d);
    });
}

function selectSlug(slug){
    currentSlug = slug;
    statSlug.textContent = slug;
    Array.from(listEl.children).forEach(el => el.classList.toggle('active', el.querySelector('div>div').textContent === '/'+slug));
    startRealtime();
}

async function fetchStats() {
    if (!currentSlug) return;
    const res = await fetch(`/panel/shortlinks/${currentSlug}/stats`);
    const json = await res.json();
    if (!json.ok) return;
    statClicks.textContent = json.summary.clicks;
    statLast.textContent = new Date().toLocaleTimeString();
    eventsBody.innerHTML = '';
    aggregates.innerHTML = '';

    const agg = json.summary.aggregate || {};
    if (agg.by_country?.length) aggregates.appendChild(tag('Negara: ' + agg.by_country.map(x => x.country+':'+x.c).join(', ')));
    if (agg.by_org?.length) aggregates.appendChild(tag('Org: ' + agg.by_org.map(x => (x.org||'-')+':'+x.c).join(', ')));
    if (agg.by_device?.length) aggregates.appendChild(tag('Device: ' + agg.by_device.map(x => x.device+':'+x.c).join(', ')));
    if (agg.by_browser?.length) aggregates.appendChild(tag('Browser: ' + agg.by_browser.map(x => x.browser+':'+x.c).join(', '))));

    json.summary.last_200.forEach(e => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${(new Date(e.clicked_at || e.created_at)).toLocaleString()}</td>
                        <td>${e.ip ?? '-'}</td>
                        <td>${e.country ?? '-'}</td>
                        <td>${e.city ?? '-'}</td>
                        <td>${e.asn ?? '-'}</td>
                        <td>${e.org ?? '-'}</td>
                        <td>${e.device ?? '-'}</td>
                        <td>${e.platform ?? '-'}</td>
                        <td>${e.browser ?? '-'}</td>
                        <td>${e.referrer ?? '-'}</td>
                        <td>${e.is_bot ? 'Yes' : 'No'}</td>`;
        eventsBody.appendChild(tr);
    });
}

function tag(text){ const d=document.createElement('div'); d.textContent=text; return d; }

let intervalId = null;
function startRealtime(){
    if (intervalId) clearInterval(intervalId);
    fetchStats();
    intervalId = setInterval(fetchStats, 2000);
}

// initial load
loadList().then(() => {
    if (!currentSlug && listEl.firstChild) {
        // auto-select first
        const slug = listEl.firstChild.querySelector('div>div').textContent.slice(1);
        selectSlug(slug);
    }
});
</script>
</body>
</html>
