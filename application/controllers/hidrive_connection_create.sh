#!/bin/bash

# script to mount hidrive

# unmount first
. /var/www/html/wrightetmathon/application/controllers/hidrive_connection_delete.sh

# create mount point if it doesn't exist
if [ ! -d "/home/wrightetmathon/.hidrive.sonrisa" ]; then
	mkdir "/home/wrightetmathon/.hidrive.sonrisa"
fi

# mount hidrive in webdav mode if not already mounted. hidrive is mounted in read/write mode
mountpoint "/home/wrightetmathon/.hidrive.sonrisa" > /dev/null
if [ $? -eq '1' ]
then
	rm -f /var/run/mount.davfs/home-wrightetmathon-.hidrive.sonrisa.pid
	mount -t davfs -o _netdev,rw,dir_mode=0777,file_mode=0666 "https://webdav.hidrive.strato.com/users/drive-6774" "/home/wrightetmathon/.hidrive.sonrisa"
fi

exit
