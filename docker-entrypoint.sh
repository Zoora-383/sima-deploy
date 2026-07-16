#!/bin/sh
set -e

# 0. Fix Apache MPM conflict (common on platforms like Railway that might inject conflicting modules)
if command -v a2dismod >/dev/null 2>&1; then
    echo "Resolving Apache MPM conflicts..."
    a2dismod mpm_event mpm_worker mpm_prefork || true
    a2enmod mpm_prefork || true
fi

# 1. Map Railway database environment variables to Laravel DB_* variables
if [ -n "$MYSQLHOST" ]; then
    echo "Railway MySQL detected. Mapping database environment variables..."
    export DB_CONNECTION=mysql
    export DB_HOST="$MYSQLHOST"
    export DB_PORT="$MYSQLPORT"
    export DB_DATABASE="$MYSQLDATABASE"
    export DB_USERNAME="$MYSQLUSER"
    export DB_PASSWORD="$MYSQLPASSWORD"
fi

if [ -n "$PGHOST" ]; then
    echo "Railway PostgreSQL detected. Mapping database environment variables..."
    export DB_CONNECTION=pgsql
    export DB_HOST="$PGHOST"
    export DB_PORT="$PGPORT"
    export DB_DATABASE="$PGDATABASE"
    export DB_USERNAME="$PGUSER"
    export DB_PASSWORD="$PGPASSWORD"
fi

# 2. If using SQLite, make sure the database file exists
if [ "$DB_CONNECTION" = "sqlite" ] && [ -n "$DB_DATABASE" ]; then
    DB_DIR=$(dirname "$DB_DATABASE")
    mkdir -p "$DB_DIR"
    if [ ! -f "$DB_DATABASE" ]; then
        echo "SQLite database file not found. Creating $DB_DATABASE..."
        touch "$DB_DATABASE"
    fi
fi

# 3. Generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
    echo "WARNING: APP_KEY is not set. Generating key..."
    export APP_KEY=$(php artisan key:generate --show --no-ansi)
    echo "Generated APP_KEY: $APP_KEY"
fi

# 4. Create storage symlink
echo "Linking storage..."
php artisan storage:link --force || true

# 5. Run database migrations ALWAYS (before caching config)
echo "Running database migrations..."
php artisan migrate --force || echo "WARNING: Migration failed, but continuing startup..."

# 6. Run database seeder if requested
if [ "$RUN_SEEDER" = "true" ]; then
    echo "Running database seeder..."
    php artisan db:seed --force || echo "WARNING: Seeder failed, but continuing startup..."
fi

# 7. Cache Laravel configurations (after migrations so everything is ready)
echo "Caching configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Execute the main container command (apache2-foreground)
exec "$@"
