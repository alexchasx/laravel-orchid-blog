# План: превращение блога в GitHub public template

Источник: [`prompts/promt-for-public-template.md`](prompts/promt-for-public-template.md)
Репозиторий: `https://github.com/alexchasx/laravel-orchid-blog` → шаблонный `laravel-blog-template`

**Цель:** любой разработчик может нажать «Use this template» и получить чистую основу для своего блога на Laravel + Orchid Platform, а не копию проекта со специфичными данными.

---

## 1. Очистить `.env.example`

Заменить все реальные значения на пустые или плейсхолдеры:

```dotenv
APP_KEY=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
SUB_LOGO=
```

- [x] Убрать захардкоженные ключи, пароли, имена баз, пользовательские подзаголовки.
- [x] Убедиться, что файл стал шаблоном конфигурации, а не копией боевого `.env`.
- [x] Проверить остальные `APP_*` / `MAIL_*` / `MY_*` переменные на реальные значения (email, хосты) и при необходимости заменить плейсхолдерами.

## 2. Удалить lock-файлы

- [x] Удалить `composer.lock` из репозитория.
- [x] Удалить `package-lock.json` из репозитория.
- [x] Добавить оба файла в `.gitignore`, если их там нет.
- [x] Убедиться, что в новом проекте из шаблона зависимости ставятся свежие (`composer install` без lock).

## 3. Очистить миграции

- [x] Найти в `database/migrations/` захардкоженные данные через `DB::table()->insert()` или аналоги.
- [x] Перенести такие данные в сидеры (`database/seeders/`) или удалить.
- [x] Миграции должны только создавать структуру таблиц.
- [x] Проверить, что `migrate:fresh --seed` восстанавливает нужное стартовое наполнение (рубрики/теги/админ).

## 4. Удалить проектные файлы

Выполнено:

- [x] Удалены рабочие заметки автора: `_ISSUES.md`, `BACKLOG.md`, `__BACKLOG.md` (не отслеживался в git).
- [x] Оставлены только публичные документы шаблона: `README.md`, `AGENTS.md`, `CONTRIBUTING.md`, `docs/architecture.md`, `docs/public-template-plan.md` (рабочий план, удалить после завершения остальных секций).
- [x] `screenshots/` — оставлен только `home.png` (используется в README); удалены артефакты разработки `home-current.png`, `pagination-fixed.png`.
- [x] `tools/` — удалён целиком (`composer.phar` не нужен: composer работает внутри Docker-контейнера).
- [x] `.codeassistant/mcp.json` — удалён (личная конфигурация MCP автора).
- [x] `.gigacode/`, `.gigacode_vsc/` — не отслеживались в git; добавлены в `.gitignore`, чтобы не попали в репозиторий (локальные каталоги не удаляются: в них зарегистрированы git-worktrees с ветками).
- [x] `opencode.json` и `prompts/` оставлены по решению автора (конфиг ИИ-агента + рабочие промты).
- [x] Проверен корень: `.htaccess` (rewrite в `public/` для shared-хостинга), `Makefile`, `docker/`, `.styleci.yml`, `.editorconfig`, конфиги Vite/Tailwind/PostCSS/PHPUnit — универсальные, оставлены.


## 5. Описать возможности («из коробки»)

Составить в README список того, что уже работает из коробки:

- [x] CRUD постов (статьи), категории (рубрики), теги;
- [x] роли и права доступа (Orchid);
- [x] гостевые комментарии с математической капчей;
- [x] форма обратной связи;
- [x] подписка на новые статьи с рассылкой по email;
- [x] автопубликация запланированных статей;
- [x] Blade-фронтенд + админ-панель Orchid на `/admin`;
- [x] медиа-загрузка (Orchid) — **уточнено**: поля `Upload`/`Picture` в Orchid есть, но к статьям не подключены (нет поля в `CreateOrUpdateArticle`, в шаблоне — заглушка-градиент). Задокументировано в README как «чего пока нет» + инструкция по включению; gotcha добавлена в AGENTS.md.
- [x] Дополнительно в README описаны: лента с поиском и оглавлением, страницы рубрик/меток, черновики `/notpublic`, тёмная/светлая тема, SEO/OG-разметка, двуязычность (ru/en), модерация комментариев, отписка по токену, Docker-инфраструктура и тесты.
- [x] Отредактирован `AGENTS.md`: добавлены раздел «Статус репозитория» (правила публичного шаблона), ссылка на раздел возможностей в README и gotcha про неподключённую загрузку изображений.

