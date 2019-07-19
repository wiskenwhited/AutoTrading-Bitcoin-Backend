#!/bin/bash
git pull
composer install
php artisan migrate
php artisan db:seed
php artisan queue:restart