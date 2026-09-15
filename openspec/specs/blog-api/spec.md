# blog-api Specification

## Purpose

Описывает Laravel JSON API публичной части блога: получение списка статей (пагинация, поиск, фильтры по рубрике и тегу), отдельной статьи, списков рубрик и тегов. API доступно внешнему Nuxt-фронтенду по CORS и отдаёт только опубликованные материалы.

## Requirements

### Requirement: Список статей с пагинацией и фильтрами
API SHALL предоставлять эндпоинт `GET /api/articles`, возвращающий пагинированный список опубликованных статей. Поддерживаемые query-параметры: `page`, `search`, `rubric`, `tag`. Ответ SHALL содержать массив статей с полями `id`, `title`, `excerpt`, `published_at`, `image`, `rubric`, `tags` и мета-данные пагинации (`current_page`, `last_page`, `per_page`, `total`). Статьи SHALL сортироваться по `published_at` по убыванию. По умолчанию на странице SHALL возвращаться 12 статей.

#### Scenario: Получение первой страницы статей
- **WHEN** выполняется `GET /api/articles`
- **THEN** возвращается HTTP 200 с массивом до 12 опубликованных статей и мета-данными пагинации

#### Scenario: Поиск по заголовку
- **WHEN** выполняется `GET /api/articles?search=<query>`
- **THEN** возвращаются только статьи, чей заголовок содержит `<query>` (без учёта регистра)

#### Scenario: Фильтр по рубрике
- **WHEN** выполняется `GET /api/articles?rubric=<rubricId>`
- **THEN** возвращаются только статьи выбранной рубрики

#### Scenario: Фильтр по тегу
- **WHEN** выполняется `GET /api/articles?tag=<tagId>`
- **THEN** возвращаются только статьи, содержащие выбранный тег

#### Scenario: Переход на следующую страницу
- **WHEN** выполняется `GET /api/articles?page=2`
- **THEN** возвращается вторая страница пагинации с корректными мета-данными

### Requirement: Публикация скрывает статью из списков
API SHALL возвращать только опубликованные статьи: `is_published = true` и `published_at` не позже текущего момента. Неопубликованные статьи SHALL NOT появляться в списках и фильтрах.

#### Scenario: Неопубликованная статья не попадает в список
- **WHEN** в базе есть статья с `is_published = false`
- **THEN** она отсутствует в ответе `GET /api/articles` и всех фильтрованных вариантах

#### Scenario: Статья с будущей датой публикации скрыта
- **WHEN** у статьи `published_at` позже текущего времени
- **THEN** она отсутствует в ответе `GET /api/articles`

### Requirement: Отдельная статья
API SHALL предоставлять эндпоинт `GET /api/articles/{article}`, возвращающий полные данные статьи: `id`, `title`, `excerpt`, `content_html`, `image`, `published_at`, `rubric`, `tags`, `viewed`, `keywords`, `meta_desc`. Если статья не найдена или не опубликована, API SHALL возвращать HTTP 404.

#### Scenario: Получение опубликованной статьи
- **WHEN** выполняется `GET /api/articles/42` и статья 42 опубликована
- **THEN** возвращается HTTP 200 с полными данными статьи, включая готовый HTML-контент

#### Scenario: Запрос неопубликованной статьи
- **WHEN** выполняется `GET /api/articles/42` и статья 42 не опубликована
- **THEN** возвращается HTTP 404

#### Scenario: Запрос несуществующей статьи
- **WHEN** выполняется `GET /api/articles/999999`
- **THEN** возвращается HTTP 404

### Requirement: Список рубрик
API SHALL предоставлять эндпоинт `GET /api/rubrics`, возвращающий список рубрик с полями `id`, `title`, `slug`, `description`.

#### Scenario: Получение рубрик
- **WHEN** выполняется `GET /api/rubrics`
- **THEN** возвращается HTTP 200 с массивом рубрик

### Requirement: Список тегов
API SHALL предоставлять эндпоинт `GET /api/tags`, возвращающий список тегов с полями `id`, `title`, `slug`.

#### Scenario: Получение тегов
- **WHEN** выполняется `GET /api/tags`
- **THEN** возвращается HTTP 200 с массивом тегов

### Requirement: Доступность по CORS
API SHALL разрешать кросс-доменные запросы с origin Nuxt-фронтенда (dev: `http://localhost:3000`). Запросы `GET` SHALL проходить без предварительной аутентификации.

#### Scenario: Запрос из браузера с другого origin
- **WHEN** Nuxt-фронтенд на `http://localhost:3000` выполняет `GET /api/articles`
- **THEN** браузер получает ответ с корректными CORS-заголовками и данные доступны для чтения

### Requirement: Структура ответа JSON
Ответы API SHALL быть валидным JSON с плоской структурой полей в snake_case: `published_at`, `meta_desc`, `content_html`, `current_page`, `last_page`.

#### Scenario: Формат поля даты
- **WHEN** API возвращает статью
- **THEN** `published_at` представлен строкой ISO 8601
