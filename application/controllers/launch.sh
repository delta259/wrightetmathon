#!/bin/bash
#
# Launch Wright et Mathon point of sale Service
# Note; this script is called from the /etc/systemd/system/wrightetmathon.service.
# Use systemctl to manage this service. It needs to be enable(ed) in order for it to run automatically at system start.
# systemd runs as root, so no need to use sudo or su in this script.
#

# set permissions
chmod -R 777  /var/www/html/
chmod -R 777  /var/lib/mysql/
#chmod -R 777  /home/

# delete dummy file
rm -f /var/www/html/dummy.txt

# delete PHP session files
rm -f /var/www/html/wrightetmathon/session/*

# delete Pidgin archives
rm -Rf /home/wrightetmathon/.purple/logs/jabber/*

# change permissions for the pole display device
if [ -e /dev/ttyUSB0 ]
then
  chmod 777 /dev/ttyUSB0
fi

# remove app lock file
if [ -f "/home/wrightetmathon/.app_running.txt" ]
then
	rm "/home/wrightetmathon/.app_running.txt"
fi

# run Fedora system upgrade
#dnf --assumeyes upgrade

# exit script
exit 0
