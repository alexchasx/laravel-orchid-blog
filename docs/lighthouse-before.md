# Замеры Lighthouse «до» изменений (Фаза 0, задача 1.3)

Дата: 2026-09-14
Инструмент: Lighthouse (npx lighthouse), Chrome headless, localhost `http://127.0.0.1:8080`
Категории: Performance, Accessibility, Best Practices, SEO

## Главная (`/`)

| Категория | Балл |
|---|---|
| Performance | 100 |
| Accessibility | 76 |
| Best Practices | 96 |
| SEO | 82 |

## Страница статьи (`/article.1`)

| Категория | Балл |
|---|---|
| Performance | 100 |
| Accessibility | 79 |
| Best Practices | 96 |
| SEO | 100 |

## Известное нарушение доступности (зум)

Аудит `meta-viewport` (axe `[user-scalable="no"]`) **провален** на обеих страницах (score 0):

> `<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">`

Причина: атрибуты `maximum-scale=1.0` и `user-scalable=0` запрещают масштабирование страницы.
Ожидание после изменений (задача 5.1 / спека «Доступное масштабирование страницы»): метатег `width=device-width, initial-scale=1.0` без `maximum-scale` и `user-scalable`, аудит `meta-viewport` — pass.

## Примечания

- Замеры сняты на локальном Docker-окружении (nginx :8080, MySQL) до внесения каких-либо изменений в стили.
- Полные JSON-отчёты сохранены во временных файлах окружения (`/tmp/lh-home.json`, `/tmp/lh-article.json`) и могут быть пересняты при необходимости.
