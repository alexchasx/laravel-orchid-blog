# Контекст под контролем: три плагина OpenCode для работы в VS Code

OpenCode — терминальный AI-агент, который живёт прямо в редакторе и помогает писать код. Но есть проблема: чем дольше сессия, тем больше контекста набивается в каждый запрос. Вывод `php artisan route:list`, логи, старые версии файлов — всё это жжёт токены, замедляет ответы и раздувает счёт за API.

Решение — плагины, которые чистят, сжимают и сохраняют контекст. Ниже — три плагина, которые меняют правила игры: DCP для автообрезки, context-mode для песочницы вывода и opencode-mem для долговременной памяти.

## Важная деталь про VS Code

OpenCode работает в VS Code **через терминал** (или через расширение вроде PrimeCode). Плагины настраиваются не в настройках редактора, а в конфигурационных файлах OpenCode — `opencode.json` в корне проекта или `~/.config/opencode/opencode.json` глобально. VS Code тут — просто оболочка, плагины живут в экосистеме OpenCode.

---

## 1. DCP (Dynamic Context Pruning) — автообрезка контекста

**Что делает:** автоматически заменяет устаревшие куски диалога (старые версии кода, повторяющиеся пояснения, побочные обсуждения) на компактные саммари. Не трогает историю сессии — подменяет содержимое только перед отправкой к LLM.

### Установка

