<html>
<head>
<title>Mail Classifier</title>
<link rel="stylesheet" type="text/css" href="/include/style.css">
<link rel="SHORTCUT ICON" href="favicon.ico">
<script  src="/include/ajaxsbmt.js" type="text/javascript"></script>
<script src="https://ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/prototype.js" type="text/javascript" charset="utf-8"></script>
<script src="https://ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/scriptaculous.js?load=effects" type="text/javascript" charset="utf-8"></script>
</head>
<body>
<h1> Mail Classifier</h1> 
<?php
$path='/var/www/html/RBL/';
require_once($path.'config.php');
require_once($path.'function.php');
require_once('function.php');

if ( !isset($version) ) {
        openlog('mailClassifierEmergency', LOG_PID, LOG_LOCAL0);
        syslog (LOG_EMERG, sprintf('unknown: I can\'t read the config files. Do you have configured the $path in %s?', __FILE__));
        closelog();
        exit(255);
}

checkSSL();
$user = username();
$isAdmin = in_array($user,array_keys($admins));
$canChange = ($isAdmin) ? '' : 'readonly';
if ( $require_auth )
	if ( $user == 'unknown' ) exit ("<p>You MUST configure your server to use authentication.</p>");


print <<<END
<form method="POST" name="Classify" action="list.php" onSubmit="xmlhttpPost('list.php', 'Classify', 'list', '<img src=\'/include/pleasewait.gif\'>', false); return false;">
<input type="submit"
       style="display:none"
       tabindex="-1" />
<table>
END;
printTableHeader('Click on the user box', array('user' => NULL, 'folder' => NULL), TRUE, 'Know your mails, trust your mails.');
print <<<END
<tr>
<td><input maxlength="255" value="$user" type="email" name="username" $canChange placeholder="type the username"
                title="Look at your syntax. You must insert a valid email address."
		onFocus="xmlhttpPost('none.htm','Classify', 'Result', '<img src=\'/include/pleasewait.gif\'>', false); return false;"
		onClick="xmlhttpPost('list.php', 'Classify', 'list', '<img src=\'/include/pleasewait.gif\'>', false); return false;"
                required>
</td>
<td id="list" style="width:215px"></td>
</tr>
</table>
</form>
END;

?>
<DIV id="Result"></DIV>
<hr>
<h5 style="margin-top:1ex;margin-bottom:0;text-align: center">Your Mail Classifier is presented by <i>RBL Tool System</i> - Version <?php echo $version; ?></h5>
</body>
</html>
