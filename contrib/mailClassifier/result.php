<?php
$path='/var/www/html/RBL/';
include_once($path.'config.php');
require_once($path.'function.php');

require_once('function.php');
$conf = parse_ini_file('imap.conf', TRUE);
$confimap = $conf['imap'];
$folder = $_POST['folder'];
$account = $_POST['username'];
$username = username();

 /* Syslog */
$tag .= $conf['syslog']['tag'];
openlog($tag, LOG_PID, $fac);

if ( $confimap['learn']=='dspamc' ) {
	$cmd = escapeshellcmd('which dspamc');
	$cmd = escapeshellcmd('which ls');
	exec ( $cmd, $out, $ret );
	if ($ret != 0) {
		$err = 'No DSPAM Client found on your system. Please, force your sysadmin to install "dspamc".';
		syslog(LOG_ERR, $username.': Error: '.$err);
		exit (sprintf('<p>%s</p>',htmlentities($err)));
	}
}


$data = array(
                'date' => NULL,
                'from' => NULL,
                'messageid' => NULL,
                'dmarc' => array(
                        'result' => NULL,
                        'dom'   => NULL
                        ),
                'spf' => array(
                        'result' => NULL,
                        'dom'   => NULL
                        ),
                'dkim' => array(
                        'result' => NULL,
                        'dom'   => NULL
                        ),
                'spam' => array(
                        'status' => NULL,
                        'score' => NULL,
                        'th'    => NULL,
                        ),
                'dspam' => array(
                        'type' => NULL,
                        'level' => NULL,
                        'learn' => NULL
                        ),
                'warn' => NULL
);

if (empty($folder)) exit ('<p>No folder found.</p>'); /* This should not occur */
$confimap['user'] = $username;
$headers = imapFind($confimap, $account, $folder);
if (empty($headers)) exit (sprintf('<p>No suitable mail found in <b>%s</b> folder.</p>', htmlentities("<$folder>")));
print '<table>';
printTableHeader($folder,$data,TRUE,sprintf('Found %d suitable mails.',count($headers)));

foreach ( $headers AS $header ) {
	$values = imapInfo($username, $header,$conf['host']['ar'],$confimap['dspamtospamass'],$confimap['learn']);
	print '<tr>';
	printTableRow($values, $confimap['learn']);
	print '</tr>';
}
print '</table>';
print '<div id="Learnbox"></div>';
?>
