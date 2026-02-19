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
    'membrete' => env('INSTITUCION_MEMBRETE', ''),
    'escudo' => env('INSTITUCION_ESCUDO', 'img/escudo.png'),

    // Encabezado institucional para PDF (cuando se maqueta con escudo + texto).
    'nombre_largo' => env('INSTITUCION_NOMBRE_LARGO', 'INSTITUCIÓN EDUCATIVA SAN JOSÉ'),
    'resolucion_texto' => env('INSTITUCION_RESOLUCION_TEXTO', 'Aprobado por resolución municipal 461 de 25 de febrero de 2009'),
    'identificacion_texto' => env('INSTITUCION_IDENTIFICACION_TEXTO', 'NIT. 811039369-3 DANE. 105360000083'),
    'lema' => env('INSTITUCION_LEMA', 'WE LIVE EDUCATIONAL EXCELLENCE'),

    // Firma fija de quien entrega/verifica (opcional).
    'firma_entrega_nombre' => env('INVENTARIO_FIRMA_ENTREGA_NOMBRE', 'Encargado de inventario'),
    'firma_entrega_cargo' => env('INVENTARIO_FIRMA_ENTREGA_CARGO', ''),
    'firma_entrega_imagen' => env('INVENTARIO_FIRMA_ENTREGA_IMAGEN', ''),
];
