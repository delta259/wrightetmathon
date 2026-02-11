# start the wrightetmathon system
#
# first restart httpd.service. This is required because without this restart it is not possibe to send Purchase Orders.
# this is a new requirement on F24. I think its something to do with the start up sequence in systemd
# this will ask the user to enter the system password
# However in V26, this is not required,so do a search for the Fedora version and only ask for the restart of httpd.service if its less than 26
# OS information is in the file /etc/os-release; its in an ini like form, so include it
#

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
	if test "$VERSION_ID" -lt 26
	then
		systemctl restart httpd.service
	fi
	#
	# now call the wrightetmathon system with normal user rights
	# & = call in background = control passes to program and terminal exits
	#
	sudo -u $USER chromium-browser --app=http://localhost/wrightetmathon --incognito &
	#
fi

exit 0
