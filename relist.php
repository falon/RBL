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
if (empty($_POST['reason'])) die ("Specify a reason, please!</td>");
if (preg_match( '/[^\x20-\x7f]/', $_POST['reason']))
        exit('ERROR: &lt;'.htmlentities($_POST['reason'],ENT_COMPAT | ENT_HTML401, 'ISO-8859-1').'&gt; contains NON ASCII chars.</td>');
$user = username();

if ( ($mysqli = myConnect($dbhost, $userdb, $pwd, $db, $dbport, $tables, $typedesc, $user)) === FALSE )
                exit ($user.': Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);

if (isFull($mysqli,$typedesc,$tables)) die("ERROR in relist: ".htmlspecialchars("$typedesc has reached maximum value of ".$tables["$typedesc"]['limit'].' listed items.') );
if (relist ($mysqli,username(),$_POST['value'],$type,$table,$_POST['unit'],$_POST['quantity'],$_POST['reason']))
 print 'OK '.$_POST["type"].' &lt;'.$_POST['value'].'&gt; relisted for '.$_POST['quantity'].$_POST['unit'];
else
 print 'ERROR in relist; check log';
$mysqli->close();
closelog();
print '</td>';
?>
