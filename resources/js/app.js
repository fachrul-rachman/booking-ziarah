import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.confirmModal = function () {
    return {
        isOpen: false,
        title: 'Konfirmasi',
        message: 'Lanjutkan aksi ini?',
        confirmText: 'Lanjutkan',
        confirmColor: 'bg-blue-700',
        formId: null,
        onConfirm: null,
        callbackEvent: null,
        open(detail = {}) {
            this.title = detail.title ?? 'Konfirmasi';
            this.message = detail.message ?? 'Lanjutkan aksi ini?';
            this.confirmText = detail.confirmText ?? 'Lanjutkan';
            this.confirmColor = detail.confirmColor ?? 'bg-blue-700';
            this.formId = detail.formId ?? null;
            this.onConfirm = typeof detail.onConfirm === 'function' ? detail.onConfirm : null;
            this.callbackEvent = typeof detail.callbackEvent === 'string' ? detail.callbackEvent : null;
            this.isOpen = true;
        },
        close() {
            this.isOpen = false;
            this.formId = null;
            this.onConfirm = null;
            this.callbackEvent = null;
        },
        confirm() {
            if (this.onConfirm) {
                try {
                    this.onConfirm();
                } finally {
                    this.close();
                }
                return;
            }

            if (this.formId) {
                const form = document.getElementById(this.formId);
                if (form) form.submit();
            }
            this.close();
        },
    };
};

Alpine.start();
