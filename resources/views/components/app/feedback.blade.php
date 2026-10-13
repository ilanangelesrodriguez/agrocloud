<div data-toast-container class="fixed bottom-4 right-4 z-[100] flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3 pb-[env(safe-area-inset-bottom)]" aria-live="polite" aria-atomic="false"></div>
@foreach (['success', 'error'] as $type)
    @if (session($type))
        <template data-flash-toast="{{ $type }}">{{ session($type) }}</template>
    @endif
@endforeach

<dialog data-confirm-dialog aria-labelledby="confirm-title" aria-describedby="confirm-description" class="m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border border-slate-200 bg-white p-6 text-slate-900 shadow-2xl backdrop:bg-slate-950/50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
    <h2 id="confirm-title" class="text-lg font-semibold">Confirmar eliminación</h2>
    <p id="confirm-description" data-confirm-description class="mt-3 text-sm text-slate-600 dark:text-slate-300"></p>
    <div class="mt-6 flex justify-end gap-3">
        <button type="button" data-confirm-cancel autofocus class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold dark:border-slate-600">Cancelar</button>
        <button type="button" data-confirm-accept class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Eliminar</button>
    </div>
</dialog>
