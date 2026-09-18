# =============================================================================
# Laravel Orchid Blog — Makefile
# =============================================================================

# ---------- Переменные -------------------------------------------------------

PHP       ?= php
COMPOSER  ?= composer
NPM       ?= npm
ARTISAN   ?= php artisan
DB_HOST   ?= 127.0.0.1
DB_PORT   ?= 3306
DB_NAME   ?= larblog
DB_USER   ?= wwwuser
DB_PASS   ?= Password+12

# ---------- Цвета для вывода -------------------------------------------------

GREEN  := \033[32m
YELLOW := \033[33m
RED    := \033[31m
RESET  := \033[0m

# ---------- Правила ----------------------------------------------------------

.PHONY: help
help: ## Показать справку по командам
	@echo ""
	@echo "$(GREEN)Laravel Orchid Blog — доступные команды:$(RESET)"
	@echo ""
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  $(YELLOW)%-25s$(RESET) %s\n", $$1, $$2}' $(MAKEFILE_LIST)
	@echo ""

.PHONY: install
install: install-composer install-npm env-copy key-generate storage-link ## Полная первоначальная установка

.PHONY: install-composer
install-composer: ## Установить PHP-зависимости (composer install)
	@echo "$(GREEN)→ Installing Composer dependencies...$(RESET)"
	$(COMPOSER) install

.PHONY: install-npm
install-npm: ## Установить JS-зависимости (npm install)
	@echo "$(GREEN)→ Installing NPM dependencies...$(RESET)"
	$(NPM) install

.PHONY: env-copy
env-copy: ## Скопировать .env.example → .env
	@echo "$(GREEN)→ Copying .env.example to .env...$(RESET)"
	@if [ ! -f .env ]; then \
		cp .env.example .env; \
		echo "$(GREEN)  .env created.$(RESET)"; \
	else \
		echo "$(YELLOW)  .env already exists, skipping.$(RESET)"; \
	fi

.PHONY: key-generate
key-generate: ## Сгенерировать APP_KEY (php artisan key:generate)
	@echo "$(GREEN)→ Generating application key...$(RESET)"
	$(ARTISAN) key:generate

.PHONY: db-create
db-create: ## Создать базу данных (если не существует)
	@echo "$(GREEN)→ Creating database '$(DB_NAME)'...$(RESET)"
	@mysql -u"$(DB_USER)" -p"$(DB_PASS)" -h"$(DB_HOST)" -P$(DB_PORT) -e "CREATE DATABASE IF NOT EXISTS \`$(DB_NAME)\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || \
	echo "$(YELLOW)  Could not create database. Check MySQL credentials.$(RESET)"

.PHONY: migrate
migrate: ## Запустить миграции и сиды (php artisan migrate:fresh --seed)
	@echo "$(GREEN)→ Running migrations and seeders...$(RESET)"
	$(ARTISAN) migrate:fresh --seed

.PHONY: storage-link
storage-link: ## Создать symlink для хранилища (php artisan storage:link)
	@echo "$(GREEN)→ Creating storage symlink...$(RESET)"
	$(ARTISAN) storage:link

.PHONY: serve
serve: ## Запустить PHP development server (php artisan serve)
	@echo "$(GREEN)→ Starting Laravel development server...$(RESET)"
	@echo "$(GREEN)  http://localhost:8000$(RESET)"
	@echo "$(GREEN)  http://localhost:8000/admin$(RESET)"
	$(ARTISAN) serve --host=127.0.0.1 --port=8000

.PHONY: serve-bg
serve-bg: ## Запустить PHP development server в фоне
	@echo "$(GREEN)→ Starting Laravel development server in background...$(RESET)"
	$(ARTISAN) serve --host=127.0.0.1 --port=8000 &
	@echo "$(GREEN)  http://localhost:8000$(RESET)"

.PHONY: frontend-dev
frontend-dev: ## Запустить Vite dev server (npm run dev)
	@echo "$(GREEN)→ Starting Vite dev server...$(RESET)"
	$(NPM) run dev

.PHONY: frontend-build
frontend-build: ## Собрать фронтенд для продакшена (npm run build)
	@echo "$(GREEN)→ Building frontend for production...$(RESET)"
	$(NPM) run build

.PHONY: orchid-admin
orchid-admin: ## Создать администратора Orchid (пароль по умолчанию: 123456)
	@echo "$(GREEN)→ Creating Orchid admin user...$(RESET)"
	$(ARTISAN) orchid:admin admin admin@localhost.ru 123456

.PHONY: optimize
optimize: ## Оптимизация Laravel (config, route, view cache)
	@echo "$(GREEN)→ Optimizing Laravel...$(RESET)"
	$(ARTISAN) config:cache
	$(ARTISAN) route:cache
	$(ARTISAN) view:cache

.PHONY: clear
clear: ## Очистить кэш Laravel
	@echo "$(GREEN)→ Clearing Laravel cache...$(RESET)"
	$(ARTISAN) config:clear
	$(ARTISAN) route:clear
	$(ARTISAN) view:clear
	$(ARTISAN) cache:clear

.PHONY: test
test: ## Запустить тесты (phpunit)
	@echo "$(GREEN)→ Running tests...$(RESET)"
	$(ARTISAN) test

.PHONY: test-coverage
test-coverage: ## Запустить тесты с покрытием (phpunit --coverage-html=tests/coverage)
	@echo "$(GREEN)→ Running tests with coverage...$(RESET)"
	$(ARTISAN) test --coverage-html=tests/coverage

.PHONY: ide-helper
ide-helper: ## Обновить IDE Helper (для IDE автодополнения)
	@echo "$(GREEN)→ Updating IDE Helper...$(RESET)"
	$(ARTISAN) ide:model
	$(ARTISAN) ide:optimize

.PHONY: docker-up
docker-up: ## Запустить Docker (docker compose up -d --build)
	@echo "$(GREEN)→ Starting Docker containers...$(RESET)"
	docker compose -f docker/docker-compose.yml up -d --build

.PHONY: docker-down
docker-down: ## Остановить Docker (docker compose down)
	@echo "$(YELLOW)→ Stopping Docker containers...$(RESET)"
	docker compose -f docker/docker-compose.yml down

.PHONY: docker-logs
docker-logs: ## Показать логи Docker (docker compose logs -f)
	docker compose -f docker/docker-compose.yml logs -f

.PHONY: docker-shell
docker-shell: ## Войти в контейнер app (docker compose exec app bash)
	docker compose -f docker/docker-compose.yml exec app bash

.PHONY: docker-php
docker-php: ## Запустить PHP внутри контейнера app
	docker compose -f docker/docker-compose.yml exec app php

.PHONY: docker-composer
docker-composer: ## Запустить Composer внутри контейнера app
	docker compose -f docker/docker-compose.yml exec app composer

.PHONY: docker-migrate
docker-migrate: ## Запустить миграции в Docker
	@echo "$(GREEN)→ Running migrations in Docker...$(RESET)"
	docker compose -f docker/docker-compose.yml exec app php artisan migrate:fresh --seed

.PHONY: docker-key
docker-key: ## Сгенерировать APP_KEY в Docker
	docker compose -f docker/docker-compose.yml exec app php artisan key:generate

.PHONY: docker-orchid-admin
docker-orchid-admin: ## Создать админа Orchid в Docker (пароль: 123456)
	docker compose -f docker/docker-compose.yml exec app php artisan orchid:admin admin admin@localhost.ru 123456

.PHONY: docker-storage-link
docker-storage-link: ## Создать symlink storage в Docker
	docker compose -f docker/docker-compose.yml exec app php artisan storage:link

.PHONY: docker-install
docker-install: docker-up docker-composer-install docker-key docker-migrate docker-orchid-admin docker-storage-link ## Полная установка в Docker
	@echo ""
	@echo "$(GREEN)✓ Docker setup complete!$(RESET)"
	@echo "$(GREEN)  Site:     http://localhost:8080$(RESET)"
	@echo "$(GREEN)  Admin:    http://localhost:8080/admin$(RESET)"
	@echo "$(GREEN)  phpMyAdmin: http://localhost:8899$(RESET)"

.PHONY: docker-composer-install
docker-composer-install:
	@echo "$(GREEN)→ Installing Composer dependencies in Docker...$(RESET)"
	docker compose -f docker/docker-compose.yml exec app composer install

.PHONY: lint
lint: ## Запустить PHP lint (php -l на всех .php файлах)
	@echo "$(GREEN)→ Running PHP lint...$(RESET)"
	@find app database routes -name '*.php' -exec php -l {} \; 2>&1 | grep -v "No syntax errors"

.PHONY: all
all: install db-create migrate serve ## Установить всё, создать БД, запустить миграции и сервер
