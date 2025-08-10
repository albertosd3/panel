@extends('layouts.envelope')

@section('title', 'Domain Management')

@section('content')
<div class="container">
    <!-- Domain Management Header -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-globe me-2"></i>Domain Management
            </h4>
        </div>
        <div class="card-body">
            <p class="mb-0">Manage your shortlink domains. Add custom domains to create branded short URLs.</p>
        </div>
    </div>

    <!-- Add Domain Form -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-plus me-2"></i>Add New Domain
            </h5>
        </div>
        <div class="card-body">
            <form id="addDomainForm">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="domain" class="form-label">Domain *</label>
                            <input type="text" class="form-control" id="domain" name="domain" 
                                   placeholder="example.com" required>
                            <div class="form-text">Enter domain without http:// or https://</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" class="form-control" id="description" name="description" 
                                   placeholder="Optional description for this domain">
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Add Domain
                    </button>
                    <a href="#domain-tutorial" class="btn btn-outline-info" data-bs-toggle="collapse">
                        <i class="fas fa-question-circle me-1"></i>Setup Tutorial
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Domain Tutorial (Collapsible) -->
    <div class="collapse" id="domain-tutorial">
        <div class="card shadow-sm mb-4 border-info">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-book me-2"></i>Domain Setup Tutorial
                </h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="tutorialAccordion">
                    <!-- Step 1: DNS Configuration -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#step1">
                                <i class="fas fa-server me-2"></i>Step 1: DNS Configuration
                            </button>
                        </h2>
                        <div id="step1" class="accordion-collapse collapse show" data-bs-parent="#tutorialAccordion">
                            <div class="accordion-body">
                                <p>Configure your domain's DNS to point to your server:</p>
                                <div class="bg-light p-3 rounded">
                                    <strong>A Record:</strong><br>
                                    <code>@ → {{ request()->getHttpHost() }}</code> (or your server IP)<br>
                                    <code>www → {{ request()->getHttpHost() }}</code> (or your server IP)
                                </div>
                                <p class="mt-2 mb-0">Wait for DNS propagation (usually 5-60 minutes).</p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Web Server Configuration -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step2">
                                <i class="fas fa-cog me-2"></i>Step 2: Web Server Configuration
                            </button>
                        </h2>
                        <div id="step2" class="accordion-collapse collapse" data-bs-parent="#tutorialAccordion">
                            <div class="accordion-body">
                                <p><strong>For Apache (.htaccess):</strong></p>
                                <div class="bg-light p-3 rounded mb-3">
                                    <code>
                                        &lt;VirtualHost *:80&gt;<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;ServerName yourdomain.com<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;ServerAlias www.yourdomain.com<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;DocumentRoot {{ base_path('public') }}<br>
                                        &lt;/VirtualHost&gt;
                                    </code>
                                </div>
                                
                                <p><strong>For Nginx:</strong></p>
                                <div class="bg-light p-3 rounded">
                                    <code>
                                        server {<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;listen 80;<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;server_name yourdomain.com www.yourdomain.com;<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;root {{ base_path('public') }};<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;index index.php;<br>
                                        }<br>
                                    </code>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: SSL Certificate -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step3">
                                <i class="fas fa-lock me-2"></i>Step 3: SSL Certificate (Recommended)
                            </button>
                        </h2>
                        <div id="step3" class="accordion-collapse collapse" data-bs-parent="#tutorialAccordion">
                            <div class="accordion-body">
                                <p>Secure your domain with HTTPS using Let's Encrypt:</p>
                                <div class="bg-light p-3 rounded">
                                    <code>
                                        # Install Certbot<br>
                                        sudo apt update<br>
                                        sudo apt install certbot python3-certbot-apache<br><br>
                                        # Get certificate<br>
                                        sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
                                    </code>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Laravel Configuration -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step4">
                                <i class="fas fa-code me-2"></i>Step 4: Laravel Configuration
                            </button>
                        </h2>
                        <div id="step4" class="accordion-collapse collapse" data-bs-parent="#tutorialAccordion">
                            <div class="accordion-body">
                                <p>Update your <code>.env</code> file:</p>
                                <div class="bg-light p-3 rounded">
                                    <code>
                                        SHORTLINK_DEFAULT_DOMAIN=yourdomain.com<br>
                                        SHORTLINK_CUSTOM_DOMAINS=yourdomain.com,anotherdomain.com<br>
                                        SHORTLINK_FORCE_HTTPS=true
                                    </code>
                                </div>
                                <p class="mt-2 mb-0">Then run: <code>php artisan config:clear</code></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Domains List -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Configured Domains
            </h5>
        </div>
        <div class="card-body">
            <div id="domainsTable">
                @if($domains->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Domain</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Default</th>
                                    <th>Shortlinks</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="domainsTableBody">
                                @foreach($domains as $domain)
                                    <tr data-domain-id="{{ $domain->id }}">
                                        <td>
                                            <strong>{{ $domain->domain }}</strong>
                                            @if($domain->is_default)
                                                <span class="badge bg-primary ms-1">Default</span>
                                            @endif
                                        </td>
                                        <td>{{ $domain->description ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $domain->is_active ? 'success' : 'secondary' }}">
                                                {{ $domain->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($domain->is_default)
                                                <i class="fas fa-check text-success"></i>
                                            @else
                                                <button class="btn btn-sm btn-outline-primary set-default-btn" 
                                                        data-domain-id="{{ $domain->id }}">
                                                    Set Default
                                                </button>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $domain->shortlinks_count ?? 0 }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-secondary test-domain-btn" 
                                                        data-domain-id="{{ $domain->id }}" title="Test Domain">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                                <button class="btn btn-outline-warning toggle-active-btn" 
                                                        data-domain-id="{{ $domain->id }}" 
                                                        title="{{ $domain->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <i class="fas fa-{{ $domain->is_active ? 'pause' : 'play' }}"></i>
                                                </button>
                                                <button class="btn btn-outline-primary edit-domain-btn" 
                                                        data-domain-id="{{ $domain->id }}" 
                                                        data-domain="{{ $domain->domain }}"
                                                        data-description="{{ $domain->description }}" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                @if(!$domain->is_default && $domain->shortlinks_count == 0)
                                                    <button class="btn btn-outline-danger delete-domain-btn" 
                                                            data-domain-id="{{ $domain->id }}" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-globe fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No domains configured</h5>
                        <p class="text-muted">Add your first custom domain above to get started.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Domain Modal -->
<div class="modal fade" id="editDomainModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Edit Domain
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editDomainForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="editDomainId" name="domain_id">
                    <div class="mb-3">
                        <label for="editDomain" class="form-label">Domain *</label>
                        <input type="text" class="form-control" id="editDomain" name="domain" required>
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <input type="text" class="form-control" id="editDescription" name="description">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Domain</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Add domain form
    document.getElementById('addDomainForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('{{ route("panel.domains.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('success', data.message);
                this.reset();
                location.reload(); // Reload to show new domain
            } else {
                showErrors(data.errors);
            }
        } catch (error) {
            showAlert('danger', 'Error adding domain: ' + error.message);
        }
    });
    
    // Edit domain form
    document.getElementById('editDomainForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const domainId = document.getElementById('editDomainId').value;
        const formData = new FormData(this);
        
        try {
            const response = await fetch(`/panel/domains/${domainId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    domain: formData.get('domain'),
                    description: formData.get('description')
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('success', data.message);
                bootstrap.Modal.getInstance(document.getElementById('editDomainModal')).hide();
                location.reload();
            } else {
                showErrors(data.errors);
            }
        } catch (error) {
            showAlert('danger', 'Error updating domain: ' + error.message);
        }
    });
    
    // Edit domain button
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-domain-btn')) {
            const btn = e.target.closest('.edit-domain-btn');
            const domainId = btn.dataset.domainId;
            const domain = btn.dataset.domain;
            const description = btn.dataset.description || '';
            
            document.getElementById('editDomainId').value = domainId;
            document.getElementById('editDomain').value = domain;
            document.getElementById('editDescription').value = description;
            
            new bootstrap.Modal(document.getElementById('editDomainModal')).show();
        }
    });
    
    // Set default domain
    document.addEventListener('click', function(e) {
        if (e.target.closest('.set-default-btn')) {
            const btn = e.target.closest('.set-default-btn');
            const domainId = btn.dataset.domainId;
            
            if (confirm('Set this domain as default?')) {
                setDefaultDomain(domainId);
            }
        }
    });
    
    // Toggle domain active status
    document.addEventListener('click', function(e) {
        if (e.target.closest('.toggle-active-btn')) {
            const btn = e.target.closest('.toggle-active-btn');
            const domainId = btn.dataset.domainId;
            
            toggleDomainActive(domainId);
        }
    });
    
    // Test domain
    document.addEventListener('click', function(e) {
        if (e.target.closest('.test-domain-btn')) {
            const btn = e.target.closest('.test-domain-btn');
            const domainId = btn.dataset.domainId;
            
            testDomain(domainId);
        }
    });
    
    // Delete domain
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-domain-btn')) {
            const btn = e.target.closest('.delete-domain-btn');
            const domainId = btn.dataset.domainId;
            
            if (confirm('Are you sure you want to delete this domain?')) {
                deleteDomain(domainId);
            }
        }
    });
    
    // Helper functions
    async function setDefaultDomain(domainId) {
        try {
            const response = await fetch(`/panel/domains/${domainId}/set-default`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('success', data.message);
                location.reload();
            } else {
                showAlert('danger', data.message);
            }
        } catch (error) {
            showAlert('danger', 'Error setting default domain: ' + error.message);
        }
    }
    
    async function toggleDomainActive(domainId) {
        try {
            const response = await fetch(`/panel/domains/${domainId}/toggle-active`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('success', data.message);
                location.reload();
            } else {
                showAlert('danger', data.message);
            }
        } catch (error) {
            showAlert('danger', 'Error toggling domain status: ' + error.message);
        }
    }
    
    async function testDomain(domainId) {
        try {
            const response = await fetch(`/panel/domains/${domainId}/test`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('success', data.message);
            } else {
                showAlert('warning', data.message);
            }
        } catch (error) {
            showAlert('danger', 'Error testing domain: ' + error.message);
        }
    }
    
    async function deleteDomain(domainId) {
        try {
            const response = await fetch(`/panel/domains/${domainId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('success', data.message);
                location.reload();
            } else {
                showAlert('danger', data.message);
            }
        } catch (error) {
            showAlert('danger', 'Error deleting domain: ' + error.message);
        }
    }
    
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.container');
        container.insertBefore(alertDiv, container.firstChild);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    
    function showErrors(errors) {
        if (errors) {
            for (const field in errors) {
                const messages = errors[field];
                messages.forEach(message => {
                    showAlert('danger', message);
                });
            }
        }
    }
});
</script>
@endsection
