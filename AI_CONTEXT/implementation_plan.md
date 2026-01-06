# Plan Definitivo: Migración a Laravel 12

## 📊 Análisis del Excel de Producción

```
📁 Sistema de Inventario Escolar Backup Completo.xlsx (237 KB)
├── Items         → 5,288 filas, 11 columnas
├── Sedes         → 2 filas, 6 columnas  
├── Ubicaciones   → 137 filas, 8 columnas
├── Articulos     → 144 filas, 4 columnas
└── Responsables  → 67 filas, 7 columnas
```

---

## 📋 Mapeo de Campos (Excel → BD)

### Hoja: Items (Tabla principal)

| Excel | Requerido | Campo BD | Tipo Laravel |
|-------|-----------|----------|--------------|
| `Sede (Nombre)*` | ✅ | `sede_id` (lookup) | `foreignId` |
| `Ubicacion (Nombre)*` | ✅ | `ubicacion_id` (lookup) | `foreignId` |
| `Articulo (Nombre)*` | ✅ | `articulo_id` (lookup) | `foreignId` |
| `Responsable (Nombre Completo)` | ❌ | `responsable_id` (lookup) | `foreignId->nullable` |
| `Placa` | ❌ | `placa` | `string->nullable` |
| `Marca` | ❌ | `marca` | `string->nullable` |
| `Serial` | ❌ | `serial` | `string->nullable` |
| `Estado Físico*` | ✅ | `estado` | `enum` |
| `Disponibilidad*` | ✅ | `disponibilidad` | `enum` |
| `Descripción` | ❌ | `descripcion` | `text->nullable` |
| `Observaciones` | ❌ | `observaciones` | `text->nullable` |

---

### Hoja: Sedes (Catálogo)

| Excel | Requerido | Campo BD |
|-------|-----------|----------|
| `Nombre*` | ✅ | `nombre` (unique) |
| `Código*` | ✅ | `codigo` (unique) |
| `Coordinador` | ❌ | `coordinador_id` (FK Responsable) |
| `Dirección` | ❌ | `direccion` |
| `Teléfono` | ❌ | `telefono` |
| `Email` | ❌ | `email` |

---

### Hoja: Ubicaciones (Catálogo)

| Excel | Requerido | Campo BD |
|-------|-----------|----------|
| `Sede (Nombre)*` | ✅ | `sede_id` (FK) |
| `Nombre*` | ✅ | `nombre` |
| `Código*` | ✅ | `codigo` |
| `Tipo*` | ✅ | `tipo` (enum) |
| `Responsable Por Defecto` | ❌ | `responsable_id` |
| `Piso` | ❌ | `piso` |
| `Capacidad` | ❌ | `capacidad` |
| `Observaciones` | ❌ | `observaciones` |

---

### Hoja: Articulos (Catálogo)

| Excel | Requerido | Campo BD |
|-------|-----------|----------|
| `Nombre*` | ✅ | `nombre` (unique) |
| `Categoría*` | ✅ | `categoria` (enum) |
| `Código` | ❌ | `codigo` (auto-gen si vacío) |
| `Descripción` | ❌ | `descripcion` |

---

### Hoja: Responsables (Catálogo)

| Excel | Requerido | Campo BD |
|-------|-----------|----------|
| `Nombre Completo*` | ✅ | `nombre_completo` |
| `Tipo Documento` | ❌ | `tipo_documento` (enum) |
| `Documento` | ❌ | `documento` |
| `Cargo` | ❌ | `cargo` |
| `Email` | ❌ | `email` |
| `Teléfono` | ❌ | `telefono` |
| `Sede (Nombre)` | ❌ | `sede_id` (FK) |

---

## 🛠️ Stack Definitivo

```
┌─────────────────────────────────────────────────────────────┐
│                      Laravel 12                             │
│  ┌─────────────────────────┐  ┌──────────────────────────┐  │
│  │      /admin/*           │  │        /app/*            │  │
│  │    FilamentPHP 4        │  │   Inertia + Vue 3        │  │
│  │  (Admin: CRUDs, Excel)  │  │  (Composition API)       │  │
│  └─────────────────────────┘  └──────────────────────────┘  │
│                         ↓                                   │
│  ┌─────────────────────────────────────────────────────┐    │
│  │               PostgreSQL + Índices                  │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                             │
│  Librerías: maatwebsite/excel | sanctum | spatie/permission │
└─────────────────────────────────────────────────────────────┘
              Deploy: Railway (~$5/mes)
```

