#!/bin/bash
###############################################################################
#  deploy_fedora43.sh — Installation compl te Wright et Mathon POS
#  Cible : Fedora 43 (fresh install)
#  Usage : sudo bash deploy_fedora43.sh
#
#  Ce script est idempotent : il peut etre relance sans danger.
###############################################################################

set -euo pipefail
IFS=$'\n\t'

# ============================================================================
# COULEURS
# ============================================================================
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

# ============================================================================
# FONCTIONS UTILITAIRES
# ============================================================================
log_info()    { echo -e "${BLUE}[INFO]${NC}  $*"; }
log_ok()      { echo -e "${GREEN}[OK]${NC}    $*"; }
log_warn()    { echo -e "${YELLOW}[WARN]${NC}  $*"; }
log_error()   { echo -e "${RED}[ERROR]${NC} $*"; }
log_section() { echo -e "\n${CYAN}${BOLD}======== $* ========${NC}\n"; }

check_root() {
    if [[ $EUID -ne 0 ]]; then
        log_error "Ce script doit etre execute en root (sudo bash $0)"
        exit 1
    fi
}

ask_value() {
    local prompt="$1"
    local default="$2"
    local varname="$3"
    local input
    read -rp "$(echo -e "${BOLD}$prompt${NC} [$default]: ")" input
    eval "$varname='${input:-$default}'"
}

# ============================================================================
# VERIFICATION ROOT
# ============================================================================
check_root

# ============================================================================
# BANNIERE
# ============================================================================
echo -e "${CYAN}${BOLD}"
echo "  ╔══════════════════════════════════════════════════════════════╗"
echo "  ║          Wright et Mathon POS — Deploiement Fedora 43      ║"
echo "  ║                      Version V15.0.1                       ║"
echo "  ╚══════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# ============================================================================
# PARAMETRES DU MAGASIN (interactif)
# ============================================================================
log_section "PARAMETRES DU MAGASIN"

ask_value "Nom de la base de donnees"           "sonrisaAGDE"          DB_NAME
ask_value "Code magasin (shopcode)"              "AGDE"                 SHOP_CODE
ask_value "Type de branche (F=filiale, S=siege)" "F"                    BRANCH_TYPE
ask_value "Description du magasin"               "YES STORE AGDE"       SHOP_DESC
ask_value "Email du magasin"                     "david@yesstore.fr"    SHOP_EMAIL
ask_value "Mot de passe MySQL user admin"        "Son@Risa&11"          DB_PASSWORD
ask_value "Nom utilisateur systeme"              "wrightetmathon"       SYS_USER
ask_value "Mot de passe utilisateur systeme"     "sonrisa@INFO"         SYS_PASSWORD

# Depot git
GIT_REPO="https://github.com/delta259/wrightetmathon.git"

# Chemins fixes
APP_DIR="/var/www/html/wrightetmathon"
INI_FILE="/var/www/html/wrightetmathon.ini"
SESSION_DIR="$APP_DIR/session"
NOTIF_DIR="$APP_DIR/notifications"
LOG_DIR="$APP_DIR/application/logs"
FONT_DIR="/usr/share/fonts/CenturyGothic"

echo ""
log_info "Resume de la configuration :"
echo "  Base de donnees   : $DB_NAME"
echo "  Code magasin      : $SHOP_CODE"
echo "  Description       : $SHOP_DESC"
echo "  Utilisateur       : $SYS_USER"
echo "  Repertoire app    : $APP_DIR"
echo ""
read -rp "$(echo -e "${BOLD}Continuer l'installation ? (o/N)${NC} ")" CONFIRM
if [[ "$CONFIRM" != "o" && "$CONFIRM" != "O" && "$CONFIRM" != "oui" ]]; then
    log_warn "Installation annulee."
    exit 0
fi

# ============================================================================
# ETAPE 1 : MISE A JOUR SYSTEME
# ============================================================================
log_section "ETAPE 1/13 — Mise a jour systeme"

dnf upgrade -y --refresh 2>&1 | tail -5
log_ok "Systeme mis a jour"

# ============================================================================
# ETAPE 2 : INSTALLATION DES PAQUETS
# ============================================================================
log_section "ETAPE 2/13 — Installation des paquets"

