<?php
// script to mount hidrive

// Check if already mounted
exec('mountpoint -q /home/wrightetmathon/.hidrive.sonrisa', $mp_output, $mp_code);
if ($mp_code === 0) {
    return; // Already mounted
}

// Create mount point if needed
if (!is_dir('/home/wrightetmathon/.hidrive.sonrisa')) {
    exec('sudo mkdir -p /home/wrightetmathon/.hidrive.sonrisa');
}

// Clean up stale PID file
exec('sudo rm -f /var/run/mount.davfs/home-wrightetmathon-.hidrive.sonrisa.pid 2>/dev/null');

// Mount HiDrive
exec('sudo mount -t davfs -o _netdev,rw,dir_mode=0777,file_mode=0666 "https://webdav.hidrive.strato.com/users/drive-6774" "/home/wrightetmathon/.hidrive.sonrisa" 2>&1', $mount_output, $mount_code);

// Wait for mount to be ready (max 10 seconds)
$max_wait = 10;
$waited = 0;
while ($waited < $max_wait) {
    exec('mountpoint -q /home/wrightetmathon/.hidrive.sonrisa', $mp_output, $mp_code);
    if ($mp_code === 0) {
        break; // Mount is ready
    }
    sleep(1);
    $waited++;
}
?>
