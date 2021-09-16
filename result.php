<?php
require_once('config.php');
require('function.php');
require_once 'vendor/autoload.php';
$net = new \dautkom\ipv4\IPv4();

$_ = $_POST['genere'];
$value = iconv(mb_detect_encoding($_POST['Value'], mb_detect_order('ISO-8859-15, ISO-8859-1')), "UTF-8", $_POST['Value']);

if ( ($tables["$_"]['field']=='email') AND ($value!='ALL') )
	if (!(filter_var($_POST['Value'], FILTER_VALIDATE_EMAIL)))
		exit ('<pre>&lt;'.$value.'&gt; is NOT a valid email address.</pre>');

if ( ($tables["$_"]['field']=='domain') AND ($value!='ALL') )
        if (!(isValid($value)))
		exit ('<pre>&lt;'.$value.'&gt; is NOT a valid domain.</pre>');

if ( ($tables["$_"]['field']=='ip')  AND ($value!='ALL') )
	if (!(filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)))
		exit ('<pre>&lt;'.$value.'&gt; is NOT a valid IP address.</pre>');
	
if ( ($tables["$_"]['field']=='network') AND ($value!='ALL') ) {
	$values = explode('/',$value);
	if (count($values) != 2)
		exit ('<pre>&lt;'.$value.'&gt; is NOT a valid Network/Netmask pair.</pre>');
	if (!$net->address($values[0])->mask($values[1])->isValid(1))
		exit ('<pre>&lt;'.$value.'&gt; is NOT a valid Network/Netmask.</pre>');
	$value = $values[0].'/'.$net->mask($values[1])->convertTo('dec');
}

if ( ($tables["$_"]['field']=='username') AND ($value!='ALL') ) {
        if ( preg_match( '/[^\x20-\x7f]/', $value) )
                exit('<pre>&lt;'.$value.'&gt; contains NON ASCII chars.</pre>');
	if ( preg_match( '/[$~=#*+%,{}()\/\\<>;:\"`\[\]&?\s]/', $value) )
		exit('<pre>&lt;'.$value.'&gt; contains invalid ASCII chars.</pre>');
	switch ( $value ) {
		case 'anonymous':
		case 'anybody':
		case 'anyone':
		case ( preg_match( '/^anyone@/',$value) == TRUE ):
			exit('<pre>&lt;'.$value.'&gt; is not allowed.</pre>');
	}
}	

if ($tables["$_"]['field']=='text') {
	if ( preg_match( '/[^\x20-\x7fàèìòù]/i', $value ) ) 
		exit('<p>ERROR: &lt;'.htmlentities($_POST['Value'],ENT_COMPAT | ENT_HTML401, 'ISO-8859-1').'&gt; contains UTF8 chars not allowed.</p>');
	if ( !preg_match( '/^\S+(?:[\s\t]+\S+){0,1}$/', $value ) )
		exit('<p>ERROR: &lt;'.htmlentities($_POST['Value'],ENT_COMPAT | ENT_HTML401, 'ISO-8859-1').'&gt; contains more than two words, or unallowed spaces.</p>');
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
	exit ('Connect Error (' . htmlentities($mysqli->connect_errno) . ') '. htmlentities($mysqli->connect_error));

rlookup($mysqli,$user,$admins,$value,$_POST['genere'],$tables);
$mysqli->close();
closelog();
?>
