### Установка проекта 

```bash
composer install    # установка composer-зависимости (см. composer.json)
npm install         # уст-ка npm-завис-ти (см. package.jsoon)
npm audit fix --force  # если попросит
npm run dev         # запуск команды из package.json: scripts: dev
php artisan storage:link    # создать симлинк папки storage в папке public

# создать файл .env по примеру .env.example
# сгенерировать APP_KEY в файле .env
php artisan key:generate

# создать базу данных
# запуск миграций + создание тестовых данных
php artisan migrate --seed

# настроить и запустить веб-сервер
# проверить результат в браузере
```
