#!/bin/bash
# =============================================================================
# SCRIPT DE MIGRATION - Wright et Mathon POS
# Module Inventaire + App Mobile + Mise à jour jQuery 1.2.6 → 3.3.1
# =============================================================================
#
# Ce script migre une installation ancienne de wrightetmathon vers la version
# incluant le module Inventaire, l'API mobile et jQuery 3.3.1.
#
# Éléments migrés :
#   1. Création des tables MySQL (inventory_sessions, inventory_session_items, api_tokens)
#      - inventory_sessions : supplier_id pour filtre par fournisseur
#      - notes stocke le terme de recherche libre ("Recherche: ...")
#   2. Optimisation base de données (index, types, clés primaires, charset, défragmentation)
#   3. Mise à jour jQuery 1.2.6 → 3.3.1 + polyfill $.browser
#   4. Patches des plugins jQuery pour compatibilité 3.x
#   5. Nouveaux fichiers Inventaire (contrôleur, modèle, vues)
#      - Filtres partiels : catégorie, fournisseur, recherche libre, date
#   6. Modification des fichiers existants (menu, navigation, langues)
#   7. API Mobile : contrôleur, JWT, session bypass, .htaccess, Apache
#      7j. session.auto_start = 1 dans php.ini (requis pour $_SESSION)
#      7k. davfs2 + HiDrive (montage WebDAV pour sync notifications)
#   8. Vérifications finales
#
# Traçabilité audit trail :
#   - ospos_inventory_session_items.counted_by = employé qui a COMPTÉ l'article
#   - ospos_inventory_session_items.counted_at = horodatage du comptage
#   - ospos_inventory.trans_user = employé qui a compté (= counted_by, pas celui qui applique)
#   - ospos_inventory_sessions.applied_by = employé qui a cliqué "Appliquer les ajustements"
#
# Usage :
#   chmod +x migrate_inventaire.sh
#   sudo ./migrate_inventaire.sh
#
# =============================================================================

set -e

# ─── Configuration ───────────────────────────────────────────────────────────
WEBROOT="/var/www/html/wrightetmathon"
INI_FILE="/var/www/html/wrightetmathon.ini"
DB_USER="admin"
DB_PASS='Son@Risa&11'
DB_HOST="localhost"
BACKUP_DIR="/tmp/wm_migration_backup_$(date +%Y%m%d_%H%M%S)"

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ─── Fonctions utilitaires ───────────────────────────────────────────────────

log_info()  { echo -e "${BLUE}[INFO]${NC}  $1"; }
log_ok()    { echo -e "${GREEN}[OK]${NC}    $1"; }
log_warn()  { echo -e "${YELLOW}[WARN]${NC}  $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

check_root() {
    if [ "$(id -u)" -ne 0 ]; then
        log_error "Ce script doit être exécuté en root (sudo)"
        exit 1
    fi
}

get_database_name() {
    if [ ! -f "$INI_FILE" ]; then
        log_error "Fichier INI introuvable : $INI_FILE"
        exit 1
    fi
    # Prend la première base de données dans le fichier INI
    DB_NAME=$(grep -m1 "^database=" "$INI_FILE" | head -1 | cut -d= -f2 | tr -d "'" | tr -d ' ')
    if [ -z "$DB_NAME" ]; then
        log_error "Impossible de lire le nom de la base de données depuis $INI_FILE"
        exit 1
    fi
    log_info "Base de données détectée : $DB_NAME"
}

test_db_connection() {
    if ! mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1" &>/dev/null; then
        log_error "Impossible de se connecter à MySQL ($DB_HOST / $DB_NAME)"
        exit 1
    fi
    log_ok "Connexion MySQL OK"
}

# ─── Vérifications préalables ────────────────────────────────────────────────

echo ""
echo "============================================================"
echo "  MIGRATION Wright et Mathon POS"
echo "  Module Inventaire + App Mobile + jQuery 3.3.1"
echo "  $(date '+%Y-%m-%d %H:%M:%S')"
echo "============================================================"
echo ""

check_root
get_database_name
test_db_connection

# ─── Étape 0 : Sauvegarde ───────────────────────────────────────────────────

log_info "Étape 0/8 : Sauvegarde des fichiers modifiés..."
mkdir -p "$BACKUP_DIR"

# Sauvegarder les fichiers qui seront modifiés
FILES_TO_BACKUP=(
    "application/views/partial/head.php"
    "application/views/partial/header_banner.php"
    "application/controllers/common_controller.php"
    "application/language/French/items_lang.php"
    "application/language/english/items_lang.php"
    "js/jquery.autocomplete.js"
    "js/jquery.validate.min.js"
    "js/jquery.tablesorter.min.js"
    "js/jquery.bgiframe.min.js"
    "js/thickbox.js"
    "js/datepicker.js"
)

for f in "${FILES_TO_BACKUP[@]}"; do
    if [ -f "$WEBROOT/$f" ]; then
        mkdir -p "$BACKUP_DIR/$(dirname "$f")"
        cp "$WEBROOT/$f" "$BACKUP_DIR/$f"
    fi
done

# Sauvegarde SQL
log_info "Sauvegarde du schéma DB..."
mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" --no-data > "$BACKUP_DIR/schema_backup.sql" 2>/dev/null

log_ok "Sauvegarde dans $BACKUP_DIR"

# =============================================================================
# ÉTAPE 1 : TABLES MySQL
# =============================================================================

log_info "Étape 1/8 : Création des tables MySQL..."

mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<'EOSQL'

-- ─── Table : ospos_inventory_sessions ────────────────────────────────────
CREATE TABLE IF NOT EXISTS `ospos_inventory_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `session_type` enum('full','rolling','partial','rolling_category','rolling_date') NOT NULL DEFAULT 'full',
  `category_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `cutoff_date` date DEFAULT NULL,
  `days_threshold` int(11) DEFAULT NULL COMMENT 'For rolling_date: items not checked in X days',
  `status` enum('in_progress','completed','cancelled') DEFAULT 'in_progress',
  `applied` tinyint(1) NOT NULL DEFAULT 0,
  `applied_at` datetime DEFAULT NULL,
  `applied_by` int(11) DEFAULT NULL,
  `total_items` int(11) DEFAULT 0,
  `items_counted` int(11) DEFAULT 0,
  `started_at` datetime DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `branch_code` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_employee` (`employee_id`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`session_type`),
  KEY `fk_inv_sessions_category` (`category_id`),
  CONSTRAINT `fk_inv_sessions_category` FOREIGN KEY (`category_id`) REFERENCES `ospos_categories` (`category_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_inv_sessions_employee` FOREIGN KEY (`employee_id`) REFERENCES `ospos_employees` (`person_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Ajout colonne supplier_id si absente (bases existantes)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ospos_inventory_sessions' AND COLUMN_NAME = 'supplier_id');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE ospos_inventory_sessions ADD COLUMN supplier_id INT(11) NULL DEFAULT NULL AFTER category_id',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ─── Table : ospos_inventory_session_items ───────────────────────────────
-- counted_by : employé ayant effectué le comptage (reporté dans ospos_inventory.trans_user)
-- counted_at : IS NOT NULL = article compté (counted_quantity=0 est un comptage valide)
-- stock_at_count_time : snapshot du stock au moment du comptage
CREATE TABLE IF NOT EXISTS `ospos_inventory_session_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `expected_quantity` decimal(15,3) NOT NULL DEFAULT 0.000,
  `counted_quantity` decimal(15,3) NOT NULL DEFAULT 0.000,
  `counted_by` int(11) DEFAULT NULL COMMENT 'Employé ayant compté → reporté dans ospos_inventory.trans_user',
  `counted_at` datetime DEFAULT NULL COMMENT 'IS NOT NULL = article compté',
  `stock_at_count_time` decimal(15,3) DEFAULT NULL,
  `adjustment` decimal(15,3) DEFAULT NULL,
  `applied` tinyint(1) NOT NULL DEFAULT 0,
  `comment` varchar(255) DEFAULT NULL,
  `variance` decimal(15,3) GENERATED ALWAYS AS (`counted_quantity` - `expected_quantity`) STORED,
  `scanned_at` datetime DEFAULT current_timestamp(),
  `synced` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_session_item` (`session_id`,`item_id`),
  KEY `idx_session` (`session_id`),
  KEY `idx_item` (`item_id`),
  KEY `idx_synced` (`synced`),
  CONSTRAINT `fk_session_items_item` FOREIGN KEY (`item_id`) REFERENCES `ospos_items` (`item_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_session_items_session` FOREIGN KEY (`session_id`) REFERENCES `ospos_inventory_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ─── Table : ospos_api_tokens (pour l'app mobile) ───────────────────────
CREATE TABLE IF NOT EXISTS `ospos_api_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `token` varchar(500) NOT NULL,
  `device_info` varchar(255) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_token` (`token`(255)),
  KEY `idx_employee` (`employee_id`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `fk_api_tokens_employee` FOREIGN KEY (`employee_id`) REFERENCES `ospos_employees` (`person_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ─── Ajout colonne rolling_inventory_indicator si absente ────────────────
-- (nécessaire pour le type de session "tournant")
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'ospos_items'
    AND COLUMN_NAME = 'rolling_inventory_indicator');

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE ospos_items ADD COLUMN rolling_inventory_indicator TINYINT(1) NOT NULL DEFAULT 0',
    'SELECT "colonne rolling_inventory_indicator existe deja"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ─── Ajout colonnes audit trail si absentes ─────────────────────────────
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'ospos_inventory'
    AND COLUMN_NAME = 'trans_stock_before');

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE ospos_inventory ADD COLUMN trans_stock_before DECIMAL(15,3) DEFAULT NULL, ADD COLUMN trans_stock_after DECIMAL(15,3) DEFAULT NULL',
    'SELECT "colonnes audit trail existent deja"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

