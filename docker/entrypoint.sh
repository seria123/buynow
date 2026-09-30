#!/usr/bin/env bash
# Container entrypoint.
#
# Renders the nginx site config from docker/nginx.conf.template, then hands over
# to supervisord (php-fpm + nginx + queue:work).
#
# The port is resolved at runtime rather than baked into the image:
#   PORT          - the port the hosting platform assigns (Render/Railway/Fly/
#                  App Runner/Heroku all set it). Defaults to 10000 for local
#                  `docker compose` and ngrok use.
#   PHP_FPM_PORT  - internal FastCGI port, never exposed. Defaults to 9000.
set -euo pipefail

: "${PORT:=10000}"
: "${PHP_FPM_PORT:=9000}"

# The template refers to ${NGINX_PORT} rather than ${PORT} so the placeholder
# name cannot be confused with nginx's own runtime variables.
NGINX_PORT="$PORT"
export NGINX_PORT PHP_FPM_PORT

TEMPLATE="/etc/nginx/buynow/nginx.conf.template"
OUTPUT="/etc/nginx/sites-available/default"

# Substitute only the two variables above. The template also contains nginx
# runtime variables ($uri, $http_host, $document_root, ...) which MUST survive,
# so envsubst is given an explicit variable list instead of an allow-everything
# pattern.
envsubst '${NGINX_PORT} ${PHP_FPM_PORT}' < "$TEMPLATE" > "$OUTPUT"

# Debian's nginx ships sites-enabled/default -> sites-available/default. Make
# sure the symlink exists and nothing else is claiming the port.
mkdir -p /etc/nginx/sites-enabled
ln -sf "$OUTPUT" /etc/nginx/sites-enabled/default
rm -f /etc/nginx/conf.d/buynow-default.conf

# Fail fast with a readable message rather than an opaque 502 later.
nginx -t

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
