<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Datos de la Institución Educativa
    |--------------------------------------------------------------------------
    |
    | Estos datos se usan en encabezados de reportes PDF y otros documentos.
    | Configura las variables de entorno o modifica directamente aquí.
    |
    */

    'nombre' => env('INSTITUCION_NOMBRE', 'IE SAN JOSÉ'),
    'nit' => env('INSTITUCION_NIT', ''),
    'direccion' => env('INSTITUCION_DIRECCION', ''),
    'telefono' => env('INSTITUCION_TELEFONO', ''),
    'email' => env('INSTITUCION_EMAIL', ''),
    'ciudad' => env('INSTITUCION_CIUDAD', ''),
    
    // Ruta relativa desde public/
    'logo' => env('INSTITUCION_LOGO', 'img/logo.png'),
];