EOSQL

log_ok "Tables MySQL créées"

# =============================================================================
# ÉTAPE 2 : OPTIMISATION BASE DE DONNÉES
# =============================================================================

log_info "Étape 2/8 : Optimisation de la base de données..."

# ─── Fonction utilitaire : ajouter un index s'il n'existe pas ─────────────

add_index_if_missing() {
    local table=$1
    local index_name=$2
    local columns=$3
    local idx_exists
    idx_exists=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e \
        "SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = '$DB_NAME' AND TABLE_NAME = '$table' AND INDEX_NAME = '$index_name'" 2>/dev/null)
    if [ "$idx_exists" = "0" ]; then
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e \
            "CREATE INDEX \`$index_name\` ON \`$table\`($columns)" 2>/dev/null
        if [ $? -eq 0 ]; then
            log_ok "Index $index_name créé sur $table($columns)"
        else
            log_error "Échec création index $index_name sur $table"
        fi
    else
        log_warn "Index $index_name existe déjà sur $table"
    fi
}

# ─── Fonction utilitaire : ajouter une clé primaire si absente ────────────

add_pk_if_missing() {
    local table=$1
    local column=$2
    local pk_exists
    pk_exists=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e \
        "SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = '$DB_NAME' AND TABLE_NAME = '$table' AND INDEX_NAME = 'PRIMARY'" 2>/dev/null)
    if [ "$pk_exists" = "0" ]; then
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e \
            "ALTER TABLE \`$table\` ADD COLUMN \`$column\` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST" 2>/dev/null
        if [ $? -eq 0 ]; then
            log_ok "Clé primaire $column ajoutée sur $table"
        else
            log_error "Échec ajout clé primaire sur $table"
        fi
    else
        log_warn "Clé primaire existe déjà sur $table"
    fi
}

# ─── 2a. INDEX CRITIQUES MANQUANTS ────────────────────────────────────────
log_info "2a. Ajout des index manquants..."

# ospos_inventory (742K lignes, 96 MB) — table la plus sollicitée
# Sans index : full table scan sur chaque recherche par article, date ou branche
add_index_if_missing "ospos_inventory" "idx_inv_items" "trans_items"
add_index_if_missing "ospos_inventory" "idx_inv_date" "trans_date"
add_index_if_missing "ospos_inventory" "idx_inv_branch" "branch_code"
add_index_if_missing "ospos_inventory" "idx_inv_user" "trans_user"

# ospos_sales (55K lignes) — rapports toujours filtrés par date
add_index_if_missing "ospos_sales" "idx_sales_time" "sale_time"
add_index_if_missing "ospos_sales" "idx_sales_branch" "branch_code"

# ospos_sales_items (113K lignes) — la plus grosse table
add_index_if_missing "ospos_sales_items" "idx_si_branch" "branch_code"

# ospos_items (23K lignes) — filtrés par category + deleted + branch
add_index_if_missing "ospos_items" "idx_items_cat_del" "category_id, deleted"
add_index_if_missing "ospos_items" "idx_items_branch" "branch_code"

# ospos_sessions — nettoyage par last_activity
add_index_if_missing "ospos_sessions" "idx_sessions_activity" "last_activity"

# ospos_receivings — filtrés par date dans les rapports
add_index_if_missing "ospos_receivings" "idx_recv_time" "receiving_time"

# ospos_stock_valuation (3.5K lignes) — aucun index du tout !
add_index_if_missing "ospos_stock_valuation" "idx_sv_item" "value_item_id"
add_index_if_missing "ospos_stock_valuation" "idx_sv_branch" "branch_code"

# ospos_cash_till (10K lignes) — aucun index du tout !
add_index_if_missing "ospos_cash_till" "idx_ct_date" "cash_year, cash_month, cash_day"
add_index_if_missing "ospos_cash_till" "idx_ct_branch" "branch_code"

# ospos_sales_items_taxes (122K lignes) — pas d'index sur branch
add_index_if_missing "ospos_sales_items_taxes" "idx_sit_branch" "branch_code"

# ─── 2b. TYPES DE DONNÉES — ospos_inventory ──────────────────────────────
log_info "2b. Correction des types de données..."

# trans_stock_before et trans_stock_after sont INT mais le stock (items.quantity) est DOUBLE(15,2)
# trans_inventory est INT mais l'ajustement peut être fractionnaire
COL_TYPE=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e \
    "SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$DB_NAME' AND TABLE_NAME = 'ospos_inventory' AND COLUMN_NAME = 'trans_stock_before'" 2>/dev/null)

if [ "$COL_TYPE" = "int" ]; then
    log_info "Conversion INT → DECIMAL sur ospos_inventory (peut prendre quelques secondes)..."
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
        ALTER TABLE ospos_inventory
            MODIFY COLUMN trans_stock_before DECIMAL(15,3) NOT NULL DEFAULT 0,
            MODIFY COLUMN trans_inventory DECIMAL(15,3) NOT NULL DEFAULT 0,
            MODIFY COLUMN trans_stock_after DECIMAL(15,3) NOT NULL DEFAULT 0;
    " 2>/dev/null
    if [ $? -eq 0 ]; then
        log_ok "ospos_inventory : INT → DECIMAL(15,3) pour stock_before, inventory, stock_after"
    else
        log_error "Échec conversion types sur ospos_inventory"
    fi
else
    log_warn "ospos_inventory : colonnes déjà en DECIMAL (ou type non-INT)"
fi

# ─── 2c. CLÉS PRIMAIRES MANQUANTES ───────────────────────────────────────
log_info "2c. Ajout des clés primaires manquantes..."

# ospos_stock_valuation — pas de PK, pas d'index
add_pk_if_missing "ospos_stock_valuation" "value_id"

# ospos_cash_till — pas de PK, pas d'index
add_pk_if_missing "ospos_cash_till" "cash_id"

# ─── 2d. COHÉRENCE branch_code ───────────────────────────────────────────
log_info "2d. Harmonisation branch_code..."

# ospos_inventory_sessions.branch_code est VARCHAR(30), toutes les autres tables sont VARCHAR(10)
BC_LEN=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e \
    "SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$DB_NAME' AND TABLE_NAME = 'ospos_inventory_sessions' AND COLUMN_NAME = 'branch_code'" 2>/dev/null)

if [ "$BC_LEN" = "30" ]; then
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e \
        "ALTER TABLE ospos_inventory_sessions MODIFY COLUMN branch_code VARCHAR(10) NOT NULL DEFAULT ''" 2>/dev/null
    log_ok "ospos_inventory_sessions.branch_code : VARCHAR(30) → VARCHAR(10)"
else
    log_warn "ospos_inventory_sessions.branch_code déjà harmonisé"
fi

# ─── 2e. CONVERSION CHARSET latin1 → utf8 ────────────────────────────────
log_info "2e. Normalisation charset (latin1 → utf8)..."

# Tables en latin1 qui peuvent contenir des caractères accentués français
LATIN1_TABLES=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e \
    "SELECT TABLE_NAME FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = '$DB_NAME' AND TABLE_COLLATION = 'latin1_swedish_ci'
     AND TABLE_NAME LIKE 'ospos_%'
     ORDER BY TABLE_NAME" 2>/dev/null)

CONVERTED=0
for tbl in $LATIN1_TABLES; do
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e \
        "ALTER TABLE \`$tbl\` CONVERT TO CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci" 2>/dev/null
    if [ $? -eq 0 ]; then
        CONVERTED=$((CONVERTED + 1))
    else
        log_warn "Impossible de convertir $tbl (peut nécessiter une vérification FK)"
    fi
done

if [ $CONVERTED -gt 0 ]; then
    log_ok "$CONVERTED tables converties latin1 → utf8mb3"
else
    log_warn "Aucune table latin1 à convertir"
fi

# Cas spécial : ospos_giftcards est en utf8_unicode_ci au lieu de utf8_general_ci
GC_COLLATION=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e \
    "SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$DB_NAME' AND TABLE_NAME = 'ospos_giftcards'" 2>/dev/null)
