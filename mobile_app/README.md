# W&M Inventaire - Application Mobile Flutter

Application mobile d'inventaire pour le POS Wright et Mathon.

## Fonctionnalités

- **Authentification** : Connexion avec les identifiants employé POS
- **Scanner code-barres** : Lecture rapide via la caméra
- **Recherche manuelle** : Recherche par nom ou référence
- **Modes d'inventaire** :
  - Inventaire complet (tous les articles actifs)
  - Inventaire tournant (articles non encore inventoriés, indicateur=0)
  - Inventaire partiel par catégorie
  - Inventaire partiel par fournisseur
  - Inventaire partiel par recherche libre (multi-mots, AND)
  - Inventaire partiel par date (articles non vérifiés depuis X)
- **Mode hors-ligne** : Synchronisation automatique des données

## Architecture

### Backend (CodeIgniter)

L'API REST est implémentée dans le contrôleur `api_mobile.php` avec authentification JWT.

**Fichiers backend créés :**
- `application/controllers/api_mobile.php` - Contrôleur API REST
- `application/libraries/Jwt_auth.php` - Gestion des tokens JWT
- `application/libraries/MY_Session.php` - Bypass session pour API stateless
- `application/models/api_token.php` - Modèle tokens API
- `application/models/inventory_session.php` - Modèle sessions d'inventaire
- `application/config/jwt.php` - Configuration JWT

**Tables MySQL requises :**
```sql
CREATE TABLE ospos_api_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    token VARCHAR(500) NOT NULL,
    device_info VARCHAR(255) DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES ospos_employees(person_id) ON DELETE CASCADE
);

CREATE TABLE ospos_inventory_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    session_type ENUM('full','rolling','partial','rolling_category','rolling_date') NOT NULL DEFAULT 'full',
    category_id INT NULL,
    supplier_id INT NULL COMMENT 'Fournisseur pour inventaire partiel par fournisseur',
    cutoff_date DATE NULL,
    days_threshold INT NULL COMMENT 'For rolling_date: items not checked in X days',
    status ENUM('in_progress','completed','cancelled') DEFAULT 'in_progress',
    applied TINYINT(1) NOT NULL DEFAULT 0,
    applied_at DATETIME NULL,
    applied_by INT NULL COMMENT 'Employé ayant cliqué Appliquer',
    total_items INT DEFAULT 0,
    items_counted INT DEFAULT 0,
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    notes TEXT NULL COMMENT 'Pour recherche libre: "Recherche: terme"',
    branch_code VARCHAR(30) NOT NULL DEFAULT '',
    FOREIGN KEY (employee_id) REFERENCES ospos_employees(person_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES ospos_categories(category_id) ON DELETE SET NULL
);

CREATE TABLE ospos_inventory_session_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    item_id INT NOT NULL,
    expected_quantity DECIMAL(15,3) NOT NULL DEFAULT 0.000,
    counted_quantity DECIMAL(15,3) NOT NULL DEFAULT 0.000,
    counted_by INT NULL COMMENT 'Employé ayant compté → reporté dans ospos_inventory.trans_user',
    counted_at DATETIME NULL COMMENT 'IS NOT NULL = article compté',
    stock_at_count_time DECIMAL(15,3) NULL,
    adjustment DECIMAL(15,3) NULL,
    applied TINYINT(1) NOT NULL DEFAULT 0,
    comment VARCHAR(255) NULL,
    variance DECIMAL(15,3) GENERATED ALWAYS AS (counted_quantity - expected_quantity) STORED,
    scanned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    synced TINYINT(1) DEFAULT 1,
    UNIQUE KEY (session_id, item_id),
    FOREIGN KEY (session_id) REFERENCES ospos_inventory_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES ospos_items(item_id) ON DELETE CASCADE
);
```

**Audit trail (ospos_inventory) :**
- `trans_user` = employé qui a **compté** l'article (`counted_by`), pas celui qui applique
- `applied_by` (sur la session) = employé qui a cliqué "Appliquer les ajustements"
- `trans_comment` = `'Inventaire comptable - Session #X'`
- `trans_stock_before` / `trans_stock_after` = snapshot avant/après ajustement

**Filtres partiels :**
- **Par catégorie** : `category_id` stocké dans `ospos_inventory_sessions.category_id`
- **Par fournisseur** : `supplier_id` stocké dans `ospos_inventory_sessions.supplier_id`, articles trouvés via JOIN `ospos_items_suppliers`
- **Par recherche libre** : terme stocké dans `notes` sous la forme `"Recherche: ACCU 18500"`, recherche multi-mots AND (chaque mot doit correspondre dans `name` OU `item_number`)
- **Par date** : `cutoff_date` stocké dans `ospos_inventory_sessions.cutoff_date`

