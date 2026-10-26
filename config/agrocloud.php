<?php

return [
    'bootstrap_superadmin' => [
        'email' => env('AGROCLOUD_SUPERADMIN_EMAIL', 'superadmin@agrocloud.com'),
        'password' => env('AGROCLOUD_SUPERADMIN_PASSWORD', 'Prueba123!'),
    ],


    // Valores iniciales importados al maestro de cargos. La administración
    // cotidiana del catálogo se realiza desde Maestros, en la base de datos.
    'cargos' => [
        'Administrador de empresa' => 'Administrador de empresa',
        'Responsable de recepción' => 'Responsable de recepción',
        'Inspector de control de calidad' => 'Inspector de control de calidad',
        'Supervisor de Pre-Empaque' => 'Supervisor de Pre-Empaque',
        'Responsable de producción' => 'Responsable de producción',
        'Jefe de calidad' => 'Jefe de calidad',
        'Operador de Pre-Empaque' => 'Operador de Pre-Empaque',
        'Asistente de calidad' => 'Asistente de calidad',
    ],
];
