# Agent prompt — OMP ↔ bookbot integration (ongoing)

You are continuing the integration between **bookbot** (Next.js web + Node/TS API,
MongoDB) and **OMP / Open Monograph Press** (PHP 8.2 + MariaDB, runs in Docker).
Work autonomously through the backlog below, one task per iteration, committing each
finished slice. Re-read this file at the start of every run.

## Ground truth (verify, don't assume — things drift)

- **OMP** runs via Docker from `f:\baraa\bookbot\omp` (its own git repo →
  github.com/Digital-Fingers-Team/aradobot-omp). Code is COPIED into the image
  (not bind-mounted). Manage from that dir: `docker compose up -d` / `--build`.
- **OMP URL:** http://localhost:8091  
  Admin: `admin` / `OmpAdmin123!`.
- **Press already created:** path `arado` (id 1), site context path is `index`.
- **bookbot API:** `apps/api`, Express, starts with `pnpm dev` (port 4000). Needs
  MongoDB (`bookbotd-mongo` container, 27017).
- **Integration code already shipped on `main`:**
  - `apps/api/src/services/omp/omp.client.ts` — typed OMP REST client.
  - `apps/api/src/routes/omp.routes.ts` — `GET /api/omp/health`, `GET /api/omp/catalog`.
  - env: `OMP_BASE_URL`, `OMP_CONTEXT_PATH` (=`arado`), `OMP_API_TOKEN`.
- **Proven working:** `GET /api/omp/catalog` → `{"configured":true,"itemsCount":0,"items":[]}`
  (empty only because no monograph is published yet).

## Hard constraints

- **Do NOT** convert bookbot to Postgres or merge the databases. They are two
  systems integrated via OMP's REST API. OMP = MariaDB, bookbot = MongoDB.
- **Secrets** (`OMP_API_TOKEN`, `config.inc.php`) live in `.env` / gitignored
  files only — never commit them. `omp/config.inc.php` is gitignored.
- Keep commits small and scoped; push to `main`. End commit messages with the
  `Co-Authored-By: Claude Opus 4.8` trailer.

## Environment gotchas (these have bitten us — handle proactively)

- **Git Bash mangles `/...` paths** passed to native exes (curl, docker exec).
  Prefix such commands with `MSYS_NO_PATHCONV=1` (e.g. container file paths,
  `filesDir`). URLs starting with `http://` are safe.
- **OMP reads the API token from `?apiToken=<jwt>`**, NOT an Authorization header.
- **Docker Desktop on this machine wedges** when many containers start/stop
  (containers stuck "Created" / unkillable). Recovery: stop Docker Desktop +
  backend procs, `wsl --shutdown`, relaunch `"C:\Program Files\Docker\Docker\Docker Desktop.exe"`,
  then poll until `docker run --rm alpine echo ok` actually prints.
- OMP token recipe (already done, repeat only if rotating): set
  `security.api_key_secret` in config; in `user_settings` set `apiKeyEnabled=1`
  and `apiKey=<sha1>` for user 1; token = `JWT::encode([apiKey], secret, 'HS256')`.

## Backlog (do in order; stop and report if blocked)

1. **Publish a test monograph in OMP** so the catalog returns a real item.
   Create a submission under the `arado` press and publish it (status=3). Verify
   `GET /api/omp/catalog` then returns `itemsCount >= 1` with a mapped title.
   Prefer the REST API; fall back to the admin UI steps if the API path is fragile.
2. **Surface the catalog in the web app** (`apps/web`): a page/section that calls
   `GET /api/omp/catalog` and lists published OMP books (title, authors, link).
3. **Harden the client:** retries/timeout already exist; add a small in-memory
   cache (e.g. 60s) for `/catalog`, and a typed error for unreachable OMP.
4. **(Option 2) Sync OMP books into bookbot MongoDB** so the AI assistant can work
   on them: a sync service mapping OMP publications → bookbot `Book` model, plus
   downloading the published galley (PDF/EPUB) into the existing ingestion flow.
   Design it idempotent (upsert by OMP submission id). Confirm scope before the
   file-download/ingestion piece — it's the largest part.
5. **Docs:** keep `apps/api` README / `.env.example` in sync with any new vars.

## Definition of done per task

Typecheck passes (`cd apps/api && pnpm exec tsc -p tsconfig.json --noEmit`),
the endpoint/flow is exercised against the live OMP on :8091, and the change is
committed and pushed to `main`. Report what was verified, with the actual output.
