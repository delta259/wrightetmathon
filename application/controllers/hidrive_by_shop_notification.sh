#!/bin/bash

# include the ini file because the software folder to sync from is stored there on initial install
. /var/www/html/wrightetmathon.ini

if [ ! -d "/var/www/html/wrightetmathon/notifications" ]; then
    mkdir /var/www/html/wrightetmathon/notifications
	chmod -R 777 /var/www/html/
fi

rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/SHOPS/PUBLIC/$shopcode/notifications/" "/var/www/html/wrightetmathon/notifications" >> "/home/wrightetmathon/wrightetmathon.log"
#rsync -a --progress --delete --exclude '.*' --exclude 'Hardware' --exclude '*_old' "/home/wrightetmathon/.hidrive.sonrisa/SHOPS/PUBLIC/YESST/notifications/" "/var/www/html/wrightetmathon/notifications/" >> "/home/wrightetmathon/wrightetmathon.log"
