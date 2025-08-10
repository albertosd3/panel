@extends('layouts.envelope')

@section('title', 'Secure Panel Access')

@push('styles')
<style>
    .login-envelope {
        width: 100%;
        max-width: 420px;
        position: relative;
    }
    
    .envelope-flap {
        position: relative;
        height: 160px;
        background: linear-gradient(135deg, var(--color-primary) 0%, #1e40af 100%);
        border-radius: 12px 12px 0 0;
        overflow: hidden;
    }
    
    .envelope-flap::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='m36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
    }
    
    .envelope-seal {
        position: absolute;
        top: 120px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 80px;
        background: var(--color-white);
        border-radius: 50%;
        border: 4px solid var(--color-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        box-shadow: var(--shadow-lg);
    }
    
    .seal-icon {
        width: 32px;
        height: 32px;
        background: var(--color-primary);
        border-radius: 50%;
        position: relative;
    }
    
    .seal-icon::before {
        content: '🔒';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 16px;
    }
    
    .letter-content {
        background: var(--color-white);
        padding: 40px 32px 32px;
        margin-top: -40px;
        border-radius: 0 0 12px 12px;
        position: relative;
        z-index: 5;
    }
    
    .security-badge {
        display: inline-flex;
        align-items: center;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 1px solid #fbbf24;
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 11px;
        font-weight: 600;
        color: #92400e;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 24px;
    }
    
    .security-badge::before {
        content: '🛡️';
        margin-right: 6px;
    }
    
    .pin-input {
        text-align: center;
        font-family: 'SF Mono', 'Monaco', 'Consolas', 'Roboto Mono', monospace;
        font-size: 18px;
        letter-spacing: 4px;
        font-weight: 600;
        padding: 16px;
        background: #f8fafc;
        border: 2px solid #e5e7eb;
    }
    
    .pin-input:focus {
        background: var(--color-white);
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
    
    .access-info {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
        margin-top: 24px;
        text-align: center;
    }
    
    .access-info-title {
        font-weight: 600;
        color: var(--color-dark);
        margin-bottom: 4px;
        font-size: 13px;
    }
    
    .access-info-text {
        color: var(--color-muted);
        font-size: 12px;
        line-height: 1.5;
    }
</style>
@endpush

@section('content')
<div class="envelope-container">
    <div class="login-envelope">
        <div class="paper">
            <!-- Envelope Flap -->
            <div class="envelope-flap">
                <div class="envelope-seal">
                    <div class="seal-icon"></div>
                </div>
            </div>
            
            <!-- Letter Content -->
            <div class="letter-content">
                <div class="text-center">
                    <div class="security-badge">Secure Access</div>
                    <h1 class="heading-primary">Panel Authentication</h1>
                    <p class="text-muted mb-4">Enter your 6-digit security PIN to continue</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-error">
                        <strong>Access Denied:</strong> {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('panel.verify') }}">
                    @csrf
                    <div class="form-group">
                        <label for="pin" class="form-label">Security PIN</label>
                        <input 
                            id="pin"
                            name="pin"
                            type="password"
                            class="form-control pin-input"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            minlength="6"
                            maxlength="6"
                            autocomplete="one-time-code"
                            placeholder="••••••"
                            required
                            value="{{ old('pin') }}"
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-full">
                        Access Control Panel
                    </button>
                </form>
                
                <div class="access-info">
                    <div class="access-info-title">Protected Environment</div>
                    <div class="access-info-text">
                        This panel is secured with enterprise-grade authentication.<br>
                        Only authorized personnel with valid PIN access are permitted.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pinInput = document.getElementById('pin');
    
    // Auto-focus on load
    pinInput.focus();
    
    // Only allow numeric input
    pinInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Auto-submit when 6 digits entered
        if (this.value.length === 6) {
            this.form.submit();
        }
    });
    
    // Prevent paste of non-numeric content
    pinInput.addEventListener('paste', function(e) {
        e.preventDefault();
        const paste = (e.clipboardData || window.clipboardData).getData('text');
        const numericPaste = paste.replace(/[^0-9]/g, '').slice(0, 6);
        this.value = numericPaste;
        
        if (numericPaste.length === 6) {
            this.form.submit();
        }
    });
});
</script>
@endpush
