<?php
require_once('config.php');
require_once('function.php');
$typedesc=$_POST['type'];
$type =  $tables["$typedesc"]['field'];
$table = $tables["$typedesc"]['name'];

openlog($tag, LOG_PID, $fac);
$user = username();
$mysqli = new mysqli($dbhost, $userdb, $pwd, $db, $dbport);
if ($mysqli->connect_error) {
	syslog (LOG_EMERG, $user.': Connect Error (' . $mysqli->connect_errno . ') '
                    . $mysqli->connect_error);
        die($user.': Connect Error (' . $mysqli->connect_errno . ') '
                    . $mysqli->connect_error);
}
syslog (LOG_INFO, $user.': Successfully connected to ' . $mysqli->host_info );

if (remove ($mysqli,$user,$_POST['value'],$type,$table))
 print 'OK '.$typedesc.' &lt;'.$_POST['value'].'&gt; permanently REMOVED!';
else
 print 'Delete operation ERROR on '.$typedesc.' &lt;'.$_POST['value'].'&gt;; check log.';
$mysqli->close();
closelog();
?>
