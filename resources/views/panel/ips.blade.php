@extends('layouts.envelope')

@section('title', 'Detected IPs')

@section('content')
<div class="envelope-container">
    <div class="paper" style="width:100%;max-width:1100px;padding:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <div>
                <div class="heading-primary">Detected IPs</div>
                <div class="text-muted">List of IPs that have accessed any shortlink</div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <input id="ipSearch" class="form-control" placeholder="Search IP" style="width:220px;" />
                <button class="btn btn-primary" id="refreshBtn">Refresh</button>
            </div>
        </div>

        <div class="card" style="padding:12px;">
            <table class="table" id="ipsTable">
                <thead>
                    <tr>
                        <th>IP</th>
                        <th>Hits</th>
                        <th>Last Seen</th>
                        <th>Shortlinks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
async function loadIps(q = ''){
    try{
        const res = await fetch('/panel/api/ips' + (q ? '?q=' + encodeURIComponent(q) : ''), {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        });
        const json = await res.json();
        const tbody = document.querySelector('#ipsTable tbody');
        tbody.innerHTML = '';
        if(json.ok){
            json.data.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${row.ip}</td>
                    <td>${row.hits}</td>
                    <td>${row.last_seen}</td>
                    <td>${(row.slugs || '').split(',').map(s => `<span class=\"badge bg-secondary\">${s}</span>`).join(' ')}</td>
                    <td><button class=\"btn btn-outline\" onclick=\"copyIp('${row.ip}')\">Copy</button></td>
                `;
                tbody.appendChild(tr);
            });
        }
    }catch(e){
        console.error(e);
    }
}

function copyIp(ip){
    navigator.clipboard.writeText(ip).then(()=> alert('IP copied: ' + ip));
}

document.getElementById('refreshBtn').addEventListener('click', ()=> loadIps(document.getElementById('ipSearch').value));
document.getElementById('ipSearch').addEventListener('keyup', (e)=> { if(e.key === 'Enter') loadIps(e.target.value); });

loadIps();
</script>
@endpush
@endsection
