import './bootstrap';

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

// Livewire bundles Alpine. Avoid starting a second Alpine instance here.
// Register Alpine components using the standard alpine:init hook.
document.addEventListener('alpine:init', () => {
    // Date picker factory used by booking form (kept global to avoid timing issues)
    const datePickerFactory = ({ minDate, selected, wire }) => {
        const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        const parseYmd = (ymd) => {
            if (!ymd) return null;
            const parts = String(ymd).split('-');
            if (parts.length !== 3) return null;
            const y = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10);
            const d = parseInt(parts[2], 10);
            if (!y || !m || !d) return null;
            return new Date(y, m - 1, d);
        };

        const toYmd = (date) => {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        };

        const daysInMonth = (year, month) => new Date(year, month + 1, 0).getDate();
        const dayOfWeekMon0 = (date) => (date.getDay() + 6) % 7;

        const min = parseYmd(minDate);
        const selectedDate = parseYmd(selected) ?? null;
        const base = selectedDate ?? min ?? new Date();

        return {
            selected,
            viewYear: base.getFullYear(),
            viewMonth: base.getMonth(),
            init() {
                if (wire && typeof wire.get === 'function') {
                    const current = wire.get('booking_date');
                    if (current === null || typeof current === 'string') this.selected = current;
                }

                const selectedParsed = parseYmd(this.selected);
                const base2 = selectedParsed ?? min ?? new Date();
                this.viewYear = base2.getFullYear();
                this.viewMonth = base2.getMonth();
            },
            get monthLabel() {
                return `${monthNames[this.viewMonth]} ${this.viewYear}`;
            },
            get cells() {
                const first = new Date(this.viewYear, this.viewMonth, 1);
                const total = daysInMonth(this.viewYear, this.viewMonth);
                const leading = dayOfWeekMon0(first);
                const selectedParsed = parseYmd(this.selected);
                const cells = [];
                for (let i = 0; i < leading; i++) {
                    cells.push({ key: `e-${i}`, day: '', date: null, isDisabled: true, isSelected: false });
                }
                for (let d = 1; d <= total; d++) {
                    const dt = new Date(this.viewYear, this.viewMonth, d);
                    const ymd = toYmd(dt);
                    const isDisabled = min ? (dt < min) : false;
                    const isSelected = selectedParsed ? toYmd(selectedParsed) === ymd : false;
                    cells.push({ key: ymd, day: d, date: ymd, isDisabled, isSelected });
                }
                return cells;
            },
            prevMonth() {
                const dt = new Date(this.viewYear, this.viewMonth - 1, 1);
                this.viewYear = dt.getFullYear();
                this.viewMonth = dt.getMonth();
            },
            nextMonth() {
                const dt = new Date(this.viewYear, this.viewMonth + 1, 1);
                this.viewYear = dt.getFullYear();
                this.viewMonth = dt.getMonth();
            },
            select(ymd) {
                if (!ymd) return;
                this.selected = ymd;
                if (wire && typeof wire.set === 'function') wire.set('booking_date', ymd);
            },
        };
    };

    window.datePicker = datePickerFactory;
    if (window.Alpine) window.Alpine.data('datePicker', datePickerFactory);
});
