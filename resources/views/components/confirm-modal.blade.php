@props([
    'title' => 'Konfirmasi',
    'message' => 'Lanjutkan aksi ini?',
    'confirmText' => 'Lanjutkan',
    'confirmColor' => 'bg-blue-700',
])

<div
    x-data="confirmModal()"
    x-on:open-confirm-modal.window="open($event.detail)"
    x-cloak
>
    <div
        class="fixed inset-0 z-40 bg-black/40"
        x-show="isOpen"
        x-transition.opacity
        aria-hidden="true"
    ></div>

    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-show="isOpen"
        x-transition
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirm-modal-title"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-5 shadow-xl ring-1 ring-black/5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 id="confirm-modal-title" class="text-base font-semibold text-gray-900" x-text="title"></h2>
                    <p class="mt-2 text-sm text-gray-700" x-text="message"></p>
                </div>
                <button type="button" class="-mr-1 -mt-1 rounded p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700" @click="close()" aria-label="Tutup">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <div class="mt-5 flex items-center justify-end gap-2">
                <button type="button" class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-900 hover:bg-gray-300" @click="close()">
                    Batal
                </button>

                <button
                    type="button"
                    class="rounded-md px-4 py-2 text-sm font-semibold text-white"
                    :class="confirmColor"
                    @click="confirm()"
                >
                    <span x-text="confirmText"></span>
                </button>
            </div>
        </div>
    </div>

    <form x-ref="callbackForm" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="callback" value="">
    </form>
</div>
