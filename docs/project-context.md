# Project Context - Inventario Escolar

## Purpose
Sistema de inventario escolar para gestionar sedes, ubicaciones, responsables, articulos e items, con exportes PDF/Excel y flujos de firma para aprobacion de inventario.

## Current Stack
- Laravel 12
- Filament 3 (panel admin)
- Livewire 3
- Inertia + Vue 3 (modulos base)
- PHP 8.5
- SQLite en local (actual), con opcion de migrar a PostgreSQL para produccion

## Core Functional Flows
1. Inventario por ubicacion
- Ruta: `/admin/reportes-inventario`
- Permite revisar resumen por articulo y detalle de items.
- Genera PDF/Excel y enlace de firma.

2. Inventario por responsable
- Ruta: `/admin/reportes-inventario-responsables`
- Flujo equivalente, centrado en responsable.

3. Inventario consolidado
- Ruta: `/admin/reportes-inventario-consolidado`
- Vista global con resumen transversal.

4. Firma del responsable (aprobacion inventario)
- Rutas publicas:
  - `GET /inventario/aprobar/{token}`
  - `POST /inventario/aprobar/{token}`
- El token se genera al preparar el envio.
- Al firmar, se dispara envio de correo con adjuntos.

5. Firma de entrega/verificacion
- Rutas publicas firmadas:
  - `GET /firma-entrega/capturar/{responsable}`
  - `POST /firma-entrega/capturar/{responsable}`
- Uso principal desde tablet/celular.

## Exports and Email
- PDF por ubicacion/responsable.
- Excel por ubicacion/responsable y reportes de auditoria.
- Servicio principal: `app/Services/InventarioFirmaEnvioService.php`
- Plantilla de correo: `resources/views/emails/inventario-report.blade.php`

## Data and Files
- DB local: `database/database.sqlite`
- Archivos publicos: `storage/app/public`
- Archivos privados temporales: `storage/app/private` (si aplica por flujo)
- Enlaces publicos por `storage:link` para disco `public`.

## Important Business Rules
- Placa:
  - Si tiene valor real, debe ser unica.
  - En muchos casos se maneja `NA` para items sin placa.
- Estados y disponibilidades se gestionan por enum.
- En reportes operativos se prioriza "en uso" para inspeccion rapida, pero exportes pueden incluir todos.

## Operational Notes
- Red local: para firma en tablet, abrir servidor con `php artisan serve --host=0.0.0.0 --port=8000` y usar IP LAN real.
- Si la red bloquea trafico entre clientes, usar alternativa temporal (hotspot/tunel).
- `APP_PUBLIC_URL` debe apuntar al host accesible por el dispositivo firmante para generar enlaces correctos.

## Source of Truth for Agents
- Este archivo es la fuente de verdad del contexto del proyecto.
- `AGENTS.md`, `CLAUDE.md` y `GEMINI.md` deben referenciar este archivo, no duplicar contexto cambiante.
- Flujo de lectura para agentes: `docs/agent-onboarding.md`.

## Precedence Rules
Si dos documentos se contradicen, aplicar este orden:
1. `docs/project-context.md`
2. `docs/changes.md`
3. `README.md`
4. cualquier nota legacy

## Update Policy
- Cambio menor (UI puntual, texto, ajuste local):
  - registrar en `docs/changes.md`.
- Cambio estructural (flujo, modelo, seguridad, arquitectura):
  - actualizar este archivo y registrar en `docs/changes.md`.
