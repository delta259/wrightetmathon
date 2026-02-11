# Application Mobile Inventaire - Documentation Technique

## Vue d'ensemble

Application mobile Flutter connectée au POS Wright et Mathon pour réaliser des inventaires (complet ou tournant). Communique avec le backend via une API REST avec authentification JWT.

---

## Architecture

```
┌─────────────────────┐         ┌─────────────────────────────────────┐
│   Application       │         │         Serveur POS                 │
│   Mobile Flutter    │  HTTP   │      (CodeIgniter 2.x)              │
│                     │◄───────►│                                     │
│  - Scanner caméra   │   JWT   │  api_mobile.php                     │
│  - Recherche        │         │  ├── login/logout                   │
│  - Saisie quantité  │         │  ├── categories                     │
│  - Mode offline     │         │  ├── items                          │
│                     │         │  ├── sessions                       │
└─────────────────────┘         │  └── sync                           │
                                └─────────────────────────────────────┘
```

---

## API REST Endpoints

### Authentification

| Endpoint | Méthode | Auth | Description |
|----------|---------|------|-------------|
| `/api_mobile/ping` | GET | Non | Health check |
| `/api_mobile/login` | POST | Non | Authentification, retourne JWT |
| `/api_mobile/logout` | POST | Oui | Révoque le token |

#### POST /api_mobile/login

**Request:**
```json
{
  "username": "admin",
  "password": "xxx",
  "device_info": "Samsung Galaxy S21"
}
```

**Response:**
```json
{
  "success": true,
  "token": "eyJ0eXAiOiJKV1Q...",
  "expires_at": "2026-02-07 17:00:00",
  "expires_in": 86400,
  "employee": {
    "id": 1,
    "username": "admin",
    "first_name": "John",
    "last_name": "Doe"
  },
  "branch": {
    "code": "AGDE",
    "name": "YES STORE AGDE"
  }
}
```

### Catégories

| Endpoint | Méthode | Auth | Description |
|----------|---------|------|-------------|
| `/api_mobile/categories` | GET | Oui | Liste des catégories |

**Response:**
```json
{
  "categories": [
    {"id": 5, "name": "CIG"},
    {"id": 21, "name": "DIY"}
  ],
  "count": 2
}
```

### Articles

| Endpoint | Méthode | Auth | Description |
|----------|---------|------|-------------|
| `/api_mobile/items` | GET | Oui | Liste articles avec filtres |
| `/api_mobile/item/{id}` | GET | Oui | Détail d'un article |
| `/api_mobile/item_by_barcode/{code}` | GET | Oui | Recherche par code-barres |

#### GET /api_mobile/items

**Paramètres:**
- `type` : `full`, `rolling_category`, `rolling_date`
- `category_id` : ID catégorie (pour rolling_category)
- `days` : Nombre de jours (pour rolling_date)
- `search` : Recherche par nom ou référence
- `limit` : Limite résultats (défaut: 100)
- `offset` : Offset pagination

**Response:**
```json
{
  "items": [
    {
      "id": 16268,
      "name": "CLASSIC M SEL DE NICOTINE 10ML 10MG",
      "item_number": "SO18209",
      "quantity": 5,
      "category_id": 85,
      "category_name": "SON"
    }
  ],
  "count": 1,
  "limit": 100,
  "offset": 0
}
```

#### GET /api_mobile/item_by_barcode/{code}

Recherche dans :
1. `ospos_items.barcode`
2. `ospos_items.item_number` (SKU)
3. `ospos_items_suppliers.supplier_bar_code`

**Response:**
```json
{
  "item": {
    "id": 16268,
    "name": "CLASSIC M SEL DE NICOTINE 10ML 10MG",
    "item_number": "SO18209",
    "quantity": 0,
    "category_id": 85,
    "category_name": "SON",
    "cost_price": 3.50,
    "unit_price": 5.90,
    "last_inventory_date": "2025-12-23 12:45:09"
  }
}
```

### Sessions d'inventaire

| Endpoint | Méthode | Auth | Description |
|----------|---------|------|-------------|
| `/api_mobile/sessions` | GET | Oui | Liste des sessions |
| `/api_mobile/sessions` | POST | Oui | Créer une session |
| `/api_mobile/session/{id}` | GET | Oui | Détail session |
| `/api_mobile/session/{id}/item` | POST | Oui | Ajouter article |
| `/api_mobile/session/{id}/complete` | POST | Oui | Terminer session |
| `/api_mobile/session/{id}/cancel` | POST | Oui | Annuler session |

