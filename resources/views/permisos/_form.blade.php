@php($editing = isset($permiso))

<div class="grid gap-5 md:grid-cols-2">
    <x-ui.input name="codigo" label="Código" :value="$editing ? $permiso->codigo : null" placeholder="usuarios.ver" required />
    <x-ui.input name="nombre" label="Nombre" :value="$editing ? $permiso->nombre : null" required />
    <x-ui.input name="modulo" label="Módulo" :value="$editing ? $permiso->modulo : null" placeholder="USUARIOS" required />
</div>

<div class="mt-5 space-y-2">
    <label for="descripcion" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Descripción</label>
    <textarea id="descripcion" name="descripcion" rows="4" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-900/50">{{ old('descripcion', $editing ? $permiso->descripcion : '') }}</textarea>
</div>

<label class="mt-5 flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
    <input type="hidden" name="estado" value="0">
    <input type="checkbox" name="estado" value="1" @checked(old('estado', $editing ? $permiso->estado : true)) class="h-4 w-4 rounded border-slate-300 dark:border-slate-600 text-brand-600 dark:text-brand-400 focus:ring-brand-500">
    Permiso activo
</label>