if [ "$GC_COLLATION" = "utf8mb3_unicode_ci" ]; then
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e \
        "ALTER TABLE ospos_giftcards CONVERT TO CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci" 2>/dev/null
    log_ok "ospos_giftcards : utf8_unicode_ci → utf8_general_ci"
fi

# ─── 2f. NETTOYAGE SESSIONS EXPIRÉES ─────────────────────────────────────
log_info "2f. Nettoyage des sessions expirées..."

EXPIRED=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e \
    "SELECT COUNT(*) FROM ospos_sessions WHERE last_activity < UNIX_TIMESTAMP(NOW() - INTERVAL 24 HOUR)" 2>/dev/null)

if [ "$EXPIRED" -gt 0 ] 2>/dev/null; then
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e \
        "DELETE FROM ospos_sessions WHERE last_activity < UNIX_TIMESTAMP(NOW() - INTERVAL 24 HOUR)" 2>/dev/null
    log_ok "$EXPIRED sessions expirées supprimées"
else
    log_warn "Aucune session expirée à supprimer"
fi

# Nettoyage tokens API expirés
EXPIRED_TOKENS=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e \
    "SELECT COUNT(*) FROM ospos_api_tokens WHERE expires_at < NOW()" 2>/dev/null)

if [ "$EXPIRED_TOKENS" -gt 0 ] 2>/dev/null; then
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e \
        "DELETE FROM ospos_api_tokens WHERE expires_at < NOW()" 2>/dev/null
    log_ok "$EXPIRED_TOKENS tokens API expirés supprimés"
fi

# ─── 2g. DÉFRAGMENTATION TABLES ──────────────────────────────────────────
log_info "2g. Défragmentation des tables volumineuses..."

# OPTIMIZE TABLE récupère l'espace libre et reconstruit les index
TABLES_TO_OPTIMIZE="ospos_inventory ospos_sales ospos_sales_items ospos_sales_items_taxes ospos_items ospos_items_suppliers ospos_receivings_items ospos_sales_payments ospos_items_taxes ospos_items_pricelists"

for tbl in $TABLES_TO_OPTIMIZE; do
    FREE_BYTES=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e \
        "SELECT DATA_FREE FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$DB_NAME' AND TABLE_NAME = '$tbl'" 2>/dev/null)
    if [ "$FREE_BYTES" -gt 1048576 ] 2>/dev/null; then
        FREE_MB=$(echo "scale=1; $FREE_BYTES / 1048576" | bc 2>/dev/null || echo "?")
        log_info "OPTIMIZE $tbl (${FREE_MB} MB à récupérer)..."
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "OPTIMIZE TABLE \`$tbl\`" &>/dev/null
        log_ok "$tbl optimisée"
    fi
done

log_ok "Optimisation base de données terminée"

# ─── 2h. INSERT touchscreen dans app_config ──────────────────────────────
log_info "2h. Ajout paramètre touchscreen..."
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e \
    "INSERT INTO ospos_app_config (\`key\`, \`value\`) VALUES ('touchscreen', '0') ON DUPLICATE KEY UPDATE \`key\`='touchscreen'" 2>/dev/null
log_ok "Paramètre touchscreen ajouté (défaut: 0 = clavier virtuel désactivé)"

# ─── 2i. INSERT item_reference_prefix dans app_config ─────────────────────
log_info "2i. Ajout paramètre item_reference_prefix..."
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e \
    "INSERT INTO ospos_app_config (\`key\`, \`value\`) VALUES ('item_reference_prefix', '') ON DUPLICATE KEY UPDATE \`key\`='item_reference_prefix'" 2>/dev/null
log_ok "Paramètre item_reference_prefix ajouté (défaut: vide)"

# =============================================================================
# ÉTAPE 3 : MISE À JOUR jQuery 1.2.6 → 3.3.1
# =============================================================================

log_info "Étape 3/8 : Mise à jour jQuery 1.2.6 → 3.3.1..."

HEAD_FILE="$WEBROOT/application/views/partial/head.php"

# Vérifier si déjà migré
if grep -q "jquery-3.3.1" "$HEAD_FILE" 2>/dev/null; then
    log_warn "jQuery 3.3.1 déjà présent dans head.php - étape ignorée"
else
    # Vérifier que jquery-3.3.1.js existe
    if [ ! -f "$WEBROOT/js/jquery-3.3.1.js" ]; then
        log_error "Fichier js/jquery-3.3.1.js introuvable. Téléchargement..."
        curl -sL "https://code.jquery.com/jquery-3.3.1.js" -o "$WEBROOT/js/jquery-3.3.1.js"
        if [ $? -ne 0 ]; then
            log_error "Échec du téléchargement de jQuery 3.3.1"
            exit 1
        fi
        chown apache:apache "$WEBROOT/js/jquery-3.3.1.js"
        log_ok "jQuery 3.3.1 téléchargé"
    fi

    # Remplacer la ligne jQuery 1.2.6 par jQuery 3.3.1 + polyfill $.browser
    # On cherche la ligne contenant jquery-1.2.6 et on la remplace
    sed -i 's|<script src="<?php echo base_url();?>js/jquery-1.2.6.min.js"[^>]*></script>|<script src="<?php echo base_url();?>js/jquery-3.3.1.js" type="text/javascript" language="javascript" charset="UTF-8"></script>|' "$HEAD_FILE"

    # Vérifier que le polyfill $.browser n'existe pas déjà
    if ! grep -q '$.browser polyfill' "$HEAD_FILE" 2>/dev/null; then
        # Injecter le polyfill juste après la ligne jQuery 3.3.1
        sed -i '/jquery-3.3.1\.js/a\  <script type="text/javascript">\n  /* $.browser polyfill for legacy plugins (autocomplete, validate, bgiframe, thickbox) */\n  if (!jQuery.browser) {\n    var ua = navigator.userAgent.toLowerCase();\n    jQuery.browser = {\n      msie: /msie/.test(ua) \&\& !/opera/.test(ua),\n      mozilla: /mozilla/.test(ua) \&\& !/(compatible|webkit)/.test(ua),\n      webkit: /webkit/.test(ua),\n      opera: /opera/.test(ua),\n      safari: /safari/.test(ua) \&\& !/chrome/.test(ua),\n      version: (ua.match(/.+(?:rv|it|ra|ie)[\\/: ]([\\d.]+)/) || [])[1]\n    };\n  }\n  </script>' "$HEAD_FILE"
    fi

    log_ok "jQuery mis à jour vers 3.3.1 avec polyfill \$.browser"
fi

# =============================================================================
# ÉTAPE 4 : PATCHES PLUGINS jQuery POUR COMPATIBILITÉ 3.x
# =============================================================================

log_info "Étape 4/8 : Patches des plugins jQuery..."

# ─── 4a. jquery.autocomplete.js ──────────────────────────────────────────
AC_FILE="$WEBROOT/js/jquery.autocomplete.js"
if [ -f "$AC_FILE" ]; then
    PATCHED=0

    # .size() → .length (4 occurrences)
    if grep -q '\.size()' "$AC_FILE"; then
        sed -i 's/\.size()/.length/g' "$AC_FILE"
        PATCHED=1
    fi

    # $.browser.opera → guard avec fallback userAgent
    if grep -q '$.browser.opera' "$AC_FILE" && ! grep -q 'var isOpera' "$AC_FILE"; then
        # Remplacer "if ($.browser.opera)" par un guard sûr
        sed -i 's/if\s*($.browser.opera)/var isOpera = (\$.browser \&\& \$.browser.opera) || \/Opera\/.test(navigator.userAgent);\n\tif (isOpera)/' "$AC_FILE"
        # Mettre à jour les références suivantes
        sed -i 's/$.browser.opera/isOpera/g' "$AC_FILE"
        PATCHED=1
    fi

    # $.browser.msie → guard
    if grep -q '$.browser.msie' "$AC_FILE" && ! grep -q '$.browser&&$.browser.msie' "$AC_FILE"; then
        sed -i 's/\$.browser\.msie/(\$.browser\&\&\$.browser.msie)/g' "$AC_FILE"
        PATCHED=1
    fi

    [ $PATCHED -eq 1 ] && log_ok "jquery.autocomplete.js patché" || log_warn "jquery.autocomplete.js déjà patché"
fi

