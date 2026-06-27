# Running / hosting OMP (aradobot-omp)

Open Monograph Press (PHP 8.2 + MariaDB), packaged to run via Docker straight
from this repository. The application code is baked into the image (not
bind-mounted), so it runs fast on any host.

- **App URL (local):** http://localhost:8091
- **Database:** MariaDB (service `omp-db`) — db `omp`, user `omp`, pass `omp_pass`

## Run locally

Requires Docker. From the repo root:

```
docker compose up -d --build
```

Then open http://localhost:8091 and complete the installation form if this is a
fresh database:

- Database driver: **MySQLi**
- Host: `omp-db`  ·  Name: `omp`  ·  User: `omp`  ·  Password: `omp_pass`
- Files directory: `/var/www/files`

> `config.inc.php` is **not** in git (it holds the DB password + app_key).
> A fresh deploy generates it during installation, or you can mount/inject your
> own. Generate the required app key inside the container with:
> `php lib/pkp/tools/appKey.php generate`

## Common commands

| Action | Command (from repo root) |
|---|---|
| Start / rebuild | `docker compose up -d --build` |
| Start (no code change) | `docker compose up -d` |
| Stop | `docker compose stop` |
| Stop + remove containers (data kept) | `docker compose down` |
| Logs | `docker compose logs -f omp-app` |

Uploaded files persist in the `omp-files` volume; the database in `omp-db-data`.

## Hosting on a platform (e.g. Railway)

Deploy this repo directly — the `Dockerfile` at the root builds the app image.
Provide a managed MySQL/MariaDB and set the DB connection in `config.inc.php`
(or via the installer on first run). Note: PHP hosts only — not Vercel/Netlify.

## REST API (integration with the bookbot web app)

OMP exposes a REST API:

```
http://<host>/index.php/<press_path>/api/v1/...
Authorization: Bearer <API_KEY>
```

Generate an API key from the admin profile (Profile → API Key).

> **Important — `api_key_secret` must live in `config.inc.php`.** API tokens are
> JWTs signed with `[security] api_key_secret`. Because the code (and config) is
> baked into the image, this secret **must** be set in the repo's
> `config.inc.php` — if it's only set on a running container it is **wiped on the
> next `--build`**, invalidating every issued token (symptom: API returns
> *"the site administrator has not configured a secret key"*). The bookbot app's
> `OMP_API_TOKEN` must be signed with this same secret.

## bookbot author SSO (bookbotSso plugin)

The bookbot app onboards authors into OMP. See
[`plugins/generic/bookbotSso/README.md`](plugins/generic/bookbotSso/README.md)
for the token-login handler, the shared `[bookbot] sso_secret`, and the
DB rows (`install.sql`) needed to enable it.
