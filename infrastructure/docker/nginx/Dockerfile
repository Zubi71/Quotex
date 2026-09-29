# ──────────────────────────────────────────────────────────────────────────────
# OTC Signal Intelligence — Nginx Dockerfile
# Simple alpine image that copies our custom configs.
# ──────────────────────────────────────────────────────────────────────────────
FROM nginx:1.25-alpine

LABEL maintainer="OTC Signal Intelligence Team"
LABEL description="Nginx reverse proxy for OTC Signal Intelligence"

# Remove default configs
RUN rm -f /etc/nginx/conf.d/default.conf \
    && rm -f /etc/nginx/nginx.conf

# Copy our custom configs
COPY infrastructure/docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY infrastructure/docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Laravel public directory (populated by api build or volume mount)
RUN mkdir -p /var/www/api/public

# Ensure nginx can read the files
RUN chown -R nginx:nginx /var/www/api/public \
    && chmod -R 755 /var/www/api/public

EXPOSE 80 443 8000 8080

CMD ["nginx", "-g", "daemon off;"]
