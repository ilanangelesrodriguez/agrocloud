@props(['name', 'class' => 'h-5 w-5'])

@switch($name)
    @case('leaf')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="M20 3C11 3 4 5 4 12a7 7 0 0 0 7 7c7 0 9-7 9-16Z"/><path d="M3 21 14 10"/></svg>
        @break
    @case('layers')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="m12 3 10 5-10 5L2 8l10-5ZM2 12l10 5 10-5M2 16l10 5 10-5"/></svg>
        @break
    @case('tag')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="M3 3h8l10 10-8 8L3 11V3Z"/><circle cx="7.5" cy="7.5" r="1"/></svg>
        @break
    @case('alert-triangle')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="m12 3 10 18H2L12 3Z"/><path d="M12 9v5M12 17v1"/></svg>
        @break
    @case('map-pin')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 0 1 14 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
        @break
    @case('ruler')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="m3 15 12-12 6 6L9 21l-6-6ZM7 11l3 3M11 7l3 3M15 3l3 3"/></svg>
        @break
    @case('clipboard-list')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><rect x="5" y="4" width="14" height="18" rx="2"/><path d="M9 4V2h6v2M9 10h6M9 14h6M9 18h4"/></svg>
        @break
    @case('camera')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="M4 7h4l2-3h4l2 3h4v13H4z"/><circle cx="12" cy="13" r="4"/></svg>
        @break
    @case('check')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="m5 12 5 5L20 7"/></svg>
        @break
    @case('dashboard')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        @break
    @case('building')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"/><path d="M16 9h2a2 2 0 0 1 2 2v10"/><path d="M8 7h4M8 11h4M8 15h4M9 21v-2h2v2M3 21h18"/></svg>
        @break
    @case('building-plus')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="M3 21h13M5 21V5a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v7"/><path d="M9 7h3M9 11h3M9 15h3"/><path d="M19 14v7M15.5 17.5h7"/></svg>
        @break
    @case('users')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.5 2.5-6 6-6s6 2.5 6 6"/><path d="M16 5.5a3 3 0 0 1 0 5.5M17 14c2.5.6 4 2.5 4 5"/></svg>
        @break
    @case('user')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.2-7 8-7s8 2.6 8 7"/></svg>
        @break
    @case('user-plus')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.5 2.5-6 6-6 1.7 0 3.1.5 4.2 1.4"/><path d="M18 13v7M14.5 16.5h7"/></svg>
        @break
    @case('shield')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="M12 3 5 6v5c0 4.8 2.8 8.2 7 10 4.2-1.8 7-5.2 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg>
        @break
    @case('key')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><circle cx="8" cy="15" r="4"/><path d="m11 12 8-8M15 8l2 2M17 6l2 2"/></svg>
        @break
    @case('lock')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
        @break
    @case('arrow-left')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="m15 18-6-6 6-6"/><path d="M9 12h11"/></svg>
        @break
    @case('chevron-down')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
        @break
    @case('chevron-right')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
        @break
    @case('plus')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        @break
    @case('search')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
        @break
    @case('filter')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="M4 5h16l-6.5 7.5V19l-3 1v-7.5z"/></svg>
        @break
    @case('pencil')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="m4 20 4-.8L19 8.2a2 2 0 0 0-3-3L5 16.2 4 20Z"/><path d="m14.5 6.5 3 3"/></svg>
        @break


        @case('trash')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true">
                <path d="M4 7h16"/>
                <path d="M10 11v6M14 11v6"/>
                <path d="M6 7l1 14h10l1-14"/>
                <path d="M9 7V4h6v3"/>
            </svg>
            @break


    @case('save')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="M5 3h12l2 2v16H5z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/></svg>
        @break
    @case('briefcase')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 12h18"/></svg>
        @break
    @case('ellipsis')
        <svg viewBox="0 0 24 24" fill="currentColor" {{ $attributes->class($class) }} aria-hidden="true"><circle cx="5" cy="12" r="1.7"/><circle cx="12" cy="12" r="1.7"/><circle cx="19" cy="12" r="1.7"/></svg>
        @break
    @case('x')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
        @break
    @case('eye-off')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 5.2A11 11 0 0 1 12 5c6 0 9.5 7 9.5 7a18 18 0 0 1-3 3.8M6.2 6.2A20 20 0 0 0 2.5 12s3.5 7 9.5 7a11 11 0 0 0 5.8-1.8"/></svg>
        @break
    @case('eye')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
        @break
    @case('power')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="M12 2v10"/><path d="M6.3 5.7a8 8 0 1 0 11.4 0"/></svg>
        @break
    @case('download')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
        @break
    @case('file-spreadsheet')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h5M9 11h6M9 15h6M12 11v8M9 19h6"/></svg>
        @break
    @case('file-text')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h5M9 11h6M9 15h6M9 19h4"/></svg>
        @break
    @case('printer')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="M7 8V3h10v5M7 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2"/><path d="M7 14h10v7H7z"/></svg>
        @break
    @case('logout')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M14 3h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5"/></svg>
        @break
    {{-- Iconos de configuración de apariencia. --}}
    @case('sun')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->class($class) }} aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
        @break
    @case('moon')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->class($class) }} aria-hidden="true"><path d="M20.7 13A8.5 8.5 0 0 1 11 3.3a8.6 8.6 0 1 0 9.7 9.7Z"/></svg>
        @break
    @case('monitor')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->class($class) }} aria-hidden="true"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 22h8M12 18v4"/></svg>
        @break
    @default
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true"><circle cx="12" cy="12" r="9"/></svg>
@endswitch