## 6. «Что нужно поменять после создания» — задокументировать в README

Выполнено: добавлен раздел `🔧 Что поменять после создания` в `README.md` (после инструкций по установке, перед «Структура проекта»).

- [x] `APP_NAME`, `APP_URL` в `.env` (описано влияние: `<title>`/OG-теги, футер, URL в письмах, префиксы кэша/сессий/Redis; рекомендация `make clear` после смены `APP_NAME`).
- [x] `SUB_LOGO` — подзаголовок сайта; рядом `SLOGAN`, `MY_GITHUB`/`MY_TELEGRAM`, `CONTACT_EMAIL`, `MAIL_FROM_ADDRESS` (таблица переменных → где используются, с указанием, что `config/my_config.php` править не нужно).
- [x] Название проекта в `composer.json` (`name`, `description`, `keywords` — сейчас дефолтные `laravel/laravel` / «The Laravel Framework.» / ключи Laravel) и `package.json` (поля `name` нет, пакет `private` — добавлять необязательно).
- [x] Favicon и логотип в `public/`: `favicon.ico` задан дефолтом Laravel и подключается только в legacy-layout `base.blade.php` (в `techlog` нужно добавить `<link rel="icon">`); логотип-текст `TECH//LOG` захардкожен в `layouts/techlog.blade.php:41`; OG-изображения не загружаются (заглушка-градиент).
- [x] Настройки Orchid в `config/platform.php`: `prefix` (`PLATFORM_PREFIX`), `domain` (`PLATFORM_DOMAIN`), `middleware`, `template.header`/`template.footer`, `notifications.enabled`, `search`, `attachment.disk` + ссылка на `app/Orchid/PlatformProvider.php` (меню, `platform.custom.*`).
- [x] Дополнительно в том же разделе: смена пароля админа Orchid (`123456`) и тестового автора сида (`author@example.test`/`password`), демо-контент сидера (6 рубрик/21 метка/25 статей — цифра уточнена в секции 12), `robots.txt`, чеклист «Перед деплоем» (nginx-конфиг, SMTP, `queue:work`, `schedule:work`, `APP_DEBUG=false`, права `www-data`, `make frontend-build`).

## 7. Обновить `composer.json`

Выполнено:

- [x] `name` → `alexchasx/laravel-blog-template` (владелец — по origin `git@github.com:alexchasx/laravel-orchid-blog.git`).
- [x] `description` → «Шаблон IT-блога на Laravel и Orchid Platform: статьи, рубрики, метки, комментарии, рассылка.» (было дефолтное «The Laravel Framework.»).
- [x] `keywords` → `blog`, `laravel`, `orchid`, `orchid-platform`, `template`, `cms` (были дефолтные `framework`, `laravel`).
- [x] `authors` — поля в файле нет, удалять нечего; личных данных в метаданных не осталось.
- [x] JSON валиден (проверено парсером), зависимости и `scripts` не тронуты — пакеты ставятся как есть, `composer.lock` по-прежнему не коммитится.

## 8. Обновить `package.json`

Выполнено:

- [x] Добавлено `name` → `laravel-blog-template` (в файле поля не было; имя — то же, что в `composer.json`, строчные буквы — требование npm). Поле `private: true` оставлено: пакет не публикуется в реестр, публиковать его не нужно.
- [x] Скрипты проверены на проектную специфику — только дефолтные `dev` (`vite`) и `build` (`vite build`). Скриптов деплоя на конкретный сервер, кастомных папок сборки и т. п. нет; `Makefile` вызывает только `npm install`, `npm run build`, `npm run dev` — все существуют. Чистить нечего.

## 9. Добавить `.gitattributes`

Выполнено: файл уже был в репозитории (дифф-драйверы + `/.github export-ignore`), дополнен списком `export-ignore`.

```gitattributes
.gitattributes export-ignore
/.github export-ignore
/.styleci.yml export-ignore
/opencode.json export-ignore
/prompts export-ignore
/docs/public-template-plan.md export-ignore
```

