<?php
require_once('config.php');
require_once('function.php');
$typedesc=$_POST['type'];
$type = $tables["$typedesc"]['field'];
$table = ($tables["$typedesc"]['milter']) ? milterTable($type) : $tables["$typedesc"]['name'];
$cl = ($tables["$typedesc"]['milter']) ? 10 : 9;
?>
<td colspan="<?php echo $cl; ?>" style="text-align: center">
<?php
openlog($tag, LOG_PID, $fac);
$user = username();

if ( ($mysqli = myConnect($dbhost, $userdb, $pwd, $db, $dbport, $tables, $typedesc, $user)) === FALSE )
                exit ($user.': Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);

if (changestatus($mysqli,username(),$_POST['value'],'0',$type,$table))
 print 'OK '.$_POST["type"].' &lt;'.$_POST['value'].'&gt; delisted.';
else
 print 'ERROR in delist &lt;'.$_POST['value'].'&gt;; check log';
print '</td>';
$mysqli->close();
closelog();
?>
