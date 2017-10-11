<?php
require_once('config.php');
require_once('function.php');
$typedesc=$_POST['type'];
$type = $tables["$typedesc"]['field'];
$table = ($tables["$typedesc"]['milter']) ? milterTable($type) : $tables["$typedesc"]['name'];

openlog($tag, LOG_PID, $fac);
if (empty($_POST['reason'])) die ("<p>Please, specify a reason!</p>");
if (preg_match( '/[^\x20-\x7f]/', $_POST['reason']))
	exit('<p>ERROR: &lt;'.htmlentities($_POST['reason'],ENT_COMPAT | ENT_HTML401, 'ISO-8859-1').'&gt; contains NON ASCII chars.</p>');
$user = username();
$err = NULL;

if ( ($mysqli = myConnect($dbhost, $userdb, $pwd, $db, $dbport, $tables, $typedesc, $user)) === FALSE )
                exit ($user.': Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);

if (addtolist ($mysqli,$user,$_POST['value'],$tables["$typedesc"],$_POST['unit'],$_POST['quantity'],$_POST['reason'],$err))
 print 'OK '.$_POST["type"].' &lt;'.$_POST['value'].'&gt; first time listed for '.$_POST['quantity'].$_POST['unit'].'.';
else
 print 'List operation ERROR; check log.';
if (!is_null($err) ) print htmlentities(' Error: ' . $err);
$mysqli->close();
closelog();
?>
