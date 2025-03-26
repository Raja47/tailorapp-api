Versions 
Php version 8.1

Instructions
create local mysql db named as tailorinch

Rename env.example to .env
    Replace below env variable with your local db configs
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=tailorinch
        DB_USERNAME=root
        DB_PASSWORD=
        
composer install 
php artisan key:generate
php artisan migrate
php artisan l5-swagger:generate
