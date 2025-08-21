@extends('layouts.envelope')

@section('title', 'Visitors - ' . $link->slug)

@section('content')
<div class="envelope-container">
    <div class="paper" style="width:100%;max-width:1100px;padding:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <div>
                <div class="heading-primary">Visitors for {{ $link->slug }}</div>
                <div class="text-muted">IP addresses that accessed this shortlink</div>
                <div class="text-small" style="margin-top:4px;">
                    <span class="badge bg-secondary">{{ $link->destination }}</span>
                </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <input id="visitorSearch" class="form-control" placeholder="Search IP" style="width:220px;" />
                <button class="btn btn-primary" id="refreshBtn">Refresh</button>
                <a href="{{ route('panel.shortlinks') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>

        <div class="card" style="padding:12px;">
            <table class="table" id="visitorsTable">
                <thead>
                    <tr>
                        <th>IP</th>
                        <th>Hits</th>
                        <th>First Seen</th>
                        <th>Last Seen</th>
                        <th>Bot</th>
                        <th>Location</th>
                        <th>ASN/Org</th>
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
const slug = '{{ $link->slug }}';

async function loadVisitors(q = ''){
    try{
        const url = `/api/shortlinks/${slug}/visitors` + (q ? '?q=' + encodeURIComponent(q) : '');
        
        const res = await fetch(url, {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        });
        
        const json = await res.json();
        const tbody = document.querySelector('#visitorsTable tbody');
        tbody.innerHTML = '';
        
        if(json.ok && json.data && json.data.length > 0){
            json.data.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${row.ip}</td>
                    <td>${row.hits}</td>
                    <td>${row.first_seen || '-'}</td>
                    <td>${row.last_seen || '-'}</td>
                    <td>${row.is_bot ? '<span class="badge bg-danger">Bot</span>' : '<span class="badge bg-success">Human</span>'}</td>
                    <td>${(row.city || '') + (row.country ? ' (' + row.country + ')' : '')}</td>
                    <td>${row.asn || ''} ${row.org || ''}</td>
                    <td><button class="btn btn-outline" onclick="copyIp('${row.ip}')">Copy</button></td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No visitors found</td></tr>';
        }
    }catch(e){
        console.error('Error loading visitors:', e);
        const tbody = document.querySelector('#visitorsTable tbody');
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error loading data: ' + e.message + '</td></tr>';
    }
}

function copyIp(ip){
    navigator.clipboard.writeText(ip).then(()=> alert('IP copied: ' + ip));
}

document.getElementById('refreshBtn').addEventListener('click', ()=> {
    loadVisitors(document.getElementById('visitorSearch').value);
});

document.getElementById('visitorSearch').addEventListener('keyup', (e)=> { 
    if(e.key === 'Enter') {
        loadVisitors(e.target.value); 
    }
});

loadVisitors();
</script>
@endpush
@endsection
