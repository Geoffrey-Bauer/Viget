# 🌐 Viget | Web Hosting Platform

## 🚀 Présentation du Projet
Viget est un projet innovant d'hébergement web, conçu comme projet final pour l'obtention du diplôme de développeur web.

## 🔧 Architecture Technique
- **Backend**: Symfony
- **Frontend**: Node.js
- **Conteneurisation**: Docker
- **Gestionnaire de dépendances**: Composer & npm

## 📦 Installation Rapide

### Prérequis
- :computer: Docker
- :whale: Docker Compose
- :octocat: Git

### Étapes d'Installation

***# 1. Clonage du Dépôt 📂***
```bash
git clone git@github.com:Geoffrey-Bauer/Viget.git
```

***# 2. Démarrage des Conteneurs 🐳***
```bash
docker compose up --build
```


***# 3. Configuration du Projet 🛠️***

***## Dépendances PHP***
```bash
docker exec -it phpbviget composer install
```

***## Dépendances Node.js***
```bash
docker exec -it nodeviget sh
npm install
npm run watch
```

***# 4. Configuration Base de Données 💾***
```bash
docker exec -it phpviget php bin/console d:m:m
docker exec -it phpviget php bin:console d:f:l
```


***## 🌟 Fonctionnalités Clés***
- :rocket: Hébergement web moderne
- :sparkles: Interface utilisateur intuitive
- :package: Déploiement containerisé
- :chart_with_upwards_trend: Scalabilité et performance

***## 🔒 Sécurité***
- :shield: Isolation par conteneurs
- :lock: Gestion des dépendances sécurisée

***## 📊 Développement***
**Statut**: :mortar_board: Projet de diplôme
**Développeur**: Geoffrey Bauer

***## 🤝 Contribution***
Les contributions sont les bienvenues ! Merci de respecter les bonnes pratiques de développement.

---

🏆 **Un projet qui représente l'aboutissement d'un parcours de développement web**