# Paquets essentiels
PACKAGES=(
    # Serveur web
    httpd
    mod_ssl

    # PHP et extensions
    php
    php-cli
    php-common
    php-fpm
    php-gd
    php-intl
    php-mbstring
    php-mysqlnd
    php-opcache
    php-pdo
    php-pecl-zip
    php-process
    php-sodium
    php-xml

    # Base de donnees
    mariadb-server
    mariadb

    # Administration
    phpMyAdmin

    # Navigateur kiosque
    chromium

    # Outils utiles
    git
    tar
    gzip
    curl
    wget
    unzip
    nano
    htop
    fontconfig
    rsync
)

log_info "Installation de ${#PACKAGES[@]} paquets..."
dnf install -y "${PACKAGES[@]}" 2>&1 | tail -10
log_ok "Paquets installes"

# Verifier la version PHP
PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
log_info "PHP version installee : $(php -v | head -1)"

if [[ "$PHP_VERSION" < "8.1" ]]; then
    log_error "PHP 8.1+ est requis. Version trouvee : $PHP_VERSION"
    exit 1
fi
log_ok "PHP $PHP_VERSION OK"

# ============================================================================
# ETAPE 3 : CREATION UTILISATEUR SYSTEME
# ============================================================================
log_section "ETAPE 3/13 — Utilisateur systeme"

if id "$SYS_USER" &>/dev/null; then
    log_warn "L'utilisateur $SYS_USER existe deja"
else
    useradd -m -s /bin/bash "$SYS_USER"
    echo "$SYS_USER:$SYS_PASSWORD" | chpasswd
    log_ok "Utilisateur $SYS_USER cree"
fi

# Ajouter apache au groupe de l'utilisateur (pour acces fichiers)
usermod -aG "$SYS_USER" apache 2>/dev/null || true
# Groupes supplementaires pour apache (imprimante, pole display)
usermod -aG lp apache 2>/dev/null || true
usermod -aG dialout apache 2>/dev/null || true
log_ok "Groupes configures"

# ============================================================================
# ETAPE 4 : CONFIGURATION MARIADB
# ============================================================================
log_section "ETAPE 4/13 — Configuration MariaDB"

systemctl enable mariadb --now
log_info "MariaDB demarre"

# Attendre que MariaDB soit pret
for i in {1..30}; do
    if mysqladmin ping --silent 2>/dev/null; then
        break
    fi
    sleep 1
done

# Creer l'utilisateur admin si inexistant
log_info "Configuration de l'utilisateur MySQL 'admin'..."
mysql -u root <<EOSQL || true
CREATE USER IF NOT EXISTS 'admin'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
EOSQL

# Creer la base de donnees si inexistante
log_info "Creation de la base de donnees '$DB_NAME' si necessaire..."
mysql -u admin -p"${DB_PASSWORD}" <<EOSQL || true
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8 COLLATE utf8_general_ci;
EOSQL

log_ok "MariaDB configure — base '$DB_NAME' prete"

# ============================================================================
# ETAPE 5 : DEPLOIEMENT DES FICHIERS APPLICATION
# ============================================================================
log_section "ETAPE 5/13 — Deploiement application"

if [[ -d "$APP_DIR/.git" ]]; then
    log_info "Repertoire git existant detecte dans $APP_DIR"
    log_info "Pull des dernieres modifications..."
    cd "$APP_DIR"
    git pull || log_warn "Git pull echoue — fichiers locaux utilises"
elif [[ -d "$APP_DIR" ]]; then
    log_warn "$APP_DIR existe deja (pas un repo git). Fichiers conserves."
else
    log_info "Clonage du depot dans $APP_DIR..."
    log_info "Depot : $GIT_REPO"
    git clone "$GIT_REPO" "$APP_DIR"
    log_ok "Depot clone"
fi

# ============================================================================
# ETAPE 6 : STRUCTURE DES REPERTOIRES
# ============================================================================
log_section "ETAPE 6/13 — Structure des repertoires"

# Session directory
mkdir -p "$SESSION_DIR"
chmod 777 "$SESSION_DIR"
log_ok "Repertoire sessions : $SESSION_DIR"

# Notifications directory + fichiers vides requis
mkdir -p "$NOTIF_DIR"
chmod 777 "$NOTIF_DIR"
for f in 01_test_notif.php 02_test_alerte.php 03_test_nouveau.php 04_test_astuce.php; do
    touch "$NOTIF_DIR/$f"
