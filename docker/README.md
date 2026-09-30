# Docker and deployment

## Layout

| File | Purpose |
| --- | --- |
| `Dockerfile` | Single image: nginx + php-fpm + node build + queue worker. Used both locally and on the cloud host. |
| `docker/nginx.conf.template` | nginx vhost. Contains `${PORT}` / `${PHP_FPM_PORT}` placeholders. |
| `docker/entrypoint.sh` | Renders the template from `$PORT`, validates with `nginx -t`, then execs supervisord. |
| `docker/php-fpm.conf` | FPM pool override. `clear_env = no` is what lets `APP_KEY`/`DB_*` reach PHP. |
| `docker/supervisord.conf` | Runs php-fpm, nginx and `artisan queue:work`. |
| `docker-compose.local.yml` | **Laptop only.** `network_mode: host` + the host's own MySQL. |
| `.env.production.example` | The variables the cloud host needs, with no real values. |

## Local (laptop + ngrok)

```bash
docker compose -f docker-compose.local.yml up -d --build
ngrok http 10000
```

This is the only place `network_mode: host` and `DB_HOST=127.0.0.1` are valid:
the database is the laptop's mysqld and it only listens on loopback.

## Cloud host

There is no production compose file on purpose. The platform builds this same
`Dockerfile` and supplies everything through environment variables:

- `PORT` — the platform injects it. `docker/entrypoint.sh` binds nginx to it, so
  the image is not tied to port 10000.
- Every key in `.env.production.example`, entered in the provider's dashboard.

`DB_HOST` must be the managed database's hostname, never `127.0.0.1` and never
the laptop's mysqld. Nothing in the repository references the laptop database.

## HTTPS and reverse proxies

ngrok and the cloud router both terminate TLS and forward plain HTTP, so the
socket the app sees is `http://internal-host:internal-port` while the browser
sees `https://public-hostname`.

`bootstrap/app.php` registers Laravel's `TrustProxies` middleware with the
`X-Forwarded-For / -Host / -Port / -Proto / -Prefix` header set. The scheme is
never hardcoded: it comes from `X-Forwarded-Proto` only when the peer is a
trusted proxy, so a direct `http://localhost:10000` request is still generated
as `http` and still works.

`TRUSTED_PROXIES` defaults to `*`, which trusts only the immediate peer (the
platform's router) rather than arbitrary `X-Forwarded-For` chains. Set an
explicit CIDR list to tighten it, then run `php artisan config:clear`.

`docker/nginx.conf.template` forwards the client's own `Host` header
(`$http_host`) rather than rebuilding it as `$host:$server_port`. The rebuilt
form was only correct for direct local access; behind a proxy it produced
`public-host:10000` and broke every generated absolute URL.

## First deploy, and every later deploy

```bash
php artisan config:clear
php artisan migrate --force     # additive migrations only, never migrate:fresh
```

`supervisord.conf` already runs `artisan queue:work`, so M-Pesa callbacks and
queued notifications are processed without any extra step.

`storage/` must be writable by `www-data`; the Dockerfile sets this at build
time. On a platform with ephemeral disks, point `FILESYSTEM_DISK` at S3 or the
platform's volume so uploaded product images survive a redeploy.
