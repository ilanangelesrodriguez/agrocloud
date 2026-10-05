@props(['name', 'class' => 'h-5 w-5'])

@switch($name)
    @case('dashboard')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true">
            <rect x="3" y="3" width="7" height="7" rx="1.5"/>
            <rect x="14" y="3" width="7" height="7" rx="1.5"/>
            <rect x="3" y="14" width="7" height="7" rx="1.5"/>
            <rect x="14" y="14" width="7" height="7" rx="1.5"/>
        </svg>
        @break
    @case('building')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true">
            <path d="M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"/>
            <path d="M16 9h2a2 2 0 0 1 2 2v10"/>
            <path d="M8 7h4M8 11h4M8 15h4M9 21v-2h2v2M3 21h18"/>
        </svg>
        @break
    @case('users')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true">
            <circle cx="9" cy="8" r="3"/>
            <path d="M3 20c0-3.5 2.5-6 6-6s6 2.5 6 6"/>
            <path d="M16 5.5a3 3 0 0 1 0 5.5M17 14c2.5.6 4 2.5 4 5"/>
        </svg>
        @break
    @case('shield')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true">
            <path d="M12 3 5 6v5c0 4.8 2.8 8.2 7 10 4.2-1.8 7-5.2 7-10V6l-7-3Z"/>
            <path d="m9 12 2 2 4-4"/>
        </svg>
        @break
    @case('key')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true">
            <circle cx="8" cy="15" r="4"/>
            <path d="m11 12 8-8M15 8l2 2M17 6l2 2"/>
        </svg>
        @break
    @case('logout')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true">
            <path d="M10 17l5-5-5-5"/>
            <path d="M15 12H3"/>
            <path d="M14 3h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5"/>
        </svg>
        @break
    @default
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" {{ $attributes->class($class) }} aria-hidden="true">
            <circle cx="12" cy="12" r="9"/>
        </svg>
@endswitch
