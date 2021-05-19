<?php
require_once('config.php');
require_once('function.php');
$typedesc=$_POST['type'];
$value = iconv(mb_detect_encoding($_POST['value'], mb_detect_order('ISO-8859-15, ISO-8859-1')), "UTF-8", $_POST['value']);
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

if (changestatus($mysqli,username(),$value,'0',$type,$table))
 print 'OK '.$_POST["type"].' &lt;'.htmlentities($value).'&gt; delisted.';
else
 print 'ERROR in delist &lt;'.htmlentities($value).'&gt;; check log';
print '</td>';
$mysqli->close();
closelog();
?>