Откройте терминал в VS Code (`Ctrl+``) и выполните:

```bash
opencode plugin @tarquinen/opencode-dcp@latest --global
```

Это установит плагин и добавит его в глобальный конфиг OpenCode.

### Настройка

DCP использует собственный конфиг — ищется в порядке: `~/.config/opencode/dcp.jsonc` → `.opencode/dcp.jsonc` в проекте. Создайте файл `.opencode/dcp.jsonc` в корне Laravel-проекта:

```jsonc
{
  "$schema": "https://raw.githubusercontent.com/Opencode-DCP/opencode-dynamic-context-pruning/master/dcp.schema.json",
  "enabled": true,
  "pruneNotification": "detailed",
  "pruneNotificationType": "chat",
  "turnProtection": {
    "enabled": true,
    "turns": 4
  }
}
```

**Параметры:**

- `pruneNotification: "detailed"` — вы видите в чате, сколько токенов сэкономлено (например: `[DCP] Reduced context: 18,240 → 6,120 tokens`).
- `turnProtection.turns: 4` — последние 4 реплики никогда не обрезаются, чтобы агент не «забыл» текущую задачу.
- Для моделей с маленьким контекстом (локальные через Ollama) снизьте `compress.minContextLimit` и `compress.maxContextLimit`.

### Проверка

Запустите OpenCode, выполните `/dcp` — откроется панель со статистикой и управлением.

### Для Laravel-проекта

DCP особенно полезен, когда:

- запускаете `php artisan test` — длинный вывод тестов сжимается;
- делаете `php artisan migrate` — DCP обрезает устаревшие сообщения об ошибках миграций;
- рефакторите несколько файлов за сессию — старые версии кода заменяются саммари.

---

## 2. context-mode — песочница для вывода инструментов (−98% токенов)

**Что делает:** перехватывает вывод инструментов (чтение файлов, вывод `php artisan route:list`, логов) и не пускает сырые данные в контекст. Вместо этого данные обрабатываются в изолированной песочнице, и в контекст попадает только сжатый результат.

### Установка

```bash
npm install -g context-mode
```

### Настройка

Добавьте плагин в `opencode.json` в корне проекта (или `~/.config/opencode/opencode.json` глобально):

```json
{
  "$schema": "https://opencode.ai/config.json",
  "plugin": ["context-mode"]
}
```

Этого достаточно — плагин регистрирует все 11 инструментов (`ctx_*`) и хуки в одном процессе, без отдельного MCP-сервера.

**Важно:** если в существующем конфиге есть и `plugin: ["context-mode"]`, и `mcp.context-mode` — инструменты не зарегистрируются. Выполните `context-mode upgrade`, чтобы убрать дубликат.

### Дополнительно: routing rules

Скопируйте файл с инструкциями для модели — он подскажет агенту, какие инструменты использовать, а какие блокировать:

```bash
cp node_modules/context-mode/configs/opencode/AGENTS.md AGENTS.md
```

Если у вас уже есть `AGENTS.md` (а для Laravel-проекта он должен быть), объедините содержимое вручную.

### Проверка

Запустите OpenCode и введите `ctx stats` — увидите разбивку экономии по инструментам, токенов потрачено и процент savings.

### Для Laravel-проекта

Главные кейсы:

- `php artisan route:list` — вывод может быть 500+ строк, context-mode сожмёт до схемы;
- `php artisan tinker` — дампы Eloquent-моделей не попадут в контекст целиком;
- чтение `storage/logs/laravel.log` — только релевантные строки, а не весь файл;
- `composer outdated` — компактный список вместо полного вывода.

---

## 3. opencode-mem — долговременная память между сессиями

**Что делает:** сохраняет архитектурные решения, соглашения по коду, причины выбора библиотек в локальную векторную базу. При следующем запуске сам подмешивает релевантные воспоминания в контекст.

### Установка

Требуется среда Bun. Установите, если ещё нет:

```bash
curl -fsSL https://bun.sh/install | bash
```

Добавьте плагин в `~/.config/opencode/opencode.json`:

```json
{
  "plugin": ["opencode-mem"]
}
```

### Настройка

Базовая конфигурация — в файле `~/.config/opencode/opencode-mem.jsonc`:

```jsonc
{
  "storagePath": "~/.opencode-mem/data",
  "embeddingModel": "Xenova/nomic-embed-text-v1",
  "webServerEnabled": true,
  "webServerPort": 4747,
  "autoCaptureEnabled": true,
  "opencodeProvider": "anthropic",
  "opencodeModel": "claude-haiku-4-5-20251001",
  "chatMessage": {
    "enabled": true,
    "maxMemories": 3,
    "injectOn": "first"
  }
}
```

**Что тут важно:**

- `embeddingModel` — локальная модель от HuggingFace, скачивается при первом запуске. Эмбеддинги считаются на вашей машине, без отправки данных наружу.
- `autoCaptureEnabled: true` — плагин сам извлекает важное из диалога, когда сессия уходит в простой. Не нужно писать «запомни это».
- `chatMessage.injectOn: "first"` — воспоминания подмешиваются в первое сообщение новой сессии.
- `opencodeProvider` и `opencodeModel` — LLM для извлечения памяти. Claude Haiku 4.5 оптимален по соотношению цена/качество.

### Ручное управление

Агент может вызывать инструмент `memory` напрямую:

```
// Сохранить решение
memory({ mode: "add", content: "Для постов используем slug вместо id в URL" })

// Найти связанные записи
memory({ mode: "search", query: "выбор между slug и id", scope: "all-projects" })

// Экспорт памяти (для бэкапа или переноса)
memory({ mode: "export", outputPath: "./memories.json" })
```

### Для Laravel-проекта

Плагин запомнит:

- структуру моделей (Post, Category, Tag) и их связи;
- что вы используете Blade + Tailwind, а не Vue;
- что миграции нельзя редактировать — только создавать новые;
- соглашения по неймингу роутов (`blog.post.show` вместо `posts.show`);
- какие пакеты уже установлены и почему.

При следующей сессии не придётся заново объяснять архитектуру.

---

## Сводный конфиг

Вот как должен выглядеть `opencode.json` в корне Laravel-проекта со всеми тремя плагинами:

```json
{
  "$schema": "https://opencode.ai/config.json",
  "plugin": [
    "context-mode",
    "opencode-mem"
  ],
  "exclude": [
    "vendor/**",
    "node_modules/**",
    "storage/**",
    "bootstrap/cache/**",
    "*.log",
    ".env*"
  ]
}
```

DCP устанавливается отдельно через `opencode plugin` и не требует записи в `plugin` — он регистрируется автоматически.

---

## Порядок включения

Рекомендую подключать по одному и проверять после каждого:

1. **Сначала DCP** — базовая защита от переполнения контекста.
2. **Потом context-mode** — максимальная экономия на выводе инструментов.
3. **В конце opencode-mem** — когда контекст уже управляется, память становится полезна, а не создаёт шум.

Не включайте все три одновременно в первый раз — если что-то сломается, будет сложно понять, какой плагин виноват.

---

## Итог

Три плагина закрывают три уровня работы с контекстом: DCP чистит текущую сессию, context-mode не пускает мусор из инструментов, opencode-mem переносит знания между сессиями. В сумме это снижает расход токенов до 98% на типичных операциях с Laravel — маршруты, логи, тесты, дампы моделей.

Полезные ссылки:

- [DCP — GitHub](https://github.com/Opencode-DCP/opencode-dynamic-context-pruning)
- [context-mode — GitHub](https://github.com/mksglu/context-mode)
- [opencode-mem — Devtrends](https://devtrends.ru/typescript/tickernelz-opencode-mem)
- [Документация OpenCode](https://open-code.ai/ru/docs/ide)