---

## 🔄 Lógica de Import Excel (Crítico)

```php
// Orden de procesamiento (respetando FKs)
1. Sedes        → Crea/actualiza por nombre*
2. Responsables → Crea/actualiza por nombre_completo*
3. Articulos    → Crea/actualiza por nombre*
4. Ubicaciones  → Crea/actualiza por (sede + nombre*)
5. Items        → Crea con lookups a todas las tablas
```

### Reglas de Import
- Si existe un registro con el mismo identificador único → **actualizar**
- Si no existe → **crear**
- Campos vacíos en Excel → **null en BD**
- Lookups por nombre (no por ID) para compatibilidad Excel

---

## 📁 Estructura del Proyecto

```
inventario-laravel/
├── app/
│   ├── Enums/
│   │   ├── EstadoFisico.php      # bueno, regular, malo
│   │   ├── Disponibilidad.php    # en_uso, extraviado, etc.
│   │   ├── TipoUbicacion.php     # aula, laboratorio, etc.
│   │   └── CategoriaArticulo.php # tecnologia, mobiliario, etc.
│   ├── Models/
│   │   ├── Sede.php
│   │   ├── Ubicacion.php
│   │   ├── Articulo.php
│   │   ├── Responsable.php
│   │   ├── Item.php
│   │   └── HistorialMovimiento.php
│   ├── Filament/
│   │   └── Resources/
│   │       ├── SedeResource.php
│   │       ├── UbicacionResource.php
│   │       ├── ArticuloResource.php
│   │       ├── ResponsableResource.php
│   │       └── ItemResource.php   # Con BulkActions
│   ├── Services/
│   │   ├── ExcelImportService.php    # Import simple (solo Items)
│   │   ├── ResetImportService.php    # Reset + Import multi-hoja
│   │   └── ExcelExportService.php    # Export con filtros
│   └── Http/
│       └── Controllers/
│           └── ExcelController.php
├── resources/
│   └── js/
│       ├── app.js
│       ├── Pages/                    # Vistas Inertia (si aplica)
│       └── Components/               # Vue 3 Composition API
└── database/
    └── migrations/
```

---

## 🚀 Cómo Trabajaremos

### Fase 1: Setup (1 hora)
```bash
composer create-project laravel/laravel inventario-laravel
cd inventario-laravel
composer require filament/filament:"^4.0" maatwebsite/excel
php artisan filament:install --panels
```

### Fase 2: Modelos + Migraciones (3 horas)
Crearé las 6 tablas con todos los índices necesarios.

### Fase 3: Filament Resources (3 horas)
CRUDs completos para los 5 catálogos + Items.

### Fase 4: Excel Import/Export (6 horas) ⭐ CRÍTICO
- `ResetImportService`: Elimina todo e importa las 5 hojas
- Validación de campos obligatorios (*)
- Lookup por nombres, no por IDs
- Export mantiene el mismo formato

### Fase 5: Vue + Inertia (4 horas)
- Si necesitas un frontend personalizado más allá de Filament
- Composition API (`<script setup>`)

### Fase 6: Deploy Railway (2 horas)
- PostgreSQL incluido
- CI/CD automático desde GitHub

**Total estimado: ~19 horas**

---

## ✅ Entregables Finales

1. **Panel Admin Filament** en `/admin`
   - CRUD de todos los catálogos
   - Gestión de Items con filtros
   - Botones Import/Export Excel

2. **Excel Import** compatible con tu archivo actual
   - Mismas columnas y headers
   - Campos obligatorios (*) validados
   - Lookups por nombre

3. **Excel Export** idéntico al formato original
   - Mismo orden de columnas
   - Mismo formato de datos

4. **Deploy funcional** en Railway

---

## ❓ Confirma antes de empezar

1. ¿El panel admin con **Filament** es suficiente, o necesitas también vistas con **Inertia+Vue** para usuarios finales?

2. ¿El Excel actual es el formato definitivo, o hay campos que quieras agregar/eliminar?

3. ¿Empezamos?
