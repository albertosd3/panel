<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Shortlinks</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; background:#0f172a; color:#e2e8f0; min-height:100vh; margin:0; }
        .container{ max-width:1100px; margin:0 auto; padding:24px; }
        .card { background:#111827; padding:20px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,.35); margin-bottom:18px; }
        input,button{ padding:10px 12px; border-radius:10px; border:1px solid #374151; background:#0b1220; color:#e5e7eb; }
        button{ background:#2563eb; border:0; cursor:pointer; }
        table{ width:100%; border-collapse: collapse; }
        th,td{ border-bottom:1px solid #1f2937; padding:10px; text-align:left; white-space:nowrap; }
        .grid{ display:grid; grid-template-columns: repeat(3, 1fr); gap:12px; }
        .stat{ background:#0b1220; padding:14px; border-radius:12px; }
        .error{ color:#fda4af; }
        .agg{ display:flex; gap:16px; flex-wrap:wrap; font-size:13px; }
        .agg div{ background:#0b1220; padding:6px 10px; border-radius:12px; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <h2>Buat Shortlink</h2>
        <form id="createForm">
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <input style="flex:2" name="destination" placeholder="https://tujuan.com/path" required>
                <input style="flex:1" name="slug" placeholder="custom-slug (opsional)">
                <button type="submit">Buat</button>
            </div>
            <p id="createError" class="error" style="display:none"></p>
            <p id="createdUrl" style="margin-top:8px;"></p>
        </form>
    </div>

    <div class="card">
        <h2>Statistik (Realtime)</h2>
        <div class="grid">
            <div class="stat"><div>Total Klik</div><div id="statClicks">0</div></div>
            <div class="stat"><div>Last Update</div><div id="statLast">-</div></div>
            <div class="stat"><div>Slug</div><div id="statSlug">-</div></div>
        </div>
        <div id="aggregates" class="agg" style="margin-top:12px"></div>
        <div style="margin-top:14px; overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Waktu</th><th>IP</th><th>Negara</th><th>Kota</th><th>ASN</th><th>Org/ISP</th><th>Device</th><th>Platform</th><th>Browser</th><th>Referrer</th><th>Bot?</th>
                    </tr>
                </thead>
                <tbody id="eventsBody"></tbody>
            </table>
        </div>
    </div>
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
        createdUrl.textContent = 'Short URL: ' + data.short_url;
        currentSlug = data.data.slug;
        statSlug.textContent = currentSlug;
        startRealtime();
    } catch (err) {
        createError.textContent = err.message;
        createError.style.display = 'block';
    }
});

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
    if (agg.by_country) aggregates.appendChild(tag('Negara: ' + agg.by_country.map(x => x.country+':'+x.c).join(', ')));
    if (agg.by_org) aggregates.appendChild(tag('Org: ' + agg.by_org.map(x => (x.org||'-')+':'+x.c).join(', ')));
    if (agg.by_device) aggregates.appendChild(tag('Device: ' + agg.by_device.map(x => x.device+':'+x.c).join(', ')));
    if (agg.by_browser) aggregates.appendChild(tag('Browser: ' + agg.by_browser.map(x => x.browser+':'+x.c).join(', ')));

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
</script>
</body>
</html>
