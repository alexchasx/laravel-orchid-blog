Готово. Создан скрипт подготовки сервера и описана полная процедура деплоя.

## Созданные/изменённые файлы

### 1. [`provision-server.sh`](provision-server.sh) — подготовка Ubuntu 24.04 LTS (VDS/VPS)

Идемпотентный bash-скрипт (проверен `bash -n`, исполняемый), который на чистом сервере ставит всё необходимое **перед** первым деплоем:

1. Обновление ОС + базовые пакеты (git, curl, rsync, jq, openssh-server…);
2. Часовой пояс `Europe/Moscow` (совпадает с `config/app.php`);
3. swap-файл (по умолчанию 2 ГБ, `vm.swappiness=10`);
4. пользователь деплоя (`sudo` + `docker` группы), пароль заблокирован, вход только по ключу;
5. ужесточение sshd: `PermitRootLogin no`, `PasswordAuthentication no`, `AllowUsers deploy` (шаг безопасно пропускается, если не передан `--ssh-key`);
6. Docker Engine + Compose plugin из официального репозитория Docker;
7. firewall ufw: только SSH/80/443 (порт SSH определяется автоматически через `sshd -T`);
8. опционально fail2ban (`--with-fail2ban`) и unattended-upgrades (`--with-unattended`);
9. каталог бэкапов `/var/backups/blog` (владелец — пользователь деплоя).

Запуск: `sudo bash provision-server.sh --user deploy --ssh-key "ssh-ed25519 AAAA... your@host" --with-fail2ban --with-unattended`. В конце выводит сводку и предупреждает о необходимости reboot при обновлении ядра.

### 2. [`README.md`](README.md:285) — новый раздел «🚀 Деплой на продакшн (Ubuntu 24.04 VPS)»

Алгоритм из 7 шагов:
- **Шаг 1.** Подготовка сервера скриптом (таблица шагов + флаги);
- **Шаг 2.** Домен и DNS (A-запись, почтовые MX/SPF/DKIM/DMARC);
- **Шаг 3.** TLS: вариант A (рекомендуемый — хостовый nginx + certbot, с готовым конфигом `proxy_pass http://127.0.0.1:8080` и правкой порта в compose), вариант B (TLS в контейнере), вариант C (Cloudflare);
- **Шаг 4.** Первый деплой: клонирование в `/opt/blog`, `make prod-env`, заполнение `docker/.env.prod`, генерация `APP_KEY` на хосте (`openssl rand` — т.к. `key:generate` в контейнере пишет в неперсистентный `.env` образа), `make prod-up`, `make prod-migrate`, создание админа Orchid, `make prod-optimize`, healthcheck `/up`;
- **Шаг 5.** Последующие деплои: [`deploy.sh`](deploy.sh:1) / `make prod-deploy`;
- **Шаг 6.** Очередь/планировщик и бэкапы (cron-дамп БД — ссылка на план, раздел 9);
- **Шаг 7.** Финальный smoke-тест (ссылка на чек-лист плана, раздел 12).

### 3. [`AGENTS.md`](AGENTS.md:39) — упоминание скрипта в разделе «Команды»

Добавлена строка о [`provision-server.sh`](provision-server.sh) и указатель на README, чтобы последующие сессии знали о скрипте.

## Особенности реализации

- Скрипт безопасен для повторного запуска: каждый шаг проверяет уже выполненное состояние (swap, пользователь, Docker, правила ufw).
- Предусмотрена защита от самоблокировки: без SSH-ключа sshd не ужесточается, а с ключом конфиг валидируется через `sshd -t` перед применением.
- Прод-стек полностью соответствует текущей архитектуре: multi-stage [`Dockerfile.prod`](docker/app/Dockerfile.prod) собирает фронтенд внутрь образа, поэтому на сервере достаточно Docker + кода; `storage:link` уже выполняется при сборке, `storage/` живёт в named volume `app_storage`.
