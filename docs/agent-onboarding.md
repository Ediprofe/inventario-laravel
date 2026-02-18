# Agent Onboarding Flow

Objetivo: que cualquier agente se ubique rapido, sin contradicciones de contexto.

## 1) Leer en este orden
1. `docs/project-context.md`
2. `docs/changes.md`
3. `README.md`
4. `AGENTS.md` (o `CLAUDE.md` / `GEMINI.md` segun agente)

## 2) Regla de precedencia
Si hay conflicto entre documentos:
1. `docs/project-context.md` (gana)
2. `docs/changes.md`
3. `README.md`
4. archivos legacy o notas antiguas

## 3) Comprobaciones minimas antes de tocar codigo
```bash
php artisan --version
php artisan route:list | head -n 20
git status --short
```

## 4) Convenciones operativas del proyecto
- Mantener cambios pequenos y verificables.
- Preferir reutilizar estructuras existentes (Filament Resources, Pages, Services).
- No introducir dependencias nuevas sin aprobacion.
- Ejecutar pruebas/lint relevantes cuando aplique.

## 5) Documentacion de cambios
- Cambio menor: agregar entrada en `docs/changes.md`.
- Cambio estructural (modelo, flujo, arquitectura):
  - actualizar `docs/project-context.md`
  - agregar entrada en `docs/changes.md`

## 6) Lo que NO se debe hacer
- No guardar contexto de negocio nuevo dentro de `AGENTS.md`, `CLAUDE.md` o `GEMINI.md`.
- No reactivar carpetas legacy de contexto duplicado.
