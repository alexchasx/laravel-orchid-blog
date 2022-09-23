Blog on Laravel with the Orchid admin panel (https://orchid.software/).
Working site: https://alexchasx.ru/

# Project installation

```bash
mkdir new_project
cd new_project
git clone git@github.com:chasovnikov/laravel-orchid-blog.git
cd laravel-orchid-blog

composer install    # installing composer dependencies (see composer.json)
npm install         # install npm dependencies (see package.json)
npm audit fix --force  # if he asks
npm run dev         # running a command from package.json: scripts: dev
php artisan storage:link    # create a storage folder symlink in the public folder
```

- Create a file .env using example .en.example and edit it.

- Create a database (for MySQL in utf8_unicode_ci encoding).
- Starting migrations + creating test data:

```bash
php artisan migrate --seed
```
- Create a user with the maximum (at the time of creation) rights:

```bash
php artisan orchid:admin nickname email@email.com secretpassword
```

- Configure and launch the web server.
- Check the result in the browser.