**Gestion des fusions :**
Quand un produit est fusionné pendant qu'une session d'inventaire est en cours, les références dans `ospos_inventory_session_items` sont automatiquement mises à jour vers le nouveau produit cible (via `items/ajax_fuzzy_merge_execute`).

### Frontend (Flutter)

Application Flutter avec architecture BLoC pour la gestion d'état.

## Installation

### Prérequis Serveur

1. PHP 8.x avec extensions MySQLi, JSON
2. Apache avec mod_rewrite activé
3. MySQL/MariaDB

### Prérequis Développement (Fedora 40)

```bash
# Installer Java 17 (requis pour Android SDK)
sudo dnf install java-17-openjdk-devel

# Installer les outils Android
sudo dnf install android-tools

# Télécharger Flutter
cd ~
curl -LO https://storage.googleapis.com/flutter_infra_release/releases/stable/linux/flutter_linux_3.24.0-stable.tar.xz
tar xf flutter_linux_3.24.0-stable.tar.xz
rm flutter_linux_3.24.0-stable.tar.xz

# Ajouter Flutter au PATH
echo 'export PATH="$PATH:$HOME/flutter/bin"' >> ~/.bashrc
source ~/.bashrc

# Vérifier l'installation
flutter doctor
```

### Installation Android SDK

```bash
# Créer le répertoire Android SDK
mkdir -p ~/Android/cmdline-tools
cd ~/Android/cmdline-tools

# Télécharger les outils
curl -LO https://dl.google.com/android/repository/commandlinetools-linux-11076708_latest.zip
unzip commandlinetools-linux-11076708_latest.zip
mv cmdline-tools latest
rm commandlinetools-linux-11076708_latest.zip

# Configurer les variables d'environnement
echo 'export ANDROID_HOME=$HOME/Android' >> ~/.bashrc
echo 'export PATH=$PATH:$ANDROID_HOME/cmdline-tools/latest/bin:$ANDROID_HOME/platform-tools' >> ~/.bashrc
source ~/.bashrc

# Accepter les licences
yes | sdkmanager --licenses

# Installer les SDK nécessaires
sdkmanager "platform-tools" "platforms;android-34" "build-tools;34.0.0"

# Configurer Flutter
flutter config --android-sdk ~/Android
```

### Configuration du Projet

1. **Cloner/Copier le projet**
```bash
cd ~/mobile_app
```

2. **Configurer l'URL du serveur**

Modifiez `lib/config/api_config.dart` :
```dart
static const String baseUrl = 'http://VOTRE_IP_SERVEUR/wrightetmathon/index.php';
```

3. **Installer les dépendances**
```bash
flutter pub get
```

4. **Construire l'APK**
```bash
flutter build apk --release
```

L'APK sera généré dans : `build/app/outputs/flutter-apk/app-release.apk`

## Configuration Réseau (VirtualBox)

Si le serveur POS tourne dans une VM VirtualBox, configurez le réseau pour que le téléphone puisse y accéder.

### Option 1 : Réseau Ponté (Bridged) - Recommandé

1. VM éteinte > **Configuration** > **Réseau** > **Adaptateur 1**
2. Mode : **Accès par pont (Bridged)**
3. Sélectionnez votre carte réseau (WiFi ou Ethernet)
4. Redémarrez la VM
5. La VM obtiendra une IP sur votre réseau local (ex: 192.168.1.x)
6. Utilisez cette IP dans `api_config.dart`

### Option 2 : Host-Only + NAT

1. **Adaptateur 1** : NAT (pour accès Internet)
2. **Adaptateur 2** : Réseau privé hôte (Host-Only)
3. La VM aura une IP sur le réseau 192.168.56.x
4. Le téléphone doit être sur le même réseau ou utiliser le port forwarding

### Option 3 : Port Forwarding NAT

1. **Configuration** > **Réseau** > **Adaptateur 1** (NAT)
2. **Avancé** > **Redirection de ports**
3. Ajouter :
   - Nom : `HTTP`
   - Protocole : `TCP`
   - Port hôte : `8080`
   - Port invité : `80`
4. Utilisez `http://IP_MACHINE_HOTE:8080/wrightetmathon/index.php`

## Transfert APK vers Téléphone