- [x] `export-ignore` для служебного: сам `.gitattributes`, `.styleci.yml` (CI в проекте отсутствует — конфиг StyleCI не нужен), `opencode.json` и `prompts/` (локальные файлы ИИ-агента автора), этот рабочий план, `/.github` (был в файле изначально).
- [x] Запись `CHANGELOG.md export-ignore` удалена — файла `CHANGELOG.md` в репозитории нет.
- [x] **Отклонение от исходного черновика плана с обоснованием:** `.gitignore`, `.editorconfig` и `README.md` в архиве **оставлены** (в черновике предлагалось их исключить). `git archive`/ZIP-архив GitHub уважает `export-ignore`, поэтому без `.gitignore` в распакованном архиве не работали бы правила для `node_modules`, `vendor`, `.env` и lock-файлов, а без `README.md` разработчик не получил бы инструкции по установке. `Makefile`, `docker/`, `tests/`, `public/`, `docs/`, `screenshots/` (на картинку ссылается README) тоже остаются — это рабочая основа шаблона.
- [x] Проверено: `git archive` не содержит исключённых путей и содержит перечисленные выше.

## 10. Очистить публичные ассеты

Выполнено: инвентаризация `public/` (26 файлов, ~9,6 МБ) и удаление мёртвого наследия.

- [x] Проверен `public/` на специфичные файлы: favicon, логотипы, загрузки, скриншоты. Скриншотов и кастомных логотипов в `public/` нет; логотип — текстовый (`TECH//LOG` в шапке, не файл), OG-изображений нет (заглушка-градиент в `article.blade.php`).
- [x] Удалён `public/config.rb` — конфиг Compass из старой темы блога: в зависимостях нет ни `compass`, ни gulp, в SCSS нет ни одного `@use/@import "compass"`, ссылок на файл в проекте нет.
- [x] Удалён каталог `public/fonts/` (FontAwesome, Glyphicons Halflings, GreatVibes — 11 файлов): в `resources/` нет ни одного `@font-face`/`url(...fonts...)`, Orchid CSS использует системные стери шрифтов, единственные классы `glyphicon` были в неподключаемом `includes/donate.blade.php` (файл удалён позже, в секции 12).
- [x] Оставлены только дефолтные/компилируемые файлы: `index.php`, `.htaccess`, `robots.txt`, `favicon.ico`; сгенерированные `build/` (нужны, чтобы сайт работал сразу после клона без `npm run build`) и `public/vendor/orchid/` (опубликованные ассеты Orchid — без них админка не рендерится, переопубликация — `php artisan orchid:publish`, добавлена заметка в README).
- [x] «Кастомные логотипы» — заменены на placeholder: `public/favicon.ico` оставлен как заглушка Laravel, но теперь явно подключён в `layouts/techlog.blade.php` с комментарием-заглушкой «замените на свой» (раньше иконка подтягивалась браузером неявно).
- [x] В README указано, что нужно добавить свои favicon и логотип: раздел «Логотип и favicon» обновлён (строка про `public/fonts/` удалена, добавлена заметка про `orchid:publish`); ссылка на этот раздел в начале README уже была.

## 11. Проверить config-файлы

Выполнено: аудит всех 16 файлов в `config/`. Правки в код **не потребовались** — конфиги уже шаблонные; изменён только этот документ.

- [x] Проверены `config/platform.php` и остальные конфиги на захардкоженные значения (URL сайта, имя проекта, email администратора) — **не найдено**. Ни одного литерала домена/имени/email проекта вне `env()`.
- [x] `config/platform.php` (253 строки) целиком на `env()`/дефолтах Orchid: `domain` → `env('PLATFORM_DOMAIN', env('DASHBOARD_DOMAIN'))`, `prefix` → `env('PLATFORM_PREFIX', env('DASHBOARD_PREFIX', '/admin'))`, `guard` → `env('AUTH_GUARD', 'web')`, `attachment.disk` → `env('PLATFORM_FILESYSTEM_DISK', 'public')`. `template.header`/`template.footer` — пустые строки, `search` — пустой массив с закомментированным примером. Захардкоженных `APP_URL`, имени проекта и email админа нет.
- [x] `config/my_config.php` (10 строк) — все 6 ключей уже на `env()` с пустым дефолтом: `sub_logo`/`slogan`/`my_github`/`my_email`/`my_telegram`/`contact_email` → `env(..., '')`. Переводить не на что; в `.env.example` соответствующие `MY_*`, `SLOGAN`, `CONTACT_EMAIL`, `SUB_LOGO` тоже пустые. Вывод в шаблонах — только через `config('my_config.*')` (`layouts/techlog.blade.php`, `layouts/base.blade.php`, `privacy.blade.php`).
- [x] Проверены остальные 14 конфигов (`app`, `auth`, `cache`, `cors`, `database`, `filesystems`, `hashing`, `logging`, `mail`, `queue`, `sanctum`, `services`, `session`, `view`) — стоковые значения Laravel с `env()` и нейтральными плейсхолдерами. Единственные «домены» — дефолты фреймворка: `config/app.php:18` `env('APP_NAME', 'Laravel')`, `config/app.php:57` `env('APP_URL', 'http://localhost')`, `config/mail.php:94` `env('MAIL_FROM_ADDRESS', 'hello@example.com')`, `config/queue.php:58` `env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id')`; в `.env.example` эти переменные заданы явно, поэтому дефолты конфигов не применяются.
- [x] Проектных значений в `docker/` нет: в nginx-конфигах отсутствует `server_name` (используется `_`), единственное упоминание домена — `getcomposer.org` в `docker/app/DockerFile:30` (официальный установщик Composer).