#### POST /api_mobile/sessions

**Request:**
```json
{
  "type": "full",
  "category_id": null,
  "days_threshold": null,
  "notes": "Inventaire mensuel"
}
```

Types disponibles :
- `full` : Inventaire complet
- `rolling_category` : Par catégorie
- `rolling_date` : Par date de dernier inventaire

**Response:**
```json
{
  "success": true,
  "session": {
    "id": 18,
    "type": "full",
    "category_id": null,
    "category_name": null,
    "status": "in_progress",
    "total_items": 1009,
    "items_counted": 0,
    "started_at": "2026-02-06 17:00:00"
  }
}
```

#### POST /api_mobile/session/{id}/item

**Request:**
```json
{
  "item_id": 16268,
  "counted_quantity": 5
}
```

**Response:**
```json
{
  "success": true,
  "item": {
    "item_id": 16268,
    "item_name": "CLASSIC M SEL DE NICOTINE 10ML 10MG",
    "expected_quantity": 0,
    "counted_quantity": 5,
    "variance": 5
  }
}
```

#### POST /api_mobile/session/{id}/complete

Finalise la session et applique les ajustements de stock.

**Response:**
```json
{
  "success": true,
  "message": "Session completed",
  "items_processed": 15,
  "adjustments_made": 8
}
```

### Synchronisation Offline

| Endpoint | Méthode | Auth | Description |
|----------|---------|------|-------------|
| `/api_mobile/sync` | POST | Oui | Sync données offline |

**Request:**
```json
{
  "session_id": 18,
  "items": [
    {"item_id": 123, "expected_quantity": 10, "counted_quantity": 8},
    {"item_id": 456, "expected_quantity": 5, "counted_quantity": 5}
  ]
}
```

**Response:**
```json
{
  "success": true,
  "synced": 2,
  "errors": []
}
```

---

## Base de Données

### Tables Créées

#### ospos_api_tokens
Stockage des tokens JWT pour authentification.

```sql
CREATE TABLE ospos_api_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    token VARCHAR(500) NOT NULL,
    expires_at DATETIME NOT NULL,
    device_info VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES ospos_employees(person_id)
);
```

#### ospos_inventory_sessions
Sessions d'inventaire créées depuis l'app mobile.

```sql
CREATE TABLE ospos_inventory_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    session_type ENUM('full', 'rolling_category', 'rolling_date') NOT NULL,
    category_id INT NULL,
    days_threshold INT NULL,
    status ENUM('in_progress', 'completed', 'cancelled') DEFAULT 'in_progress',
    total_items INT DEFAULT 0,
    items_counted INT DEFAULT 0,
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    notes TEXT NULL,
    INDEX idx_employee (employee_id),
    INDEX idx_status (status),
    INDEX idx_type (session_type)
);
```

#### ospos_inventory_session_items
Articles scannés pendant une session.

```sql
CREATE TABLE ospos_inventory_session_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    item_id INT NOT NULL,
    expected_quantity DECIMAL(15,3) NOT NULL DEFAULT 0,
    counted_quantity DECIMAL(15,3) NOT NULL DEFAULT 0,
    variance DECIMAL(15,3) GENERATED ALWAYS AS (counted_quantity - expected_quantity) STORED,
    scanned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    synced TINYINT(1) DEFAULT 1,
    FOREIGN KEY (session_id) REFERENCES ospos_inventory_sessions(id),
    FOREIGN KEY (item_id) REFERENCES ospos_items(item_id),
    INDEX idx_session (session_id),
    INDEX idx_item (item_id),
    INDEX idx_synced (synced)
);
```

### Requêtes Utiles pour le Backoffice

#### Liste des sessions d'inventaire
```sql
SELECT
    s.id,
    s.session_type,
    s.status,
    s.total_items,
    s.items_counted,
    s.started_at,
    s.completed_at,
    CONCAT(p.first_name, ' ', p.last_name) as employee_name,
    c.category_name
FROM ospos_inventory_sessions s
JOIN ospos_employees e ON e.person_id = s.employee_id
JOIN ospos_people p ON p.person_id = e.person_id
LEFT JOIN ospos_categories c ON c.category_id = s.category_id
ORDER BY s.started_at DESC;
```

