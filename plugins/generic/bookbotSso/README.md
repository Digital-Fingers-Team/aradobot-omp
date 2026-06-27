# bookbotSso

Token-based auto-login from the **bookbot** app into OMP. Lets a bookbot user
who activated an author account land straight in OMP (session opened, Author
role granted) without a second password prompt.

## How it works

1. bookbot signs a short-lived token: `base64url(JSON{uid,exp}) "." base64url(HMAC-SHA256(payload))`
   using the secret shared in `[bookbot] sso_secret`.
2. The browser opens `/<context>/bbsso/login?token=...`.
3. This plugin verifies the HMAC + expiry, opens an OMP session via
   `Validation::registerUserSession`, ensures the user is in the Author group,
   and redirects to the submissions dashboard. Invalid/expired tokens bounce to
   the normal login page.

## Setup on a fresh environment

1. Add to `config.inc.php` (gitignored), matching bookbot's `OMP_SSO_SECRET`:

   ```ini
   [bookbot]
   sso_secret = "<same value as OMP_SSO_SECRET in bookbot .env>"
   ```

2. Register + enable the plugin in the DB (the plugin files alone are not
   enough — these rows live in the DB volume, not git):

   ```bash
   docker exec omp-db mysql -uomp -p<pass> omp < install.sql
   ```

3. Rebuild so the code + config are baked into the image, then clear cache
   (opcache runs with `validate_timestamps=0`):

   ```bash
   docker compose build omp-app && docker compose up -d
   docker exec omp-app sh -c 'find /var/www/html/cache -type f -name "*.php" -delete'
   ```

## Verify

```bash
# valid token -> 302 to /submissions ; tampered/expired -> 302 to /login
curl -s -o /dev/null -w "%{http_code} %{redirect_url}\n" \
  "http://localhost:8091/index.php/arado/bbsso/login?token=<token>"
```