Замечания (не входили в объём правок):

- `config/my_config.php:7` — ключ `my_email` (`MY_EMAIL`) **не используется ни в одном шаблоне** (в `resources/views` есть только `sub_logo`, `my_github`, `my_telegram`, `slogan`, `contact_email`). Мёртвый конфиг; либо использовать под контактный email в футере, либо удалить ключ + строку `MY_EMAIL=` из `.env.example`. Заодно в `AGENTS.md:76` перечисление `MY_*` не совпадает с `docs/architecture.md:187` — там `MY_EMAIL` есть.
- `docker/app/DockerFile:3` — `LABEL maintainer="alexchasx"`: метаданные автора в образе. Не утечка секретов и согласуется с `composer.json` → `alexchasx/laravel-blog-template` (секция 7), но после передачи шаблона другому владельцу метку стоит заменить/удалить.

## 12. Финальная проверка

Выполнено: полный аудит репозитория и git-истории (262 коммита, 20+ веток), прогон миграций с нуля, тестов, линтера и сборки фронтенда. Устранены две утечки в рабочем дереве (личный email + платёжный кошелёк); обнаружена утечка в истории git (требует решения владельца репозитория — см. п. 12.4).

### 12.1. Пароли, ключи, токены, email

- [x] Просканированы **все отслеживаемые git-файлы** (335 шт.) на `base64:…`-ключи, hex-строки ≥32 символов, литералы `password|secret|token|api_key` и email-адреса.
- [x] `APP_KEY` в `.env.example` пустой; в отслеживаемых файлах нет ни одного `base64:`-ключа (проверено `git grep -E 'base64:[A-Za-z0-9+/]{20,}'` — пусто). Единственные длинные hex-хеши — `id=` в `public/vendor/orchid/mix-manifest.json` (хеши файлов Orchid, не секреты).
- [x] **Найдена и устранена утечка:** `resources/views/contact.blade.php:11-12` содержал захардкоженные **`mailto:mail@yandex.ru`** (реальный почтовый ящик) и `https://github.com/alexchasx`. Заменено на `config('my_config.contact_email')` и `config('my_config.my_github')` с `@if`-обёртками (пусто в `.env` — блок не рендерится). Попутно плейсхолдер имени в форме «Алексей» → нейтральный «Как к вам обращаться».
  - Бонус: README обещал, что `CONTACT_EMAIL` выводится на странице «Контакты», но до правки переменная нигде не использовалась — теперь обещание выполнено (строка README уточнена).
- [x] **Найдена и устранена утечка:** `resources/views/includes/donate.blade.php` — три формы доната на Яндекс.Деньги с захардкоженным **кошельком `41001986385381`** и `successURL=http://web-programming.com.ua` (домен исходной темы). Инклюд нигде не подключался (в `includes/sidebar.blade.php` остался только закомментированный HTML-блок) — файл **удалён** (`git rm`). Проверено, что на удаление ссылок ни в одном шаблоне нет.
  - Побочно: `includes/publication_date.blade.php` — тоже мёртвый инклюд (0 ссылок), но данных автора не содержит, поэтому оставлен; в секции 10 он отмечался как «не подключаемый» из-за классов `glyphicon`.