# ─── 4b. jquery.validate.min.js ──────────────────────────────────────────
VAL_FILE="$WEBROOT/js/jquery.validate.min.js"
if [ -f "$VAL_FILE" ]; then
    PATCHED=0

    # [@for= → [for= (syntaxe attribut supprimée depuis jQuery 1.3)
    if grep -q '\[@for=' "$VAL_FILE"; then
        sed -i "s/\[@for=/[for=/g" "$VAL_FILE"
        PATCHED=1
    fi

    # $.event.handle → ($.event.dispatch||$.event.handle)
    if grep -q '$.event.handle' "$VAL_FILE" && ! grep -q '$.event.dispatch' "$VAL_FILE"; then
        sed -i 's/\$.event\.handle\.apply/(\$.event.dispatch||\$.event.handle).apply/g' "$VAL_FILE"
        PATCHED=1
    fi

    # setArray → pushStack
    if grep -q 'setArray' "$VAL_FILE"; then
        sed -i 's/this\.setArray(this\.add(t)\.get())/this.pushStack(this.add(t).get())/g' "$VAL_FILE"
        PATCHED=1
    fi

    [ $PATCHED -eq 1 ] && log_ok "jquery.validate.min.js patché" || log_warn "jquery.validate.min.js déjà patché"
fi

# ─── 4c. jquery.tablesorter.min.js ──────────────────────────────────────
TS_FILE="$WEBROOT/js/jquery.tablesorter.min.js"
if [ -f "$TS_FILE" ]; then
    if grep -q '{if($.browser.msie)' "$TS_FILE" && ! grep -q '{if($.browser&&$.browser.msie)' "$TS_FILE"; then
        sed -i 's/{if(\$.browser\.msie)/{if(\$.browser\&\&\$.browser.msie)/g' "$TS_FILE"
        log_ok "jquery.tablesorter.min.js patché"
    else
        log_warn "jquery.tablesorter.min.js déjà patché"
    fi
fi

# ─── 4d. jquery.bgiframe.min.js ─────────────────────────────────────────
BG_FILE="$WEBROOT/js/jquery.bgiframe.min.js"
if [ -f "$BG_FILE" ]; then
    if grep -q 'if($.browser.msie)' "$BG_FILE" && ! grep -q 'if(($.browser&&$.browser.msie))' "$BG_FILE"; then
        sed -i 's/if(\$.browser\.msie)/if((\$.browser\&\&\$.browser.msie))/g' "$BG_FILE"
        log_ok "jquery.bgiframe.min.js patché"
    else
        log_warn "jquery.bgiframe.min.js déjà patché"
    fi
fi

# ─── 4e. thickbox.js ────────────────────────────────────────────────────
TB_FILE="$WEBROOT/js/thickbox.js"
if [ -f "$TB_FILE" ]; then
    PATCHED=0

    # .unload( → .on('unload',   (méthode supprimée jQuery 3.x)
    if grep -q '\.unload(' "$TB_FILE"; then
        sed -i "s/\.unload(\s*function/.on('unload', function/g" "$TB_FILE"
        PATCHED=1
    fi

    # $.browser.safari → guard avec fallback
    if grep -q '$.browser.safari' "$TB_FILE" && ! grep -q '($.browser && $.browser.safari)' "$TB_FILE"; then
        sed -i "s/\$.browser\.safari/($.browser \&\& $.browser.safari) || \/Safari\/.test(navigator.userAgent)/g" "$TB_FILE"
        PATCHED=1
    fi

    # jQuery.browser.msie → guard (dans tb_position)
    # Pas besoin de toucher si le polyfill est en place, mais on garde la sécurité
    [ $PATCHED -eq 1 ] && log_ok "thickbox.js patché" || log_warn "thickbox.js déjà patché"
fi

# ─── 4f. datepicker.js ──────────────────────────────────────────────────
DP_FILE="$WEBROOT/js/datepicker.js"
if [ -f "$DP_FILE" ]; then
    if grep -q ".bind('unload'" "$DP_FILE"; then
        sed -i "s/\.bind('unload'/\.on('unload'/g" "$DP_FILE"
        log_ok "datepicker.js patché"
    else
        log_warn "datepicker.js déjà patché"
    fi
fi

# =============================================================================
# ÉTAPE 5 : NOUVEAUX FICHIERS (contrôleur, modèle, vues)
# =============================================================================

log_info "Étape 5/8 : Copie des nouveaux fichiers..."

# ─── Contrôleur inventaire.php ───────────────────────────────────────────
if [ ! -f "$WEBROOT/application/controllers/inventaire.php" ]; then
    log_error "ATTENTION : application/controllers/inventaire.php introuvable !"
    log_error "Copiez-le depuis l'installation source."
else
    log_ok "application/controllers/inventaire.php présent"
fi

# ─── Modèle inventory_session.php ────────────────────────────────────────
if [ ! -f "$WEBROOT/application/models/inventory_session.php" ]; then
    log_error "ATTENTION : application/models/inventory_session.php introuvable !"
    log_error "Copiez-le depuis l'installation source."
else
    log_ok "application/models/inventory_session.php présent"
fi

# ─── Répertoire vues inventaire ──────────────────────────────────────────
VIEWS_DIR="$WEBROOT/application/views/inventaire"
mkdir -p "$VIEWS_DIR"
chown apache:apache "$VIEWS_DIR"

for view in manage.php create.php count.php view.php; do
    if [ ! -f "$VIEWS_DIR/$view" ]; then
        log_error "ATTENTION : application/views/inventaire/$view introuvable !"
        log_error "Copiez-le depuis l'installation source."
    else
        log_ok "application/views/inventaire/$view présent"
    fi
done

# (les fichiers API mobile sont traités à l'étape 6)

# =============================================================================
# ÉTAPE 6 : MODIFICATION DES FICHIERS EXISTANTS
# =============================================================================

log_info "Étape 6/8 : Modification des fichiers existants..."

# ─── 7a. header_banner.php : ajout menu Inventaire ──────────────────────
BANNER_FILE="$WEBROOT/application/views/partial/header_banner.php"
if [ -f "$BANNER_FILE" ]; then
    if grep -q 'inventaire' "$BANNER_FILE"; then
        log_warn "Lien Inventaire déjà présent dans header_banner.php"
    else
        # Ajouter après la ligne suppliers dans le sous-menu Produits
        sed -i '/modules_suppliers/a\                    <li><a href="<?php echo site_url("inventaire");?>">Inventaire</a></li>' "$BANNER_FILE"
        if grep -q 'inventaire' "$BANNER_FILE"; then
            log_ok "Lien Inventaire ajouté au menu Produits"
        else
            log_error "Impossible d'ajouter le lien Inventaire au menu (pattern suppliers non trouvé)"
            log_error "Ajoutez manuellement : <li><a href=\"<?php echo site_url('inventaire');?>\">Inventaire</a></li>"
        fi
    fi
fi

# ─── 7b. common_controller.php : ajout case 'IV' ────────────────────────
CC_FILE="$WEBROOT/application/controllers/common_controller.php"
if [ -f "$CC_FILE" ]; then
    if grep -q "'IV'" "$CC_FILE"; then
        log_warn "Case 'IV' déjà présent dans common_controller.php"
    else
        # Ajouter avant le case 'ST' ou avant 'default'
        sed -i "/case\s*'IK'/,/return;/{
            /return;/a\\
\\
\t\t\tcase 'IV':\\
\t\t\t\tunset(\$_SESSION['title']);\\
\t\t\t\tunset(\$_SESSION['origin']);\\
\t\t\t\tredirect('inventaire');\\
\t\t\t\treturn;
        }" "$CC_FILE"

        if grep -q "'IV'" "$CC_FILE"; then
            log_ok "Case 'IV' ajouté dans common_controller.php"
        else
            log_error "Impossible d'ajouter le case 'IV' automatiquement"
            log_error "Ajoutez manuellement dans common_exit() :"
            log_error "  case 'IV': unset(\$_SESSION['title']); unset(\$_SESSION['origin']); redirect('inventaire'); return;"
        fi
    fi
fi

# ─── 7c. Fichiers de langue : French ────────────────────────────────────
FR_LANG="$WEBROOT/application/language/French/items_lang.php"
if [ -f "$FR_LANG" ]; then
    if grep -q 'inventaire_title' "$FR_LANG"; then
        log_warn "Clés inventaire déjà présentes dans items_lang.php (French)"
    else
        # Supprimer le ?> final, ajouter les clés, remettre ?>
        sed -i '/^?>$/d' "$FR_LANG"
        cat >> "$FR_LANG" << 'EOLANG_FR'

