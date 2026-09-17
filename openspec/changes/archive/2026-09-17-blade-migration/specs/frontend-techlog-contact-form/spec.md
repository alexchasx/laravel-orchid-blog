## Purpose

Открытая форма обратной связи для гостей: сохранение name, email, message в модель Contact.

## ADDED Requirements

### Requirement: Открытая форма

Страница `contact` **SHALL** принимать сообщения от гостей (без авторизации):

- Поля: `name` (min 2 chars), `email` (valid email), `message` (min 10 chars)
- Все поля обязательные (`required`)
- POST → `ContactController@store`
- После сохранения: redirect back с `session('success')` или `session('error')`

#### Scenario: Успешная отправка

- **WHEN** пользователь заполняет форму и нажимает «Отправить»
- **THEN** запись создаётся в `Contact`, redirect back с `session('success', 'Сообщение отправлено!')`

#### Scenario: Неудачная отправка

- **WHEN** при сохранении возникает ошибка
- **THEN** redirect back с `session('error', 'Сообщение не получилось отправить.')`

#### Scenario: Валидация name

- **WHEN** поле `name` < 2 символов
- **THEN** форма не отправляется (HTML5 validation)

#### Scenario: Валидация email

- **WHEN** поле `email` невалидно
- **THEN** форма не отправляется (HTML5 validation)

#### Scenario: Валидация message

- **WHEN** поле `message` < 10 символов
- **THEN** форма не отправляется (HTML5 validation)

### Requirement: Модель Contact

Модель `Contact` **SHALL** хранить:

- `name` — string, nullable (если авторизован — берётся из User)
- `email` — string, nullable (если авторизован — берётся из User)
- `message` — text
- `title` — string, nullable
- `user_id` — foreign key, nullable
- `read` — boolean, default false

#### Scenario: Сообщение от гостя

- **WHEN** форма отправлена неавторизованным пользователем
- **THEN** `user_id = null`, `name` и `email` берутся из формы

#### Scenario: Сообщение от авторизованного

- **WHEN** форма отправлена авторизованным пользователем
- **THEN** `user_id` установлен, `name` и `email` берутся из User
