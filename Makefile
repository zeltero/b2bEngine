# Makefile for B2B Engine Docker Management
# Common commands for managing the Docker environment

.PHONY: help build up down restart logs shell mysql clean backup

# Colors for terminal output
BLUE := \033[0;34m
GREEN := \033[0;32m
YELLOW := \033[0;33m
RED := \033[0;31m
NC := \033[0m # No Color

help: ## Show this help message
	@echo "$(BLUE)B2B Engine Docker Management$(NC)"
	@echo ""
	@echo "$(GREEN)Available commands:$(NC)"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(YELLOW)%-15s$(NC) %s\n", $$1, $$2}'

setup: ## Run initial setup (creates .env and .htpasswd)
	@bash setup-docker.sh

build: ## Build Docker images
	@echo "$(BLUE)Building Docker images...$(NC)"
	@docker compose build

up: ## Start all containers
	@echo "$(GREEN)Starting containers...$(NC)"
	@docker compose up -d
	@echo "$(GREEN)Containers started!$(NC)"
	@docker compose ps

down: ## Stop all containers
	@echo "$(YELLOW)Stopping containers...$(NC)"
	@docker compose down
	@echo "$(GREEN)Containers stopped!$(NC)"

restart: ## Restart all containers
	@echo "$(YELLOW)Restarting containers...$(NC)"
	@docker compose restart
	@echo "$(GREEN)Containers restarted!$(NC)"

logs: ## View logs from all containers
	@docker compose logs -f

logs-php: ## View PHP-FPM logs
	@docker compose logs -f php-fpm

logs-nginx: ## View Nginx logs
	@docker compose logs -f nginx

logs-mysql: ## View MySQL logs
	@docker compose logs -f mysql

shell: ## Open bash shell in PHP container
	@docker compose exec php-fpm bash

shell-nginx: ## Open shell in Nginx container
	@docker compose exec nginx sh

mysql: ## Connect to MySQL CLI
	@docker compose exec mysql mysql -u root -p

status: ## Show container status
	@docker compose ps

install-magento: ## Install Magento (interactive)
	@echo "$(BLUE)Starting Magento installation...$(NC)"
	@echo "$(YELLOW)Make sure you have configured .env file!$(NC)"
	@docker compose exec php-fpm bash -c "php bin/magento setup:install \
		--base-url=$${MAGENTO_BASE_URL:-http://localhost} \
		--db-host=mysql \
		--db-name=$${MYSQL_DATABASE:-magento_b2b} \
		--db-user=$${MYSQL_USER:-magento} \
		--db-password=$${MYSQL_PASSWORD} \
		--admin-firstname=$${MAGENTO_ADMIN_FIRSTNAME:-Admin} \
		--admin-lastname=$${MAGENTO_ADMIN_LASTNAME:-User} \
		--admin-email=$${MAGENTO_ADMIN_EMAIL:-admin@example.com} \
		--admin-user=$${MAGENTO_ADMIN_USER:-admin} \
		--admin-password=$${MAGENTO_ADMIN_PASSWORD} \
		--language=en_US \
		--currency=USD \
		--timezone=America/Chicago \
		--use-rewrites=1 \
		--search-engine=elasticsearch8 \
		--elasticsearch-host=elasticsearch \
		--elasticsearch-port=9200"

enable-b2b: ## Enable B2B module
	@echo "$(BLUE)Enabling B2B module...$(NC)"
	@docker compose exec php-fpm bash -c "php bin/magento module:enable Zeltero_B2B && \
		php bin/magento setup:upgrade && \
		php bin/magento setup:di:compile && \
		php bin/magento setup:static-content:deploy -f && \
		php bin/magento cache:flush"
	@echo "$(GREEN)B2B module enabled!$(NC)"

cache-flush: ## Flush Magento cache
	@docker compose exec php-fpm php bin/magento cache:flush

cache-clean: ## Clean Magento cache
	@docker compose exec php-fpm php bin/magento cache:clean

reindex: ## Reindex Magento
	@docker compose exec php-fpm php bin/magento indexer:reindex

upgrade: ## Run Magento upgrade
	@docker compose exec php-fpm bash -c "php bin/magento setup:upgrade && \
		php bin/magento setup:di:compile && \
		php bin/magento cache:flush"

deploy-static: ## Deploy static content
	@docker compose exec php-fpm php bin/magento setup:static-content:deploy -f en_US

production-mode: ## Set production mode
	@docker compose exec php-fpm php bin/magento deploy:mode:set production

developer-mode: ## Set developer mode
	@docker compose exec php-fpm php bin/magento deploy:mode:set developer

backup-db: ## Backup database to backups/ directory
	@mkdir -p backups
	@echo "$(BLUE)Creating database backup...$(NC)"
	@docker compose exec mysql mysqldump -u root -p$${MYSQL_ROOT_PASSWORD} $${MYSQL_DATABASE:-magento_b2b} > backups/backup-$$(date +%Y%m%d-%H%M%S).sql
	@echo "$(GREEN)Backup created in backups/ directory$(NC)"

restore-db: ## Restore database from backup (specify file: make restore-db FILE=backup.sql)
	@if [ -z "$(FILE)" ]; then \
		echo "$(RED)Please specify backup file: make restore-db FILE=backup.sql$(NC)"; \
		exit 1; \
	fi
	@echo "$(YELLOW)Restoring database from $(FILE)...$(NC)"
	@docker compose exec -T mysql mysql -u root -p$${MYSQL_ROOT_PASSWORD} $${MYSQL_DATABASE:-magento_b2b} < $(FILE)
	@echo "$(GREEN)Database restored!$(NC)"

clean: ## Stop containers and remove volumes (WARNING: deletes data!)
	@echo "$(RED)WARNING: This will delete all data including database!$(NC)"
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		docker compose down -v; \
		echo "$(GREEN)Cleaned up!$(NC)"; \
	else \
		echo "$(YELLOW)Cancelled.$(NC)"; \
	fi

update-password: ## Update HTTP Basic Auth password
	@echo "$(BLUE)Updating HTTP Basic Auth password...$(NC)"
	@read -p "Enter username (default: b2badmin): " user; \
	user=$${user:-b2badmin}; \
	read -sp "Enter new password: " pass; \
	echo; \
	echo "$$user:$$(openssl passwd -apr1 $$pass)" > docker/nginx/.htpasswd
	@docker compose restart nginx
	@echo "$(GREEN)Password updated!$(NC)"

prod-up: ## Start in production mode with resource limits
	@echo "$(GREEN)Starting in production mode...$(NC)"
	@docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
	@docker compose ps

prod-logs: ## View production logs
	@docker compose -f docker-compose.yml -f docker-compose.prod.yml logs -f

health-check: ## Check health of all services
	@echo "$(BLUE)Checking service health...$(NC)"
	@echo ""
	@echo "$(YELLOW)Nginx:$(NC)"
	@curl -s -o /dev/null -w "%{http_code}" http://localhost/health && echo " - OK" || echo " - FAILED"
	@echo ""
	@echo "$(YELLOW)Elasticsearch:$(NC)"
	@curl -s http://localhost:9200/_cluster/health?pretty | grep status || echo "FAILED"
	@echo ""
	@echo "$(YELLOW)MySQL:$(NC)"
	@docker compose exec mysql mysqladmin ping -u root -p$${MYSQL_ROOT_PASSWORD} || echo "FAILED"
	@echo ""
	@echo "$(YELLOW)Redis:$(NC)"
	@docker compose exec redis redis-cli ping || echo "FAILED"