// Inventaire Sessions
$lang['inventaire_title'] = 'Inventaire';
$lang['inventaire_sessions'] = 'Sessions d\'inventaire';
$lang['inventaire_new_session'] = 'Nouvelle session';
$lang['inventaire_create_session'] = 'Créer une session';
$lang['inventaire_type_full'] = 'Total';
$lang['inventaire_type_rolling'] = 'Tournant';
$lang['inventaire_type_partial'] = 'Partiel';
$lang['inventaire_type_partial_date'] = 'Partiel (par date)';
$lang['inventaire_type_partial_category'] = 'Partiel (par famille)';
$lang['inventaire_select_type'] = 'Type de session';
$lang['inventaire_select_category'] = 'Filtrer par famille';
$lang['inventaire_cutoff_date'] = 'Non inventoriés depuis le';
$lang['inventaire_notes'] = 'Notes';
$lang['inventaire_status_in_progress'] = 'En cours';
$lang['inventaire_status_completed'] = 'Terminé';
$lang['inventaire_status_cancelled'] = 'Annulé';
$lang['inventaire_session_active'] = 'Une session est en cours';
$lang['inventaire_session_active_warning'] = 'Une session d\'inventaire est déjà en cours. Vous devez la terminer ou l\'annuler avant d\'en créer une nouvelle.';
$lang['inventaire_continue_counting'] = 'Continuer le comptage';
$lang['inventaire_session_number'] = 'Session #';
$lang['inventaire_date'] = 'Date';
$lang['inventaire_type'] = 'Type';
$lang['inventaire_created_by'] = 'Créé par';
$lang['inventaire_articles'] = 'Articles';
$lang['inventaire_counted'] = 'Comptés';
$lang['inventaire_status'] = 'Statut';
$lang['inventaire_actions'] = 'Actions';
$lang['inventaire_count'] = 'Comptage';
$lang['inventaire_view'] = 'Détail';
$lang['inventaire_apply'] = 'Appliquer les ajustements';
$lang['inventaire_cancel_session'] = 'Annuler la session';
$lang['inventaire_category'] = 'Famille';
$lang['inventaire_reference'] = 'Référence';
$lang['inventaire_designation'] = 'Désignation';
$lang['inventaire_theoretical_stock'] = 'Stk théorique';
$lang['inventaire_counted_qty'] = 'Qté comptée';
$lang['inventaire_comment'] = 'Commentaire';
$lang['inventaire_validate'] = 'Valider';
$lang['inventaire_progress'] = 'Progression';
$lang['inventaire_filter_all'] = 'Tous';
$lang['inventaire_filter_counted'] = 'Comptés';
$lang['inventaire_filter_uncounted'] = 'Non comptés';
$lang['inventaire_session_created'] = 'Session d\'inventaire créée avec succès';
$lang['inventaire_session_cancelled'] = 'Session d\'inventaire annulée';
$lang['inventaire_adjustments_applied'] = 'Ajustements de stock appliqués avec succès';
$lang['inventaire_no_sessions'] = 'Aucune session d\'inventaire';
$lang['inventaire_confirm_apply'] = 'Êtes-vous sûr de vouloir appliquer les ajustements de stock ? Cette action est irréversible.';
$lang['inventaire_confirm_cancel'] = 'Êtes-vous sûr de vouloir annuler cette session ? Les comptages seront perdus.';
$lang['inventaire_adjustment'] = 'Ajustement';
$lang['inventaire_current_stock'] = 'Stock actuel';
$lang['inventaire_expected_stock'] = 'Stock ouverture';
$lang['inventaire_no_items'] = 'Aucun article dans cette session';
$lang['inventaire_applied'] = 'Appliqué';
$lang['inventaire_applied_at'] = 'Appliqué le';
$lang['inventaire_applied_by'] = 'Appliqué par';
$lang['inventaire_back_to_list'] = 'Retour à la liste';

?>
EOLANG_FR
        log_ok "Clés de langue ajoutées (French)"
    fi
fi

# ─── 7d. Fichiers de langue : English ───────────────────────────────────
EN_LANG="$WEBROOT/application/language/english/items_lang.php"
if [ -f "$EN_LANG" ]; then
    if grep -q 'inventaire_title' "$EN_LANG"; then
        log_warn "Clés inventaire déjà présentes dans items_lang.php (english)"
    else
        sed -i '/^?>$/d' "$EN_LANG"
        cat >> "$EN_LANG" << 'EOLANG_EN'

// Inventory Sessions
$lang['inventaire_title'] = 'Inventory';
$lang['inventaire_sessions'] = 'Inventory Sessions';
$lang['inventaire_new_session'] = 'New Session';
$lang['inventaire_create_session'] = 'Create a session';
$lang['inventaire_type_full'] = 'Full';
$lang['inventaire_type_rolling'] = 'Rolling';
$lang['inventaire_type_partial'] = 'Partial';
$lang['inventaire_type_partial_date'] = 'Partial (by date)';
$lang['inventaire_type_partial_category'] = 'Partial (by category)';
$lang['inventaire_select_type'] = 'Session type';
$lang['inventaire_select_category'] = 'Filter by category';
$lang['inventaire_cutoff_date'] = 'Not inventoried since';
$lang['inventaire_notes'] = 'Notes';
$lang['inventaire_status_in_progress'] = 'In progress';
$lang['inventaire_status_completed'] = 'Completed';
$lang['inventaire_status_cancelled'] = 'Cancelled';
$lang['inventaire_session_active'] = 'A session is in progress';
$lang['inventaire_session_active_warning'] = 'An inventory session is already in progress. You must complete or cancel it before creating a new one.';
$lang['inventaire_continue_counting'] = 'Continue counting';
$lang['inventaire_session_number'] = 'Session #';
$lang['inventaire_date'] = 'Date';
$lang['inventaire_type'] = 'Type';
$lang['inventaire_created_by'] = 'Created by';
$lang['inventaire_articles'] = 'Items';
$lang['inventaire_counted'] = 'Counted';
$lang['inventaire_status'] = 'Status';
$lang['inventaire_actions'] = 'Actions';
$lang['inventaire_count'] = 'Counting';
$lang['inventaire_view'] = 'Details';
$lang['inventaire_apply'] = 'Apply adjustments';
$lang['inventaire_cancel_session'] = 'Cancel session';
$lang['inventaire_category'] = 'Category';
$lang['inventaire_reference'] = 'Reference';
$lang['inventaire_designation'] = 'Name';
$lang['inventaire_theoretical_stock'] = 'Expected stock';
$lang['inventaire_counted_qty'] = 'Counted qty';
$lang['inventaire_comment'] = 'Comment';
$lang['inventaire_validate'] = 'Validate';
$lang['inventaire_progress'] = 'Progress';
$lang['inventaire_filter_all'] = 'All';
$lang['inventaire_filter_counted'] = 'Counted';
$lang['inventaire_filter_uncounted'] = 'Uncounted';
$lang['inventaire_session_created'] = 'Inventory session created successfully';
$lang['inventaire_session_cancelled'] = 'Inventory session cancelled';
$lang['inventaire_adjustments_applied'] = 'Stock adjustments applied successfully';
$lang['inventaire_no_sessions'] = 'No inventory sessions';
$lang['inventaire_confirm_apply'] = 'Are you sure you want to apply stock adjustments? This action is irreversible.';
$lang['inventaire_confirm_cancel'] = 'Are you sure you want to cancel this session? Counts will be lost.';
$lang['inventaire_adjustment'] = 'Adjustment';
$lang['inventaire_current_stock'] = 'Current stock';
$lang['inventaire_expected_stock'] = 'Opening stock';
$lang['inventaire_no_items'] = 'No items in this session';
$lang['inventaire_applied'] = 'Applied';
$lang['inventaire_applied_at'] = 'Applied on';
$lang['inventaire_applied_by'] = 'Applied by';
$lang['inventaire_back_to_list'] = 'Back to list';

?>
EOLANG_EN
        log_ok "Clés de langue ajoutées (english)"
    fi
fi

# =============================================================================
# ÉTAPE 7 : API MOBILE (contrôleur, JWT, session bypass, .htaccess, Apache)
# =============================================================================

log_info "Étape 7/8 : Installation de l'API mobile..."

# ─── 7a. Vérifier / activer mod_rewrite et mod_headers Apache ───────────
log_info "Vérification des modules Apache..."

APACHE_RESTART_NEEDED=0

# Détection du système : Fedora/RHEL (httpd) ou Debian/Ubuntu (apache2)
if command -v httpd &>/dev/null; then
    APACHE_CMD="httpd"
    APACHE_SERVICE="httpd"
elif command -v apache2 &>/dev/null; then
    APACHE_CMD="apache2"
    APACHE_SERVICE="apache2"
else
    APACHE_CMD=""
    APACHE_SERVICE=""
    log_warn "Apache non détecté dans le PATH"
fi

