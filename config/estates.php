<?php

return [
    'exports' => [
        /*
         * Excel soporta hasta 1,048,576 filas por hoja.
         * Como una fila es el encabezado, el máximo real de bienes sería 1,048,575.
         * Usamos 200,000 por rendimiento y peso del archivo.
         */
        'rows_per_sheet' => env('ESTATES_EXPORT_ROWS_PER_SHEET', 200000),

        /*
         * Cantidad consultada por bloque. OpenSpout escribe cada bloque y
         * mantiene estable el consumo de memoria.
         */
        'chunk_size' => env('ESTATES_EXPORT_CHUNK_SIZE', 2000),

        'queue' => env('ESTATES_EXPORT_QUEUE', 'exports'),
        'disk' => env('ESTATES_EXPORT_DISK', 'local'),
        'directory' => env('ESTATES_EXPORT_DIRECTORY', 'exports/estates'),
        'expires_hours' => env('ESTATES_EXPORT_EXPIRES_HOURS', 24),
    ],
];
