#!/usr/bin/php
<?php
/*
#
# Splunk to Policy Listing is Awesome.
# This script can be triggered from a Scheduled Saved Search in Splunk to send
# malicious address to the RBL Manager.
# Place this script on $SPLUNK_HOME/bin/scripts/ for your Splunk app alert.
#
#
*/

$shortopts = "c:";  // Required value
$options = getopt($shortopts);
if ( !isset($options['c']) ) exit ("\n\nUSAGE: ${_SERVER['SCRIPT_NAME']} -c<file.conf>\n\n");
if ( !file_exists(dirname(__FILE__) . '/' . $options['c']) ) exit ("\n\nThe file <".$options['c']."> doesn't exists.\nExiting...\n\n");

/************** Start of conf ************************/
require_once('config.php');

 /* Syslog */
$tag            .= 'SplunkLister';

$conf = parse_ini_file( dirname(__FILE__) . '/' . $options['c'] );

 /* Splunk inherited parameters */
$threshold = $conf['threshold'];         /* Threshold value on trigger condition; the same which engage the alert */
$splfile = $argv[10];    		/* Full path of result Splunk file, see at
                           		   http://docs.splunk.com/Documentation/Splunk/6.2.2/Alert/Configuringscriptedalerts
                           		   It is 8+2 because of -c <conf> */
 /* Blacklist name */
$typedesc  = $conf['typedesc'];

 /* How long to list's parameters */
$unit = $conf['unit'];          /* MySQL language ;) */
$quantity = $conf['quantity'];

 /* Syslog */
$tag            .= $conf['tag'];

/************** End of conf *************************/


require_once('function.php');

openlog($tag, LOG_PID, $fac);
$user = 'Splunk';

/* check you select a blocklist */
if ( !$tables["$typedesc"]['bl'] ) {
        syslog(LOG_EMERG,"$user: <$typedesc> is not a blocklist. Are you stupid? Do you want to whitelist a spammer? I refuse to continue.");
        exit (254);
}


/* Read the Splunk result file
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

if ( !file_exists($splfile) ) {
        syslog(LOG_ERR,"$user: File <$splfile> not found! Exit.");
        exit (254);
}

if (($handle = gzopen($splfile, 'r')) !== FALSE) {
        $row = -1;
        while (($data = fgetcsv($handle, 500, ',')) !== FALSE) {
                $row++;
                if ($row == 0) continue; /* Skip heading line */
                $thisVal = $data[1];
                unset($data[1]);
                $data = array_values($data);
                if ( !in_array($thisVal,array_keys($tolist))  )
                        $tolist["$thisVal"] = $data;
                else if ($data[3]>$tolist[$thisVal][3])
                        $tolist["$thisVal"] = $data;
        }
        fclose($handle);
}

/* Make MYSQL connection */

$mysqli = new mysqli($dbhost, $userdb, $pwd, $db, $dbport);
if ($mysqli->connect_error) {
        syslog (LOG_EMERG, $user.': Connect Error (' . $mysqli->connect_errno . ') '
        . $mysqli->connect_error);
        exit (254);

}

syslog(LOG_INFO, $user.': Successfully mysql connected to ' . $mysqli->host_info) ;

foreach ( array_keys($tolist) as $value) {
	$reason = 'On ['.$tolist["$value"][0]."] <$value> sent ".$tolist["$value"][1].' messages to '.$tolist["$value"][2].' recipients.';
        if ( $tolist["$value"][3] >= $threshold ) {
                if ( searchAndList ($mysqli,$user,$tables,$typedesc,$value,$unit,$quantity,$reason) ) {
                        syslog (LOG_INFO, "$user: ".'Listing reason: '.$reason);
                        /* Send a email to domain admin if you list an email */
                        if ( $tables["$typedesc"]['field'] == 'email' ) {
                                $domain = array_pop(explode('@',$value,2));
                                $recip = emailToNotify($domainNotify_file,$domain);
                                $subject = "<$value> is now blocked because exceedes limits on outgoing emails";
                                if (!empty($recip))
                                        if ( sendEmailWarn($tplfile,'postmaster@csi.it',$recip,$subject,$value,"$quantity $unit",$reason) )
                                                syslog(LOG_INFO, "$user: \"$recip\" was notified about the \"$value\" abuse.");
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
