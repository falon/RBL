<?php
require_once('config.php');
require('function.php');
require_once 'vendor/autoload.php';
$net = new \dautkom\ipv4\IPv4();

$_ = $_POST['genere'];
if ( ($tables["$_"]['field']=='email') AND ($_POST['Value']!='ALL') )
	if (!(filter_var($_POST['Value'], FILTER_VALIDATE_EMAIL)))
		exit ('<pre>&lt;'.$_POST['Value'].'&gt; is NOT a valid email address.</pre>');

if ( ($tables["$_"]['field']=='domain') AND ($_POST['Value']!='ALL') )
        if (!(isValid($_POST['Value'])))
		exit ('<pre>&lt;'.$_POST['Value'].'&gt; is NOT a valid domain.</pre>');

if ( ($tables["$_"]['field']=='ip')  AND ($_POST['Value']!='ALL') )
	if (!(filter_var($_POST['Value'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)))
		exit ('<pre>&lt;'.$_POST['Value'].'&gt; is NOT a valid IP address.</pre>');
	
if ( ($tables["$_"]['field']=='network') AND ($_POST['Value']!='ALL') ) {
	$value = explode('/',$_POST['Value']);
	if (count($value) != 2)
		exit ('<pre>&lt;'.$_POST['Value'].'&gt; is NOT a valid Network/Netmask pair.</pre>');
	if (!$net->address($value[0])->mask($value[1])->isValid(1))
		exit ('<pre>&lt;'.$_POST['Value'].'&gt; is NOT a valid Network/Netmask.</pre>');
	$_POST['Value'] = $value[0].'/'.$net->mask($value[1])->convertTo('dec');
}

if ( ($tables["$_"]['field']=='username') AND ($_POST['Value']!='ALL') ) {
        if ( preg_match( '/[^\x20-\x7f]/', $_POST['Value']) )
                exit('<pre>&lt;'.$_POST['Value'].'&gt; contains NON ASCII chars.</pre>');
	if ( preg_match( '/[$~=#*+%,{}()\/\\<>;:\"`\[\]&?\s]/', $_POST['Value']) )
		exit('<pre>&lt;'.$_POST['Value'].'&gt; contains invalid ASCII chars.</pre>');
	switch ( $_POST['Value'] ) {
		case 'anonymous':
		case 'anybody':
		case 'anyone':
		case ( preg_match( '/^anyone@/',$_POST['Value']) == TRUE ):
			exit('<pre>&lt;'.$_POST['Value'].'&gt; is not allowed.</pre>');
	}
}	

if (empty($_GET)) {
	if ($tables["$_"]['milter']) print "<p><i>$_</i> is a miltermap of ".$tables["$_"]['field'].'.</p>';
	else {
		if ($tables["$_"]['bl']) print "<p><i>$_</i> is a blocklist of ".$tables["$_"]['field'].'.</p>';
		else                     print "<p><i>$_</i> is a whitelist of ".$tables["$_"]['field'].'.</p>';
	}
}

openlog($tag, LOG_PID, $fac);
$user = username();

if ( ($mysqli = myConnect($dbhost, $userdb, $pwd, $db, $dbport, $tables, $_, $user)) === FALSE )
	exit ('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
	
rlookup($mysqli,username(),$admins,$_POST['Value'],$_POST['genere'],$tables);
$mysqli->close();
closelog();
?>
