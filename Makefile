# =============================================================================
# Laravel Orchid Blog — Makefile (только Docker)
# =============================================================================
#
# Проект запускается ТОЛЬКО через Docker Compose (docker/docker-compose.yml).
# Локальные php/composer/npm/artisan на хосте не используются — все команды
# выполняются внутри контейнеров.

# ---------- Переменные -------------------------------------------------------

COMPOSE := docker compose -f docker/docker-compose.yml

# ---------- Цвета для вывода -------------------------------------------------

GREEN  := \033[32m
YELLOW := \033[33m
RED    := \033[31m
RESET  := \033[0m

# ---------- Общий блок: вывод ссылок на сервисы ------------------------------

# Печатает ссылки на сайт, админку и вспомогательные сервисы.
# Используется в up / setup / install, чтобы вывод оставался синхронным.
# Первый аргумент ($(1)) — заголовок блока (что именно готово).
define PRINT_SERVICE_LINKS
	@echo ""
	@echo "$(GREEN)✓ $(1)$(RESET)"
	@echo "$(GREEN)  Site:        http://localhost:8080$(RESET)"
	@echo "$(GREEN)  Admin:       http://localhost:8080/nexus$(RESET)"
	@echo "$(GREEN)  phpMyAdmin:  http://localhost:8899$(RESET)"
	@echo "$(GREEN)  MailHog:     http://localhost:8026$(RESET)"
endef

# ---------- Правила ----------------------------------------------------------

.PHONY: help
help: ## Показать справку по командам
	@echo ""
	@echo "$(GREEN)Laravel Orchid Blog — доступные команды (Docker):$(RESET)"
	@echo ""
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  $(YELLOW)%-25s$(RESET) %s\n", $$1, $$2}' $(MAKEFILE_LIST)
	@echo ""

.PHONY: env-copy
env-copy: ## Скопировать .env.example → .env (если .env ещё нет)
	@echo "$(GREEN)→ Copying .env.example to .env...$(RESET)"
	@if [ ! -f .env ]; then \
		cp .env.example .env; \
		echo "$(GREEN)  .env created.$(RESET)"; \
	else \
		echo "$(YELLOW)  .env already exists, skipping.$(RESET)"; \
	fi

.PHONY: install
install: env-copy up storage-perm setup ## Полная первоначальная установка в Docker (env-copy + up + setup)
	@echo ""

.PHONY: up
up: ## Собрать образы и запустить контейнеры (docker compose up -d --build)
	@echo "$(GREEN)→ Starting Docker containers...$(RESET)"
	$(COMPOSE) up -d --build
	$(call PRINT_SERVICE_LINKS,Containers are up!)

.PHONY: down
down: ## Остановить контейнеры (docker compose down)
	@echo "$(YELLOW)→ Stopping Docker containers...$(RESET)"
	$(COMPOSE) down

.PHONY: restart
restart: ## Перезапустить контейнеры (docker compose restart)
	@echo "$(GREEN)→ Restarting Docker containers...$(RESET)"
	$(COMPOSE) restart

.PHONY: setup
setup: composer-install key-generate migrate orchid-admin storage-link frontend-install frontend-build ## Полная установка в уже запущенный Docker (composer, ключ, миграции, админ, фронтенд)
	$(call PRINT_SERVICE_LINKS,Setup complete!)

.PHONY: logs
logs: ## Показать логи контейнеров (docker compose logs -f)
	$(COMPOSE) logs -f

.PHONY: shell
shell: ## Войти в контейнер app (bash)
	$(COMPOSE) exec app bash

.PHONY: storage-perm
storage-perm: ## Починить права на storage/ и bootstrap/cache для www-data
	@echo "$(GREEN)→ Fixing storage permissions for www-data...$(RESET)"
	$(COMPOSE) exec -u root app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

.PHONY: php
php: ## Открыть PHP REPL внутри контейнера app
	$(COMPOSE) exec app php

.PHONY: composer
composer: ## Запустить Composer внутри контейнера app
	$(COMPOSE) exec app composer

.PHONY: composer-install
composer-install: ## Установить PHP-зависимости (composer install)
	@echo "$(GREEN)→ Installing Composer dependencies...$(RESET)"
	$(COMPOSE) exec app composer install

.PHONY: key-generate
key-generate: ## Сгенерировать APP_KEY (php artisan key:generate)
	@echo "$(GREEN)→ Generating application key...$(RESET)"
	$(COMPOSE) exec app php artisan key:generate

.PHONY: migrate
migrate: ## Запустить миграции
	@echo "$(GREEN)→ Stopping queue workers...$(RESET)"
	$(COMPOSE) stop queue schedule
	@echo "$(GREEN)→ Running migrations...$(RESET)"
	$(COMPOSE) exec app php artisan migrate
	@echo "$(GREEN)→ Restarting queue workers...$(RESET)"
	$(COMPOSE) start queue schedule

.PHONY: migrate-fresh
migrate-fresh: ## Запустить миграции и сиды (migrate:fresh --seed; разрушает данные)
	@echo "$(GREEN)→ Stopping queue workers...$(RESET)"
	$(COMPOSE) stop queue schedule
	@echo "$(GREEN)→ Running migrations and seeders...$(RESET)"
	$(COMPOSE) exec app php artisan migrate:fresh --seed
	@echo "$(GREEN)→ Restarting queue workers...$(RESET)"
	$(COMPOSE) start queue schedule
	@echo "$(GREEN)→ Creating Orchid admin user...$(RESET)"
	$(COMPOSE) exec app php artisan orchid:admin alexchasx a.s.chasovnikov@yandex.ru 123456

.PHONY: orchid-admin
orchid-admin: ## Создать администратора Orchid (admin@localhost.ru / 123456)
	@echo "$(GREEN)→ Creating Orchid admin user...$(RESET)"
	$(COMPOSE) exec app php artisan orchid:admin admin admin@localhost.ru 123456

.PHONY: storage-link
storage-link: ## Создать симлинк для хранилища (php artisan storage:link)
	@echo "$(GREEN)→ Creating storage symlink...$(RESET)"
	$(COMPOSE) exec app php artisan storage:link

.PHONY: frontend-install
frontend-install: ## Установить JS-зависимости (npm install) внутри контейнера node
	@echo "$(GREEN)→ Installing NPM dependencies...$(RESET)"
	$(COMPOSE) exec node npm install

.PHONY: frontend-build
frontend-build: ## Собрать фронтенд для продакшена (npm run build)
	@echo "$(GREEN)→ Building frontend for production...$(RESET)"
	$(COMPOSE) exec node npm run build

.PHONY: frontend-dev
frontend-dev: ## Запустить Vite dev server внутри контейнера node (:5173)
	@echo "$(GREEN)→ Starting Vite dev server...$(RESET)"
	@echo "$(GREEN)  http://localhost:5173$(RESET)"
	$(COMPOSE) exec node npm run dev -- --host 0.0.0.0

.PHONY: optimize
optimize: ## Оптимизация Laravel (config, route, view cache)
	@echo "$(GREEN)→ Optimizing Laravel...$(RESET)"
	$(COMPOSE) exec app php artisan config:cache
	$(COMPOSE) exec app php artisan route:cache
	$(COMPOSE) exec app php artisan view:cache

.PHONY: clear
clear: ## Очистить кэш Laravel
	@echo "$(GREEN)→ Clearing Laravel cache...$(RESET)"
	$(COMPOSE) exec app php artisan config:clear
	$(COMPOSE) exec app php artisan route:clear
	$(COMPOSE) exec app php artisan view:clear
	$(COMPOSE) exec app php artisan cache:clear

.PHONY: test
test: ## Запустить тесты (php artisan test)
	@echo "$(GREEN)→ Running tests...$(RESET)"
	$(COMPOSE) exec app php artisan test

.PHONY: test-coverage
test-coverage: ## Запустить тесты с покрытием (phpunit --coverage-html=tests/coverage)
	@echo "$(GREEN)→ Running tests with coverage...$(RESET)"
	$(COMPOSE) exec app php artisan test --coverage-html=tests/coverage

.PHONY: ide-helper
ide-helper: ## Обновить IDE Helper (для IDE автодополнения)
	@echo "$(GREEN)→ Updating IDE Helper...$(RESET)"
	$(COMPOSE) exec app php artisan ide:model
	$(COMPOSE) exec app php artisan ide:optimize

.PHONY: lint
lint: ## Запустить PHP lint (php -l на всех .php файлах)
	@echo "$(GREEN)→ Running PHP lint...$(RESET)"
	$(COMPOSE) exec app sh -c 'find app database routes -name "*.php" -exec php -l {} \; 2>&1 | grep -v "No syntax errors" || true'

.PHONY: import-articles
import-articles: ## Импортировать статьи из _import_articles (php artisan articles:import $(ARGS))
	@echo "$(GREEN)→ Importing articles from _import_articles...$(RESET)"
	$(COMPOSE) exec app php artisan articles:import $(ARGS)

# =============================================================================
# Продакшн-цели (docker/docker-compose.prod.yml + docker/.env.prod)
# =============================================================================
#
# Перед первым запуском:
#   make prod-env        # скопирует docker/env.prod.example → docker/.env.prod
#   # заполнить docker/.env.prod реальными значениями (chmod 600 уже сделан)
#
# Все команды идут с --env-file docker/.env.prod — без него интерполяция
# ${DB_*} в prod-compose даст пустые значения (см. шапку prod-файла).

COMPOSE_PROD := docker compose -f docker/docker-compose.prod.yml --env-file docker/.env.prod

.PHONY: prod-check-env
prod-check-env: ## Проверить наличие docker/.env.prod
	@test -f docker/.env.prod || { echo "$(RED)docker/.env.prod не найден — выполните make prod-env и заполните файл.$(RESET)"; exit 1; }

.PHONY: prod-env
prod-env: ## Скопировать docker/env.prod.example → docker/.env.prod (если нет)
	@if [ ! -f docker/.env.prod ]; then \
		cp docker/env.prod.example docker/.env.prod; \
		chmod 600 docker/.env.prod; \
		echo "$(GREEN)→ docker/.env.prod создан — заполните его реальными значениями.$(RESET)"; \
	else \
		echo "$(YELLOW)  docker/.env.prod уже существует, пропускаю.$(RESET)"; \
	fi

.PHONY: prod-build
prod-build: prod-check-env ## Собрать продакшн-образы (app + nginx, multi-stage)
	$(COMPOSE_PROD) build

.PHONY: prod-up
prod-up: prod-check-env ## Собрать образы и поднять прод-стек (nginx, app, schedule, queue, db)
	$(COMPOSE_PROD) up -d --build

.PHONY: prod-down
prod-down: prod-check-env ## Остановить прод-стек (данные в volumes сохраняются)
	$(COMPOSE_PROD) down

.PHONY: prod-status
prod-status: prod-check-env ## Статус прод-контейнеров
	$(COMPOSE_PROD) ps

.PHONY: prod-logs
prod-logs: prod-check-env ## Логи прод-контейнеров (follow)
	$(COMPOSE_PROD) logs -f

.PHONY: prod-shell
prod-shell: prod-check-env ## Войти в контейнер app (bash)
	$(COMPOSE_PROD) exec app bash

.PHONY: prod-migrate
prod-migrate: prod-check-env ## Миграции в проде (migrate --force, НЕ migrate:fresh)
	$(COMPOSE_PROD) exec -T app php artisan migrate --force

.PHONY: prod-optimize
prod-optimize: prod-check-env ## Кэш конфигов и представлений в проде (без route:cache)
	$(COMPOSE_PROD) exec -T app php artisan config:cache
	$(COMPOSE_PROD) exec -T app php artisan view:cache

.PHONY: prod-backup
prod-backup: prod-check-env ## Бэкап БД и storage в /var/backups/blog (ротация 14 дней)
	@mkdir -p /var/backups/blog
	@$(COMPOSE_PROD) exec -T db sh -c 'exec mysqldump --single-transaction --quick -u"$$MYSQL_USER" -p"$$MYSQL_PASSWORD" "$$MYSQL_DATABASE"' | gzip > /var/backups/blog/db_$$(date +%F_%H-%M).sql.gz
	@find /var/backups/blog -name 'db_*.sql.gz' -mtime +14 -delete
	@echo "$(GREEN)→ Бэкап БД готов: /var/backups/blog/db_$$(ls /var/backups/blog | grep '^db_' | tail -1)$(RESET)"

.PHONY: db-dump
db-dump: ## Сохранить дамп БД в database_dump.sql (docker compose exec db mysqldump)
	@echo "$(GREEN)→ Dumping database to database_dump.sql...$(RESET)"
	$(COMPOSE) exec -T db mysqldump -u"root" -p"root" "laraorchid" > database_dump.sql
	@echo "$(GREEN)  Database dump saved: database_dump.sql$(RESET)"

.PHONY: db-restore
db-restore: ## Восстановить БД из database_dump.sql (разрушает данные!)
	@echo "$(YELLOW)→ Restoring database from database_dump.sql...$(RESET)"
	@echo "$(YELLOW)  WARNING: all data will be replaced!$(RESET)"
	$(COMPOSE) exec -T db mysql -u"root" -p"root" "laraorchid" < database_dump.sql
	@echo "$(GREEN)  Database restored from database_dump.sql$(RESET)"

.PHONY: prod-deploy
prod-deploy: prod-check-env ## Полный деплой: бэкап + git pull + сборка + миграции + кэш (./deploy.sh)
	@bash deploy.sh
