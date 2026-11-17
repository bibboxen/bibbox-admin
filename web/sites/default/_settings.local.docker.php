<?php

$databases['default']['default'] = array(
 'database' => 'db',
 'username' => 'db',
 'password' => 'db',
 'host' => 'mariadb',
 'port' => '',
 'driver' => 'mysql',
 'prefix' => '',
);

// Replace the domain on a server.
$settings['trusted_host_patterns'] = ['^bibbox-admin\.local\.itkdev\.dk$'];