done
log_ok "Repertoire notifications : $NOTIF_DIR"

# Logs directory
mkdir -p "$LOG_DIR"
chmod 777 "$LOG_DIR"
log_ok "Repertoire logs : $LOG_DIR"

# Cache directory
mkdir -p "$APP_DIR/application/cache"
chmod 777 "$APP_DIR/application/cache"
log_ok "Repertoire cache"

# Permissions generales
chown -R "$SYS_USER":"$SYS_USER" "$APP_DIR"
chmod -R 755 "$APP_DIR"
# Repertoires specifiques en ecriture pour apache
chmod 777 "$SESSION_DIR" "$NOTIF_DIR" "$LOG_DIR" "$APP_DIR/application/cache"
chmod -R 777 "$APP_DIR/images" 2>/dev/null || true
log_ok "Permissions appliquees"

# ============================================================================
# ETAPE 7 : FICHIER INI DE CONFIGURATION
# ============================================================================
log_section "ETAPE 7/13 — Fichier wrightetmathon.ini"

if [[ -f "$INI_FILE" ]]; then
    log_warn "$INI_FILE existe deja — sauvegarde en .bak"
    cp "$INI_FILE" "${INI_FILE}.bak.$(date +%Y%m%d%H%M%S)"
fi

cat > "$INI_FILE" <<EOINI
hostname=localhost
database='${DB_NAME}'
shopcode='${SHOP_CODE}'
custom1_name='Y'
email='${SHOP_EMAIL}'
ip='127.0.0.1'
branchtype='${BRANCH_TYPE}'
description='${SHOP_DESC}'
software_folder='VERSION_14.2'
EOINI

chmod 644 "$INI_FILE"
chown "$SYS_USER":"$SYS_USER" "$INI_FILE"
log_ok "Fichier INI cree : $INI_FILE"

# ============================================================================
# ETAPE 8 : CONFIGURATION PHP
# ============================================================================
log_section "ETAPE 8/13 — Configuration PHP"

PHP_INI="/etc/php.ini"

# Sauvegarder l'original
if [[ ! -f "${PHP_INI}.original" ]]; then
    cp "$PHP_INI" "${PHP_INI}.original"
    log_info "Sauvegarde de php.ini original"
fi

# Fonction pour modifier ou ajouter une directive php.ini
php_ini_set() {
    local key="$1"
    local value="$2"
    # Retirer les lignes existantes (commentees ou non)
    sed -i "s|^;\?\s*${key}\s*=.*||g" "$PHP_INI"
    # Ajouter la directive
    echo "${key} = ${value}" >> "$PHP_INI"
}

php_ini_set "session.save_path"     "$SESSION_DIR"
php_ini_set "date.timezone"         "Europe/Paris"
php_ini_set "memory_limit"          "1024M"
php_ini_set "post_max_size"         "128M"
php_ini_set "upload_max_filesize"   "64M"
php_ini_set "max_execution_time"    "120"
php_ini_set "max_input_time"        "120"
php_ini_set "max_input_vars"        "5000"
php_ini_set "display_errors"        "Off"
php_ini_set "error_reporting"       "E_ALL & ~E_DEPRECATED & ~E_STRICT"
php_ini_set "log_errors"            "On"

log_ok "php.ini configure"

# ============================================================================
# ETAPE 9 : CONFIGURATION APACHE
# ============================================================================
log_section "ETAPE 9/13 — Configuration Apache"

# Configuration specifique wrightetmathon
APACHE_CONF="/etc/httpd/conf.d/wrightetmathon.conf"

cat > "$APACHE_CONF" <<'EOAPACHE'
#
# Wright et Mathon POS — Apache Configuration
#

# Timeout long pour les operations lourdes (rapports, exports)
ProxyTimeout 10000
TimeOut 600

# Configuration du repertoire application
<Directory /var/www/html/wrightetmathon>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted

    # Securite : bloquer acces aux fichiers sensibles
    <FilesMatch "\.(ini|log|bak|sh|md)$">
        Require all denied
    </FilesMatch>
</Directory>

# Bloquer l'acces direct au fichier INI
<Files "wrightetmathon.ini">
    Require all denied
</Files>

# Performance : compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/css
    AddOutputFilterByType DEFLATE application/javascript application/json
</IfModule>