- [x] Email-адреса в отслеживаемых файлах — только плейсхолдеры: `admin@example.com`, `author@example.test`, `hello@example.com`, `guest@example.com`, `test@example.com` и т. п. плюс документированные дефолты `admin@localhost.ru` ( Orchid-админ из `make orchid-admin`, в README помечен «смените сразу») и `git@github.com` (remote репозитория).
- [x] Два «настоящих» адреса найдены **только внутри `public/vendor/orchid/js/vendor.js.map`** — это авторы сторонних библиотек из бандла Orchid (`Sortable 1.15.0`: `RubaXa <trash@rubaxa.org>`, `owenm <owen23355@gmail.com>`). Это опубликованные ассеты стороннего пакета, данные автора шаблона там нет; переписывать вендорный файл нельзя.
- [x] `docker/docker-compose.yml:69` — `MYSQL_ROOT_PASSWORD: root` и `MYSQL_DATABASE: laraorchid`. Это дефолты локальной dev-БД, порт `:8101` торчит наружу только на localhost; они же документированы в `AGENTS.md`. Оставлены как есть: перенос на `env()` в compose не даст эффекта, т.к. проект запускается как `docker compose -f docker/docker-compose.yml` из корня и `.env` для compose резолвится относительно `docker/`, а не корня репозитория.
- [x] `LABEL maintainer="alexchasx"` в `docker/app/DockerFile:3` — метаданные, не секрет (см. замечание секции 11).

### 12.2. `key:generate`

- [x] `APP_KEY=` в `.env.example` пустой (строка 3).
- [x] `php artisan key:generate --force` в контейнере `blog_app` — ключ реально сгенерирован и подставлен в `.env` (`base64:…`, 51 символ), INFO «Application key set successfully». Локальный `.env` после проверки восстановлен из резервной копии.

### 12.3. Запуск с нуля (`migrate --seed`)

Проверено на **отдельной базе `seed_check`** (`DB_DATABASE=seed_check php artisan migrate:fresh --seed --force`), чтобы не уничтожать рабочие данные в `laraorchid`.

- [x] `composer validate` — `composer.json` валиден.
- [x] Разрешение зависимостей **без `composer.lock`** (`composer update --dry-run` в отдельной копии `composer.json`) проходит без конфликтов: `laravel/framework v13.33.0` (в текущем `vendor/` — v13.32.0, т.е. поднимется на один патч), `orchid/platform 14.53.0` (совпадает с установленным), `league/commonmark 2.10.3`. Локальный `vendor/` не трогали.
- [x] `migrate:fresh --seed` — 25 миграций (Orchid + доменные + `jobs`) прошли без ошибок, сидер отработал без ошибок.
- [x] Данные после сида: `users` 1, `roles` 0, `rubrics` 6, `tags` 21, `articles` 25 (все `is_published=1` и `published_at <= now()`), `article_tags` 66, `jobs` 0.
- [x] **Поправка README:** в разделе «Демо-контент» было написано «5 статей» — фактически сидер создаёт 25 (два цикла по 5 и 20). Цифра исправлена, добавлен email тестового автора `author@example.test`.
- [x] Смоук-тест работающего сайта (`http://localhost:8080`): `/` → 200, `/contact` → 200, `/admin/login` → 200, `/up` → 200, `/article/<реальный slug>` → 200, несуществующие `/article` и `/rubric/nonexistent` → 404, `GET /subscribe` → 405 (метод не тот — корректно).
- [x] `make test` — **122 теста, 291 assertion, все зелёные** (6.16 с).
- [x] `make lint` — синтаксических ошибок нет. Единственное замечание: `Deprecated: App\Orchid\Presenters\UserPresenter::searchQuery(): Implicitly marking parameter $query as nullable` (`app/Orchid/Presenters/UserPresenter.php:78`) — это PHP 8.5 deprecation, не ошибка линтера.

### 12.4. Утечки в git-истории

