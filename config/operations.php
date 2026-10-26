<?php

return [
    'manual_sampling_enabled' => filter_var(env('MANUAL_SAMPLING_ENABLED', true), FILTER_VALIDATE_BOOL),
    'display_timezone' => 'America/Lima',
    'sampling_max_fruits' => max(1, min(100000, (int) env('SAMPLING_MAX_FRUITS', 100000))),
    'sampling_warning_fruits' => max(1, (int) env('SAMPLING_WARNING_FRUITS', 500)),
];
