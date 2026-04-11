#!/bin/bash
set -e

# Function to wait for PostgreSQL
wait_for_postgres() {
    echo "Waiting for PostgreSQL..."
    for i in $(seq 1 15); do
        if php -r "try { new PDO('pgsql:host=db;dbname=unione_backend', 'unione_user', 'unione_password'); exit(0); } catch (Exception \$e) { exit(1); }" 2>/dev/null; then
            echo "Database is ready!"
            return 0
        fi
        echo "Waiting for database... attempt $i/15"
        sleep 2
    done
    echo "Warning: Could not connect to database after 30 seconds. Continuing anyway."
    return 0
}

# Only run setup if ENTRYPOINT_SETUP is not set to 'false'
if [ "${ENTRYPOINT_SETUP}" != "false" ]; then
    # Wait for database
    wait_for_postgres

    # Check if .env exists
    if [ ! -f /var/www/html/.env ]; then
        echo "Creating .env file..."
        cp /var/www/html/.env.example /var/www/html/.env
    fi

    # Generate application key if not set
    if ! grep -q "APP_KEY=" /var/www/html/.env || grep -q "APP_KEY=$" /var/www/html/.env; then
        echo "Generating application key..."
        php artisan key:generate --force
    fi

    # Run database migrations (only if database is accessible)
    if php -r "try { new PDO('pgsql:host=db;dbname=unione_backend', 'unione_user', 'unione_password'); exit(0); } catch (Exception \$e) { exit(1); }" 2>/dev/null; then
        echo "Running database migrations..."
        php artisan migrate --force --no-interaction || echo "Migration failed or already applied, continuing..."
    fi

    # Clear caches (but don't cache in dev mode)
    echo "Clearing Laravel caches..."
    php artisan config:clear || true
    php artisan route:clear || true
    php artisan view:clear || true

    # Set permissions
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
    chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache
fi

# Execute the CMD
exec "$@"
