#!/bin/bash

# Install dependencies if vendor directory doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader
fi

# Generate application key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "Generating application key..."
    php artisan key:generate
fi

# Install Passport keys if they don't exist
if [ ! -f "app/secrets/oauth/oauth-private.key" ]; then
    echo "Installing Passport keys..."
    php artisan passport:install --force
fi


# Set correct permissions for Passport keys
echo "Setting correct permissions for Passport keys..."
chmod 600 app/secrets/oauth/oauth-private.key
chmod 600 app/secrets/oauth/oauth-public.key

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Run seeders
echo "Running database seeders..."
php artisan db:seed --force

# # Run scheduled jobs
# echo "Running scheduled jobs..."
# php artisan jobs:run-scheduled

# # Start queue workers for different queues
# echo "Starting queue workers..."

# # Worker for default queue (general jobs)
# php artisan queue:work --queue=default --tries=3 --timeout=90 --sleep=3 --max-jobs=1000 > storage/logs/worker.log 2>&1 &

# # Worker for email notifications
# php artisan queue:work --queue=emails --tries=3 --timeout=90 --sleep=3 --max-jobs=1000 > storage/logs/worker.log 2>&1 &

# # Worker for SMS notifications
# php artisan queue:work --queue=sms --tries=3 --timeout=90 --sleep=3 --max-jobs=1000 > storage/logs/worker.log 2>&1 &

# # Worker for notifications (fallback)
# php artisan queue:work --queue=notifications --tries=3 --timeout=90 --sleep=3 --max-jobs=1000 > storage/logs/worker.log 2>&1 &

# Generate API documentation
echo "Generating API documentation..."
php artisan l5-swagger:generate

# Clear and cache config
echo "Clearing and caching configuration..."
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache

# Start Laravel scheduler in background (for cron jobs)
echo "Starting Laravel scheduler..."
php artisan schedule:work &

# --- Démarrer le serveur principal ---
echo "🌐 Starting main process..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-9000}