# Performance : cache navigateur pour assets statiques
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
    ExpiresByType text/css "access plus 1 week"
    ExpiresByType application/javascript "access plus 1 week"
</IfModule>
EOAPACHE

log_ok "Configuration Apache creee : $APACHE_CONF"

# S'assurer que DirectoryIndex inclut index.php
if ! grep -q 'DirectoryIndex.*index\.php' /etc/httpd/conf/httpd.conf; then
    sed -i 's|DirectoryIndex index.html|DirectoryIndex index.php index.html|' /etc/httpd/conf/httpd.conf
    log_info "DirectoryIndex : index.php ajoute"
fi

# Verifier la syntaxe Apache
if httpd -t 2>&1 | grep -q "Syntax OK"; then
    log_ok "Syntaxe Apache OK"
else
    log_warn "Probleme de syntaxe Apache :"
    httpd -t 2>&1
fi

# ============================================================================
# ETAPE 10 : POLICES DE CARACTERES
# ============================================================================
log_section "ETAPE 10/13 — Polices de caracteres"

FONT_SRC="$APP_DIR/application/fonts/Century-Gothic.ttf"

mkdir -p "$FONT_DIR"

if [[ -f "$FONT_SRC" ]]; then
    cp "$FONT_SRC" "$FONT_DIR/"
    fc-cache -f 2>/dev/null
    log_ok "Police Century Gothic installee"
else
    log_warn "Police Century-Gothic.ttf non trouvee dans $APP_DIR/application/fonts/"
    log_warn "Elle sera installee au premier lancement de wm.sh"
fi

# ============================================================================
# ETAPE 11 : CERTIFICAT SSL AUTO-SIGNE
# ============================================================================
log_section "ETAPE 11/13 — Certificat SSL"

SSL_CERT="/etc/pki/tls/certs/localhost.crt"
SSL_KEY="/etc/pki/tls/private/localhost.key"

if [[ -f "$SSL_CERT" && -s "$SSL_CERT" ]]; then
    log_ok "Certificat SSL existant : $SSL_CERT"
else
    log_info "Generation du certificat SSL auto-signe (10 ans)..."
    openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
        -keyout "$SSL_KEY" \
        -out "$SSL_CERT" \
        -subj "/CN=localhost" 2>/dev/null
    chmod 600 "$SSL_KEY"
    chmod 644 "$SSL_CERT"
    log_ok "Certificat SSL genere : $SSL_CERT"
fi

# ============================================================================
# ETAPE 12 : SERVICES SYSTEMD
# ============================================================================
log_section "ETAPE 12/13 — Services systemd"

# Service de lancement wrightetmathon (initialisation au boot)
LAUNCH_SCRIPT="$APP_DIR/application/controllers/launch.sh"

# Creer le script launch.sh s'il n'existe pas
if [[ ! -f "$LAUNCH_SCRIPT" ]]; then
    cat > "$LAUNCH_SCRIPT" <<EOLAUNCH
