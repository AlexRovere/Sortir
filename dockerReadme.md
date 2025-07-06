# Application Symfony avec Vue.js - Configuration Docker

Cette application Symfony utilise Vue.js avec Vite pour le frontend, PostgreSQL comme base de données, et Docker pour l'environnement de développement.

## 🚀 Stack Technique

- **Backend**: Symfony 6.4 avec PHP 8.3
- **Frontend**: Vue.js 3 + Vite + Tailwind CSS
- **Base de données**: PostgreSQL 16
- **Outils**: Chart.js, Leaflet, Flowbite
- **Conteneurisation**: Docker + Docker Compose

## 📋 Prérequis

- Docker et Docker Compose installés
- Git
- Make (optionnel, pour les commandes simplifiées)

## 🛠️ Installation

### 1. Cloner le projet

```bash
git clone [URL_DE_VOTRE_REPO]
cd [NOM_DU_PROJET]
```

### 2. Configuration de l'environnement

Créez le fichier `.env` à partir du template :

```bash
cp .env.example .env
```

Modifiez les variables dans `.env` selon vos besoins :

```bash
# Environment
APP_ENV=dev
APP_SECRET=votre-clé-secrète-ici

# Database
DATABASE_URL=postgresql://symfony:symfony@postgres:5432/sortir

# Mailer
MAILER_DSV=null://null
```

### 3. Démarrage avec Make (recommandé)

Installation complète en une commande :

```bash
make install
```

Cette commande va :

- Construire les images Docker
- Démarrer tous les services
- Créer la base de données
- Installer les dépendances PHP et Node.js
- Exécuter les migrations

### 4. Démarrage manuel (alternative)

Si vous n'avez pas Make :

```bash
# Construire les images
docker-compose build

# Démarrer les services
docker-compose up -d

# Créer la base de données
docker exec -it sortir php bin/console doctrine:database:create

# Installer les dépendances
docker exec -it sortir composer install
docker exec -it sortir npm install

# Exécuter les migrations
docker exec -it sortir php bin/console doctrine:migrations:migrate -n
```

## 🌐 Accès à l'application

Une fois les services démarrés, vous pouvez accéder à :

- **Application principale** : [http://localhost](http://localhost)
- **PgAdmin (gestion BDD)** : [http://localhost:8181](http://localhost:8181)
- **Serveur Vite (dev)** : [http://localhost:5173](http://localhost:5173)

### Login PGAdmin

- **Login** : `admin@admin.com`
- **Password** : `password`

### Connexion au serveur PGadmin

- **Serveur** : `postgres`
- **Utilisateur** : `symfony`
- **Mot de passe** : `symfony`
- **Base de données** : `sortir`

## 📝 Commandes utiles

### Gestion des conteneurs

```bash
# Démarrer tous les services
make up

# Arrêter tous les services
make down

# Redémarrer les services
make restart

# Voir les logs
make logs

# Nettoyer complètement
make clean
```

### Commandes Symfony

```bash
# Accéder à la console Symfony
make symfony-console ARGS="cache:clear"

# Vider le cache
make symfony-cache-clear

# Exécuter les migrations
make symfony-migrate

# Charger les fixtures
make symfony-fixtures

# Accéder au shell du conteneur
make shell
```

### Gestion de la base de données

```bash
# Créer la base de données
make db-create

# Supprimer la base de données
make db-drop

# Réinitialiser complètement la BDD
make db-reset
```

### Frontend (Node.js/Vite)

```bash
# Installer les dépendances Node.js
make npm-install

# Lancer le serveur de développement
make npm-dev

# Build pour la production
make npm-build
```

## 🔧 Développement

### Hot Reload

Le hot reload est configuré pour :

- Les fichiers Vue.js
- Les fichiers PHP/Twig (rechargement complet)
- Les styles CSS/Tailwind

### Accès aux shells

```bash
# Shell du conteneur Symfony
make shell

# Shell PostgreSQL
make shell-postgres

# Ou directement avec Docker
docker exec -it sortir bash
docker exec -it symfony_postgres psql -U symfony -d sortir
```

### Commandes Symfony communes

```bash
# Créer un contrôleur
docker exec -it sortir php bin/console make:controller

# Créer une entité
docker exec -it sortir php bin/console make:entity

# Créer une migration
docker exec -it sortir php bin/console make:migration

# Créer un formulaire
docker exec -it sortir php bin/console make:form
```

## 🐛 Dépannage

### Problèmes courants

**Les conteneurs ne démarrent pas :**

```bash
# Vérifier les logs
make logs

# Reconstruire les images
make build
```

**Problèmes de permissions :**

```bash
# Corriger les permissions
sudo chown -R $USER:$USER ./
chmod -R 755 var/
```

**Base de données non accessible :**

```bash
# Vérifier que PostgreSQL est démarré
docker-compose ps

# Recréer la base de données
make db-reset
```

**Assets non compilés :**

```bash
# Vérifier que le serveur Vite fonctionne
docker-compose logs vite

# Relancer le build
make npm-build
```

### Commandes de diagnostic

```bash
# Statut des conteneurs
docker-compose ps

# Logs spécifiques
docker-compose logs [nom_service]

# Ressources utilisées
docker stats

# Nettoyer les images inutilisées
docker system prune -f
```

## 🚀 Déploiement

### Build pour la production

```bash
# Construire les assets
make npm-build

# Optimiser l'autoloader
docker exec -it sortir composer dump-autoload --optimize

# Vider le cache de production
docker exec -it sortir php bin/console cache:clear --env=prod
```

### Variables d'environnement pour la production

Modifiez `.env` pour la production :

```bash
APP_ENV=prod
APP_DEBUG=false
DATABASE_URL=postgresql://user:password@host:5432/database
```

## 📚 Documentation

- [Symfony Documentation](https://symfony.com/doc)
- [Vue.js Documentation](https://vuejs.org/guide/)
- [Vite Documentation](https://vitejs.dev/guide/)
- [Docker Documentation](https://docs.docker.com/)

## 🤝 Contribution

1. Forkez le projet
2. Créez une branche pour votre fonctionnalité
3. Commitez vos changements
4. Poussez vers la branche
5. Ouvrez une Pull Request

## 📄 License

Ce projet est sous licence [MIT](LICENSE).

---

**Bon développement ! 🎉**
