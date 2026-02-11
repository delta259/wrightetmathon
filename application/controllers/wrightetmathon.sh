# start the wrightetmathon system
#
# first restart httpd.service. This is required because without this restart it is not possibe to send Purchase Orders.
# this is a new requirement on F24. I think its something to do with the start up sequence in systemd
# this will ask the user to enter the system password
# However in V26, this is not required,so do a search for the Fedora version and only ask for the restart of httpd.service if its less than 26
# OS information is in the file /etc/os-release; its in an ini like form, so include it
#

# clean chromium cache
rm -r -d  /home/wrightetmathon/.cache/chromium/Default/Cache/
rm -r -d  /tmp/cache/
rm -fr ~/.cache

#add files vs_sales.txt && vs_credit.txt if not exist    for vapeself distributor 

#if [ -d "/home/wrightetmathon/tmp/vs_distributeur/" ]; then
#	
#    sudo chmod -R 777 /home/wrightetmathon/tmp/vs_distributeur/
#    if [ ! -f "/home/wrightetmathon/tmp/vs_distributeur/vs_ventes.txt" ]; then
#	   sudo touch "/home/wrightetmathon/tmp/vs_distributeur/vs_ventes.txt" 
#    fi
#    if [ ! -f "/home/wrightetmathon/tmp/vs_distributeur/vs_credit.txt" ]; then
#	   sudo touch "/home/wrightetmathon/tmp/vs_distributeur/vs_credit.txt"
#    fi
#	sudo chmod -R 777 /home/wrightetmathon/tmp/vs_distributeur/
#fi
#if [ ! -d "/home/wrightetmathon/tmp/vs_distributeur/" ]; then
#    sudo mkdir /home/wrightetmathon/tmp/
#    sudo mkdir /home/wrightetmathon/tmp/vs_distributeur/ 
#    sudo chmod -R 777 /home/wrightetmathon/tmp/vs_distributeur/
#    touch "/home/wrightetmathon/tmp/vs_distributeur/vs_ventes.txt"
#    touch "/home/wrightetmathon/tmp/vs_distributeur/vs_credit.txt"
#    sudo chmod -R 777 /home/wrightetmathon/tmp/vs_distributeur/
#fi
#
#sudo chown -R root:root /home/wrightetmathon/tmp/vs_distributeur/

sudo mkdir /usr/share/fonts/CenturyGothic
sudo cp /var/www/html/wrightetmathon/application/fonts/Century-Gothic.ttf /usr/share/fonts/CenturyGothic/
sudo fc-cache -v
sudo rm -r /home/*/.config/chromium/Default/Local\ Storage/* 
sudo rm -rf ~/.cache/google-chrome/Default/*
sudo rm -rf ~/.cache/chromium/Default/*


# check already running
if [ -f "/home/wrightetmathon/.app_running.txt" ]
then
	# process is already running
	exit  
else
	# process is not already running
	# touch the file
	touch "/home/wrightetmathon/.app_running.txt"
	#
	# include the os-release information
	#
	. /etc/os-release
	#
	# test the version to see if restart of httpd is required
	#
	#sudo systemctl restart httpd.service
	#
	# now call the wrightetmathon system with normal user rights
	# & = call in background = control passes to program and terminal exits
	#
	# sudo -u $USER chromium-browser --app=http://localhost/wrightetmathon --incognito &
	sudo -u $USER chromium-browser --app=http://localhost/wrightetmathon --incognito $chrome --disk-cache-dir=/tmp/cache --start-maximized
	#
fi

exit 0