### Via ADB (USB)
```bash
# Activer "Débogage USB" sur le téléphone
adb install build/app/outputs/flutter-apk/app-release.apk
```

### Via Serveur HTTP Local
```bash
cd build/app/outputs/flutter-apk/
python3 -m http.server 8000
```
Sur le téléphone, ouvrir : `http://IP_PC:8000/app-release.apk`

### Via USB (Gestionnaire de fichiers)
1. Brancher le téléphone en mode "Transfert de fichiers"
2. Copier `app-release.apk` dans le dossier Download
3. Installer depuis le gestionnaire de fichiers

## Structure du projet

```
lib/
├── main.dart                 # Point d'entrée
├── config/
│   ├── api_config.dart       # Configuration API
│   └── app_theme.dart        # Thème de l'application
├── models/
│   ├── user.dart             # Modèle utilisateur
│   ├── item.dart             # Modèle article
│   ├── category.dart         # Modèle catégorie
│   └── inventory_session.dart # Modèle session d'inventaire
├── services/
│   ├── api_service.dart      # Appels API REST
│   ├── auth_service.dart     # Gestion authentification
│   ├── database_service.dart # SQLite local (offline)
│   └── sync_service.dart     # Synchronisation offline
├── blocs/
│   └── auth/
│       └── auth_bloc.dart    # Gestion état authentification
├── screens/
│   ├── splash_screen.dart    # Écran de démarrage
│   ├── login_screen.dart     # Connexion
│   ├── home_screen.dart      # Accueil
│   ├── new_session_screen.dart # Création session
│   └── scanner_screen.dart   # Scanner et saisie
└── widgets/
    └── quantity_input_dialog.dart # Saisie quantité
```

## API Endpoints

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api_mobile/login` | POST | Authentification (retourne JWT) |
| `/api_mobile/logout` | POST | Déconnexion |
| `/api_mobile/ping` | GET | Health check |
| `/api_mobile/categories` | GET | Liste des catégories |
| `/api_mobile/items` | GET | Liste des articles (filtrable) |
| `/api_mobile/item_by_barcode/{code}` | GET | Recherche par code-barres |
| `/api_mobile/sessions` | GET/POST | Liste/Créer sessions |
| `/api_mobile/session/{id}/item` | POST | Ajouter article scanné |
| `/api_mobile/session/{id}/complete` | POST | Terminer session |
| `/api_mobile/sync` | POST | Synchronisation offline |

### Test API avec curl

```bash
# Test ping
curl http://SERVEUR/wrightetmathon/index.php/api_mobile/ping

# Authentification
curl -X POST http://SERVEUR/wrightetmathon/index.php/api_mobile/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"xxx"}'

# Requête authentifiée
curl http://SERVEUR/wrightetmathon/index.php/api_mobile/categories \
  -H "Authorization: Bearer TOKEN_JWT"
```

## Dépannage

### "Impossible de se connecter au serveur"

1. Vérifiez que le téléphone et le serveur sont sur le même réseau
2. Testez l'API avec curl depuis le téléphone ou un PC sur le même réseau
3. Vérifiez les règles de firewall
4. Pour VirtualBox, vérifiez la configuration réseau (voir section dédiée)

### Erreur 500 sur l'API

1. Vérifiez les logs PHP : `application/logs/log-YYYY-MM-DD.php`
2. Vérifiez que les tables API existent dans la base de données
3. Vérifiez les permissions des fichiers

### Build APK échoue

1. Vérifiez Java 17 : `java -version`
2. Vérifiez Android SDK : `flutter doctor`
3. Nettoyez et reconstruisez :
```bash
flutter clean
flutter pub get
flutter build apk --release
```

### Erreur "jlink not found"

```bash
sudo dnf install java-17-openjdk-devel
sudo alternatives --config java  # Sélectionner Java 17
```

### Espace disque insuffisant

Le build Android nécessite ~5 Go d'espace libre. Libérez de l'espace ou utilisez un autre PC pour le build.

## Permissions Android

Configurées dans `android/app/src/main/AndroidManifest.xml` :

```xml
<uses-permission android:name="android.permission.CAMERA" />
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />
<uses-permission android:name="android.permission.VIBRATE" />

<uses-feature android:name="android.hardware.camera" android:required="true" />
```

## Développement

### Lancer en mode debug (Chrome)
```bash
flutter run -d chrome
```

### Lancer en mode debug (Android connecté)
```bash
flutter run
```

### Analyser le code
```bash
flutter analyze
```

### Formater le code
```bash
dart format lib/
```

## Licence

Propriétaire - Wright et Mathon POS
