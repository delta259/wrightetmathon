#!/bin/bash

#create connection
/var/www/html/wrightetmathon/application/controllers/hidrive_connection_create.sh

sudo chmod -R 777 /var/www/html/wrightetmathon/

#delete file STOCK_LOGISTIQUE.csv
#rm /var/www/html/wrightetmathon/STOCK/STOCK_LOGISTIQUE.csv

#synchronise les répértoires pour les stocks à l'entrepôt
rsync -a --progress "/home/wrightetmathon/.hidrive.sonrisa/SONRISA_CENTRAL/ARTICLES/STOCK/" "/var/www/html/wrightetmathon/STOCK/"

#delete connection
#/var/www/html/wrightetmathon/application/controllers/hidrive_connection_delete.sh

#scp /home/wrightetmathon/.hidrive.sonrisa/SONRISA_CENTRAL/ARTICLES/STOCK/STOCK_LOGISTIQUE.csv /var/www/html/wrightetmathon/STOCK/
sudo chmod -R 777 /var/www/html/wrightetmathon/STOCK/
