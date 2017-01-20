#!/usr/bin/php

<?php

/*
# See at config.php to set expire time in years.
# All messages recorded through syslog
*/

$user = 'cronjob';
$base = '/var/www/html/RBL';
require $base.'/config.php';
require $base.'/function.php';

openlog($tag, LOG_PID, $fac);
$mysqli = new mysqli($dbhost, $userdb, $pwd, $db, $dbport);
if ($mysqli->connect_error) {
            syslog (LOG_EMERG, $user."\t".'Connect Error (' . $mysqli->connect_errno . ') '
                    . $mysqli->connect_error);
            die($user."\t".'Connect Error (' . $mysqli->connect_errno . ') '
                    . $mysqli->connect_error);
}

syslog (LOG_INFO, $user."\t".'Successfully connected to ' . $mysqli->host_info );

expire($mysqli,$user,$tables,$expireTime);
$mysqli->close();
closelog();
?>
