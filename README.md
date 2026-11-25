# api-evenements-campus

## Description

`api-evenements-campus` est une API REST Laravel 13 dédiée à la gestion des événements et activités des clubs étudiants sur le campus. Les visiteurs peuvent consulter les événements publiés, filtrer par club ou par thème et voir le nombre d'événements par club. Les organisateurs authentifiés par jeton Sanctum peuvent publier, modifier ou supprimer leurs propres événements.

## Les quatre rôles

| Rôle | Modèle | Équivalent | Description |
|---|---|---|---|
| Propriétaire | `User` | Organisateur | Utilisateur authentifié qui crée et gère ses événements (`hasMany`) |
| Ressource principale | `Event` | Événement | Activité sur le campus avec titre, slug, résumé, description, lieu, `max_seats`, `starts_at` et `is_published` |
| Ressource parente | `Club` | Club organisateur | Entité organisatrice (`name`, `slug` unique), exposée en lecture seule avec `events_count` |
| Étiquette | `Theme` | Thème | Mot-clé thématique lié en N:N aux événements via `event_theme` |

## Installation

### Bash

```bash
git clone https://github.com/yassinebouazizi-dev/api-evenements-campus.git
cd api-evenements-campus
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan test
php artisan serve
```

### PowerShell

```powershell
git clone https://github.com/yassinebouazizi-dev/api-evenements-campus.git
cd api-evenements-campus
composer install
copy .env.example .env
php artisan key:generate
New-Item -ItemType File -Force database\database.sqlite
php artisan migrate:fresh --seed
php artisan test
php artisan serve
```

L'API est accessible sur `http://localhost:8000`.

## Comptes de test

Le seeder crée 3 utilisateurs (tous avec le mot de passe `password`) :

| Rôle | E-mail | Mot de passe |
|---|---|---|
| Organisateur 1 | `test@example.com` | `password` |
| Organisateur 2 & 3 | 2 comptes générés par `UserFactory` | `password` |

## Endpoints

Toutes les requêtes doivent envoyer l'en-tête `Accept: application/json`.

| Méthode | URI | Accès | Description |
|---|---|---|---|
| POST | `/api/login` | Public | Authentifie un utilisateur et renvoie un jeton Bearer (`201`) |
| POST | `/api/logout` | `auth:sanctum` | Révoque le jeton courant (`204`) |
| GET | `/api/user` | `auth:sanctum` | Renvoie le profil de l'utilisateur connecté (`200`) |
| GET | `/api/events` | Public | Liste paginée des événements publiés (`?per_page=`, `?club=<slug>`, `?theme=<slug>`) |
| GET | `/api/events/{event}` | Public | Détail d'un événement (`404` si inexistant) |
| POST | `/api/events` | `auth:sanctum` | Crée un événement pour l'utilisateur connecté et synchronise ses thèmes (`201`) |
| PUT/PATCH | `/api/events/{event}` | `auth:sanctum` + `EventPolicy` | Met à jour un événement (propriétaire uniquement) |
| DELETE | `/api/events/{event}` | `auth:sanctum` + `EventPolicy` | Supprime un événement (`204`, propriétaire uniquement) |
| GET | `/api/clubs` | Public | Liste des clubs avec `events_count` (événements publiés uniquement) |
| GET | `/api/clubs/{club}` | Public | Détail d'un club avec `events_count` (`404` si inexistant) |

## Authentification par jeton

1. Appeler `POST /api/login` avec `{"email": "test@example.com", "password": "password"}`.
2. L'API renvoie `201 Created` avec `token` et `token_type: "Bearer"`.
3. Envoyer `Authorization: Bearer VOTRE_JETON` sur les routes protégées.
4. Appeler `POST /api/logout` pour supprimer le jeton courant.

## Codes d'erreur

| Code | Signification |
|---|---|
| `401` | Jeton absent, invalide ou révoqué (`{"message": "Unauthenticated."}`) |
| `403` | Action refusée par `EventPolicy` (modification ou suppression d'un événement appartenant à un autre utilisateur) |
| `404` | Ressource introuvable en JSON |
| `422` | Données invalides ou identifiants incorrects (messages en français) |

## Exemple curl.exe

Créer un événement avec un jeton Bearer :

```powershell
curl.exe -X POST -H "Accept: application/json" -H "Content-Type: application/json" -H "Authorization: Bearer VOTRE_JETON" -d "{\"club_id\":1,\"title\":\"Atelier Docker\",\"slug\":\"atelier-docker\",\"summary\":\"Initiation aux conteneurs.\",\"description\":\"Atelier pratique de 2h en salle B12.\",\"location\":\"Salle B12\",\"max_seats\":30,\"starts_at\":\"2026-12-15 14:00:00\",\"is_published\":true,\"themes\":[1,2]}" http://localhost:8000/api/events
```

## Collection Postman

La collection exportée au format v2.1 se trouve dans `docs/postman/api-evenements-campus.postman_collection.json`. Elle définit les variables `base_url` (`http://localhost:8000`) et `token` (vide par défaut) ; la requête **Connexion** enregistre automatiquement le jeton reçu dans `token` pour les requêtes suivantes.

## Choix d'architecture

### Pourquoi la ressource principale appartient-elle à l'utilisateur, et que protège la Policy ?

L'événement (`Event`) est la ressource créée et gérée par un organisateur de club : c'est donc lui qui porte `user_id`. Dans le contexte du campus, `EventPolicy` empêche qu'un étudiant ou un autre club puisse modifier le lieu, réduire le nombre de places (`max_seats`) ou supprimer l'événement d'un autre organisateur.

### À quel besoin réel d'un utilisateur de l'API répond le filtre ?

Un étudiant qui consulte le calendrier du campus cherche souvent les activités d'un club précis (par exemple : « quels événements organise le Club Robotique ? ») sans avoir à parcourir toutes les pages de `/api/events`. Le filtre `?club=<slug>` répond directement à ce besoin avec une comparaison exacte du slug validée par `IndexEventRequest`.

### Quelle requête produirait un problème N+1 sans with(), et comment l'avez-vous évité ?

Sur `GET /api/events`, `EventResource` expose l'organisateur (`user`), le club (`club`) et les thèmes (`themes`) de chaque événement paginé : sans `with('user', 'club', 'themes')`, chaque élément de la page déclencherait des requêtes SQL supplémentaires. J'ai évité cela en chargeant ces trois relations avec `with()` dans `EventController@index` et en activant `Model::preventLazyLoading(! app()->isProduction())` dans `AppServiceProvider`, ce qui lève immédiatement une exception si un chargement anticipé est oublié.
