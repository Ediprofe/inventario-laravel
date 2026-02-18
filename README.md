# Sistema de Inventario Escolar (Laravel)

Aplicacion de inventario escolar construida con Laravel + Filament.
Gestiona sedes, ubicaciones, responsables, articulos e items, con reportes PDF/Excel y flujos de firma para aprobacion de inventario.

## Stack
- PHP 8.5
- Laravel 12
- Filament 3 + Livewire 3
- Inertia + Vue 3
- SQLite (local)

## Inicio rapido local
1. Instalar dependencias PHP y JS:
```bash
composer install
npm install
```
2. Configurar entorno:
```bash
cp .env.example .env
php artisan key:generate
```
3. Base de datos local:
```bash
php artisan migrate
```
4. Link de storage publico:
```bash
php artisan storage:link
```
5. Ejecutar:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

## Panel admin
- URL local: `http://127.0.0.1:8000/admin`

## Rutas operativas clave
- Inventario por ubicacion: `/admin/reportes-inventario`
- Inventario por responsable: `/admin/reportes-inventario-responsables`
- Inventario consolidado: `/admin/reportes-inventario-consolidado`
- Firma responsable (publica): `/inventario/aprobar/{token}`
- Firma entrega (publica, signed): `/firma-entrega/capturar/{responsable}`

## Flujo de firma en tablet/celular
1. Levantar servidor con `--host=0.0.0.0`.
2. Configurar `APP_PUBLIC_URL` con IP o dominio accesible por el dispositivo firmante.
3. Generar enlace de firma desde la interfaz.
4. Abrir desde tablet/celular (misma red o canal alterno).

## Backups locales recomendados
Para restauracion completa en local, respaldar:
- `database/database.sqlite`
- `storage/app/public`
- `storage/app/private` (si aplica)
- `.env`

## Contexto para agentes IA
Fuente unica de verdad:
- `docs/project-context.md`

Registro incremental:
- `docs/changes.md`

Flujo estandar para agentes:
- `docs/agent-onboarding.md`

Archivos de instrucciones por agente (generados por Boost):
- `AGENTS.md`
- `CLAUDE.md`
- `GEMINI.md`

Regla: no duplicar contexto cambiante en esos 3 archivos; mantener contexto en `docs/project-context.md`.
