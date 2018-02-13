#!/usr/bin/php
<?php
/* Config */
$path='/var/www/html/RBL/';
include_once($path.'config.php');
require_once($path.'function.php');
if ( !isset($version) ) {
        openlog('myRBLemergency', LOG_PID, LOG_LOCAL0);
        syslog (LOG_EMERG, 'unknown: I can\'t read the config files. Do you have configured the $path in getip.php?');
        closelog();
        exit(255);
}
include_once(dirname(__FILE__) . '/function.php');
$conf = parse_ini_file($confImap_file, TRUE, INI_SCANNER_TYPED);


 /* Syslog */
$tag .= $conf['syslog']['tag'];
openlog($tag, LOG_PID, $fac);


if ( !$imapListActive ) {
	syslog (LOG_INFO, $conf['syslog']['user'].': This plugin isn\'t active.');
	closelog();
	exit(255);
}
	

/* For report file name */
$arr_tpl_vars = array('{date}');
$arr_tpl_data = array(date("Y-m-d", time()));

/* Start of check list */
if ( !$conf['listingip']['onlyReport']['spam'] ) {

	/* check you select a right list */
	if ( !$tables[$conf['listingip']['list']['spam']]['bl'] ) {
       		syslog(LOG_EMERG, $conf['syslog']['user'].': <'.$conf['listingip']['list']['spam'].'> is not a blocklist. Are you stupid? Do you want to whitelist a spammer? I refuse to continue.');
	       	exit (254);
	}
	if ( !$tables[$conf['listingip']['list']['spam']]['active'] ) {
		syslog(LOG_EMERG, $conf['syslog']['user'].': <'.$conf['listingip']['list']['spam'].'> is not active. Please, activate it to continue with this process.');
		exit (254);
        }
}

if ( !$conf['listingip']['onlyReport']['ham'] ) {
	if ( $tables[$conf['listingip']['list']['ham']]['bl'] ) {
        	syslog(LOG_EMERG, $conf['syslog']['user'].': <'.$conf['listingip']['list']['ham'].'> is a blocklist. Are you stupid? Do you want to block a legitimate sender? I refuse to continue.');
	        exit (254);
	}
        if ( !$tables[$conf['listingip']['list']['ham']]['active'] ) {
                syslog(LOG_EMERG, $conf['syslog']['user'].': <'.$conf['listingip']['list']['ham'].'> is not active. Please, activate it
 to continue with this process.');
                exit (254);
        }
}

if ( !$conf['listingdom']['onlyReport']['spam'] ) {

        /* check you select a right list */
        if ( !$tables[$conf['listingdom']['list']['spam']]['bl'] ) {
                syslog(LOG_EMERG, $conf['syslog']['user'].': <'.$conf['listingdom']['list']['spam'].'> is not a blocklist. Are you stupid? Do you want to whitelist a spam domain? I refuse to continue.');
                exit (254);
        }
        if ( !$tables[$conf['listingdom']['list']['spam']]['active'] ) {
                syslog(LOG_EMERG, $conf['syslog']['user'].': <'.$conf['listingdom']['list']['spam'].'> is not active. Please, activate it to continue with this process.');
                exit (254);
        }
}

if ( !$conf['listingdom']['onlyReport']['ham'] ) {
        if ( $tables[$conf['listingdom']['list']['ham']]['bl'] ) {
                syslog(LOG_EMERG, $conf['syslog']['user'].': <'.$conf['listingdom']['list']['ham'].'> is a blocklist. Are you stupid? Do you want to block a legitimate sender? I refuse to continue.');
                exit (254);
        }
        if ( !$tables[$conf['listingdom']['list']['ham']]['active'] ) {
                syslog(LOG_EMERG, $conf['syslog']['user'].': <'.$conf['listingdom']['list']['ham'].'> is not active. Please, activate it
 to continue with this process.');
                exit (254);
        }

}
/* End of check list */

/* Make MYSQL connection Array */
$mysqlconf= array(
	'dbhost' => $dbhost,
	'userdb' => $userdb,
	'pwd'	 => $pwd,
	'db'	 => $db,
	'dbport' => $dbport
);


/* Splunk connection */
require_once($conf['SplunkSDK']['splpath'].'/Splunk.php');
$splservice = new Splunk_Service($conf['SplunkSDK']['splunkConn']);
$splservice->login();
/********************/


/* The hard work has hidden in imapReport */
$learnfromArray = array('ham','spam');
foreach ( $learnfromArray as $learnfrom ) {
	$conf['report']['reportFile']["$learnfrom"] = str_replace($arr_tpl_vars, $arr_tpl_data, $conf['report']['reportFile']["$learnfrom"]);
	$conf['report']['badreportFile']["$learnfrom"] = str_replace($arr_tpl_vars, $arr_tpl_data, $conf['report']['badreportFile']["$learnfrom"]);
	$conf['report']['reportDomFile']["$learnfrom"] = str_replace($arr_tpl_vars, $arr_tpl_data, $conf['report']['reportDomFile']["$learnfrom"]);
	imapReport ($conf,$mysqlconf,$splservice,$tables,$learnfrom);
}

syslog (LOG_INFO, $conf['syslog']['user'].': End of session.');
closelog();
?>
