# frontend-techlog-interactions

## Purpose

JavaScript-модуль для интерактивности публичного фронтенда: мобильное меню, переключение темы, scroll-reveal, кнопка «наверх», валидация форм.

## Requirements

### Requirement: Мобильное меню

Кнопка `.menu-toggle` **SHALL** переключать класс `.open` на `.nav-links`:

- При клике на `.menu-toggle`: `nav.classList.toggle('open')`, `menu.setAttribute('aria-expanded', ...)`
- При клике на ссылку внутри `.nav-links`: меню закрывается (`nav.classList.remove('open')`)
- `aria-expanded` **SHALL** отражать состояние меню (`true`/`false`)

#### Scenario: Меню открывается

- **WHEN** пользователь нажимает `.menu-toggle`
- **THEN** `.nav-links` получает класс `.open` и `aria-expanded="true"`

#### Scenario: Меню закрывается при клике на ссылку

- **WHEN** пользователь кликает на `<a>` внутри `.nav-links`
- **THEN** меню закрывается (`class="open"` удаляется)

### Requirement: Переключение темы

Кнопка `.theme-toggle` **SHALL** переключать класс `.light` на `<html>` и сохранять в `localStorage`:

- При клике: `root.classList.toggle('light')`
- При включении light: `localStorage.setItem('techlog-theme', 'light')`
- При выключении: `localStorage.setItem('techlog-theme', 'dark')`
- При загрузке: если `localStorage('techlog-theme') === 'light'`, добавить `.light`

#### Scenario: Тема переключается

- **WHEN** пользователь нажимает `.theme-toggle`
- **THEN** класс `.light` добавляется/удаляется на `<html>`

#### Scenario: Тема сохраняется

- **WHEN** страница перезагружена после переключения на light
- **THEN** `.light` применяется автоматически

### Requirement: Scroll-reveal

Элементы с классом `.reveal` **SHALL** анимироваться при появлении в viewport:

- Использовать `IntersectionObserver` с `threshold: 0.12`
- При пересечении: добавить класс `.visible`, `unobserve` элемент
- После появления элемент **SHALL** оставаться видимым

#### Scenario: Элемент появляется при скролле

- **WHEN** `.reveal` элемент входит в viewport
- **THEN** ему добавляется класс `.visible`

#### Scenario: Элемент не анимируется повторно

- **WHEN** элемент уже имеет `.visible`
- **THEN** `IntersectionObserver` не обрабатывает его повторно

### Requirement: Кнопка «Наверх»

Кнопка `.to-top` **SHALL** появляться при скролле > 600px и прокручивать наверх:

- При `scrollY > 600`: добавить класс `.visible`
- При клике: `scrollTo({ top: 0, behavior: 'smooth' })`
- `aria-label="Наверх"`

#### Scenario: Кнопка появляется при скролле

- **WHEN** прокрутка > 600px
- **THEN** `.to-top` получает класс `.visible`

#### Scenario: Кнопка прокручивает наверх

- **WHEN** пользователь нажимает `.to-top`
- **THEN** страница плавно прокручивается к верху

### Requirement: Валидация форм

Формы **SHALL** валидироваться на клиенте:

- `[data-validate]` (newsletter): проверка email, сообщение об успехе
- `[data-contact-form]` (contact): проверка всех полей, сообщение об успехе
- При невалидности: `form.reportValidity()`, сообщение в `.form-message`
- При валидности: сообщение об успехе, `form.reset()`

#### Scenario: Newsletter валидация

- **WHEN** пользователь отправляет форму подписки с невалидным email
- **THEN** отображается «Введите корректный email.» и фокус на инпут

#### Scenario: Contact-форма валидация

- **WHEN** пользователь отправляет пустую контактную форму
- **THEN** отображается «Проверьте заполнение полей формы.»
