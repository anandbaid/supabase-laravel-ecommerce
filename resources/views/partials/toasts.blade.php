{{-- Toast notification system. Include once per layout. --}}
<div id="toast-container" style="position:fixed;top:1rem;right:1rem;z-index:9999;display:flex;flex-direction:column;gap:0.5rem;max-width:22rem;"></div>

<script>
    (function () {
        function iconFor(type) {
            switch (type) {
                case 'success': return '✅';
                case 'error': return '⚠️';
                case 'info': return 'ℹ️';
                default: return '🔔';
            }
        }

        function colorFor(type) {
            switch (type) {
                case 'success': return { bg: '#ecfdf5', border: '#10b981', text: '#065f46' };
                case 'error': return { bg: '#fef2f2', border: '#ef4444', text: '#991b1b' };
                case 'info': return { bg: '#eff6ff', border: '#3b82f6', text: '#1e40af' };
                default: return { bg: '#f9fafb', border: '#9ca3af', text: '#374151' };
            }
        }

        window.showToast = function (message, type = 'info', duration = 5000) {
            try {
                var container = document.getElementById('toast-container');
                if (!container) return;

                var colors = colorFor(type);
                var toast = document.createElement('div');
                toast.setAttribute('role', 'status');
                toast.style.cssText = [
                    'background:' + colors.bg,
                    'border-left:4px solid ' + colors.border,
                    'color:' + colors.text,
                    'padding:0.75rem 1rem',
                    'border-radius:0.5rem',
                    'box-shadow:0 4px 12px rgba(0,0,0,0.1)',
                    'font-size:0.875rem',
                    'display:flex',
                    'align-items:flex-start',
                    'gap:0.5rem',
                    'opacity:0',
                    'transform:translateX(1rem)',
                    'transition:opacity 0.2s ease, transform 0.2s ease',
                    'font-family:inherit'
                ].join(';');

                toast.innerHTML = '<span>' + iconFor(type) + '</span><span style="flex:1;word-break:break-word;">' +
                    String(message).replace(/</g, '&lt;') + '</span>' +
                    '<button style="background:none;border:none;cursor:pointer;color:inherit;opacity:0.6;font-size:1rem;line-height:1;" aria-label="Dismiss">&times;</button>';

                container.appendChild(toast);
                requestAnimationFrame(function () {
                    toast.style.opacity = '1';
                    toast.style.transform = 'translateX(0)';
                });

                function dismiss() {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(1rem)';
                    setTimeout(function () { toast.remove(); }, 200);
                }

                toast.querySelector('button').addEventListener('click', dismiss);
                if (duration > 0) {
                    setTimeout(dismiss, duration);
                }
            } catch (e) {
                console.error('Toast failed to render:', e);
            }
        };

        // Convert any Laravel session flash messages into toasts on page load.
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success'))
                window.showToast(@json(session('success')), 'success');
            @endif
            @if(session('error'))
                window.showToast(@json(session('error')), 'error');
            @endif
            @if(session('info'))
                window.showToast(@json(session('info')), 'info');
            @endif
            @if($errors->any())
                @foreach($errors->all() as $error)
                    window.showToast(@json($error), 'error');
                @endforeach
            @endif
        });
    })();
</script>
