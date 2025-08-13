@extends('layouts.envelope')

@section('title', 'Secure Panel Access')

@push('styles')
<style>
    .terminal-wrap { width: 100%; max-width: 520px; }

    /* Terminal window */
    .terminal-window { border: 1px solid var(--color-border); border-radius: 12px; background: var(--color-surface); box-shadow: var(--shadow-xl); overflow: hidden; }
    .terminal-topbar { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #0b160e; border-bottom: 1px solid var(--color-border); }
    .traffic { display: flex; gap: 8px; }
    .dot { width: 10px; height: 10px; border-radius: 50%; }
    .dot.red { background: #ef4444; } .dot.yellow { background: #f59e0b; } .dot.green { background: #22c55e; }
    .term-title { font-family: var(--font-mono); font-size: 12px; color: var(--color-text-muted); letter-spacing: .04em; }

    .terminal-body { padding: 28px 24px 22px; background: repeating-linear-gradient(0deg, rgba(34,197,94,0.02) 0, rgba(34,197,94,0.02) 2px, transparent 2px, transparent 24px); }

    .brand { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
    .brand-logo { width: 28px; height: 28px; border-radius: 6px; background: linear-gradient(135deg, #0d1a12, #0f1f15); border: 1px solid var(--color-border); display: grid; place-items: center; box-shadow: 0 0 14px rgba(34,197,94,.15); }
    .brand-logo::after { content: '#'; color: var(--color-primary); font-family: var(--font-mono); font-weight: 800; }

    .brand-text h1 { font-family: var(--font-mono); font-size: 22px; margin: 0; letter-spacing: .02em; }
    .brand-text p { color: var(--color-text-muted); font-size: 12px; margin-top: 2px; }

    .prompt { font-family: var(--font-mono); color: var(--color-text-secondary); font-size: 12px; margin-bottom: 8px; }
    .prompt .path { color: var(--color-primary); }

    /* Segmented PIN */
    .pin-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; margin: 8px 0 18px; }
    .pin-box { width: 100%; height: 56px; text-align: center; font-family: var(--font-mono); font-size: 20px; font-weight: 700; background: var(--color-bg-secondary); color: var(--color-text-primary); border: 1px solid var(--color-border); border-radius: 10px; transition: all .2s ease; caret-color: transparent; }
    .pin-box:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(34,197,94,.25), 0 0 12px rgba(34,197,94,.25); background: var(--color-bg-tertiary); transform: translateY(-1px); }
    .pin-box.filled { border-color: var(--color-primary); box-shadow: 0 0 6px rgba(34,197,94,.3); }

    .cmd { display: flex; align-items: center; gap: 10px; font-family: var(--font-mono); }
    .caret { width: 8px; height: 16px; background: var(--color-primary); animation: blink 1.2s steps(2, start) infinite; }
    @keyframes blink { to { visibility: hidden; } }

    .access-info { background: var(--color-bg-tertiary); border: 1px solid var(--color-border); border-radius: 8px; padding: 14px; margin-top: 18px; }
    .access-info-title { font-weight: 700; font-family: var(--font-mono); font-size: 12px; color: var(--color-text-secondary); letter-spacing: .06em; text-transform: uppercase; margin-bottom: 4px; }
    .access-info-text { color: var(--color-text-muted); font-size: 12px; }

    .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0;}
</style>
@endpush

@section('content')
<div class="envelope-container">
    <div class="terminal-wrap">
        <div class="terminal-window">
            <div class="terminal-topbar">
                <div class="traffic">
                    <span class="dot red"></span>
                    <span class="dot yellow"></span>
                    <span class="dot green"></span>
                </div>
                <div class="term-title">admin@panel: ~/secure/login</div>
            </div>
            <div class="terminal-body">
                <div class="brand">
                    <div class="brand-logo"></div>
                    <div class="brand-text">
                        <h1>Access Control</h1>
                        <p>Enter 6-digit passcode to continue</p>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-error"><strong>Access Denied:</strong> {{ $errors->first() }}</div>
                @endif

                <div class="prompt">$ <span class="path">auth</span> --mode=pin --digits=6</div>

                <form id="pinForm" method="POST" action="{{ route('panel.verify') }}" data-initial-pin="{{ old('pin') }}">
                    @csrf

                    <label class="form-label" for="pin-box-0">Passcode</label>
                    <input type="hidden" name="pin" id="pin" value="">

                    <div class="pin-grid" aria-label="6-digit PIN input" role="group">
                        <span class="sr-only" id="pin-instructions">Masukkan 6 digit. Gunakan panah kiri/kanan untuk navigasi.</span>
                        <input id="pin-box-0" class="pin-box" type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-labelledby="pin-instructions" autocomplete="one-time-code" />
                        <input id="pin-box-1" class="pin-box" type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-labelledby="pin-instructions" />
                        <input id="pin-box-2" class="pin-box" type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-labelledby="pin-instructions" />
                        <input id="pin-box-3" class="pin-box" type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-labelledby="pin-instructions" />
                        <input id="pin-box-4" class="pin-box" type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-labelledby="pin-instructions" />
                        <input id="pin-box-5" class="pin-box" type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-labelledby="pin-instructions" />
                    </div>

                    <div class="cmd">
                        <button type="submit" class="btn btn-primary">$ submit --with=pin</button>
                        <span class="caret"></span>
                    </div>
                </form>

                <div class="access-info">
                    <div class="access-info-title">Security Notice</div>
                    <div class="access-info-text">Multiple failed attempts will trigger rate limiting. Authorized personnel only.</div>
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

    let suppressAuto = false;

    boxes[0].focus();

    function updateHiddenAndAutoSubmit() {
        const val = boxes.map(b => (b.value || '').replace(/\D/g, '').slice(0,1)).join('');
        hidden.value = val;
        boxes.forEach(b => b.classList.toggle('filled', !!b.value));
        if (!suppressAuto && val.length === 6) { form.submit(); }
    }

    // Prefill from previous attempt if available
    const initial = (form.getAttribute('data-initial-pin') || '').replace(/\D/g,'').slice(0,6);
    if (initial.length) {
        suppressAuto = true;
        for (let i = 0; i < boxes.length; i++) {
            boxes[i].value = initial[i] || '';
        }
        updateHiddenAndAutoSubmit();
        suppressAuto = false;
        // Place cursor at the end
        const next = initial.length < boxes.length ? boxes[initial.length] : boxes[boxes.length - 1];
        next.focus();
    }

    boxes.forEach((box, idx) => {
        box.addEventListener('input', () => {
            const d = (box.value || '').replace(/\D/g, '');
            box.value = d.slice(0,1);
            if (d.length && idx < boxes.length - 1) boxes[idx+1].focus();
            updateHiddenAndAutoSubmit();
        });

        box.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft' && idx > 0) { e.preventDefault(); boxes[idx-1].focus(); }
            else if (e.key === 'ArrowRight' && idx < boxes.length - 1) { e.preventDefault(); boxes[idx+1].focus(); }
            else if (e.key === 'Backspace' && !box.value && idx > 0) { boxes[idx-1].value=''; boxes[idx-1].focus(); updateHiddenAndAutoSubmit(); e.preventDefault(); }
        });

        box.addEventListener('paste', (e) => {
            e.preventDefault();
            const digits = ((e.clipboardData||window.clipboardData).getData('text')||'').replace(/\D/g,'').slice(0,6);
            if (!digits) return;
            let i = idx; for (const ch of digits) { if (i>=boxes.length) break; boxes[i].value = ch; i++; }
            (i <= boxes.length-1 ? boxes[i] : boxes[boxes.length-1]).focus();
            updateHiddenAndAutoSubmit();
        });
    });

    form.addEventListener('submit', (e) => {
        const val = boxes.map(b => b.value).join('');
        hidden.value = val;
        if (val.length !== 6 || /\D/.test(val)) e.preventDefault();
    });
});
</script>
@endpush
