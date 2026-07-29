<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999;"></div>

<style>
.toast-container .toast {
    min-width: 280px;
    max-width: 400px;
    border: none;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    backdrop-filter: blur(10px);
    margin-bottom: 8px;
    animation: toastSlideIn 0.35s ease;
}
.toast-container .toast.toast-exit {
    animation: toastSlideOut 0.3s ease forwards;
}
@keyframes toastSlideIn {
    from { transform: translateX(100%); opacity: 0; }
    to   { transform: translateX(0); opacity: 1; }
}
@keyframes toastSlideOut {
    from { transform: translateX(0); opacity: 1; }
    to   { transform: translateX(100%); opacity: 0; }
}
.toast-icon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    flex-shrink: 0;
}
.toast-success .toast-icon { background: rgba(25,135,84,0.15); color: #198754; }
.toast-error   .toast-icon { background: rgba(220,53,69,0.15);  color: #dc3545; }
.toast-warning .toast-icon { background: rgba(255,193,7,0.2);   color: #d4a017; }
.toast-info    .toast-icon { background: rgba(13,202,240,0.15); color: #0dcaf0; }

.toast-body-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
}
.toast-msg {
    font-size: 0.9rem;
    font-weight: 500;
    line-height: 1.4;
    flex-grow: 1;
}
</style>

<script>
(function() {
    if (window.__toastSystemInit) return;
    window.__toastSystemInit = true;

    window.showToast = function(message, type, duration) {
        type = type || 'info';
        duration = duration || 3000;

        var container = document.getElementById('toast-container');
        if (!container) return;

        var icons = {
            success: 'fas fa-check',
            error:   'fas fa-times',
            warning: 'fas fa-exclamation',
            info:    'fas fa-info'
        };

        var toast = document.createElement('div');
        toast.className = 'toast toast-' + type + ' show';
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML =
            '<div class="toast-body-wrapper p-3">' +
                '<div class="toast-icon"><i class="' + (icons[type] || icons.info) + '"></i></div>' +
                '<span class="toast-msg">' + message + '</span>' +
                '<button type="button" class="btn-close btn-close-sm ms-2" aria-label="Close" style="font-size:0.65rem;"></button>' +
            '</div>';

        var closeBtn = toast.querySelector('.btn-close');
        closeBtn.addEventListener('click', function() {
            dismissToast(toast);
        });

        container.appendChild(toast);

        var timer = setTimeout(function() {
            dismissToast(toast);
        }, duration);

        toast._timer = timer;
    };

    function dismissToast(toast) {
        if (toast._dismissed) return;
        toast._dismissed = true;
        if (toast._timer) clearTimeout(toast._timer);
        toast.classList.add('toast-exit');
        toast.classList.remove('show');
        setTimeout(function() {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, 300);
    }

    window.showToastSuccess = function(msg, dur) { window.showToast(msg, 'success', dur); };
    window.showToastError   = function(msg, dur) { window.showToast(msg, 'error', dur); };
    window.showToastWarning = function(msg, dur) { window.showToast(msg, 'warning', dur); };
    window.showToastInfo    = function(msg, dur) { window.showToast(msg, 'info', dur); };
})();
</script>
