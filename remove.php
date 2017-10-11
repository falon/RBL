<?php
require_once('config.php');
require_once('function.php');
$typedesc=$_POST['type'];
$type =  $tables["$typedesc"]['field'];
$table = ($tables["$typedesc"]['milter']) ? milterTable($type) : $tables["$typedesc"]['name'];
$cl = ($tables["$typedesc"]['milter']) ? 10 : 9;
printf('<td colspan="%d" style="text-align: center">', $cl);
openlog($tag, LOG_PID, $fac);
$user = username();

if ( ($mysqli = myConnect($dbhost, $userdb, $pwd, $db, $dbport, $tables, $typedesc, $user)) === FALSE )
                exit ($user.': Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
if (remove ($mysqli,$user,$_POST['value'],$type,$table))
 print 'OK '.$typedesc.' &lt;'.$_POST['value'].'&gt; permanently REMOVED!';
else
 print 'Delete operation ERROR on '.$typedesc.' &lt;'.$_POST['value'].'&gt;; check log.';
print '</td>';
$mysqli->close();
closelog();
?>
