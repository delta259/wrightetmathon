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
rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/POS_SYSTEM/SOFTWARE/$software_folder/" "/var/www/html/wrightetmathon" >> "/home/wrightetmathon/wrightetmathon.log"


# change owner for hidrive mount and unmount php programs
chown root:root /var/www/html/wrightetmathon/application/controllers/hidrive_mount.php
chown root:root /var/www/html/wrightetmathon/application/controllers/hidrive_unmount.php
chown root:root /var/www/html/wrightetmathon/application/controllers/hidrive_software_sync.php

exit