#!/bin/bash
# Wright et Mathon POS — Script d'initialisation au demarrage
chmod -R 777 /var/www/html/wrightetmathon/session/
chmod -R 777 /var/www/html/wrightetmathon/application/logs/
chmod -R 777 /var/www/html/wrightetmathon/notifications/
rm -f /var/www/html/wrightetmathon/session/*
rm -f /home/${SYS_USER}/.app_running.txt
# Pole display (si connecte)
[ -e /dev/ttyUSB0 ] && chmod 666 /dev/ttyUSB0
EOLAUNCH
    chmod +x "$LAUNCH_SCRIPT"
    log_info "Script launch.sh cree"
fi

# Service systemd
cat > /etc/systemd/system/wrightetmathon.service <<EOSERVICE
[Unit]
Description=Initialise wrightetmathon POS system
After=httpd.service mariadb.service

[Service]
Type=oneshot
ExecStart=${LAUNCH_SCRIPT}
RemainAfterExit=no

[Install]
WantedBy=multi-user.target
EOSERVICE

# Activer les services
systemctl daemon-reload
systemctl enable httpd mariadb php-fpm wrightetmathon
log_ok "Services actives : httpd, mariadb, php-fpm, wrightetmathon"

# Demarrer les services
systemctl start mariadb
systemctl start php-fpm
systemctl start httpd
log_ok "Services demarres"

# ============================================================================
# ETAPE 13 : SELINUX & FIREWALL
# ============================================================================
log_section "ETAPE 13/13 — SELinux et Firewall"

# SELinux : desactiver (coherent avec l'installation actuelle)
SELINUX_CONF="/etc/selinux/config"
if [[ -f "$SELINUX_CONF" ]]; then
    CURRENT_SELINUX=$(getenforce 2>/dev/null || echo "Unknown")
    if [[ "$CURRENT_SELINUX" == "Enforcing" || "$CURRENT_SELINUX" == "Permissive" ]]; then
        log_info "SELinux actuellement : $CURRENT_SELINUX"
        sed -i 's/^SELINUX=enforcing/SELINUX=disabled/' "$SELINUX_CONF"
        sed -i 's/^SELINUX=permissive/SELINUX=disabled/' "$SELINUX_CONF"
        setenforce 0 2>/dev/null || true
        log_warn "SELinux desactive (necessite un reboot pour etre permanent)"
    else
        log_ok "SELinux deja desactive"
    fi
fi

# Firewall : ouvrir le port HTTP
if systemctl is-active firewalld &>/dev/null; then
    firewall-cmd --permanent --add-service=http 2>/dev/null || true
    firewall-cmd --permanent --add-service=https 2>/dev/null || true
    firewall-cmd --reload 2>/dev/null || true
    log_ok "Firewall : ports HTTP/HTTPS ouverts"
else
    log_warn "firewalld n'est pas actif — ports non configures"
fi

# ============================================================================
# CREATION DU RACCOURCI BUREAU
# ============================================================================
log_section "RACCOURCI BUREAU"

DESKTOP_DIR="/home/$SYS_USER/Desktop"
mkdir -p "$DESKTOP_DIR" 2>/dev/null || true

if [[ -d "$DESKTOP_DIR" ]]; then
    cat > "$DESKTOP_DIR/WrightEtMathon.desktop" <<EODESKTOP
[Desktop Entry]
Name=Wright et Mathon POS
Comment=${SHOP_DESC}
Exec=bash ${APP_DIR}/wm.sh
Icon=chromium-browser
Terminal=false
Type=Application
Categories=Office;Finance;
EODESKTOP
    chmod +x "$DESKTOP_DIR/WrightEtMathon.desktop"
    chown "$SYS_USER":"$SYS_USER" "$DESKTOP_DIR/WrightEtMathon.desktop"
    log_ok "Raccourci bureau cree"
else
    log_warn "Repertoire Desktop non trouve"
fi

# ============================================================================
# CONFIGURATION AUTOLOGIN (optionnel)
# ============================================================================
log_section "AUTOLOGIN"

echo -e "Configurer l'autologin de l'utilisateur $SYS_USER au demarrage ?"
read -rp "$(echo -e "${BOLD}(o/N)${NC} ")" AUTOLOGIN
if [[ "$AUTOLOGIN" == "o" || "$AUTOLOGIN" == "O" ]]; then
    # GDM autologin
    GDM_CONF="/etc/gdm/custom.conf"
    if [[ -f "$GDM_CONF" ]]; then
        # Verifier si la section [daemon] existe
        if grep -q "\[daemon\]" "$GDM_CONF"; then
            sed -i "/\[daemon\]/a AutomaticLoginEnable=True\nAutomaticLogin=${SYS_USER}" "$GDM_CONF"
        else
            cat >> "$GDM_CONF" <<EOGDM

[daemon]
AutomaticLoginEnable=True
AutomaticLogin=${SYS_USER}
EOGDM
        fi
        log_ok "Autologin GDM configure pour $SYS_USER"
    else
        log_warn "Fichier GDM non trouve ($GDM_CONF). Autologin non configure."
    fi
fi

# ============================================================================
# AUTOSTART WM.SH A L'OUVERTURE DE SESSION
# ============================================================================
AUTOSTART_DIR="/home/$SYS_USER/.config/autostart"
mkdir -p "$AUTOSTART_DIR"

cat > "$AUTOSTART_DIR/wrightetmathon.desktop" <<EOAUTO
[Desktop Entry]
Name=Wright et Mathon POS Autostart
Exec=bash ${APP_DIR}/wm.sh
Type=Application
X-GNOME-Autostart-enabled=true
EOAUTO

chown -R "$SYS_USER":"$SYS_USER" "/home/$SYS_USER/.config"
log_ok "Autostart configure : wm.sh se lancera a l'ouverture de session"

# ============================================================================
# VERIFICATION FINALE
# ============================================================================
log_section "VERIFICATION FINALE"

ERRORS=0

# Test Apache
if systemctl is-active httpd &>/dev/null; then
    log_ok "Apache    : actif"
else
    log_error "Apache    : INACTIF"
    ((ERRORS++))
fi

# Test PHP-FPM
if systemctl is-active php-fpm &>/dev/null; then
    log_ok "PHP-FPM   : actif"
else
    log_error "PHP-FPM   : INACTIF"
    ((ERRORS++))
fi

# Test MariaDB
if systemctl is-active mariadb &>/dev/null; then
    log_ok "MariaDB   : actif"
else
    log_error "MariaDB   : INACTIF"
    ((ERRORS++))
fi

# Test connexion DB
if mysql -u admin -p"${DB_PASSWORD}" -e "USE \`${DB_NAME}\`;" 2>/dev/null; then
    log_ok "Base      : $DB_NAME accessible"
else
    log_error "Base      : $DB_NAME INACCESSIBLE"
    ((ERRORS++))
fi

# Test INI file
if [[ -f "$INI_FILE" ]]; then
    log_ok "INI       : $INI_FILE present"
else
    log_error "INI       : $INI_FILE MANQUANT"
    ((ERRORS++))
fi

# Test app directory
if [[ -f "$APP_DIR/index.php" ]]; then
    log_ok "App       : index.php present"
else
    log_warn "App       : index.php non trouve (deployer les fichiers)"
fi

# Test session dir
if [[ -d "$SESSION_DIR" && -w "$SESSION_DIR" ]]; then
    log_ok "Sessions  : repertoire accessible en ecriture"
else
    log_error "Sessions  : probleme de permissions"
    ((ERRORS++))
fi

# Test HTTP
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/wrightetmathon/ 2>/dev/null || echo "000")
if [[ "$HTTP_CODE" == "200" || "$HTTP_CODE" == "302" ]]; then
    log_ok "HTTP      : reponse $HTTP_CODE"
elif [[ "$HTTP_CODE" == "000" ]]; then
    log_warn "HTTP      : pas de reponse (Apache vient de demarrer, reessayer)"
else
    log_warn "HTTP      : reponse $HTTP_CODE"
fi

# Resume
echo ""
echo -e "${CYAN}${BOLD}════════════════════════════════════════════════════════════════${NC}"
if [[ $ERRORS -eq 0 ]]; then
    echo -e "${GREEN}${BOLD}  INSTALLATION TERMINEE AVEC SUCCES${NC}"
else
    echo -e "${YELLOW}${BOLD}  INSTALLATION TERMINEE AVEC $ERRORS AVERTISSEMENT(S)${NC}"
fi
echo -e "${CYAN}${BOLD}════════════════════════════════════════════════════════════════${NC}"
echo ""
echo "  URL d'acces      : http://localhost/wrightetmathon"
echo "  phpMyAdmin        : http://localhost/phpMyAdmin"
echo "  Base de donnees   : $DB_NAME"
echo "  Utilisateur MySQL : admin / ${DB_PASSWORD}"
echo "  Utilisateur Linux : $SYS_USER / ${SYS_PASSWORD}"
echo "  Fichier INI       : $INI_FILE"
echo ""
echo -e "${YELLOW}  ACTIONS MANUELLES RESTANTES :${NC}"
echo "  1. Si les fichiers applicatifs ne sont pas encore deployes :"
echo "     rsync -a /source/wrightetmathon/ $APP_DIR/"
echo ""
echo "  2. Importer la base de donnees :"
echo "     mysql -u admin -p'${DB_PASSWORD}' ${DB_NAME} < dump.sql"
echo ""
echo "  3. Si le RCS n'est pas configure :"
echo "     mysql -u admin -p'${DB_PASSWORD}' ${DB_NAME} -e \\"
echo "       \"INSERT INTO ospos_app_config (\\\`key\\\`, \\\`value\\\`) VALUES ('rcs', 'RCS Montpellier');\""
echo ""
echo "  4. Redemarrer le PC pour appliquer SELinux + autologin :"
echo "     sudo reboot"
echo ""

exit $ERRORS
