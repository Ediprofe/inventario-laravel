# Walkthrough: Sistema de Inventario en Laravel 12

Hemos completado la migración del sistema de inventario a Laravel 12 + FilamentPHP 4. A continuación se detallan los pasos para probar, operar y desplegar la aplicación.

## 🚀 Estado del Proyecto

- **Stack:** Laravel 12, Filament 4 (Admin Panel), Inertia + Vue 3 (Frontend), PostgreSQL (Prod) / SQLite (Dev).
- **Funcionalidad:**
    - Panel Administrativo completo (`/admin`).
    - Gestión de `Items`, `Sedes`, `Responsables`, `Ubicaciones`, `Articulos`.
    - **Importación Masiva (Excel):** Lógica `ResetImportService` que procesa las 5 hojas en orden, gestiona dependencias y crea catálogos automáticamente.
    - **Exportación (Excel):** Genera un archivo con formato idéntico para backup o reportes.
    - **Validación:** Reglas de unicidad para "Placa" (excepto "NA") y creación dinámica de catálogos por nombre.

## 🛠️ Cómo Ejecutar Localmente

El proyecto se encuentra en: `Documents/Proyectos/Inventario-Laravel`

1.  **Iniciar Servidor:**
    ```bash
    cd ../Inventario-Laravel  # Si estás en la carpeta antigua
    # O abre la carpeta Inventario-Laravel en tu editor
    php artisan serve
    ```
2.  **Acceder al Panel:**
    - URL: `http://127.0.0.1:8000/admin`
    - Usuario: `admin@example.com` (o el que hayas configurado al instalar Filament).
    - Password: El que definiste en el setup.
    *Nota:* Si necesitas crear un usuario nuevo:
    ```bash
    php artisan make:filament-user
    ```

## 📦 Importación de Excel (Reset)

Esta es la funcionalidad crítica para migrar tus datos actuales.

1.  Ve a la sección **Items** en el menú lateral.
2.  Haz clic en el botón rojo **"Importar Excel (Reset)"** en la cabecera.
3.  Sube el archivo `Sistema de Inventario Escolar Backup Completo.xlsx`.
4.  El sistema:
    - Truncará la tabla de Items.
    - Procesará las hojas `Sedes` -> `Responsables` -> `Articulos` -> `Ubicaciones` -> `Items`.
    - Buscará relaciones por **Nombre**.
    - Reportará el éxito o errores.

## 📤 Exportación de Backup

1.  En la sección **Items**, haz clic en **"Exportar Backup"**.
2.  Se descargará un Excel multi-hoja con toda la información actual del sistema.

## 🚢 Despliegue en Railway

Se han creado los archivos de configuración necesarios:
- `nixpacks.toml`: Configura las extensiones de PHP (`intl`, `xsl`, `gd`, `pgsql`) y comandos de build (Composer, NPM).
- `railway.json`: Define el comando de inicio (`php artisan migrate --force && php artisan serve...`).

**Pasos:**
1.  Sube el código a GitHub.
2.  Conecta el repositorio en Railway.
3.  Agrega las **Variables de Entorno** en Railway:
    - `APP_KEY`: (Generar con `php artisan key:generate --show`)
    - `DB_CONNECTION`: `pgsql`
    - `DATABASE_URL`: (Railway lo provee si agregas un servicio PostgreSQL)
    - `APP_URL`: Tu dominio en Railway (https://....railway.app)
    - `FILAMENT_FILESYSTEM_DISK`: `public` (o S3 si prefieres almacenamiento persistente externo).

## 📝 Notas sobre Frontend (Inertia + Vue)

- Se instaló **Laravel Breeze** configurado con Vue 3 e Inertia.
- Las vistas públicas están en `resources/js/Pages`.
- Si `npm run build` falla localmente por dependencias de Mac (`rollup-darwin`), en Railway (Linux) debería funcionar correctamente gracias a `npm install`.

---
¡El sistema está listo para recibir la carga inicial de datos!
