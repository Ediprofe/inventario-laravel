# 📋 CONTEXTO DEL PROYECTO - Sistema de Inventario Escolar

> **Documento de referencia para cualquier agente de IA.**  
> Última actualización: 2026-01-05

---

## 🎯 Objetivo del Proyecto

Migrar un sistema de inventario escolar de **Django 5.2 + Next.js 16** a **Laravel 12 + FilamentPHP 4 + Vue 3**.

---

## 📁 Ubicaciones Clave

| Elemento | Ruta |
|----------|------|
| **Proyecto origen (Django)** | `/Users/edilbertosuarez/Documents/Proyectos/Inventario/` |
| **Proyecto destino (Laravel)**Ubicación:** `/Users/edilbertosuarez/Documents/Proyectos/Inventario-Laravel` (Migrado fuera del repo original) |
| **Excel de producción** | `Sistema de Inventario Escolar Backup Completo.xlsx` (raíz) |
| **Documentación Django** | `docs/modelo/entidades.md` |

---

## 🛠️ Stack Definitivo

```
Laravel 12 + FilamentPHP 4 + Vue 3 (Composition API)
PostgreSQL + Maatwebsite/Excel + Sanctum
Deploy: Railway (~$5/mes)
```

---

## 📊 Estructura del Excel (Fuente de Verdad)

### Items (5,288 filas)
| Columna | Requerido | Mapeo BD |
|---------|-----------|----------|
| Sede (Nombre)* | ✅ | `sede_id` FK |
| Ubicacion (Nombre)* | ✅ | `ubicacion_id` FK |
| Articulo (Nombre)* | ✅ | `articulo_id` FK |
| Responsable (Nombre Completo) | ❌ | `responsable_id` FK |
| Placa | ❌ | `placa` |
| Marca | ❌ | `marca` |
| Serial | ❌ | `serial` |
| Estado Físico* | ✅ | `estado` enum |
| Disponibilidad* | ✅ | `disponibilidad` enum |
| Descripción | ❌ | `descripcion` |
| Observaciones | ❌ | `observaciones` |

### Sedes (2 filas)
`Nombre*`, `Código*`, Coordinador, Dirección, Teléfono, Email

### Ubicaciones (137 filas)
`Sede (Nombre)*`, `Nombre*`, `Código*`, `Tipo*`, Responsable Por Defecto, Piso, Capacidad, Observaciones

### Articulos (144 filas)
`Nombre*`, `Categoría*`, Código, Descripción

### Responsables (67 filas)
`Nombre Completo*`, Tipo Documento, Documento, Cargo, Email, Teléfono, Sede (Nombre)

---

## 🔑 Reglas de Negocio Críticas

1. **Import Excel:** Lookup por **nombre** (no por ID)
2. **Orden de importación:** Sedes → Responsables → Articulos → Ubicaciones → Items
3. **Campos * son obligatorios**
4. **Export:** Mismo formato que el import
5. **Placa:** Si vacía → `"NA"`. Si tiene valor → **debe ser única**

---

## 📦 Enums

```php
EstadoFisico: bueno, regular, malo
Disponibilidad: en_uso, en_reparacion, extraviado, de_baja
TipoUbicacion: aula, laboratorio, oficina, biblioteca, deposito, auditorio, salon_multiple, otro
CategoriaArticulo: tecnologia, mobiliario, laboratorio, deportes, audiovisual, libros, herramientas, vehiculos, otros
```

---

## ✅ Estado Actual

- [x] Análisis proyecto Django completado
- [x] Análisis Excel completado
- [x] Plan de implementación aprobado
- [ ] **SIGUIENTE:** Crear proyecto Laravel en `laravel/`

---

## 📚 Documentos Detallados

- `implementation_plan.md` - Plan técnico completo
- `task.md` - Checklist de tareas
- `PETICION.md` (raíz) - Solicitud original del usuario