if [ -n "$APACHE_CMD" ]; then
    # Vérifier mod_rewrite
    if $APACHE_CMD -M 2>/dev/null | grep -q "rewrite_module"; then
        log_ok "mod_rewrite activé"
    else
        log_warn "mod_rewrite NON activé - nécessaire pour le pass-through du header Authorization"
        log_warn "Activez-le : sudo a2enmod rewrite (Debian) ou LoadModule dans httpd.conf (RHEL)"
        APACHE_RESTART_NEEDED=1
    fi

    # Vérifier mod_headers
    if $APACHE_CMD -M 2>/dev/null | grep -q "headers_module"; then
        log_ok "mod_headers activé"
    else
        log_warn "mod_headers NON activé - recommandé pour CORS"
        APACHE_RESTART_NEEDED=1
    fi

    # Vérifier AllowOverride (pour .htaccess)
    if [ -f "/etc/httpd/conf/httpd.conf" ]; then
        HTTPD_CONF="/etc/httpd/conf/httpd.conf"
    elif [ -f "/etc/apache2/apache2.conf" ]; then
        HTTPD_CONF="/etc/apache2/apache2.conf"
    else
        HTTPD_CONF=""
    fi

    if [ -n "$HTTPD_CONF" ]; then
        if grep -q "AllowOverride All" "$HTTPD_CONF" 2>/dev/null || grep -q "AllowOverride FileInfo" "$HTTPD_CONF" 2>/dev/null; then
            log_ok "AllowOverride configuré"
        else
            log_warn "AllowOverride peut nécessiter 'All' dans $HTTPD_CONF"
        fi
    fi
fi

# ─── 7b. Créer .htaccess (pass-through Authorization + URL rewriting) ───
HTACCESS_FILE="$WEBROOT/.htaccess"
if [ -f "$HTACCESS_FILE" ]; then
    if grep -q "HTTP_AUTHORIZATION" "$HTACCESS_FILE"; then
        log_warn ".htaccess déjà configuré avec pass-through Authorization"
    else
        cat >> "$HTACCESS_FILE" << 'EOHTACCESS'

# Pass Authorization header to PHP (required for JWT Bearer tokens)
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [E=HTTP_AUTHORIZATION:%1]
EOHTACCESS
        log_ok "Règle Authorization ajoutée au .htaccess existant"
    fi
else
    cat > "$HTACCESS_FILE" << 'EOHTACCESS'
# Wright et Mathon POS - .htaccess
# Enable mod_rewrite
RewriteEngine On

# Pass Authorization header to PHP (required for JWT Bearer tokens)
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [E=HTTP_AUTHORIZATION:%1]

# Remove index.php from URL (optional, for cleaner URLs)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]
EOHTACCESS
    chown apache:apache "$HTACCESS_FILE"
    log_ok ".htaccess créé avec RewriteEngine + Authorization pass-through"
fi

# ─── 7c. Créer la config JWT avec secret unique ─────────────────────────
JWT_CONFIG="$WEBROOT/application/config/jwt.php"
if [ -f "$JWT_CONFIG" ]; then
    log_warn "application/config/jwt.php existe déjà"
else
    # Générer un secret unique pour cette installation
    JWT_SECRET=$(openssl rand -hex 32 2>/dev/null || head -c 64 /dev/urandom | od -An -tx1 | tr -d ' \n' | head -c 64)
    cat > "$JWT_CONFIG" << EOJWT
<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * JWT Configuration for Mobile API
 *
 * Generated by migrate_inventaire.sh on $(date '+%Y-%m-%d %H:%M:%S')
 */

// Read JWT secret from INI file (priority) or use fallback
\$jwt_secret = '${JWT_SECRET}';
\$ini_path = '/var/www/html/wrightetmathon.ini';
if (file_exists(\$ini_path)) {
    \$ini = file_get_contents(\$ini_path);
    if (preg_match("/jwt_secret='([^']+)'/", \$ini, \$m)) {
        \$jwt_secret = \$m[1];
    }
}
\$config['jwt_secret_key'] = \$jwt_secret;

// Token expiration time in seconds (24 hours = 86400)
\$config['jwt_expiration'] = 86400;

// Issuer claim
\$config['jwt_issuer'] = 'wrightetmathon-pos';

// Algorithm for signing
\$config['jwt_algorithm'] = 'HS256';

// Refresh token expiration (7 days)
\$config['jwt_refresh_expiration'] = 604800;

/* End of file jwt.php */
/* Location: ./application/config/jwt.php */
EOJWT
    chown apache:apache "$JWT_CONFIG"
    log_ok "application/config/jwt.php créé (secret unique généré)"

    # Ajouter le secret JWT au fichier INI pour cohérence avec jwt.php
    if [ -f "$INI_FILE" ] && ! grep -q "jwt_secret=" "$INI_FILE"; then
        echo "jwt_secret='${JWT_SECRET}'" >> "$INI_FILE"
        log_ok "jwt_secret ajouté au fichier INI"
    fi
fi

# Ajouter allowed_origins au fichier INI si absent
if [ -f "$INI_FILE" ] && ! grep -q "allowed_origins=" "$INI_FILE"; then
    echo "allowed_origins='*'" >> "$INI_FILE"
    log_ok "allowed_origins='*' ajouté au fichier INI"
fi

# ─── 7d. Créer la bibliothèque JWT ──────────────────────────────────────
JWT_LIB="$WEBROOT/application/libraries/Jwt_auth.php"
if [ -f "$JWT_LIB" ]; then
    log_warn "application/libraries/Jwt_auth.php existe déjà"
else
    cat > "$JWT_LIB" << 'EOJWTLIB'
<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * JWT Authentication Library
 *
 * Simple JWT implementation for mobile API authentication.
 * Uses HS256 (HMAC-SHA256) for token signing.
 */
class Jwt_auth
{
    private $CI;
    private $secret_key;
    private $expiration;
    private $issuer;
    private $algorithm;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->config->load('jwt', TRUE);
        $this->secret_key = $this->CI->config->item('jwt_secret_key', 'jwt');
        $this->expiration = $this->CI->config->item('jwt_expiration', 'jwt');
        $this->issuer = $this->CI->config->item('jwt_issuer', 'jwt');
        $this->algorithm = $this->CI->config->item('jwt_algorithm', 'jwt');
    }

    public function generate_token($employee_id, $username, $extra_claims = array())
    {
        $issued_at = time();
        $expiration_time = $issued_at + $this->expiration;

        $header = array('typ' => 'JWT', 'alg' => $this->algorithm);
        $payload = array(
            'iss' => $this->issuer, 'iat' => $issued_at,
            'exp' => $expiration_time, 'sub' => $employee_id,
            'username' => $username
        );
        if (!empty($extra_claims)) $payload = array_merge($payload, $extra_claims);

        $header_encoded = $this->base64url_encode(json_encode($header));
        $payload_encoded = $this->base64url_encode(json_encode($payload));
        $signature = $this->sign($header_encoded . '.' . $payload_encoded);
        $signature_encoded = $this->base64url_encode($signature);
        $token = $header_encoded . '.' . $payload_encoded . '.' . $signature_encoded;

        return array(
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', $expiration_time),
            'expires_in' => $this->expiration
        );
    }

    public function validate_token($token)
    {
        if (empty($token)) return false;
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;

        list($header_encoded, $payload_encoded, $signature_encoded) = $parts;
        $expected_signature = $this->base64url_encode(
            $this->sign($header_encoded . '.' . $payload_encoded)
        );
        if (!hash_equals($expected_signature, $signature_encoded)) return false;

        $payload = json_decode($this->base64url_decode($payload_encoded), true);
        if (!$payload) return false;
        if (isset($payload['exp']) && $payload['exp'] < time()) return false;
        if (isset($payload['iss']) && $payload['iss'] !== $this->issuer) return false;

        return $payload;
    }

    public function get_token_from_header()
    {
        $headers = $this->get_authorization_header();
        if (empty($headers)) return false;
        if (preg_match('/Bearer\s+(.*)$/i', $headers, $matches)) return $matches[1];
        return false;
    }

    private function get_authorization_header()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $request_headers = apache_request_headers();
            $request_headers = array_combine(
                array_map('ucwords', array_keys($request_headers)),
                array_values($request_headers)
            );
            if (isset($request_headers['Authorization'])) $headers = trim($request_headers['Authorization']);
        }
        return $headers;
    }

    private function sign($data)
    {
        return hash_hmac('sha256', $data, $this->secret_key, true);
    }

    private function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64url_decode($data)
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }

    public function get_employee_id($token = null)
    {
        if ($token === null) $token = $this->get_token_from_header();
        $payload = $this->validate_token($token);
        if ($payload && isset($payload['sub'])) return (int)$payload['sub'];
        return false;
    }

    public function is_authenticated()
    {
        $token = $this->get_token_from_header();
        return $this->validate_token($token) !== false;
    }
}

/* End of file Jwt_auth.php */
/* Location: ./application/libraries/Jwt_auth.php */
EOJWTLIB
    chown apache:apache "$JWT_LIB"
    log_ok "application/libraries/Jwt_auth.php créé"
fi

# ─── 7e. Créer le bypass session pour API stateless ─────────────────────
MY_SESSION="$WEBROOT/application/libraries/MY_Session.php"
if [ -f "$MY_SESSION" ]; then
    log_warn "application/libraries/MY_Session.php existe déjà"
else
    cat > "$MY_SESSION" << 'EOSESSION'
<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Extended Session Library
 *
 * Extends CI Session to allow API routes to bypass session validation.
 * Prevents "session cookie data did not match" errors for stateless API requests.
 */
