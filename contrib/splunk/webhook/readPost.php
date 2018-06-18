<?php
/*
#
# Splunk to Policy Listing is Awesome.
# This script can be triggered from a Splunk Webhook to send
# malicious address or username to the RBL Manager.
# Splunk requires to place this script in a folder
# without HTTP AUTH. This is mandatory, at the moment...
#
# Requirement:
# Splunk must connect through webhook to the server running RBL Manager
# RBL manager server must connect to the Splunk results_link uri
#
*/


/************** Conf *******************/
$base = '/var/www/html/RBL';
require_once($base . '/config.php');

 /* Syslog basic */
$tag            .= 'SplunkLister';
$user		 = 'Splunk';

openlog($tag, LOG_PID, $fac);

 /* Conf */
if (!isset($_GET['conf'])) {
        syslog(LOG_ALERT,
        sprintf('%s: you must insert the config file name as a GET parameter, such as %s?conf=listEmail.conf',
                $user, $_SERVER['SCRIPT_NAME']) );
        exit(254);
}
$fileconf = $_GET['conf'];
if ( !file_exists(dirname(__FILE__) . '/../' . $fileconf) ) {
        syslog(LOG_ALERT,
        sprintf('%s: the configuration file <%s> doesn\'t exist.',
                $user, $fileconf ));
        exit(254);
}

closelog();
$conf = parse_ini_file( dirname(__FILE__) . '/../' . $fileconf );

 /* Splunk inherited parameters */
$threshold = $conf['threshold'];         /* Threshold value on trigger condition; the same which engage the alert */

 /* Blacklist name */
$typedesc  = $conf['typedesc'];

 /* How long to list's parameters */
$unit = $conf['unit'];          /* MySQL language ;) */

 /* Syslog extended info */
$tag            .= $conf['tag'];

 /* Splunk password of alert owner*/
$splpwd = $conf['splunkpassword'];
/************** End of conf *************************/

openlog($tag, LOG_PID, $fac);
require_once($base . '/function.php');


/* check you select a blocklist */
if ( !$tables["$typedesc"]['bl'] ) {
        syslog(LOG_EMERG,"$user: <$typedesc> is not a blocklist. Are you stupid? Do you want to whitelist a spammer? I refuse to continue.");
        exit (254);
}



/*
	How to read a webhook, eh eh eh
	http://coconut.co/how-to-create-webhooks
	https://stackoverflow.com/questions/8893574/php-php-input-vs-post
*/

$raw = file_get_contents("php://input");
$webhook = json_decode($raw, true);

#file_put_contents('postalert', print_r($webhook, true));

/*
   The webhook return only the first result. I need to read the 
   link_results to get ALL values:

   curl -k -u <user>:<pass>  http://<splunkhostname>:8089/servicesNS/admin/postfix/search/jobs/<SID>/results?"output_mode=csv"
*/

/* result link example
[results_link] => http://<splunkhost>:8000/app/postfix/@go?sid=scheduler__admin__postfix__RMD53cb038e5bc1899c7_at_1510131600_1915
*/

if (preg_match_all('/^https?\:\/\/(?<splunkhost>[\w\.\-]+)\:8000(?:\/[\w\-\_\d]+)*\/app\/(?<splunkapp>[\w\.\-\_\d]+)\/\@go\?sid=(?<job>[\w\.\-\d]+)$/',
	$webhook['results_link'], $out, PREG_PATTERN_ORDER) === FALSE) {
	syslog(LOG_ALERT,
        	sprintf('%s: unexpected error: can\'t parse the results link returned by webhook (<%s>).',
		$user, $webhook['results_link']) );
	return 255;
}

if ( $webhook['app'] != $out['splunkapp'][0] ) {
	syslog(LOG_ALERT,
        	sprintf('%s: unexpected error: the APP returned by webhook (<%s>) doesn\'t match the app (<%s>) in result link.',
                $user, $webhook['app'], $out['splunkapp'][0] ) );
	return 255;
}

$url = sprintf('http://%s:%d/servicesNS/%s/%s/search/jobs/%s/results',
	$out['splunkhost'][0], $conf['splunkport'], $webhook['owner'], $out['splunkapp'][0], $out['job'][0]);

$get = array(
	'output_mode' => 'csv'
);
$copt = array(
	CURLOPT_HTTPAUTH => CURLAUTH_ANY,
	CURLOPT_USERPWD  => $webhook['owner'].":$splpwd"
);

$results = str_getcsv(curl_get($url, $get, $copt, $user), "\n");

/* Read the Splunk by-CURL result
        [0] = interval
        [1] = element
        [2] = Num msg
        [3] = Tot recips
        [4] = Trigger condition
  into
       [element][0] = interval
       [element][1] = Num msg
       [element][2] = Tot recips
       [element][3] = Trigger condition (max value if multiple occurrency of element)
*/

$tolist = array();

$nr = count ($results);
for ($i=1; $i<$nr; $i++) {	/* We skip first header line (i=0) */
        $data = str_getcsv($results[$i], ',');
        $thisVal = $data[1];
        unset($data[1]);
        $data = array_values($data);
        if ( !in_array($thisVal,array_keys($tolist))  )
	        $tolist["$thisVal"] = $data;
        else if ($data[3]>$tolist[$thisVal][3])
	        $tolist["$thisVal"] = $data;
}

/* Make MYSQL connection */

$mysqli = myConnect($dbhost, $userdb, $pwd, $db, $dbport, $tables, $typedesc, $user);
if ( $mysqli === FALSE )
	exit (254);

foreach ( array_keys($tolist) as $value) {
        $quantity = $conf['quantity'];
        $reason = 'On ['.$tolist["$value"][0]."] <$value> sent ".$tolist["$value"][1].' messages to '.$tolist["$value"][2].' recipients.';
        if ( $tolist["$value"][3] >= $threshold ) {
                if ( searchAndList ($mysqli,$user,$tables,$typedesc,$value,$unit,$quantity,$reason) ) {
                        syslog (LOG_INFO, "$user: ".'Listing reason: '.$reason);
                        /* Send a email to domain admin if you list an email */
                        if ( ( $tables["$typedesc"]['field'] == 'email' ) OR ( $tables["$typedesc"]['field'] == 'username' ) ) {
                                /* Sometime uid are in the form of <user>@<domain> ... */
                                if ( strpos($value, '@') !== FALSE ) {
                                        $domain = substr(strrchr($value, '@'), 1);
                                        if ( strpos($domain, '@') === FALSE ) {
                                                $recip = emailToNotify($domainNotify_file,$domain);
                                                $subject = sprintf('%s <%s> is now blocked because exceeds limits on outgoing emails',
                                                                $tables["$typedesc"]['field'], $value);
                                                if (!empty($recip))
                                                        if ( sendEmailWarn($tplfile,'postmaster@csi.it',$recip,
                                                                $subject,$value,"$quantity $unit",$reason) )
                                                                syslog(LOG_INFO, "$user: \"$recip\" was notified about the \"$value\" abuse.");
                                        }
                                        else syslog(LOG_ERR,"$user: <$domain> contains the '@' char. Notification cannot be sent.");
                                }
                        }
                }
        }
        else {
                $reason .= " But it has NOT been listed because it doesn't apply to the trigger condition.";
                syslog (LOG_INFO, "$user: ".$reason);
        }
}

/* Close connection */
syslog (LOG_INFO, "$user: ".'Successfully end of session.');
$mysqli->close();
closelog();

?>
