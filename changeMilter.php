<?php
require_once('config.php');
require_once('function.php');
$typedesc=$_POST['type'];
$type = $tables["$typedesc"]['field'];
$col = milterTable($type);
if ( $col === FALSE )
	exit ("<p>Error in you config at field <b>$type</b>.</p>");
?>
<td colspan="10" style="text-align: center">

<?php
openlog($tag, LOG_PID, $fac);
$user = $_POST['user'];

/* Compare old values with new ones */
if (isset($_POST['newvalues']))
	$new = $_POST['newvalues'];
else
	$new = array();
if (! empty($_POST['oldvalues']))
	$old = explode(',', $_POST['oldvalues']);
else
	$old=array();
$values=array();
$logs=array();

print '<pre>';
/* Check need to disable all milters, removing unnecessary setting */
if ( in_array('DISABLE ALL', $new) )
	$new = array('DISABLE ALL');

/* Compare the values determining what to do */
if (count(array_diff(array_merge($new, $old), array_intersect($new, $old))) !== 0) {
	/* New and old are different (we assume we don't have duplicate values) */
	if (! empty($new) ) {
		foreach ($new as $item) {
			if ( in_array($item, $old) )
				$values["$item"] = 'keep';
			else
				$values["$item"] = 'add';
			$logs[] = "<$item>: ". $values["$item"];
		}
	}
	if (! empty($old) ) {
		foreach ($old as $item) {
        		if (! in_array($item, $new) ) {
                		$values["$item"] = 'del';
				$logs[] = "<$item>: ". $values["$item"];
			}
		}
	}
}

/* Logging */
if ( empty($values) ) 
	print 'No values to change.';
else {
	$msg = sprintf('%s: Changing Milter setting on list <%s> for %s <%s>.',$user,$typedesc,$type, $_POST['object']);
	syslog(LOG_INFO, $msg);
	foreach ($logs as $log)
		syslog(LOG_INFO, "$user: milter $log");
	
	/* Store new values */
	if ( ($mysqli = myConnect($dbhost, $userdb, $pwd, $db, $dbport, $tables, $typedesc, $user)) === FALSE )
        	exit ($user.': Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);

	if (changeMilter ($mysqli,$user,$values,$col,$_POST['miltId']))
		print 'OK milter setting changed.';
	else
		print 'ERROR updating milter setting; check your syslog. No changes made.';

	$mysqli->close();
}

print ' To view the current status click again the "Check" button above.';
closelog();
?>

</td>
