## Resumen breve

Describe en una o dos líneas qué cambia este PR y por qué.

---

## Tipo de cambio

- [ ] Bugfix
- [ ] Nueva funcionalidad
- [ ] Cambios en la base de datos (migraciones / scripts)
- [ ] Refactor / limpieza
- [ ] Documentación

---

## Qué probar (pasos manuales / checks)

1. Para cambios en `app/` (Next.js):

   - Instalar dependencias: `npm install`
   - Levantar frontend: `npm run dev`
   - Abrir `http://localhost:3000` y verificar la pantalla afectada. Revisar consola del navegador y terminal por errores SSR/JS.

2. Para cambios en PHP (páginas en la raíz):

   - Sitúa el repositorio en `htdocs` de XAMPP y asegúrate de que Apache+MySQL estén en marcha.
   - Probar páginas clave: `login.php`, `dashboard.php`, `empleados.php`.
   - Si el cambio afecta DB, ejecutar/importar el script en `bd/` o `scripts/` y documentar el SQL en este PR.

3. Verificar sesiones y permisos:

   - Revisar `config/session.php` y `functions/auth.php` para comprobar roles y permisos. Nota: hay inconsistencias de capitalización de roles (ver comentario del PR si lo corriges).

4. Otros checks rápidos:

   - No sobrescribir `includes/header.php` o `includes/footer.php` sin validar rutas de assets.
   - No dejar credenciales reales en `config/database.php`.

---

## Migraciones y datos

- [ ] Incluí script SQL en `bd/` o `scripts/` (obligatorio si se cambia esquema)
- [ ] Documenté el orden de ejecución y riesgos (pérdida de datos, backup requerido)

---

## Archivos más relevantes para revisar

- PHP: `config/database.php`, `config/session.php`, `functions/*.php`, `includes/*`, `*.php` en raíz
- Next: `app/layout.tsx`, `app/page.tsx`, `components/ui/*`, `package.json`, `next.config.mjs`
- SQL: `bd/tablaEmpleados.sql`, `bd/tablaUsuarios.sql`, `scripts/database_schema.sql`

---

## Notas para el revisor

- Si el PR cambia permisos/roles, listar exactamente qué valores (ej. 'empleado' vs 'Empleado') se estandarizaron.
- Para cambios que toquen ambos stacks (PHP y Next), indicar el flujo de datos afectado y pruebas manuales realizadas en ambos entornos.

---

## Checklist final (antes de merge)

- [ ] Build de Next (`npm run build`) pasa localmente o se documenta por qué no es necesario.
- [ ] Scripts SQL incluidos y probados en un entorno de staging (o documentado).
- [ ] Revisados cambios en `includes/header.php`/`footer.php` si fueron modificados.
- [ ] No credenciales sensibles en el PR.