#### Détail d'une session avec articles
```sql
SELECT
    si.id,
    i.item_number,
    i.name,
    si.expected_quantity,
    si.counted_quantity,
    si.variance,
    si.scanned_at,
    c.category_name
FROM ospos_inventory_session_items si
JOIN ospos_items i ON i.item_id = si.item_id
LEFT JOIN ospos_categories c ON c.category_id = i.category_id
WHERE si.session_id = ?
ORDER BY si.scanned_at DESC;
```

#### Résumé des écarts d'une session
```sql
SELECT
    COUNT(*) as total_articles,
    SUM(CASE WHEN variance = 0 THEN 1 ELSE 0 END) as conformes,
    SUM(CASE WHEN variance > 0 THEN 1 ELSE 0 END) as surplus,
    SUM(CASE WHEN variance < 0 THEN 1 ELSE 0 END) as manquants,
    SUM(variance) as ecart_net,
    SUM(ABS(variance)) as ecart_absolu
FROM ospos_inventory_session_items
WHERE session_id = ?;
```

#### Articles avec écarts significatifs
```sql
SELECT
    i.item_number,
    i.name,
    si.expected_quantity,
    si.counted_quantity,
    si.variance,
    ABS(si.variance) as ecart_absolu
FROM ospos_inventory_session_items si
JOIN ospos_items i ON i.item_id = si.item_id
WHERE si.session_id = ?
  AND si.variance != 0
ORDER BY ABS(si.variance) DESC;
```

---

## Fichiers Backend

### Fichiers Créés

| Fichier | Description |
|---------|-------------|
| `application/controllers/api_mobile.php` | Contrôleur API REST principal |
| `application/models/inventory_session.php` | Modèle gestion sessions |
| `application/models/api_token.php` | Modèle gestion tokens JWT |
| `application/libraries/Jwt_auth.php` | Bibliothèque authentification JWT |
| `application/config/jwt.php` | Configuration JWT (secret, expiration) |

### Fichiers Modifiés

| Fichier | Modification |
|---------|--------------|
| `application/config/routes.php` | Routes API ajoutées |

### Routes Ajoutées

```php
// application/config/routes.php
$route['api_mobile/session/(:num)/item'] = 'api_mobile/session_item/$1';
$route['api_mobile/session/(:num)/complete'] = 'api_mobile/session_action/$1/complete';
$route['api_mobile/session/(:num)/cancel'] = 'api_mobile/session_action/$1/cancel';
$route['api_mobile/session/(:num)'] = 'api_mobile/session/$1';
```

---

## Application Mobile Flutter

### Emplacement
`/var/www/html/wrightetmathon/mobile_app/`

### Structure du Projet

```
mobile_app/
├── lib/
│   ├── main.dart                    # Point d'entrée
│   ├── config/
│   │   ├── api_config.dart          # URL serveur, timeouts
│   │   └── app_theme.dart           # Thème visuel
│   ├── models/
│   │   ├── user.dart                # Modèle utilisateur
│   │   ├── item.dart                # Modèle article
│   │   ├── category.dart            # Modèle catégorie
│   │   └── inventory_session.dart   # Modèle session
│   ├── services/
│   │   ├── api_service.dart         # Appels API REST
│   │   ├── auth_service.dart        # Gestion authentification
│   │   ├── database_service.dart    # SQLite local (offline)
│   │   └── sync_service.dart        # Synchronisation
│   ├── blocs/
│   │   └── auth/
│   │       └── auth_bloc.dart       # État authentification
│   ├── screens/
│   │   ├── splash_screen.dart       # Écran démarrage
│   │   ├── login_screen.dart        # Connexion
│   │   ├── home_screen.dart         # Accueil
│   │   ├── new_session_screen.dart  # Création session
│   │   ├── scanner_screen.dart      # Scanner et saisie
│   │   └── history_screen.dart      # Historique sessions
│   └── widgets/
│       └── quantity_input_dialog.dart
├── android/                         # Configuration Android
├── pubspec.yaml                     # Dépendances
└── README.md                        # Documentation
```

### Dépendances Principales