- [x] **Файл `.env` не коммитился ни разу** за всю историю (`git log --all --diff-filter=A --name-only | grep .env` → только `.env.example` и `frontend/.env.example`). Поэтому предписанная планом процедура `git rm --cached` **не требуется** — в индексе нет ни одного env-файла, кроме шаблона.
- [x] `.gitignore` уже содержал `.env` и `.env.backup`; **усилено** до `.env.*` с исключением `!.env.example` — теперь не попадут `.env.production`, `.env.local`, `.env.testing.local` и т. п. Проверено: `.env.example` по-прежнему отслеживается, все варианты `.env.*` — игнорируются.
- [!] **Найдена утечка в истории `.env.example`** (в рабочем дереве её уже нет, но она видна в старых коммитах, во **всех** ветках, включая `main`/`develop`, и запушена в `origin`):
  - `APP_KEY=base64:uNbY6PiGH4GOx3PrKkyGPe6LcQro+itcoPSW/cbtSzo=` — коммиты `69b3263` (2022-08-07), `0f85152` (2022-08-09), `a75722b` (2022-09-15), `2afd76a` (2022-09-20), `dbada2a` (2022-10-16), `2abd203` (2022-10-16);
  - `CONTACT_EMAIL="a.chasovnikov@yandex.ru"` — коммиты `69b3263`, `0f85152`;
  - `DB_PASSWORD=Password+12` — коммиты `2abd203`, `e5343bb`; также встречались `DB_DATABASE=prod_blog`, `blog_laravel_9`, `larblog`.
  - **Это решение владельца репозитория, а не правка этого плана.** Варианты: (1) переписать историю (`git filter-repo --replace-text` по списку значений + `--force` на всех ветках и тегах) — надёжно, но ломает все существующие клоны и требует force-push; (2) опубликовать шаблон из «чистой» истории (squash/новый репозиторий) — проще и безопаснее для старого проекта; (3) оставить как есть, если значения давно не используются. **Настоятельно рекомендуется (1) или (2) до публикации шаблона**, а текущий `APP_KEY` в локальном `.env` — перевыпустить через `make key-generate`.

### 12.5. Сборка фронтенда без lock-файлов

- [x] Чистый `npm install` без `package-lock.json` — установлено 105 пакетов без ошибок: `vite 6.4.3`, `laravel-vite-plugin 1.3.0`, `sass 1.105.0`, `tailwindcss 3.4.19`, `autoprefixer 10.6.1`, `alpinejs 3.17.4`, `postcss 8.5.28`, `@tailwindcss/forms 0.5.11`.
- [x] `vite build` — 7 модулей собрано за 1.3 с, 0 ошибок и 0 предупреждений, 6 ассетов + `manifest.json`.
- [x] Хеши собранных файлов **совпали с отслеживаемыми** в `public/build/assets/` (`app-CX5PChzu.css`, `app-DRNvDO9s.js`, `cookie-banner-DHK6a08j.js`, `index-ByNVDgTm.css`, `style-ByX_u3H6.css`, `techlog-CMsT4qZp.js`) — то есть закоммиченные ассеты воспроизводимы, расхождений в `git status` нет.
- [x] Сборка выполнялась в отдельный каталог (`vite build --outDir /tmp/build-check`), чтобы не перетирать отслеживаемый `public/build/`.

---

## Порядок выполнения

1. Секции 1–4 (очистка) — базовая гигиена репозитория.
2. Секции 7–11 (метаданные, ассеты, конфиги) — приведение к шаблонному виду.
3. Секции 5–6 (README: возможности + что менять) — документация шаблона.
4. Секция 12 — финальный аудит перед публикацией как template.

Статус: секции 1–12 выполнены, кроме решения владельца по утечкам в git-истории (п. 12.4) — его нужно принять **до** публикации шаблона. После этого документ можно удалить (он уже помечен `export-ignore` в `.gitattributes`).

> Примечание: проект работает только через Docker (`make` из `docker/docker-compose.yml`); проверки из секции 12 выполнять через `make setup` / `make test` внутри контейнеров, локальные php/composer/npm не используются.

Фактически применённые команды при проверке секции 12:

```bash
make test                                            # 122 теста
make lint                                           # php -l
docker compose -f docker/docker-compose.yml exec -T app php artisan key:generate --force
docker compose -f docker/docker-compose.yml exec -T -e DB_DATABASE=seed_check \
  app php artisan migrate:fresh --seed --force      # проверка на отдельной базе
docker compose -f docker/docker-compose.yml exec -T node npx vite build   # --outDir /tmp/...
```

Безопасные альтернативы, которыми проверялись неразрушающие пункты: `composer update --dry-run` в копии `composer.json` вместо `composer install`; `npm install` в `/tmp/npmtest` вместо рабочего `node_modules`; `vite build --outDir /tmp/build-check` вместо пересборки отслеживаемого `public/build/`; `migrate:fresh --seed` на базе `seed_check` вместо рабочей `laraorchid`.
