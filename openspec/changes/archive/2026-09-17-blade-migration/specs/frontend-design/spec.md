## MODIFIED Requirements

### Requirement: Единый источник дизайн-токенов

Система SHALL предоставлять дизайн-токены в виде CSS custom properties, разделённых по layout:
- Legacy-токены: `resources/sass/_tokens.scss` → `:root` для `base.blade`
- Techlog-токены: `resources/sass/techlog/_variables.scss` → `:root` и `html.light` для `techlog.blade`

Legacy-токены (`:root` в `_tokens.scss`):
- `--color-body-bg: #f8fafc`, `--color-surface: #eee`, `--color-text: #333`, `--color-link: #5d92b7`, `--color-accent: #5d92b7`
- Типографика, spacing, радиусы, тени — без изменений

Techlog-токены (`:root` в `techlog/_variables.scss`):
- `--bg: #0a0a0a`, `--surface: #151715`, `--surface-2: #1a1a1a`, `--text: #f0f5f2`, `--muted: #8b9992`, `--green: #00ff88`, `--line: rgba(0,255,136,.15)`, `--shadow: 10px 10px 28px rgba(0,0,0,.65),-8px -8px 24px rgba(255,255,255,.025)`, `--max: 1180px`, `--radius: 20px`
- `html.light`: `--bg: #e8eee9`, `--surface: #e8eee9`, `--surface-2: #edf2ee`, `--text: #112018`, `--muted: #53645b`, `--line: rgba(0,105,58,.18)`, `--shadow: 10px 10px 25px rgba(91,110,99,.18),-8px -8px 22px rgba(255,255,255,.85)`

#### Scenario: Токены определены в :root

- **WHEN** загружается собранный CSS публичного блога
- **THEN** селектор `:root` объявляет CSS custom properties для цвета, типографики, spacing, радиусов и теней

#### Scenario: Стили используют токены

- **WHEN** стиль публичной части задаёт цвет, радиус или тень
- **THEN** значение берётся из CSS custom property, а не из хардкод-литерала
