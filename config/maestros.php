<?php

return [
    'cargo' => ['label' => 'Cargos laborales', 'scope' => 'shared', 'description' => 'Cargos de usuarios. Cada empresa puede agregar sus propios cargos.', 'fields' => ['usuario' => 'cargo']],
    'tipo_empresa' => ['label' => 'Tipos de empresa', 'scope' => 'global', 'description' => 'Tipos de organización para el registro de empresas.', 'fields' => ['empresa' => 'tipo_empresa']],
    'rubro' => ['label' => 'Rubros empresariales', 'scope' => 'global', 'description' => 'Actividades principales de las empresas.', 'fields' => ['empresa' => 'rubro']],
    'producto' => ['label' => 'Productos agrícolas', 'scope' => 'global', 'description' => 'Productos disponibles al registrar lotes.', 'fields' => ['lote' => 'producto']],
    'variedad' => ['label' => 'Variedades agrícolas', 'scope' => 'global', 'parent' => 'producto', 'description' => 'Cada variedad pertenece a un producto agrícola.', 'fields' => ['lote' => 'variedad']],
    'categoria' => ['label' => 'Categorías comerciales', 'scope' => 'company', 'description' => 'Clasificaciones definidas por el protocolo de cada empresa.', 'fields' => ['dictamen_calidad' => 'categoria']],
    'metodo' => ['label' => 'Métodos de muestreo', 'scope' => 'global', 'description' => 'Métodos disponibles para tomar muestras.', 'fields' => ['muestreo' => 'metodo']],
    'defecto' => ['label' => 'Defectos y enfermedades', 'scope' => 'shared', 'description' => 'Hallazgos manuales. Cada empresa puede configurar los tipos que utiliza.', 'fields' => ['hallazgo_manual' => 'clase']],
    'motivo' => ['label' => 'Motivos de rechazo u observación', 'scope' => 'company', 'description' => 'Motivos definidos por empresa para sustentar sus dictámenes.', 'fields' => ['dictamen_calidad' => 'motivo']],
    'ubigeo' => ['label' => 'Ubicaciones geográficas', 'scope' => 'readonly', 'description' => 'Catálogo nacional UBIGEO de solo lectura.'],
    'grupo' => ['label' => 'Grupos empresariales', 'scope' => 'global', 'description' => 'Agrupaciones de empresas.', 'fields' => ['empresa' => 'grupo_empresarial']],
    'unidad' => ['label' => 'Unidades de medida', 'scope' => 'global', 'description' => 'Unidades para lotes y operaciones. El peso se conserva en kilogramos.', 'fields' => ['lote' => 'unidad_medida']],
];
