#!/usr/bin/php
<?php

if (PHP_SAPI != "cli")
    exit;

$home = '/var/www/html/RBL';
require_once($home.'/config.php');
require($home.'/function.php');


function usage()
{
	print "\nUsage: {$_SERVER['SCRIPT_FILENAME']} [ -f <template.conf>  -t <list> ]\n\t<template.conf> is a template file with RBLDNSD headers of the list.\n\t See at <conf.default> as instance.\n\n\t<list> is a name of active list in your config.\n\n";
	return 255;
}

openlog($tag, LOG_PID, $fac);
$user = username();

$opts = getopt('f:t:');
if ( isset($opts['f']) ) {
        $filetemplate = $opts['f'];
} else {
	print "\nNo option for '-f' given.\n";
        exit ( usage() );
}

if ( isset($opts['t']) ) {
        $tablename = $opts['t'];
} else {
	print "\nNo option for '-t' given.\n";
        exit ( usage() );
}

if ( ($typedescN = array_search( $tablename, array_column($tables, 'name'))) === FALSE ) {
        print "\nDB <$tablename> doesn't exist!\n";
        syslog (LOG_EMERG, "$user: DB <$tablename> doesn't exist!");
        exit ( usage() );
}
$typedesc = array_keys($tables)[$typedescN];

if (! file_exists($filetemplate) ) {
        print "\nFile <$filetemplate> doesn't exists!\n";
	syslog (LOG_EMERG, "$user: File <$filetemplate> doesn't exist!");
        exit ( usage() );
}

if (! in_array( $typedesc, array_keys($tables) ) ) {
	print "\nUnknown list <$typedesc>. Please provide an existent list name.\n";
	exit ( usage() );
}

if (! $tables["$typedesc"]['active'] ) {
	print "\nList <$typedesc> is not active. Please provide an active list name.\n";
        exit ( usage() );
}



$now=new DateTime('NOW');
$timeunix = $now->format('U');
$dateRFC822 = $now->format('r');
$year = $now->format('Y');
$rbltype= ($tables["$typedesc"]['bl']) ? 'Blocklist' : 'Whitelist';


$tmpl = file_get_contents($filetemplate);
$arr_tpl_vars = array('{rblname}','{rbltype}','{date822}','{year}','{unixtimestamp}','{rblname64}','{hostname}');
$arr_tpl_data = array($typedesc,$rbltype,$dateRFC822,$year,$timeunix,base64_encode($typedesc),gethostname());
$headerList = str_replace($arr_tpl_vars, $arr_tpl_data, $tmpl);


$mysqli = new mysqli($dbhost, $userdb, $pwd, $db, $dbport);
if ($mysqli->connect_error) {
            syslog (LOG_EMERG, $user.': Connect Error (' . $mysqli->connect_errno . ') '
                    . $mysqli->connect_error);
            exit ($user.': Connect Error (' . $mysqli->connect_errno . ') '
                    . $mysqli->connect_error);
}

syslog(LOG_INFO, $user.': Successfully mysql connected to ' . $mysqli->host_info) ;

$result = searchentry ($mysqli,'ALL',$tables["$typedesc"]);
if ($result->num_rows) {
	$element = array();
	while ($riga = $result->fetch_array(MYSQLI_ASSOC)) {
		if (isListed($riga)) {
			switch ( $tables["$typedesc"]['field'] ) {
                                  case 'ip':
                                        $element[] = long2ip($riga['ip']);
                                        break;
                                  case 'network':
                                        $element[] = long2ip($riga['network']).'/'.long2ip($riga['netmask']);
                                        break;
                                  default:
					$type = $tables["$typedesc"]['field'];
                                        $element[] = $riga["$type"];
			}
		}
	}
}

$result->free();
$mysqli->close();

/* Print to file */
file_put_contents( $tables["$typedesc"]['name'], $headerList . implode("\n",$element) );
closelog();
?>
