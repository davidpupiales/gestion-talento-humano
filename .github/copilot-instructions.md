## Resumen rápido

Proyecto híbrido: una aplicación PHP monolítica (páginas en el root: `index.php`, `login.php`, `dashboard.php`, etc.) y una carpeta `app/` con un frontend moderno en Next.js (TypeScript + React + Tailwind + Radix UI). El backend PHP usa MySQL vía `config/database.php`.

## Qué debe saber un agente para ser productivo

- Arquitectura: PHP (servidor/HTML tradicional) y Next.js (nuevo frontend). No son dos capas equivalentes: las páginas PHP (root) dependen de `includes/header.php`, `includes/footer.php`, `config/*` y `functions/*`. El directorio `app/` contiene un frontend independiente (Next 14).
- Base de datos: conexión en `config/database.php` (constantes DB_HOST, DB_NAME, DB_USER, DB_PASS). Es un singleton `Database` que usa mysqli y tiene helpers (`query`, `insert`, `getUserByUsername`, `getConnection`). Los scripts de esquema/ejemplo están en `bd/` y `scripts/database_schema.sql`.
- Sesiones y permisos: `config/session.php` define `SessionManager` (roles: 'empleado', 'gerente', 'administrador') y timeout. `functions/auth.php` contiene mocks y helpers (nota: hay inconsistencias de capitalización de roles entre archivos — ver sección de riesgos).
- JS/Frontend legado: `assets/js/*.js` son scripts usados por las páginas PHP. Cambios aquí afectan vistas server-rendered.
- UI/Next: `components/ui/*` contiene componentes reusables con Radix/sonner; `app/layout.tsx` y `app/page.tsx` son el punto de entrada del Next app.

## Comandos y flujos de desarrollo (específicos)

- Frontend Next (desarrollo):

```powershell
npm install
npm run dev
```

- Build/producción Next:

```powershell
npm run build
npm run start
```

- PHP local (Windows/XAMPP): colocar el repo en `htdocs`, iniciar Apache + MySQL desde XAMPP. Importar esquemas SQL si hace falta:

```powershell
# desde PowerShell (si mysql está en PATH)
mysql -u root -p rrhh_personal < .\bd\tablaEmpleados.sql
# o usar phpMyAdmin bundled con XAMPP
```

## Convenciones y patrones del proyecto

- Idioma/nombrado: la mayor parte del código PHP usa español (nombres, comentarios). Los componentes React están en inglés/TS. Mantener el idioma del área que modifiques.
- Archivos comunes para incluir en páginas PHP: `includes/header.php`, `includes/footer.php`. No eliminar estas inclusiones en cambios pequeños.
- Acceso a DB: usar `Database::getInstance()->query(...)` o `insert(...)`. Evitar consultas inline sin `prepare` donde ya existe un helper.
- CSRF y sesión: `functions/auth.php` implementa `generar_token_csrf()` y `verificar_token_csrf()`. Usar estos helpers en formularios PHP nuevos.

## Puntos críticos y riesgos detectados (útiles para PRs)

- Inconsistencia de roles: `config/session.php` usa roles en minúscula ('empleado','gerente','administrador') mientras que `functions/auth.php` usa 'Empleado','Gerente','Administrador' en mayúscula. Esto puede causar errores silenciosos en checks de permisos. Recomendación: unificar y escribir pruebas/manual QA.
- Secrets en repo: `config/database.php` contiene credenciales por defecto. No comites cambios con credenciales reales; usa variables de entorno o documentación para instructores.
- Next config: `next.config.mjs` desactiva fallos de lint/types en build (ignoreDuringBuilds y ignoreBuildErrors). No asumir que el código TS está 100% tipado.
- Migración de UI: el Next app y las páginas PHP no están automáticamente sincronizadas: si cambias endpoints o nombres de campos DB, actualizar ambas capas manualmente.

## Dónde buscar ejemplos concretos (referencias rápidas)

- Conexión DB y helpers: `config/database.php` (singleton, `query`, `insert`, `getUserByUsername`).
- Gestión de sesión y permisos: `config/session.php` (SessionManager::verificarSesion, ::tienePermiso).
- Autenticación y CSRF: `functions/auth.php` (mocks, `autenticar_usuario`, `generar_token_csrf`).
- SQL de esquema/ejemplo: `bd/tablaEmpleados.sql`, `bd/tablaUsuarios.sql`, `scripts/database_schema.sql`.
- Frontend moderno: `app/layout.tsx`, `app/page.tsx`, `components/ui/` para patrones de componentes.
- JS legado usado por PHP: `assets/js/app.js`, `assets/js/header_scripts.js`.

## Reglas para PRs automáticas (lo que un agente debe hacer antes de proponer cambios)

- Asegurar que cualquier cambio en la DB tenga SQL de migración/actualización en `bd/` o `scripts/` y documentación breve en el PR.
- Probar páginas PHP clave manualmente: `login.php`, `dashboard.php`, `empleados.php` después de cambios en sesiones/DB.
- Para cambios en UI/Next: levantar `npm run dev` y verificar `http://localhost:3000` (o puerto por defecto) y revisar la consola por errores JS/SSR.
- No sobrescribir cambios en `includes/header.php` / `includes/footer.php` sin validar que no rompen rutas relativas de assets.

## Plantillas útiles

- Plantilla de Pull Request: `.github/PULL_REQUEST_TEMPLATE.md` (usar para describir cambios, pasos de prueba y checklist antes de merge).
- Plantilla de mensaje de commit: `.github/commit_message_template.txt` (formato sugerido para los mensajes de commit).

## Hooks y validaciones locales

Hemos añadido un hook local de ejemplo y scripts para validar el formato del mensaje de commit:

- Hook de commit: `.githooks/commit-msg` (bash) — valida formato `type(scope): summary`.
- Scripts de validación: `scripts/validate-commit-msg.sh` (bash) y `scripts/validate-commit-msg.ps1` (PowerShell).

Para activar hooks localmente (recomendado):

1. Configurar el path de hooks en el repo (una vez por máquina):

```powershell
git config core.hooksPath .githooks
```

2. Alternativa (sin cambiar config): crear un enlace simbólico desde `.git/hooks` a `.githooks`.

3. Probar localmente (bash):

```bash
./scripts/validate-commit-msg.sh .git/COMMIT_EDITMSG
```

O en PowerShell:

```powershell
.\scripts\validate-commit-msg.ps1 .git\COMMIT_EDITMSG
```

Nota: estos hooks son auxiliares. Los checks reales del CI están en `.github/workflows/ci.yml` y ejecutan `npm run build` y una revisión rápida de sintaxis PHP.

## Lista de chequeo corta para el agente

1. ¿Cambio afecta PHP, Next o ambos? Identificar y probar en ambos entornos.
2. ¿Se modifica DB? Añadir SQL en `bd/` y actualizar `config/database.php` si es necesario (solo para dev).
3. Ejecutar `npm run dev` para cambios en `app/` y abrir la UI; probar formularios en las páginas PHP relevantes.
4. Revisar roles y permisos (buscar `tienePermiso`, `verificar_permisos`).

— Fin —

Si algo está incompleto o quieres que añada ejemplos de PR/commit, dime qué parte no quedó clara y la ajusto.
