#!/usr/bin/env bash
cd /var/www/html/clearpay

# Run Laravel migrations
php artisan migrate
php artisan db:seed --class=TaxYearSeeder

# Create symbolic link for storage
php artisan storage:link

php artisan key:generate

# Clear and optimize the application cache
php artisan optimize:clear
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear

#cache setup
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

chmod -R 777 /var/www/html/clearpay/storage

# Start Supervisor (this replaces apache + php-fpm start)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
