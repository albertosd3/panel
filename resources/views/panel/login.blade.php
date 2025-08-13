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
        background: var(--color-surface);
        border-radius: 50%;
        border: 4px solid var(--color-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        box-shadow: var(--shadow-lg), 0 0 12px rgba(59, 130, 246, 0.35);
    }
    
    .seal-icon {
        width: 32px;
        height: 32px;
        background: var(--color-primary);
        border-radius: 50%;
        position: relative;
        box-shadow: 0 0 10px var(--color-primary), 0 0 20px rgba(59,130,246,0.6);
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
        background: var(--color-surface);
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

    /* Segmented PIN UI */
    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0; }

    .pin-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 10px;
        margin: 10px 0 18px;
    }

    .pin-box {
        width: 100%;
        height: 56px;
        text-align: center;
        font-family: var(--font-mono, 'SF Mono', 'Monaco', 'Consolas', 'Roboto Mono', monospace);
        font-size: 20px;
        font-weight: 700;
        background: var(--color-bg-secondary);
        color: var(--color-text-primary);
        border: 2px solid var(--color-border);
        border-radius: 10px;
        transition: all 0.2s ease;
        caret-color: transparent;
    }

    .pin-box:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(59,130,246,0.25), 0 0 12px rgba(59,130,246,0.35);
        background: var(--color-bg-tertiary);
        transform: translateY(-1px);
    }

    .pin-box.filled {
        border-color: var(--color-primary);
        box-shadow: 0 0 6px rgba(59,130,246,0.35);
    }

    .access-info {
        background: var(--color-bg-tertiary);
        border: 1px solid var(--color-border);
        border-radius: 8px;
        padding: 16px;
        margin-top: 24px;
        text-align: center;
    }
    
    .access-info-title {
        font-weight: 600;
        color: var(--color-text-primary);
        margin-bottom: 4px;
        font-size: 13px;
    }
    
    .access-info-text {
        color: var(--color-text-muted);
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

                <form id="pinForm" method="POST" action="{{ route('panel.verify') }}">
                    @csrf

                    <label class="form-label" for="pin-box-0">Security PIN</label>
                    <!-- Hidden aggregated field sent to backend -->
                    <input type="hidden" name="pin" id="pin" value="">

                    <div class="pin-grid" aria-label="6-digit PIN input" role="group">
                        <span class="sr-only" id="pin-instructions">Enter 6 digits. Use left/right keys to navigate.</span>
                        <input id="pin-box-0" class="pin-box" type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-labelledby="pin-instructions" autocomplete="one-time-code" />
                        <input id="pin-box-1" class="pin-box" type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-labelledby="pin-instructions" />
                        <input id="pin-box-2" class="pin-box" type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-labelledby="pin-instructions" />
                        <input id="pin-box-3" class="pin-box" type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-labelledby="pin-instructions" />
                        <input id="pin-box-4" class="pin-box" type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-labelledby="pin-instructions" />
                        <input id="pin-box-5" class="pin-box" type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-labelledby="pin-instructions" />
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
    const boxes = Array.from(document.querySelectorAll('.pin-box'));
    const hidden = document.getElementById('pin');
    const form = document.getElementById('pinForm');

    // Focus first
    boxes[0].focus();

    function updateHiddenAndAutoSubmit() {
        const val = boxes.map(b => (b.value || '').replace(/\D/g, '').slice(0,1)).join('');
        hidden.value = val;
        boxes.forEach(b => b.classList.toggle('filled', !!b.value));
        if (val.length === 6) {
            form.submit();
        }
    }

    boxes.forEach((box, idx) => {
        box.addEventListener('input', (e) => {
            // keep only one digit
            const d = (box.value || '').replace(/\D/g, '');
            box.value = d.slice(0,1);
            if (d.length) {
                // move to next
                if (idx < boxes.length - 1) boxes[idx+1].focus();
            }
            updateHiddenAndAutoSubmit();
        });

        box.addEventListener('keydown', (e) => {
            const key = e.key;
            if (key === 'ArrowLeft' && idx > 0) {
                e.preventDefault();
                boxes[idx-1].focus();
            } else if (key === 'ArrowRight' && idx < boxes.length - 1) {
                e.preventDefault();
                boxes[idx+1].focus();
            } else if (key === 'Backspace') {
                if (!box.value && idx > 0) {
                    boxes[idx-1].value = '';
                    boxes[idx-1].focus();
                    updateHiddenAndAutoSubmit();
                    e.preventDefault();
                }
            }
        });

        // Paste handler: distribute digits
        box.addEventListener('paste', (e) => {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text') || '';
            const digits = paste.replace(/\D/g, '').slice(0, 6);
            if (!digits) return;
            let i = idx;
            for (const ch of digits) {
                if (i >= boxes.length) break;
                boxes[i].value = ch;
                i++;
            }
            if (i <= boxes.length - 1) {
                boxes[i].focus();
            } else {
                boxes[boxes.length - 1].focus();
            }
            updateHiddenAndAutoSubmit();
        });
    });

    // Ensure hidden field is set on submit
    form.addEventListener('submit', (e) => {
        const val = boxes.map(b => b.value).join('');
        hidden.value = val;
        // Basic guard
        if (val.length !== 6 || /\D/.test(val)) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
