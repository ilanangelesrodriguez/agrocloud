<dialog data-user-status-dialog aria-labelledby="user-status-title" aria-describedby="user-status-description" class="m-auto max-h-[calc(100dvh-2rem)] w-[calc(100%-2rem)] max-w-lg overflow-y-auto rounded-2xl border border-slate-200 bg-white p-0 text-left text-slate-900 shadow-2xl backdrop:bg-slate-950/50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
    <form method="POST" data-ajax-form data-user-status-form class="space-y-5 p-5 sm:p-6">
        @csrf @method('PATCH')
        <input type="hidden" name="estado_actual" value="1">
        <div class="flex items-start justify-between gap-4"><h2 id="user-status-title" class="text-lg font-semibold">Desactivar cuenta</h2><button type="button" data-user-status-close aria-label="Cerrar diálogo" class="rounded-lg p-2 hover:bg-slate-100 dark:hover:bg-slate-800"><x-ui.icon name="x" class="h-5 w-5" /></button></div>
        <p id="user-status-description" class="text-sm text-slate-600 dark:text-slate-300">Se cerrará el acceso de <strong data-user-status-name></strong>. Sus registros se conservarán y podrás reactivar la cuenta después.</p>
        <label class="block text-sm font-medium">Motivo de desactivación *<textarea name="motivo_seguridad" required maxlength="500" rows="3" autofocus class="mt-2 w-full resize-y rounded-xl border border-slate-300 bg-white p-3 dark:border-slate-600 dark:bg-slate-900"></textarea></label>
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"><button type="button" data-user-status-close class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold dark:border-slate-600">Cancelar</button><button type="submit" class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Desactivar cuenta</button></div>
    </form>
</dialog>