class MY_Session extends CI_Session
{
    public function __construct($params = array())
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        if (strpos($uri, '/api_mobile') !== false || strpos($uri, 'api_mobile') !== false) {
            $this->CI =& get_instance();
            $this->sess_cookie_name = $this->CI->config->item('sess_cookie_name');
            if ($this->sess_cookie_name === FALSE) $this->sess_cookie_name = 'ci_session';
            $this->userdata = array(
                'session_id' => '', 'ip_address' => '',
                'user_agent' => '', 'last_activity' => time()
            );
            return;
        }
        parent::__construct($params);
    }

    function sess_read()
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        if (strpos($uri, '/api_mobile') !== false || strpos($uri, 'api_mobile') !== false) return false;
        return parent::sess_read();
    }

    function sess_write()
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        if (strpos($uri, '/api_mobile') !== false || strpos($uri, 'api_mobile') !== false) return;
        parent::sess_write();
    }

    function sess_destroy()
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        if (strpos($uri, '/api_mobile') !== false || strpos($uri, 'api_mobile') !== false) {
            $this->userdata = array();
            return;
        }
        parent::sess_destroy();
    }
}

/* End of file MY_Session.php */
/* Location: ./application/libraries/MY_Session.php */
EOSESSION
    chown apache:apache "$MY_SESSION"
    log_ok "application/libraries/MY_Session.php créé"
fi

# ─── 7f. Créer le modèle Api_token ──────────────────────────────────────
API_TOKEN_MODEL="$WEBROOT/application/models/api_token.php"
if [ -f "$API_TOKEN_MODEL" ]; then
    log_warn "application/models/api_token.php existe déjà"
else
    cat > "$API_TOKEN_MODEL" << 'EOTOKEN'
<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * API Token Model - Manages JWT tokens for mobile API authentication.
 */
class Api_token extends CI_Model
{
    function save_token($employee_id, $token, $expires_at, $device_info = null)
    {
        $data = array(
            'employee_id' => $employee_id, 'token' => $token,
            'expires_at' => $expires_at, 'device_info' => $device_info,
            'created_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert('api_tokens', $data);
        if ($this->db->affected_rows() > 0) return $this->db->insert_id();
        return false;
    }

    function get_token($token)
    {
        $sql = "SELECT * FROM ospos_api_tokens WHERE token = ? AND expires_at > ? LIMIT 1";
        $query = $this->db->query($sql, array($token, date('Y-m-d H:i:s')));
        if ($query && $query->num_rows() == 1) return $query->row();
        return false;
    }

    function get_employee_tokens($employee_id)
    {
        $this->db->from('api_tokens');
        $this->db->where('employee_id', $employee_id);
        $this->db->where('expires_at >', date('Y-m-d H:i:s'));
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get();
    }

    function revoke_token($token)
    {
        $this->db->where('token', $token);
        $this->db->delete('api_tokens');
        return $this->db->affected_rows() > 0;
    }

    function revoke_all_employee_tokens($employee_id)
    {
        $this->db->where('employee_id', $employee_id);
        $this->db->delete('api_tokens');
        return $this->db->affected_rows();
    }

    function cleanup_expired()
    {
        $this->db->where('expires_at <', date('Y-m-d H:i:s'));
        $this->db->delete('api_tokens');
        return $this->db->affected_rows();
    }

    function extend_token($token, $new_expires_at)
    {
        $this->db->where('token', $token);
        $this->db->update('api_tokens', array('expires_at' => $new_expires_at));
        return $this->db->affected_rows() > 0;
    }
}

/* End of file api_token.php */
/* Location: ./application/models/api_token.php */
EOTOKEN
    chown apache:apache "$API_TOKEN_MODEL"
    log_ok "application/models/api_token.php créé"
fi

# ─── 7g. Vérifier le contrôleur API mobile ──────────────────────────────
API_MOBILE="$WEBROOT/application/controllers/api_mobile.php"
if [ -f "$API_MOBILE" ]; then
    log_ok "application/controllers/api_mobile.php présent"
else
    log_error "ATTENTION : application/controllers/api_mobile.php introuvable !"
    log_error "Copiez-le depuis l'installation source."
fi

# ─── 7h. Test rapide de l'API ────────────────────────────────────────────
log_info "Test de l'endpoint API ping..."
API_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/wrightetmathon/index.php/api_mobile/ping" 2>/dev/null || echo "000")
if [ "$API_RESPONSE" = "200" ]; then
    log_ok "API mobile accessible (HTTP 200 sur /api_mobile/ping)"
elif [ "$API_RESPONSE" = "000" ]; then
    log_warn "Impossible de tester l'API (Apache non démarré ?)"
else
    log_warn "API mobile : HTTP $API_RESPONSE (peut nécessiter un redémarrage Apache)"
    APACHE_RESTART_NEEDED=1
fi

# ─── 7j. Vérifier/corriger session.auto_start dans php.ini ───────────────
# L'application utilise $_SESSION partout (login, module_id, panier, etc.)
# et nécessite session.auto_start = 1 pour démarrer la session native PHP.
log_info "Vérification session.auto_start dans php.ini..."

PHP_INI="/etc/php.ini"
if [ ! -f "$PHP_INI" ]; then
    # Debian/Ubuntu
    PHP_INI=$(php -r 'echo php_ini_loaded_file();' 2>/dev/null)
fi

if [ -f "$PHP_INI" ]; then
    AUTOSTART=$(php -r 'echo ini_get("session.auto_start");' 2>/dev/null)
    if [ "$AUTOSTART" != "1" ]; then
        # Remplacer la valeur existante ou ajouter si absente
        if grep -q '^session.auto_start' "$PHP_INI"; then
            sed -i 's/^session.auto_start\s*=.*/session.auto_start = 1/' "$PHP_INI"
        elif grep -q '^;\s*session.auto_start' "$PHP_INI"; then
            sed -i 's/^;\s*session.auto_start\s*=.*/session.auto_start = 1/' "$PHP_INI"
        else
            echo "session.auto_start = 1" >> "$PHP_INI"
        fi
        log_ok "session.auto_start = 1 configuré dans $PHP_INI"
        APACHE_RESTART_NEEDED=1
        # Redémarrer php-fpm si présent
        if systemctl is-active php-fpm &>/dev/null; then
            systemctl restart php-fpm 2>/dev/null
            log_ok "php-fpm redémarré"
        fi
    else
        log_ok "session.auto_start déjà à 1"
    fi
else
    log_warn "php.ini introuvable - vérifiez manuellement que session.auto_start = 1"
fi

# ─── 7k. Installation davfs2 + configuration HiDrive ─────────────────────
# davfs2 est requis pour le montage WebDAV HiDrive (sync notifications/slides).
# Sans davfs2, les scripts hidrive_mount.php bloquent la page sales pendant ~20s.
# Credentials et structure alignés avec l'ancien script phase1_install.sh.
log_info "7k. Installation davfs2 et configuration HiDrive..."

HIDRIVE_MOUNT="/home/wrightetmathon/.hidrive.sonrisa"
HIDRIVE_URL="https://webdav.hidrive.strato.com/users/drive-6774"
HIDRIVE_ROOT="https://webdav.hidrive.strato.com"
HIDRIVE_USER="drive-6774"
HIDRIVE_PASS="sonrisa@POS"
DAVFS_SECRETS="/etc/davfs2/secrets"

# Lire le shopcode depuis le fichier INI
SHOPCODE=$(grep -m1 "^shopcode=" "$INI_FILE" | cut -d= -f2 | tr -d "'" | tr -d ' ')
if [ -z "$SHOPCODE" ]; then
    log_warn "shopcode introuvable dans $INI_FILE — credentials per-shop non configurés"
fi

# --- Installation davfs2 ---
if command -v mount.davfs &>/dev/null; then
    log_ok "davfs2 déjà installé"
else
    if command -v dnf &>/dev/null; then
        dnf install -y davfs2 2>/dev/null
    elif command -v apt-get &>/dev/null; then
        apt-get install -y davfs2 2>/dev/null
    elif command -v yum &>/dev/null; then
        yum install -y davfs2 2>/dev/null
    else
        log_error "Gestionnaire de paquets non détecté — installez davfs2 manuellement"
    fi
    if command -v mount.davfs &>/dev/null; then
        log_ok "davfs2 installé"
    else
        log_error "Échec installation davfs2"
    fi
fi

# --- Créer le point de montage ---
if [ ! -d "$HIDRIVE_MOUNT" ]; then
    mkdir -p "$HIDRIVE_MOUNT"
    chown wrightetmathon:wrightetmathon "$HIDRIVE_MOUNT"
    log_ok "Point de montage créé : $HIDRIVE_MOUNT"
fi

# --- Credentials davfs2 ---
# Credential principal (accès racine HiDrive, pour hidrive_mount.php)
if [ -f "$DAVFS_SECRETS" ]; then
    # Backup du fichier secrets
    if [ ! -f "${DAVFS_SECRETS}.saved_by_wrightetmathon" ]; then
        cp "$DAVFS_SECRETS" "${DAVFS_SECRETS}.saved_by_wrightetmathon"
    fi

