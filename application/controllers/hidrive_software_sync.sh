#!/bin/bash

# create log file
if [ -f "/home/wrightetmathon/wrightetmathon.log" ]; then
	rm "/home/wrightetmathon/wrightetmathon.log"
fi
touch "/home/wrightetmathon/wrightetmathon.log"

# include the ini file because the software folder to sync from is stored there on initial install
. /var/www/html/wrightetmathon.ini

# script to sync the software from hidrive to local machine
echo "This is the software folder -> $software_folder <- that will get synchronised." >> "/home/wrightetmathon/wrightetmathon.log"
#
if [ -d "/var/www/html/w_backup" ]; then
    mkdir /var/www/html/w_backup
	chmod -R 777 /var/www/html/w_backup
fi
rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/var/www/html/wrightetmathon/" "/var/www/html/w_backup"
#rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' --backup --backup-dir="/var/www/html/w_backup/" "/home/wrightetmathon/.hidrive.sonrisa/POS_SYSTEM/SOFTWARE/$software_folder/" "/var/www/html/wrightetmathon" >> "/home/wrightetmathon/wrightetmathon.log"
rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/POS_SYSTEM/SOFTWARE/$software_folder/" "/var/www/html/wrightetmathon" >> "/home/wrightetmathon/wrightetmathon.log"
#rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/POS_SYSTEM/SOFTWARE/$software_folder/application/" "/var/www/html/wrightetmathon/application/" >> "/home/wrightetmathon/wrightetmathon.log"
#rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/POS_SYSTEM/SOFTWARE/$software_folder/Charts/" "/var/www/html/wrightetmathon/Charts/" >> "/home/wrightetmathon/wrightetmathon.log"
#rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/POS_SYSTEM/SOFTWARE/$software_folder/css/" "/var/www/html/wrightetmathon/css/" >> "/home/wrightetmathon/wrightetmathon.log"
#rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/POS_SYSTEM/SOFTWARE/$software_folder/images/" "/var/www/html/wrightetmathon/images/" >> "/home/wrightetmathon/wrightetmathon.log"
#rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/POS_SYSTEM/SOFTWARE/$software_folder/images2/" "/var/www/html/wrightetmathon/images2/" >> "/home/wrightetmathon/wrightetmathon.log"
#rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/POS_SYSTEM/SOFTWARE/$software_folder/images_sonrisa/" "/var/www/html/wrightetmathon/images_sonrisa/" >> "/home/wrightetmathon/wrightetmathon.log"
#rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/POS_SYSTEM/SOFTWARE/$software_folder/images_yes/" "/var/www/html/wrightetmathon/images_yes/" >> "/home/wrightetmathon/wrightetmathon.log"
#rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/POS_SYSTEM/SOFTWARE/$software_folder/jquery-ui-1.12.1.custom/" "/var/www/html/wrightetmathon/jquery-ui-1.12.1.custom/" >> "/home/wrightetmathon/wrightetmathon.log"
#rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/POS_SYSTEM/SOFTWARE/$software_folder/js/" "/var/www/html/wrightetmathon/js/" >> "/home/wrightetmathon/wrightetmathon.log"
#rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/POS_SYSTEM/SOFTWARE/$software_folder/Procedures/" "/var/www/html/wrightetmathon/Procedures/" >> "/home/wrightetmathon/wrightetmathon.log"
#rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/POS_SYSTEM/SOFTWARE/$software_folder/SLIDES/" "/var/www/html/wrightetmathon/SLIDES/" >> "/home/wrightetmathon/wrightetmathon.log"
#rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/POS_SYSTEM/SOFTWARE/$software_folder/SLIDES_VENTES/" "/var/www/html/wrightetmathon/SLIDES_VENTES/" >> "/home/wrightetmathon/wrightetmathon.log"
#rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/POS_SYSTEM/SOFTWARE/$software_folder/system/" "/var/www/html/wrightetmathon/system/" >> "/home/wrightetmathon/wrightetmathon.log"
#rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/POS_SYSTEM/SOFTWARE/$software_folder/book.pdf" "/var/www/html/wrightetmathon/book.pdf" >> "/home/wrightetmathon/wrightetmathon.log"
#rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/POS_SYSTEM/SOFTWARE/$software_folder/flash_info_publish_me.pdf" "/var/www/html/wrightetmathon/flash_info_publish_me.pdf" >> "/home/wrightetmathon/wrightetmathon.log"
#rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/POS_SYSTEM/SOFTWARE/$software_folder/version.ini" "/var/www/html/wrightetmathon/version.ini" >> "/home/wrightetmathon/wrightetmathon.log"
#
rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/SONRISA_CENTRAL/ARTICLES/STOCK/" "/var/www/html/wrightetmathon/STOCK/" >> "/home/wrightetmathon/wrightetmathon.log"


# change owner for hidrive mount and unmount php programs
chown root:root /var/www/html/wrightetmathon/application/controllers/hidrive_mount.php
chown root:root /var/www/html/wrightetmathon/application/controllers/hidrive_unmount.php
chown root:root /var/www/html/wrightetmathon/application/controllers/hidrive_software_sync.php

chmod -R 777 /var/www/html
chown root:root /var/www/html/wrightetmathon/application/controllers/wrightetmathon.sh
chown root:root /var/www/html/wrightetmathon/application/controllers/shutdown_all.sh
chown root:root /var/www/html/wrightetmathon/application/controllers/hidrive_remote_stock_logistique.sh


chown root:root /var/www/html/wrightetmathon/application/controllers/whoami.sh
chmod u+x /var/www/html/wrightetmathon/application/controllers/whoami.sh


chmod -R 777 /var/www/html/w_backup
chown root:root /var/www/html/w_backup/application/controllers/wrightetmathon.sh

#sudo chown -R root:root /home/wrightetmathon/tmp/vs_distributeur/

exit
