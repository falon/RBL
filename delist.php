<?php
require_once('config.php');
require_once('function.php');
$typedesc=$_POST['type'];
        $type = $tables["$typedesc"]['field'];
        $table = $tables["$typedesc"]['name'];
?>
<td colspan="9" style="text-align: center">
<?php
openlog($tag, LOG_PID, $fac);
$user = username();
$mysqli = new mysqli($dbhost, $userdb, $pwd, $db, $dbport);
        if ($mysqli->connect_error) {
            syslog (LOG_EMERG, $user."\t".'Connect Error (' . $mysqli->connect_errno . ') '
                    . $mysqli->connect_error);
            exit ($user."\t".'Connect Error (' . $mysqli->connect_errno . ') '
                    . $mysqli->connect_error);
}
syslog(LOG_INFO, $user."\t".'Successfully connected to ' . $mysqli->host_info) ;

if (changestatus($mysqli,username(),$_POST['value'],'0',$type,$table))
 print 'OK '.$_POST["type"].' &lt;'.$_POST['value'].'&gt; delisted.';
else
 print 'ERROR in delist &lt;'.$_POST['value'].'&gt;; check log';
print '</td>';
$mysqli->close();
closelog();
?>
