FROM dunglas/frankenphp:1.13.5-php8.3-bookworm

ENV SERVER_NAME=:8080 \
    XDG_CONFIG_HOME=/tmp/caddy \
    XDG_DATA_HOME=/tmp/caddy

# GD (with FreeType) watermarks the uploaded previews.
RUN install-php-extensions gd

# Unprivileged user, no bind capability needed for :8080. Bookings, pictures and sessions live in /data.
RUN useradd --system --uid 10001 --no-create-home app \
    && setcap -r /usr/local/bin/frankenphp \
    && rm -f /app/public/index.php \
    && mkdir -p /data/sessions \
    && chown -R app:app /data

COPY Caddyfile /etc/frankenphp/Caddyfile
COPY php.ini /usr/local/etc/php/conf.d/site.ini
COPY . /app/public
RUN rm /app/public/Caddyfile /app/public/php.ini

USER 10001
EXPOSE 8080
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s CMD ["curl", "-fsS", "-o", "/dev/null", "http://127.0.0.1:8080/healthz"]