```yaml
dependencies:
  flutter_bloc: ^8.1.3      # Gestion d'état
  dio: ^5.3.2               # HTTP client
  mobile_scanner: ^3.5.5    # Scanner code-barres
  sqflite: ^2.3.0           # Base locale SQLite
  shared_preferences: ^2.2.1
  connectivity_plus: ^6.0.0  # Détection réseau
  flutter_secure_storage: ^9.0.0
  intl: ^0.19.0             # Formatage dates
  google_fonts: ^6.1.0
```

### Build APK

```bash
cd /var/www/html/wrightetmathon/mobile_app
flutter pub get
flutter build apk --release
# APK généré: build/app/outputs/flutter-apk/app-release.apk
```

---

## Sécurité

### Authentification JWT

- **Algorithme** : HS256 (HMAC-SHA256)
- **Expiration** : 24 heures
- **Stockage** : Token stocké en base `ospos_api_tokens`
- **Révocation** : Possible via endpoint logout ou suppression en base

### Configuration JWT

```php
// application/config/jwt.php
$config['jwt_secret_key'] = 'votre_clé_secrète_très_longue';
$config['jwt_expiration'] = 86400; // 24 heures
$config['jwt_issuer'] = 'wrightetmathon-pos';
$config['jwt_algorithm'] = 'HS256';
```

### CORS

Headers configurés dans `api_mobile.php` :
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
```

---

## Intégration Backoffice Suggérée

### Nouveau Menu

Ajouter dans le menu principal :
- **Inventaires Mobiles** (icône: inventory)

### Écrans à Créer

1. **Liste des sessions** (`inventory_mobile.php`)
   - Tableau des sessions avec filtres (statut, date, employé)
   - Actions : voir détail, exporter

2. **Détail session** (`inventory_mobile_detail.php`)
   - Résumé : total articles, écarts, pourcentage conformité
   - Liste articles avec colonnes : référence, nom, attendu, compté, écart
   - Filtre : tous / écarts uniquement
   - Export PDF/Excel

3. **Rapport écarts** (`reports/inventory_mobile_variance.php`)
   - Graphiques écarts par catégorie
   - Top 10 articles avec plus gros écarts
   - Historique par période

### Modèle Suggéré

```php
// application/models/inventory_mobile.php
class Inventory_mobile extends CI_Model {

    function get_sessions($filters = array()) { ... }

    function get_session_detail($session_id) { ... }

    function get_session_items($session_id, $variance_only = false) { ... }

    function get_session_summary($session_id) { ... }

    function export_session_pdf($session_id) { ... }

    function export_session_excel($session_id) { ... }
}
```

---

## Tests API avec cURL

```bash
# Health check
curl http://localhost/wrightetmathon/index.php/api_mobile/ping

# Login
TOKEN=$(curl -s -X POST "http://localhost/wrightetmathon/index.php/api_mobile/login" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"xxx"}' | grep -o '"token":"[^"]*"' | cut -d'"' -f4)

# Liste catégories
curl -H "Authorization: Bearer $TOKEN" \
  "http://localhost/wrightetmathon/index.php/api_mobile/categories"

# Recherche article par code-barres
curl -H "Authorization: Bearer $TOKEN" \
  "http://localhost/wrightetmathon/index.php/api_mobile/item_by_barcode/3760048159074"

# Créer session
curl -X POST -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"type":"full"}' \
  "http://localhost/wrightetmathon/index.php/api_mobile/sessions"

# Ajouter article à session
curl -X POST -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"item_id":16268,"counted_quantity":5}' \
  "http://localhost/wrightetmathon/index.php/api_mobile/session/18/item"

# Liste sessions
curl -H "Authorization: Bearer $TOKEN" \
  "http://localhost/wrightetmathon/index.php/api_mobile/sessions"
```

---

## Historique des Modifications

| Date | Modification |
|------|--------------|
| 2026-02-06 | Création API mobile et app Flutter |
| 2026-02-06 | Correction recherche code-barres (ajout `ospos_items_suppliers`) |
| 2026-02-06 | Correction nom colonne `categories.category_name` |
| 2026-02-06 | Ajout routes pour `/session/{id}/item`, `/complete`, `/cancel` |
| 2026-02-06 | Conversion requêtes Active Record vers SQL direct (compatibilité) |
| 2026-02-06 | Ajout écran historique dans l'app mobile |
