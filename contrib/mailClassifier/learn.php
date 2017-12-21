<div id="content">
<h3>Log</h3>
<?php
$path='/var/www/html/RBL/';
require_once($path.'function.php');
require_once($path.'config.php');
$conf = parse_ini_file('imap.conf', TRUE);
$username = username();

 /* Syslog */
$tag .= $conf['syslog']['tag'];
openlog($tag, LOG_PID, $fac);
$par = json_decode(base64_decode($_POST['par'], true));
printf('<p>The mail with signature %s was so classified when it was received:</p>', htmlentities('<'.$par->learn.'>'));
printf('<ul><li>Type: <b>%s</b></li><li>Level: <b>%d</b></li></ul>', $par->type, $par->level); 

syslog(LOG_INFO, sprintf('%s: Learn as <%s> on signature: <%s>', $username, $par->class, $par->learn));
$cmd = escapeshellcmd($_POST['cmd']);
exec ( $cmd, $out, $ret );
if ($ret != 0) {
	$err = 'DSPAM Client returns a bad exit state. Sorry, probably the learn was successful, but I don\'t know...';
	syslog(LOG_ERR, $username.': Learn Error: '.$err);	
	exit (sprintf('<p>%s</p>',htmlentities($err)));
}
syslog(LOG_INFO,'%s: Learn result: "%s"',$username, $out[0]);  
if ( preg_match ('/^X-DSPAM-Result:\s+(?P<user>[\w\.\@]+);\s+result="(?P<result>\w+)";\s+class="(?P<class>\w+)";\s+probability=(?P<prob>[\d\.]+);\s+confidence=(?P<conf>[\d\.]+);\s+signature=(?P<sig>[\w\,]+)$/',$out[0],$received) != 1) {
		$err = 'DSPAM Client returned an unparseable result.';
		syslog(LOG_ERR, $username.': Learn Error: '.$err);
                exit (sprintf('<p>%s</p>',htmlentities($err)));
}

printf('<p>Message learned successfully with following result:</p><ul><li>Owner: <b>%s</b></li><li>Result: <b>%s</b></li><li>Class: <b>%s</b></li></ul>',
	htmlentities($received['user']),
        $received['result'], $received['class']);
closelog();
?>
</div>