    # Credential racine : https://webdav.hidrive.strato.com drive-6774 sonrisa@POS
    if grep -q "$HIDRIVE_ROOT $HIDRIVE_USER" "$DAVFS_SECRETS" 2>/dev/null; then
        log_ok "Credential HiDrive racine déjà présent"
    else
        echo "" >> "$DAVFS_SECRETS"
        echo "# Credentials added by migrate_inventaire.sh" >> "$DAVFS_SECRETS"
        echo "$HIDRIVE_ROOT $HIDRIVE_USER $HIDRIVE_PASS" >> "$DAVFS_SECRETS"
        log_ok "Credential HiDrive racine ajouté"
    fi

    # Credential sous-dossier users : .../users/drive-6774
    if grep -q "$HIDRIVE_URL $HIDRIVE_USER" "$DAVFS_SECRETS" 2>/dev/null; then
        log_ok "Credential HiDrive users déjà présent"
    else
        echo "$HIDRIVE_URL $HIDRIVE_USER $HIDRIVE_PASS" >> "$DAVFS_SECRETS"
        log_ok "Credential HiDrive users ajouté"
    fi

    # Credential per-shop (pour autofs/notifications sync)
    if [ -n "$SHOPCODE" ]; then
        SHOP_URL="$HIDRIVE_URL/SHOPS/PUBLIC/$SHOPCODE"
        if grep -q "$SHOP_URL" "$DAVFS_SECRETS" 2>/dev/null; then
            log_ok "Credential HiDrive shop $SHOPCODE déjà présent"
        else
            echo "" >> "$DAVFS_SECRETS"
            echo "# Credential for shop $SHOPCODE" >> "$DAVFS_SECRETS"
            echo "$SHOP_URL $HIDRIVE_USER $HIDRIVE_PASS" >> "$DAVFS_SECRETS"
            log_ok "Credential HiDrive shop $SHOPCODE ajouté"
        fi
    fi
else
    log_warn "$DAVFS_SECRETS introuvable — davfs2 mal installé ?"
fi

# --- visudo : permettre à apache d'exécuter mount/umount sans mot de passe ---
# Requis par hidrive_mount.php qui appelle exec('sudo mount ...')
if grep -q "apache ALL=NOPASSWD" /etc/sudoers 2>/dev/null; then
    log_ok "visudo apache NOPASSWD déjà configuré"
else
    echo "apache ALL=NOPASSWD: ALL" | (EDITOR="tee -a" visudo) >/dev/null 2>&1
    if [ $? -eq 0 ]; then
        log_ok "visudo : apache ALL=NOPASSWD ajouté"
    else
        log_warn "Impossible de modifier sudoers — ajoutez manuellement : apache ALL=NOPASSWD: ALL"
    fi
fi

# --- Entrée fstab (montage automatique au boot) ---
if ! grep -q "hidrive.sonrisa" /etc/fstab 2>/dev/null; then
    echo "$HIDRIVE_URL $HIDRIVE_MOUNT davfs _netdev,auto,rw,dir_mode=0777,file_mode=0666 0 0" >> /etc/fstab
    log_ok "Entrée fstab ajoutée pour HiDrive"
else
    log_ok "Entrée fstab HiDrive déjà présente"
fi

# --- Tester le montage ---
if mountpoint -q "$HIDRIVE_MOUNT" 2>/dev/null; then
    log_ok "HiDrive déjà monté sur $HIDRIVE_MOUNT"
else
    mount "$HIDRIVE_MOUNT" 2>/dev/null
    if mountpoint -q "$HIDRIVE_MOUNT" 2>/dev/null; then
        log_ok "HiDrive monté avec succès"
    else
        log_warn "Montage HiDrive échoué — vérifiez les credentials dans $DAVFS_SECRETS"
    fi
fi

# ─── 7i. Redémarrer Apache si nécessaire ─────────────────────────────────
if [ $APACHE_RESTART_NEEDED -eq 1 ] && [ -n "$APACHE_SERVICE" ]; then
    log_info "Redémarrage d'Apache..."
    systemctl restart "$APACHE_SERVICE" 2>/dev/null
    if [ $? -eq 0 ]; then
        log_ok "Apache redémarré"
    else
        log_warn "Impossible de redémarrer Apache automatiquement"
        log_warn "Exécutez : sudo systemctl restart $APACHE_SERVICE"
    fi
fi

# =============================================================================
# ÉTAPE 8 : VÉRIFICATIONS FINALES
# =============================================================================

log_info "Étape 8/8 : Vérifications finales..."

# Vérifier les permissions
chown -R apache:apache "$WEBROOT/application/views/inventaire/" 2>/dev/null
chown apache:apache "$WEBROOT/application/controllers/inventaire.php" 2>/dev/null
chown apache:apache "$WEBROOT/application/controllers/api_mobile.php" 2>/dev/null
chown apache:apache "$WEBROOT/application/models/inventory_session.php" 2>/dev/null
chown apache:apache "$WEBROOT/application/models/api_token.php" 2>/dev/null
chown apache:apache "$WEBROOT/application/libraries/Jwt_auth.php" 2>/dev/null
chown apache:apache "$WEBROOT/application/libraries/MY_Session.php" 2>/dev/null
chown apache:apache "$WEBROOT/application/config/jwt.php" 2>/dev/null
chown apache:apache "$WEBROOT/.htaccess" 2>/dev/null

# Vérifier les tables
TABLES_OK=1
for table in ospos_inventory_sessions ospos_inventory_session_items ospos_api_tokens; do
    if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1 FROM $table LIMIT 1" &>/dev/null; then
        log_ok "Table $table OK"
    else
        log_error "Table $table NON TROUVÉE"
        TABLES_OK=0
    fi
done

# Vérifier jQuery
if grep -q "jquery-3.3.1" "$HEAD_FILE" 2>/dev/null; then
    log_ok "jQuery 3.3.1 chargé dans head.php"
else
    log_error "jQuery 3.3.1 NON détecté dans head.php"
fi

if grep -q 'jQuery.browser' "$HEAD_FILE" 2>/dev/null; then
    log_ok "Polyfill \$.browser présent"
else
    log_error "Polyfill \$.browser NON détecté"
fi

# Vérifier le menu
if grep -q 'inventaire' "$BANNER_FILE" 2>/dev/null; then
    log_ok "Menu Inventaire présent dans header_banner.php"
else
    log_error "Menu Inventaire NON détecté dans header_banner.php"
fi

# Vérifier .htaccess
if [ -f "$WEBROOT/.htaccess" ] && grep -q "HTTP_AUTHORIZATION" "$WEBROOT/.htaccess"; then
    log_ok ".htaccess avec Authorization pass-through"
else
    log_error ".htaccess manquant ou incomplet"
fi

# Vérifier les fichiers critiques
MISSING=0
CRITICAL_FILES=(
    # Module Inventaire web
    "application/controllers/inventaire.php"
    "application/models/inventory_session.php"
    "application/views/inventaire/manage.php"
    "application/views/inventaire/create.php"
    "application/views/inventaire/count.php"
    "application/views/inventaire/view.php"
    # API Mobile
    "application/controllers/api_mobile.php"
    "application/models/api_token.php"
    "application/libraries/Jwt_auth.php"
    "application/libraries/MY_Session.php"
    "application/config/jwt.php"
    ".htaccess"
)
for f in "${CRITICAL_FILES[@]}"; do
    if [ ! -f "$WEBROOT/$f" ]; then
        log_error "MANQUANT : $f"
        MISSING=$((MISSING + 1))
    fi
done

echo ""
echo "============================================================"
if [ $MISSING -eq 0 ] && [ $TABLES_OK -eq 1 ]; then
    echo -e "  ${GREEN}MIGRATION TERMINÉE AVEC SUCCÈS${NC}"
else
    echo -e "  ${YELLOW}MIGRATION TERMINÉE AVEC AVERTISSEMENTS${NC}"
    [ $MISSING -gt 0 ] && echo -e "  ${RED}$MISSING fichier(s) manquant(s) - copie manuelle requise${NC}"
fi
echo ""
echo "  Sauvegarde : $BACKUP_DIR"
echo "  Base de données : $DB_NAME"
echo ""
echo "  Fichiers à copier manuellement si absents :"
echo "    - application/controllers/inventaire.php"
echo "    - application/controllers/api_mobile.php"
echo "    - application/models/inventory_session.php"
echo "    - application/views/inventaire/*.php"
echo ""
echo "  Tests :"
echo "    Web : http://localhost/wrightetmathon/index.php/inventaire"
echo "    API : curl http://localhost/wrightetmathon/index.php/api_mobile/ping"
echo "============================================================"
echo ""
