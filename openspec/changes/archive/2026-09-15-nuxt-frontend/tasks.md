## 1. Laravel JSON API

- [x] 1.1 Создать `routes/api.php` с эндпоинтами `GET /api/articles`, `GET /api/articles/{article}`, `GET /api/rubrics`, `GET /api/tags`; verify: `php artisan route:list` показывает api-маршруты
- [x] 1.2 Создать `app/Http/Resources/ArticleResource.php`, `RubricResource.php`, `TagResource.php` с полями snake_case (см. spec blog-api); verify: `php artisan test` либо ручной JSON-ответ содержит поля `published_at`, `content_html`, `meta_desc`
- [x] 1.3 Реализовать выборку статей через существующий `ArticleService` (published, пагинация 12, фильтры `search`/`rubric`/`tag`, сортировка по `published_at` desc) и 404 для неопубликованных статей; verify: `curl '/api/articles?rubric=1'`, `?tag=1`, `?search=q` и `curl -i /api/articles/999999` возвращают ожидаемые данные/404
- [x] 1.4 Обновить `config/cors.php` — разрешить origin `http://localhost:3000` для публичных GET; verify: preflight-запрос из браузера фронтенда проходит
- [x] 1.5 Добавить публичный `POST /api/contact` без CSRF, переиспользуя логику `ContactController@store` и `ContactRequest`; verify: `curl -X POST /api/contact` с валидными и невалидными данными возвращает корректные 2xx/422

## 2. Scaffold Nuxt 4 (frontend/)

- [x] 2.1 Создать `frontend/` с `package.json`, `nuxt.config.ts` (Nuxt 4, `ssr: true`, `modules: ['vuetify-nuxt-module']`, TypeScript strict) и установить зависимости; verify: `npm install` и `npm run dev` поднимают сервер на :3000 без ошибок
- [x] 2.2 Настроить `app/app.vue`, `app/layouts/default.vue`, `app.head` (`lang: ru`, titleTemplate, дефолтный description) и nitro `devProxy` для `/api` и `/storage` → `http://127.0.0.1:8000`; verify: `curl http://localhost:3000/` отдаёт HTML c `lang="ru"` и `<title>`
- [x] 2.3 Настроить `app/plugins/vuetify.ts` (темы light/dark с акцентом), `app/assets/css/main.css` (стилизация md-тегов контента) и inline-скрипт против «вспышки» темы в head; verify: переключение темы работает, при перезагрузке страницы нет смены светлая→тёмная
- [x] 2.4 Создать `app/types/` (Article, Rubric, Tag, Paginated) и `app/composables/useBlogApi.ts` ($fetch с apiBase); verify: `npm run typecheck` без ошибок

## 3. Компоненты Vuetify

- [x] 3.1 Реализовать `AppHeader.vue` (логотип, ссылки «Главная/О блоге/Контакты», переключатель темы), `ThemeToggle.vue` и `AppFooter.vue`; verify: навигация присутствует на всех страницах, на ширине 375px меню сворачивается в бургер
- [x] 3.2 Реализовать `ArticleCard.vue` (v-card, ленивое изображение `loading="lazy"`, дата, рубрика, теги, placeholder при отсутствии изображения); verify: в списке карточки корректны, изображения имеют `loading="lazy"`
- [x] 3.3 Реализовать `ArticleLayout.vue` (`v-html` контента, ограничение ширины ~720px, типографика Vuetify); verify: md-теги (h2/pre/code/blockquote/table/a) стилизованы читаемо в обеих темах
- [x] 3.4 Реализовать `BlogSidebar.vue` (поиск, рубрики, теги) и `PaginationBar.vue`; verify: фильтры и переходы страниц работают с сохранением query

## 4. Страницы

- [x] 4.1 Реализовать `app/pages/index.vue`: hero, список статей, пагинация, сайдбар, заголовок результатов поиска, `noindex` для `?search=`; verify: SSR-ответ содержит карточки статей и корректный `<title>`
- [x] 4.2 Реализовать `app/pages/articles/[id].vue`: единственный `h1`, `useHead` (title из заголовка, description из `meta_desc`, Open Graph `og:type=article`), 404 для неопубликованных/отсутствующих; verify: `curl http://localhost:3000/articles/42` содержит meta-теги и контент, несуществующий id отдаёт 404
- [x] 4.3 Реализовать `app/pages/rubrics/[id].vue` и `app/pages/tags/[id].vue` с пагинацией и 404; verify: открываются фильтрованные списки, несуществующие id отдают 404
- [x] 4.4 Реализовать `app/pages/about.vue` (статический контент, собственный title); verify: страница открывается с корректным `<title>`
- [x] 4.5 Реализовать `app/pages/contact.vue`: форма обратной связи, отправка на `POST /api/contact`, сообщения об успехе/ошибке (v-alert); verify: отправка формы показывает результат и обрабатывает 422

## 5. Проверка и качество

- [x] 5.1 SSR-проверка всех публичных маршрутов (/, /articles/:id, /rubrics/:id, /tags/:id, /about, /contact): контент и мета в исходном HTML; verify: `curl` каждого маршрута возвращает HTML с контентом и `<title>`
- [x] 5.2 Проверка адаптивности (320/600/1280px) и обеих тем в браузере; verify: отсутствует горизонтальный скролл, темы переключаются, скриншоты-подтверждения
- [x] 5.3 Проверка производительности: ленивые изображения, размер бандла, отсутствие лишних UI-библиотек; verify: Lighthouse не показывает критических проблем
- [x] 5.4 Обновить README: запуск двух серверов (Laravel :8000 + Nuxt :3000) и переменные окружения; verify: инструкция воспроизводима с чистого клона
