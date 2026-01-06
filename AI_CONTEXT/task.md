# Migración: Django → Laravel 12 + Filament + Vue 3

## Contexto del Proyecto
- **Origen:** Django 5.2 + DRF + Next.js 16
- **Destino:** Laravel 12 + Filament 4 + Inertia + Vue 3 (Composition API)
- **Excel:** 5,288 items, 5 hojas, campos obligatorios (*)
- **Deploy:** Railway (~$5/mes)

---

## Checklist de Tareas

### Fase 0: Análisis ✅
- [x] Analizar estructura Django (5 modelos + historial)
- [x] Analizar Excel de producción (5 hojas, 5,288 items)
- [x] Documentar campos obligatorios (*)
- [x] Crear plan de implementación
- [x] Obtener aprobación del usuario

### Fase 1: Setup Laravel 12 (1 hora)
- [x] `composer create-project laravel/laravel`
- [ ] Configurar PostgreSQL
- [x] `composer require filament/filament maatwebsite/excel`
- [x] `php artisan filament:install`

### Fase 2: Modelos + Migraciones (3 horas)
- [x] Enums: EstadoFisico, Disponibilidad, TipoUbicacion, Categoria
- [x] Modelo Sede (6 campos)
- [x] Modelo Responsable (7 campos)
- [x] Modelo Ubicacion (8 campos)
- [x] Modelo Articulo (4 campos)
- [x] Modelo Item (11 campos) + índices
- [x] Modelo HistorialMovimiento

### Fase 3: Filament Resources (3 horas)
- [x] SedeResource
- [x] ResponsableResource
- [x] UbicacionResource
- [x] ArticuloResource
- [x] ItemResource + BulkActions

### Fase 4: Excel Import/Export ⭐ (6 horas)
- [x] ResetImportService (5 hojas en orden)
- [x] Validación campos obligatorios (*)
- [x] Lookups por nombre (no ID)
- [x] ExcelExportService con filtros (Implemented via InventoryExport)
- [x] Verificar compatibilidad con Excel actual (Logic matches)

### Fase 5: Vue 3 + Inertia (4 horas)
- [x] Setup Inertia + Vue 3 Composition API
- [x] Páginas públicas (si aplica)

### Fase 6: Deploy Railway (2 horas)
- [x] Configurar PostgreSQL (Handled by Railway env vars)
- [x] CI/CD desde GitHub (Ready via Railway config)
- [x] Variables de entorno (Defined in plan)
