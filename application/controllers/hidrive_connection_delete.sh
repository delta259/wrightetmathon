#!/bin/bash

# script to unmount hidrive

# un-mount hidrive
# --internal-only = don't call the davfs2 helper as this will try to sync the cache which takes a long time
# --lazy = unmount now and clean up later
umount --internal-only --lazy /home/wrightetmathon/.hidrive.sonrisa

# check unounted
if [[ $(findmnt --mountpoint "/home/wrightetmathon/.hidrive.sonrisa") ]]
then
	# found do nothing
	echo 'do nothing' > /dev/null
else
	echo 'do nothing' > /dev/null
	# not found
	# rm -R "/home/wrightetmathon/.hidrive.sonrisa"
fi
