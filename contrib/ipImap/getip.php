#!/usr/bin/php
<?php
/* Config */
$path='/web/RBL/';
include_once($path.'config.php');
require_once($path.'function.php');
include_once(dirname(__FILE__) . '/function.php');
$conf = parse_ini_file($confImap_file);

if ( $conf === FALSE ) {
	openlog('myRBLemergency', LOG_PID, LOG_LOCAL0);
        syslog (LOG_EMERG, 'unknown: I can\'t read the config files. Do you have configured the $path in getip.php?');
        closelog();
        exit(255);
}

 /* Syslog */
$tag .= $conf['tag'];
openlog($tag, LOG_PID, $fac);


if ( !$imapListActive ) {
	syslog (LOG_INFO, $conf['user'].': This plugin isn\'t active.');
	closelog();
	exit(255);
}
	

/* For report file name */
$arr_tpl_vars = array('{date}');
$arr_tpl_data = array(date("Y-m-d", time()));


if ( !$conf['onlyReport'] ) {

	/* check you select a right list */
	if ( !$tables[$conf['list']['spam']]['bl'] ) {
       		syslog(LOG_EMERG, $conf['user'].': <'.$conf['list']['spam'].'> is not a blocklist. Are you stupid? Do you want to whitelist a spammer? I refuse to continue.');
	       	exit (254);
	}
	if ( $tables[$conf['list']['ham']]['bl'] ) {
        	syslog(LOG_EMERG, $conf['user'].': <'.$conf['list']['ham'].'> is a blocklist. Are you stupid? Do you want to block a legitimate sender? I refuse to continue.');
	        exit (254);
	}

	/* Make MYSQL connection Array */
	$mysqlconf= array(
		'dbhost' => $dbhost,
		'userdb' => $userdb,
		'pwd'	 => $pwd,
		'db'	 => $db,
		'dbport' => $dbport
	);
}

else {
	$mysqlconf = NULL;
	syslog(LOG_INFO, $conf['user'].': Report only, no listing activated in configuration.') ;
}


/* Splunk connection */
require_once($conf['splpath'].'/Splunk.php');
$splservice = new Splunk_Service($conf['splunkConn']);
$splservice->login();
/********************/


/* The hard work has hidden in imapReport */
$learnfromArray = array('ham','spam');
foreach ( $learnfromArray as $learnfrom ) {
	$conf['reportFile']["$learnfrom"] = str_replace($arr_tpl_vars, $arr_tpl_data, $conf['reportFile']["$learnfrom"]);
	$conf['badreportFile']["$learnfrom"] = str_replace($arr_tpl_vars, $arr_tpl_data, $conf['badreportFile']["$learnfrom"]);
	imapReport ($conf,$mysqlconf,$splservice,$tables,$learnfrom);
}

if ( !$conf['onlyReport'] ) {
	/* Close connection */
	syslog (LOG_INFO, $conf['user'].': Successfully end of session.');
}
closelog();
?>
