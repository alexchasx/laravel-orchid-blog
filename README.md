Blog on Laravel with the Orchid admin panel (https://orchid.software/).
Working site: https://alexchasx.ru/

---
# Installing the project on the development environment

```bash
mkdir projects && cd projects
git clone git@github.com:alexchasx/laravel-orchid-blog.git
cd laravel-orchid-blog

composer install
npm install
npm run dev
php artisan storage:link

cp .env.example .env
composer require laravel/sail --dev
php artisan sail:install
# select `mysql`

alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
sail up -d

sail artisan migrate --seed

# Create a user with the maximum (at the time of creation) rights:
sail artisan orchid:admin admin email@email.com 123456

# check in the browser: http://localhost 
```
