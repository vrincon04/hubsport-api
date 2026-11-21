# HubSport — API (Laravel)

Backend REST de HubSport. Auth con **Laravel Sanctum**; la app Flutter vive en [sport](https://github.com/serforin0/sport).

## Requisitos

- PHP `^8.2` con extensiones: `mbstring`, `pdo_mysql` (o sqlite), `gd` (PDF demo), `tokenizer`, `xml`, `ctype`, `json`, `bcmath`
- Composer 2
- MySQL 8 (recomendado) o SQLite para pruebas rápidas

## Arranque rápido

```bash
composer install
cp .env.example .env   # si no existe, pide a un teammate un .env de desarrollo
php artisan key:generate
php artisan migrate
php artisan serve
```

API base local: `http://127.0.0.1:8000/api/v1`.

Asegúrate de que `APP_URL` en `.env` coincida con la URL que usa la app (afecta links absolutos, p. ej. PDF demo).

### Demo (opcional)

En `.env`:

```env
DEMO_LOGIN_ENABLED=true
DEMO_USER_EMAIL=demo@hubsport.test
DEMO_TOKEN_EXPIRATION_MINUTES=120
```

Sembrar / resetear el dataset demo:

```bash
php artisan demo:reset
```

## Tests

```bash
php artisan test
./vendor/bin/pint --test
```

Los tests en `tests/Feature/Flows/` cubren auth, feed, stories, conexiones, chat, search, jobs, references, events, sponsorships, billing/meta, perfil/settings/notificaciones, safety, scout metrics y certificación de fases 6–7.

E2E HTTP demo: `tests/Feature/E2E/HubSportDemoJourneyE2ETest.php`.

CI: `.github/workflows/ci.yml` (MySQL 8 + PHP 8.3 + extensión `gd`).

## Comandos útiles

```bash
php artisan migrate
php artisan demo:reset
php artisan optimize:clear
./vendor/bin/pint
```

## Estructura útil

| Ruta | Contenido |
| ---- | --------- |
| `routes/api/v1/` | Rutas de la API |
| `app/Http/Controllers/V1/` | Controllers versionados |
| `app/Demo/` | Acceso, fixtures y facturas PDF del modo demo |
| `app/Support/` | AgePolicy, RolePermissionPolicy, ScamDetector |
| `tests/Feature/` | Feature + Flows + E2E |

No commits de `.env`, `vendor/` ni PDFs/APKs de entrega al cliente. El **README del proyecto sí pertenece al repo**. Documentación de producto vive fuera de este repositorio.
