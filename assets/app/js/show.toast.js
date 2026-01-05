// show.toast.js (Vanilla JS + Bootstrap 5)
// Replaces jquery-toast-plugin with Native Bootstrap 5 Toasts

(function () {
    'use strict';

    // Helper to create/get container
    function getToastContainer() {
        let container = document.getElementById('toast-container-bs5');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container-bs5';
            // Default position: Top Right
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1055'; // High z-index (above modals)
            document.body.appendChild(container);
        }
        return container;
    }

    function showBsToast(type, heading, message) {
        const container = getToastContainer();

        // Colors mapping
        let headerClass = 'bg-primary text-white';
        let iconClass = 'fa-info-circle';

        switch (type) {
            case 'success':
                headerClass = 'bg-success text-white';
                iconClass = 'fa-check-circle';
                break;
            case 'error':
            case 'danger':
                headerClass = 'bg-danger text-white';
                iconClass = 'fa-exclamation-circle';
                break;
            case 'warning':
                headerClass = 'bg-warning text-dark';
                iconClass = 'fa-exclamation-triangle';
                break;
            case 'info':
            default:
                headerClass = 'bg-info text-white';
                iconClass = 'fa-info-circle';
                break;
        }

        const id = 'toast-' + Date.now();
        // Close button color
        const btnCloseClass = (type === 'warning' || type === 'light') ? '' : 'btn-close-white';

        const html = `
            <div id="${id}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header ${headerClass}">
                    <i class="fa ${iconClass} me-2"></i>
                    <strong class="me-auto">${heading}</strong>
                    <button type="button" class="btn-close ${btnCloseClass}" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body bg-white text-dark">
                    ${message}
                </div>
            </div>
        `;

        // Safe DOM Insertion
        const temp = document.createElement('div');
        temp.innerHTML = html.trim();
        const toastEl = temp.firstChild;
        container.appendChild(toastEl);

        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
            toast.show();
            // Cleanup after hidden
            toastEl.addEventListener('hidden.bs.toast', () => {
                toastEl.remove();
            });
        } else {
            console.warn('Bootstrap 5 JS not loaded. Toast fallback.');
            toastEl.style.display = 'block';
            setTimeout(() => {
                toastEl.remove();
            }, 4000);
        }
    }

    // Global exports for compatibility
    window.showSuccessToast = function (msg) {
        showBsToast('success', 'Berhasil', msg);
    };
    window.showInfoToast = function (msg) {
        showBsToast('info', 'Info', msg);
    };
    window.showWarningToast = function (msg) {
        showBsToast('warning', 'Peringatan', msg);
    };
    window.showDangerToast = function (msg) {
        showBsToast('danger', 'Gagal', msg);
    };

    // CBT Explicit Helper (Avoid conflicts with alert() definitions elsewhere)
    window.showCbtWarning = function (msg) {
        showBsToast('warning', 'Peringatan', msg);
    };

    // Stub for resetToastPosition (no-op in BS5 implementation)
    window.resetToastPosition = function () { };

})();
