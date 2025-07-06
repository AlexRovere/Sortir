# Variables
DOCKER_COMPOSE = docker-compose
DOCKER_EXEC = docker exec -it
SYMFONY_CONTAINER = sortir
POSTGRES_CONTAINER = symfony_postgres

# Commandes de base
.PHONY: help build up down restart logs

help: ## Affiche l'aide
	@echo "Commandes disponibles:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}'

build: ## Construit les conteneurs
	$(DOCKER_COMPOSE) build

up: ## Démarre les conteneurs
	$(DOCKER_COMPOSE) up -d

down: ## Arrête les conteneurs
	$(DOCKER_COMPOSE) down

restart: ## Redémarre les conteneurs
	$(DOCKER_COMPOSE) restart

logs: ## Affiche les logs
	$(DOCKER_COMPOSE) logs -f

# Commandes Symfony
.PHONY: symfony-install symfony-console symfony-cache-clear symfony-migrate symfony-fixtures

symfony-install: ## Installe les dépendances Symfony
	$(DOCKER_EXEC) $(SYMFONY_CONTAINER) composer install
	$(DOCKER_EXEC) $(SYMFONY_CONTAINER) npm install

symfony-console: ## Accède à la console Symfony
	$(DOCKER_EXEC) $(SYMFONY_CONTAINER) php bin/console $(ARGS)

symfony-cache-clear: ## Vide le cache Symfony
	$(DOCKER_EXEC) $(SYMFONY_CONTAINER) php bin/console cache:clear

symfony-migrate: ## Exécute les migrations
	$(DOCKER_EXEC) $(SYMFONY_CONTAINER) php bin/console doctrine:migrations:migrate -n

symfony-fixtures: ## Charge les fixtures
	$(DOCKER_EXEC) $(SYMFONY_CONTAINER) php bin/console doctrine:fixtures:load -n

# Commandes base de données
.PHONY: db-create db-drop db-reset

db-create: ## Crée la base de données
	$(DOCKER_EXEC) $(SYMFONY_CONTAINER) php bin/console doctrine:database:create --if-not-exists

db-drop: ## Supprime la base de données
	$(DOCKER_EXEC) $(SYMFONY_CONTAINER) php bin/console doctrine:database:drop --force --if-exists

db-reset: ## Remet à zéro la base de données
	$(DOCKER_EXEC) $(SYMFONY_CONTAINER) php bin/console doctrine:database:drop --force --if-exists
	$(DOCKER_EXEC) $(SYMFONY_CONTAINER) php bin/console doctrine:database:create
	$(DOCKER_EXEC) $(SYMFONY_CONTAINER) php bin/console doctrine:migrations:migrate -n
	$(DOCKER_EXEC) $(SYMFONY_CONTAINER) php bin/console doctrine:fixtures:load -n

# Commandes Node.js/Vite
.PHONY: npm-install npm-dev npm-build npm-watch

npm-install: ## Installe les dépendances Node.js
	$(DOCKER_EXEC) $(SYMFONY_CONTAINER) npm install

npm-dev: ## Lance le serveur de développement Vite
	$(DOCKER_EXEC) symfony_vite npm run dev

npm-build: ## Build les assets pour la production
	$(DOCKER_EXEC) symfony_vite npm run build

npm-watch: ## Lance le watch des assets
	$(DOCKER_EXEC) symfony_vite npm run dev

# Commandes utiles
.PHONY: shell shell-postgres clean

shell: ## Accède au shell du conteneur Symfony
	$(DOCKER_EXEC) $(SYMFONY_CONTAINER) bash

shell-postgres: ## Accède au shell PostgreSQL
	$(DOCKER_EXEC) $(POSTGRES_CONTAINER) psql -U symfony -d sortir

clean: ## Nettoie les conteneurs et volumes
	$(DOCKER_COMPOSE) down -v --remove-orphans
	docker system prune -f

fixtures: ## Charge les fixtures
	$(DOCKER_EXEC) $(SYMFONY_CONTAINER) php bin/console doctrine:fixtures:load --no-interaction

# Installation complète
.PHONY: install

install: build up db-create symfony-install symfony-migrate ## Installation complète du projet
	@echo "Installation terminée!"
	@echo "Application accessible sur: http://localhost"
	@echo "PGAdmin accessible sur: http://localhost:8181"
	@echo "Vite dev server: http://localhost:5173"