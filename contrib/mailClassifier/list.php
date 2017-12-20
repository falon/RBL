<select name="folder" style="width:215px" onChange="xmlhttpPost('result.php', 'Classify', 'Result', '<img src=\'/include/pleasewait.gif\'>', true); return false;">
<?php
$path='/var/www/html/RBL/';
require_once($path.'function.php');
require_once($path.'config.php');
require_once('function.php');
$conf = parse_ini_file('imap.conf', TRUE);

 /* Syslog */
$tag .= $conf['syslog']['tag'];
openlog($tag, LOG_PID, $fac);

$conf['imap']['user'] = username();
$folders=imapFolder($conf['imap'], $_POST['username']);
print '<option  value="" selected disabled>Choose a folder</option>';
foreach ( $folders as $folder )
        printf('<option  value="%s">%s</option>',
		$folder,
		htmlspecialchars(mb_convert_encoding($folder, "UTF-8", "UTF7-IMAP")));
closelog();
?>
</select>
