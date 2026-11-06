# Aplicación sobre Nucleo

Aplicación PHP 8.1 construida sobre `brigantia/nucleo`: kernel con frontales (`app`, `mobile`, `api`, `conector`), módulos, `ModelBase` guiado por el esquema y Twig. Cambia esta línea por lo que hace la aplicación y para quién.

El plugin de Claude `nucleo` (activado en `.claude/settings.json`) trae la guía completa en sus skills (`nucleo`, `nucleo-modulo`, `nucleo-auditoria`) y dos hooks que revisan capas y SQL tras cada edición. Cada carpeta de `src/` tiene su `CLAUDE.md` con las reglas de esa capa.

## Comandos

```bash
composer install
cp .env.example .env                    # y rellenar DB_DSN, DB_USERNAME, DB_PASSWORD, JWT_KEY
vendor/bin/nucleo key:generate          # valor para JWT_KEY
vendor/bin/nucleo schema:sync [--apply] # SQL que falta para el esquema declarado (solo añade)
vendor/bin/nucleo schema:check          # esquema frente a base, en los dos sentidos
vendor/bin/nucleo migrate [--pretend]   # migraciones .sql pendientes
vendor/bin/nucleo routes [frontal]      # rutas con módulo, permiso y si son públicas
vendor/bin/nucleo make:module <nombre> --entity=<Entidad>
vendor/bin/phpunit
php -S localhost:8080 -t public public/index.php
```

## Estructura

| Ruta | Qué hay |
|---|---|
| `config/app.php` | Frontales, guards, módulos cargados |
| `src/dbschema.php` | Tablas propias de la aplicación |
| `src/Routes*.php`, `src/Menu.php` | Rutas por frontal y menú |
| `src/Screens`, `src/MobileScreens`, `src/Api` | Presentación |
| `src/Domain`, `src/Domain/Models` | Reglas de negocio y acceso a datos |
| `modules/` | Módulos propios |
| `migrations/` | Cambios de esquema no aditivos |

## Reglas

- **Capas**: `Screens`/`MobileScreens`/`Api` → `Domain` → `Domain\Models`. Una pantalla no usa modelos ni ejecuta SQL. Domain y Models no leen `$_POST`/`$_GET`/`$_SESSION` ni pintan.
- **Comprueba el código real antes de planificar**: nombre exacto de tabla (en plural) y columna en el esquema, que el método que vas a llamar existe, qué layout existe.
- **Domain recibe `$app` primero**; los modelos, `$db`. Transacciones en Domain.
- **SQL a mano solo en `Domain\Models`**, con parámetros. Identificadores desde el esquema o con `Dialect::assertidentifier()`.
- **Importes** en enteros de diezmilésimas; **fechas** de negocio en entero `AAAAMMDD`. Domain devuelve valores en bruto; **el formato lo pone Twig** (`monedaatexto`, `dateinttofrontend`).
- **Nombres** de métodos, variables, claves y variables de Twig en minúsculas sin separadores. Solo ASCII en identificadores.
- **Rutas**: las existentes no se renombran; las nuevas, en inglés.
- **Seguro por defecto**: rutas protegidas salvo `public`, permisos negados salvo concedidos, CSRF (`_csrf`) en todo lo que escribe. Token de API solo en la cabecera `Authorization`.
- **Sin tipos** en firmas que sobrescriben `ModelBase`, `ScreenBase` o `ApiBase`.
- **Comentarios** solo para el porqué; sin `TODO`, código comentado ni banners. Interfaz, comentarios y documentación en español.
- **Tests** con `ModuleTestCase` sobre SQLite en memoria; una prueba por cada protección.
- **Despliegue**: caché de Twig vaciada en cada despliegue; `vendor/` sin dependencias de desarrollo.

## Qué no hacer

- No ejecutar SQL contra la base de producción: preparar el `.sql`, explicarlo y que lo aplique una persona tras la copia de seguridad.
- No leer, copiar ni subir `.env`. Ningún secreto, host ni dato real en el código, en los tests, en este fichero ni en `.claude/settings.json`.
- No subir `vendor/`, `composer.lock` de librerías ni cachés.
