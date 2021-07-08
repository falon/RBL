<?php

ini_set('error_log', 'syslog');

function username() {
	if (isset ($_SERVER['REMOTE_USER'])) $user = $_SERVER['REMOTE_USER'];
        	else if (isset ($_SERVER['USER'])) $user = $_SERVER['USER'];
                	else $user='unknown';
	return $user;
}

function checkSSL() {
	if ( empty( $_SERVER['HTTPS'] ) )
		printf ('<div id="content">Ehi sysadmin! Your site is not secure. Please enable SSL on your server and configure a redirect, such as' .
			'<pre>' .
			htmlspecialchars('<VirtualHost *:80>' . "\n" .
			'  ServerName %s' . "\n" .
			'  Redirect permanent / https://%s/' . "\n" .
			'</VirtualHost>') .
			'</pre></div>', gethostname(), gethostname());
}

function myConnect($host, $user, $pass, $db, $port, $tablelist, $typedesc, $loguser) {
        $db = ( $tablelist["$typedesc"]['milter'] ) ? $tablelist["$typedesc"]['name'] : $db;
	$mysqli = new mysqli($host, $user, $pass, $db, $port);
        if ($mysqli->connect_error) {
           	syslog (LOG_EMERG, $loguser.': Connect Error to DB <'.$db.'> (' . $mysqli->connect_errno . ') '
                    		. $mysqli->connect_error);
		return FALSE;
	}
	if (!$mysqli->set_charset("utf8mb4")) {
		syslog(LOG_EMERG, $loguser.': Error loading character set utf8mb4: ' . $mysqli->error);
		return FALSE;
	}
	#$mysqli->query("set names 'utf8mb4' collate 'utf8mb4_unicode_520_ci' ");
	syslog(LOG_INFO, $loguser.': Successfully MySQL connected at DB <'.$db.'> to ' . $mysqli->host_info) ;
	return $mysqli;
}

function addtolist ($myconn,$user,$value,$tabledesc,$expUnit,$expQ,$myreason,&$err) {
// See MySQL manual for $expQ and $expUnit at
// https://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_timestampadd

	$result=FALSE;
	$sub=array();
	$type = $tabledesc['field'];
	$milt = $tabledesc['milter'];
	$table = ($milt) ? milterTable($type) : $tabledesc['name'];

	switch ($type) {
	  case 'ip':
		$query= sprintf("INSERT INTO `$table` (
			`$type` ,
			`date` ,
			`exp` ,
			`active` ,
			`user` ,
			`reason`
		)
		VALUES (
			INET_ATON( '%s' ) ,
			CURRENT_TIMESTAMP , TIMESTAMPADD(%s,%d,CURRENT_TIMESTAMP), '1', '%s', '%s'
		)" ,$value,$expUnit,$expQ,$user,$myreason);
		break;

	  case 'network':
		if (!$milt) {
			if ( netOverlap($myconn, $tabledesc, $value, $overlappedNet, $user) ) {
				$err = "<$value> overlaps the existing network <$overlappedNet>";
				return FALSE;
			}
		}
		list($sub['net'],$sub['mask'])=explode('/',$value);
                $query= sprintf("INSERT INTO `$table` (
                        `$type` ,
			`netmask`,
                        `date` ,
                        `exp` ,
                        `active` ,
                        `user` ,
                        `reason`
                )
                VALUES (
                        INET_ATON( '%s' ) , INET_ATON( '%s' ) ,
                        CURRENT_TIMESTAMP , TIMESTAMPADD(%s,%d,CURRENT_TIMESTAMP), '1', '%s', '%s'
                )" ,$sub['net'],$sub['mask'],$expUnit,$expQ,$user,$myreason);
                break;

	  default:
                $query= sprintf("INSERT INTO `$table` (
                        `$type` ,
                        `date` ,
                        `exp` ,
                        `active` ,
                        `user` ,
                        `reason`
                )
                VALUES (
                        '%s' ,
                        CURRENT_TIMESTAMP , TIMESTAMPADD(%s,%d,CURRENT_TIMESTAMP), '1', '%s', '%s'
                )" ,$value,$expUnit,$expQ,$user,$myreason);
	}

	if ($myconn->query($query) === TRUE) {
	    syslog(LOG_INFO, "$user: $type <$value> successfully listed on <$table> for $expQ $expUnit.");
	    $result=TRUE;
	}
	else syslog(LOG_ERR, "$user: Error: ".$myconn->error);
	return $result;
}

function relist ($myconn,$user,$value,$type,$table,$expUnit,$expQ,$myreason, $exptime = 0) {

	$result=FALSE;
	if ( $exptime ) { /* Entry already listed */
		$nlist = '`nlist`';
		$exptime = sprintf('\'%s\'', $exptime);  /* Eh MySQL... an hour lost to notice this */
	}
	else {
		$exptime = 'CURRENT_TIMESTAMP';
		$nlist = '`nlist` + 1';
	}

        switch ($type) {
	  case 'ip':
                $query= sprintf("UPDATE `$table` SET
			`active` = '1',
			`user` = '%s',
			`exp` = TIMESTAMPADD(%s,%d,%s),
			`nlist` = %s,
			`reason` = '%s'
			WHERE `$table`.`$type` = INET_ATON('%s') LIMIT 1" ,$user,$expUnit,$expQ,$exptime,$nlist,$myreason,$value);
		break;
          case 'network':
		list($sub['net'],$sub['mask'])=explode('/',$value);
                $query= sprintf("UPDATE `$table` SET
                        `active` = '1',
                        `user` = '%s',
                        `exp` = TIMESTAMPADD(%s,%d,%s),
                        `nlist` = %s,
                        `reason` = '%s'
                        WHERE (`$table`.`$type` = INET_ATON('%s') AND `$table`.`netmask` = INET_ATON('%s')) LIMIT 1" ,$user,$expUnit,$expQ,$exptime,$nlist,$myreason,$sub['net'],$sub['mask']);
		break;
	  default:
                $query= sprintf("UPDATE `$table` SET
                        `active` = '1',
                        `user` = '%s',
                        `exp` = TIMESTAMPADD(%s,%d,%s),
                        `nlist` = %s,
                        `reason` = '%s'
			WHERE `$table`.`$type` = '%s' LIMIT 1" ,$user,$expUnit,$expQ,$exptime,$nlist,$myreason,$value);
	}

        if ($myconn->query($query) === TRUE) {
            syslog(LOG_INFO, "$user: relist $type <$value> on <$table> for $expQ $expUnit from $exptime.");
		$result=TRUE;
        }
        else syslog (LOG_ERR, "$user: Error: ". $myconn->error);
	return $result;
}

function remove ($myconn,$user,$value,$type,$table) {

        switch ($type) {
          case 'ip':
		$query = sprintf("DELETE FROM `$table` WHERE
                        `$table`.`$type` = INET_ATON('%s') LIMIT 1", $value);
		break;
	  case 'network':
		list($sub['net'],$sub['mask'])=explode('/',$value);
		$query = sprintf("DELETE FROM `$table` WHERE
			`$table`.`$type` = INET_ATON('%s') AND `$table`.`netmask` = INET_ATON('%s') LIMIT 1",
			$sub['net'],$sub['mask']);
		break;
	  default:
		$query = sprintf("DELETE FROM `$table` WHERE
                        `$table`.`$type` = %s LIMIT 1", $value);
	}


        if ($return=$myconn->query($query) === TRUE) 
            syslog(LOG_INFO, "$user: permanently DELETED $type <$value> from <$table>.");
        else syslog(LOG_ERR, "$user: Error: ". $myconn->error);

        return $return;
}


function changestatus ($myconn,$user,$value,$status,$type,$table) {

	switch ($type) {
          case 'ip':
		$query= sprintf("UPDATE `$table` SET `active` = '$status', `user` = '%s' WHERE `$table`.`$type` = INET_ATON('%s') LIMIT 1" ,$user, $value);
		break;
	  case 'network':
		list($sub['net'],$sub['mask'])=explode('/',$value);
		$query= sprintf("UPDATE `$table` SET `active` = '$status', `user` = '%s' WHERE (`$table`.`$type` = INET_ATON('%s') AND `$table`.`netmask` = INET_ATON('%s')) LIMIT 1" ,$user, $sub['net'],$sub['mask']);
		break;
	  default:
		$query= sprintf("UPDATE `$table` SET `active` = '$status', `user` = '%s' WHERE `$table`.`$type` = '%s' LIMIT 1" ,$user, $value);
	}

        if ($return=$myconn->query($query) === TRUE) {
            syslog(LOG_INFO, "$user: change status of $type <$value>. The status is now <$status>");
        }
        else syslog(LOG_ERR, "$user: Error: ". $myconn->error);
	return $return;	
}


function expire ($myconn,$user,$tables,$expireTime) {
        $return=TRUE;
	$log=array();
	$desc = array_keys($tables);
	foreach ($desc as $tdesc) {
		/* Exclude milter dbs */
                switch ($tables["$tdesc"]['name']) {
                        case 'miltermap':
                        case 'milteripmap':
                                continue 2;
                        default:
                }
		/* QUERY */
		$query  = 'DELETE FROM `'.$tables["$tdesc"]['name']."` WHERE `exp` < DATE_SUB( NOW(), INTERVAL $expireTime YEAR);";
		$query .= 'DELETE FROM `'.$tables["$tdesc"]['name']."` WHERE `datemod` < DATE_SUB( NOW(), INTERVAL $expireTime YEAR) AND `active` = 0";
		/* END OF QUERY */
		$log[0] = 'expired for';
		$log[1] = 'disabled for';
        	if ($myconn->multi_query($query)) {
			$j = 0;
			do {
		    		$numdel = $myconn->affected_rows;
	            		syslog(LOG_INFO, "Expire job - <$user> Permanently DELETED $numdel records ".$log[$j]." $expireTime YEARS from <".$tables["$tdesc"]['name'].'>.');
				$j++;

			} while ($myconn->next_result());
		}
		else {
			syslog(LOG_ERR, "Expire job - Error: ". $myconn->error);
			$return = FALSE;
		}
	}
	if ( !($return) ) syslog(LOG_EMERG, 'End of Expire job with error. See above logs. SQL Connection terminated');
	else  syslog(LOG_INFO, 'Successfully End of Expire job. SQL Connection successfully terminated.');
        return $return;
}


function isListed($row) {

	$exp=new DateTime($row['exp']);
	$now=new DateTime('NOW');
	if (($exp > $now) and ($row['active'])) return true;
	else return false;

}


function askMilter($myconn,$id,$obj,$typedesc,$miltId,$value,$user,$adm)  {
	$milts = readMiltName($myconn,$user);
	$size = count($milts);
	if (in_array($user,array_keys($adm))) {
		$button = <<<END
		<form style="margin:0; display:inline;" name="Milter$id" enctype="text/plain" method="post" target="_self" action="changeMilter.php" onSubmit="xmlhttpPost('changeMilter.php', 'Milter$id', 'id$id', '<img src=\'/include/pleasewait.gif\'>'); return false;" />
		<input name="object" type="hidden" value="$obj" /><input name="oldvalues" type="hidden" value="$value" />
		<input name="type" type="hidden" value="$typedesc" />
		<input name="user" type="hidden" value="$user" />
		<input name="miltId" type="hidden" value="$miltId" />
		<div class="noscroll">
		<select class="input_text" name="newvalues[]" multiple size="$size">
END;
		$activeMilts = explode(',',$value);
		foreach ( $milts as $milter ) {
			if ( in_array($milter, $activeMilts) )
				$selected= 'selected';
			else
				$selected= NULL;
			$button .= sprintf('<option value="%s" %s>%s</option>', $milter, $selected, $milter);
		}	
		$button .= '</select></div><input class="button" name="Change" type="submit" value="Change" /></form>';
		return $button;
	}
	return $value;	


}


function ask($myconn,$id,$what,$alltables,$typedesc,$value,$lock,$user,$adm) {

	$whynot=NULL;
	switch ($what) {
		case 'Ok':
			if ($lock) return NULL;
			if (in_array($user,array_keys($adm)))
				if ( consistentListing($myconn,$alltables,$typedesc,$value,$whynot) ) return require('relistButton.php');
			return htmlspecialchars($whynot);
		case 'Listed':
		case 'WhiteListed':
			return require('delistButton.php');
	}
}


function consistentListing($myconn,$alltables,$typed,$value,&$warn) {
/* Check if there are no pending mislisting */
	$warn = NULL;
	if (! isset($alltables["$typed"]['depend']) ) return TRUE;
	foreach ($alltables["$typed"]['depend'] as $listdep) {
		if ($alltables["$typed"]['field'] != $alltables["$listdep"]['field'] ) {
			$warn = "Config ERROR: <$typed> and <$listdep> are of different types! I can't check consistency!";
			return FALSE;
		}
		$entry = searchentry($myconn,$value,$alltables["$listdep"]);
		if ( $entry->num_rows ) {
			if ( $entry->num_rows == 1 ) {
				$riga = $entry->fetch_array(MYSQLI_ASSOC);
                        	if (isListed($riga)) {
					$warn = "<$value> is already present in <$listdep> list!";
					$entry->free();
					return FALSE;
				}
			}
			if ( $entry->num_rows > 1 ) {$warn = "<$value> seems to be present more than once in <$listdep>. Contact a sysadmin NOW!";}
		}
		$entry->free();
	}

	return TRUE;
}

function searchentry ($myconn,$value,$tablelist) {
/* Make a MYSQL query and return result */

        $type = $tablelist['field'];
	
	if ( $tablelist['milter'] ) {
		$table = milterTable($type);
		if ($value == 'ALL')
			$query = sprintf('SELECT *, GROUP_CONCAT(milt.name) as miltnames FROM `%s` LEFT JOIN milt ON (%s.idmilt=milt.id) GROUP by idmilt',
				$table,$table);
		else {
			switch ($type) {
				case 'network':
					list($sub['net'],$sub['mask'])=explode('/',$value);
					$query = sprintf('SELECT * FROM (
							SELECT *, GROUP_CONCAT(milt.name) as miltnames FROM `%s` LEFT JOIN milt ON (%s.idmilt=milt.id)
				 				WHERE (
									inet_aton(\'%s\') >= network AND
									( inet_aton(\'%s\') | ( inet_aton(\'%s\') ^ (power(2,32)-1) ) )
										<= network | ( netmask ^ (power(2,32)-1) )
								)
				 				GROUP by idmilt
							) AS val WHERE val.network IS NOT null', $table, $table, $sub['net'], $sub['net'], $sub['mask']);
					break;
				case 'ip':
					$query = sprintf('SELECT * FROM (
							SELECT *, GROUP_CONCAT(milt.name) as miltnames FROM `%s` LEFT JOIN milt ON (%s.idmilt=milt.id)' .
                                                		'WHERE `ip` =  INET_ATON(\'%s\')
							 ) AS val WHERE val.ip IS NOT null', $table, $table, $value);
					break;
				default:
					syslog(LOG_EMERG, 'ALERT: The type <'.$type.'> is not allowed for milter lists.' );
					return FALSE;
			}
		}
	}

	else {
	        $table = $tablelist['name'];
	        if ($value == 'ALL') $query = 'select * from '.$table;
	        else {
	                switch ($type) {
	                  case 'ip':
	                        $query= "select * from $table where $type =  INET_ATON('$value')";
				break;
	                  case 'network':
	                        list($sub['net'],$sub['mask'])=explode('/',$value);
	                        $query= sprintf('select * from `%s`
						WHERE (
							inet_aton(\'%s\') >= network AND
							( inet_aton(\'%s\') | ( inet_aton(\'%s\') ^ (power(2,32)-1) ) )
								<= network | ( netmask ^ (power(2,32)-1) )
						)', $table, $sub['net'], $sub['net'], $sub['mask']);
;
	                        break;
	                  default:
	                        $query= "select * from $table where $type = '$value'";
	                }
	        }
	}

	$result = $myconn->query($query);
	if($result === false)
		syslog(LOG_EMERG, "ALERT: Query <$query> failed: ".$myconn->error);
        return $result;
}

function countListed ($myconn,$table) {
/* Return number of current listed items into a rbl table */
	$query = "SELECT COUNT(*) as `count` FROM `$table` WHERE (`active`=1 AND TIMESTAMPDIFF(MICROSECOND,NOW(),`exp`)>0) GROUP BY `active` ORDER BY `count` DESC LIMIT 1";
	$row = $myconn->query($query);
	$number = $row->fetch_array(MYSQLI_ASSOC);
	$number = $number['count'];
	$row->free();
	return $number;
}


function isFull($myconn,$typedesc,$alltables) {
        if (isset($alltables["$typedesc"]['limit'])) {
		if ( $alltables["$typedesc"]['milter'] )
			$tab = 'net';
		else
			$tab = $alltables["$typedesc"]['name'];
                if ( countListed($myconn,$tab) >= $alltables["$typedesc"]['limit'] ) 
                        return TRUE;
        }
	return FALSE;
}

function rlookup ($myconn,$user,$adm,$value,$typedesc,$tables) {

	$type = $tables["$typedesc"]['field'];
	$whynot=NULL;

	$tabhtm = <<<END
	<table><thead><tr><th>$type</th><th title="The date this object has been listed for the first time">DateAdd</th><th>DateMod</th><th>Exp</th><th>Status</th><th title="Number of times this object has been listed">#List</th>
END;
	if ( $tables["$typedesc"]['milter'] )
		$tabhtm .= '<th title="Milter active for this object">Milters</th>';
	$tabhtm .= '<th>Authored by</th><th width="250">Reason</th><th>Action</th></tr></thead><tfoot><tr></tr></tfoot><tbody>'."\n";

	if (($type == 'domain') AND ($value != 'ALL'))
		$value = nsdom($value);
	if (is_null($value))
		return FALSE;

	$result = searchentry ($myconn,$value,$tables["$typedesc"]);
	if ($result) {
		printf("<pre>Your request for $type &lt;%s&gt; returned %d items.\n</pre>", htmlentities($value), $result->num_rows);

        /* Check for limit in number of listed items */
	$full = isFull($myconn,$typedesc,$tables);
	if ($full) print '<p>'.htmlspecialchars("$typedesc has reached maximum value of ".$tables["$typedesc"]['limit'].' listed items.').'</p>';

		if ($result->num_rows) {
			print $tabhtm;
			$i=0;
        		while ($riga = $result->fetch_array(MYSQLI_ASSOC)) {
				if (isListed($riga)) {
					if ($tables["$typedesc"]['bl']) $listed='Listed';
					else $listed='WhiteListed';
				}	
				else
					$listed='Ok';

				switch ($type) {
				  case 'ip':
					$element = long2ip($riga['ip']);
					break;
				  case 'network':
					$element = long2ip($riga['network']).'/'.long2ip($riga['netmask']);
					break;
				  default:
					$element = $riga["$type"];
				}

				if ( $tables["$typedesc"]['milter'] AND checkMilterConf($tables["$typedesc"]) )
					printf ("<tr id=id$i><td id='status$listed'>%s</td><td id='status$listed'>%s</td><td id='status$listed'>%s</td><td id='status$listed'>%s</td><td id='status$listed'>%s</td><td id='status$listed'>%s</td><td nowrap id='status$listed'>%s</td><td id='status$listed'>%s</td><td id='status$listed'>%s</td><td>%s</td></tr>\n",
					$element, $riga['date'], $riga['datemod'], $riga['exp'], $riga['active'], $riga['nlist'], askMilter($myconn,$i,$element,$typedesc,$riga['idmilt'],$riga['miltnames'],$user,$adm), $riga['user'],htmlspecialchars($riga['reason']),ask($myconn,$i,$listed,$tables,$typedesc,$element,$full,$user,$adm));
				else
					 printf ("<tr id=id$i><td id='status$listed'>%s</td><td id='status$listed'>%s</td><td id='status$listed'>%s</td><td id='status$listed'>%s</td><td id='status$listed'>%s</td><td id='status$listed'>%s</td><td id='status$listed'>%s</td><td id='status$listed'>%s</td><td>%s</td></tr>\n",
					$element, $riga['date'], $riga['datemod'], $riga['exp'], $riga['active'], $riga['nlist'], $riga['user'],htmlspecialchars($riga['reason']),ask($myconn,$i,$listed,$tables,$typedesc,$element,$full,$user,$adm));
				$i++;
        		}
			print '</tbody></table>';
		}
		else {
			printf ("<pre>$type &lt;%s&gt; is not listed!\n</pre>", htmlentities($value));
			if ( in_array($user,array_keys($adm)) AND ($value != 'ALL') )
				if ( (!$full) AND (consistentListing($myconn,$tables,$typedesc,$value,$whynot)) ) require_once('listForm.php');
									else print '<p>'.htmlspecialchars($whynot).'</p>';
				
		}
		$result->free();
	}
	else print '<pre>Query error or something wrong in DB schema'."\n</pre>";
}



        
function sendEmailWarn($tplf,$from,$to,$sbj,$emailListed,$intervalToExpire,$detail) {
	$now = time();
        setlocale (LC_TIME, 'it_IT');
        $date = date("r",$now);
	$messageID = md5(uniqid($now,1)) . '@' . gethostname();
	$mua = 'PHP/' . phpversion();

	/* Parsing headers */
	if (!file_exists($tplf['header'])) {
    		syslog(LOG_ERR, 'Sending email... template file <'.$tplf['header'].'> not found!');
    		exit;
	}

	$head_tmpl = file_get_contents($tplf['header']);
	$arr_tpl_vars = array('{from}','{to}','{date}','{messageID}','{mua}');
	$arr_tpl_data = array($from,$to,$date,$messageID,$mua);
	$headers = str_replace($arr_tpl_vars, $arr_tpl_data, $head_tmpl);
	$headers = preg_replace( '/\r|\n/', "\r\n", $headers );

        /* Parsing body */

        if (!file_exists($tplf['body'])) {
                syslog(LOG_ERR, 'Sending email... template file <'.$tplf['body'].'> not found!');
                exit;
        }

        $body_tmpl = file_get_contents($tplf['body']);
        $arr_tpl_vars = array('{emailListed}','{expInterval}','{reason}');
        $arr_tpl_data = array($emailListed,$intervalToExpire,$detail);
        $body = str_replace($arr_tpl_vars, $arr_tpl_data, $body_tmpl);
        $body = preg_replace( "/\r|\n/", "\r\n", $body );
	$body = wordwrap ( $body, 75 , "\r\n" );	

	/* Send the mail! */
        if ( strlen(ini_get("safe_mode"))< 1) {
                $old_mailfrom = ini_get("sendmail_from");
                ini_set("sendmail_from", $from);
                $params = sprintf("-oi -f %s", '<>');
                if (!(mail($to,$sbj, $body,$headers,$params))) $flag=FALSE;
                else $flag=TRUE;
                if (isset($old_mailfrom))
                        ini_set("sendmail_from", $old_mailfrom);
        }
        else {
                if (!(mail($to,$sbj, $body,$headers))) $flag=FALSE;
                else $flag=TRUE;
        }
        return $flag;
}

function emailToNotify($notify_file,$dom) {
	$ini_array = parse_ini_file($notify_file);
	if (in_array($dom,array_keys($ini_array)))
		return $ini_array["$dom"];
	else return FALSE;
}


function searchAndList ($myconn,$loguser,$tables,$typedesc,$value,$unit,&$quantity,&$reason) {

/* Search and list value */
        $type = $tables["$typedesc"]['field'];
        $table = $tables["$typedesc"]['name'];
        $result = searchentry ($myconn,$value,$tables["$typedesc"]);

        /* Manage abnormal conditions */
        /* Value already present in db more than once. This is absurd. Panic! */
        if ($result->num_rows > 1) {
                syslog(LOG_EMERG,"$loguser: PANIC! Select for $type '$value' returned ". $result->num_rows ." items instead of one. Abnormal. Contact a sysadmin or a developer.");
                $result->free();
                return FALSE;
        }

        /* Value already present in db or not present: to list anyway */
        if ($result->num_rows >= 0) {
                /* First, check for limit in number of listed items */
                if (isFull($myconn,$typedesc,$tables)) {
                        syslog(LOG_EMERG,"$loguser: $typedesc has reached maximum value of ".$tables["$typedesc"]['limit'].' listed items. Abnormal exit.');
                        $result->free();
                        return FALSE;
                }
                /* Second, check if the (re)list would be consistent now */
                if (! consistentListing($myconn,$tables,$typedesc,$value,$whynot) ) {
                        syslog(LOG_ERR, $loguser.': '.$whynot);
                        $result->free();
                        return FALSE;
                }
        }
        /* End of abnormal conditions */


        /* Finally, here I can list the value! */
	$thisentry = $result->fetch_array(MYSQLI_ASSOC);
        switch ($result->num_rows) {
                /* Relist value if already present */
                case 1:
                        if ( isListed($thisentry) ) {
				/* Entry already listed */
				$expdate = $thisentry['exp'];
				$reason = sprintf('%s. Already listed. Adding 1 DAY to previous expire date.',
					 $reason);
				$quantity = 1;
				$unit = 'DAY';
                        }
			else {
                        	/* Entry delisted */
				$quantity *= $thisentry['nlist'];
				$expdate = 0; /* This forces expiration from CURRENT_TIMESTAMP */
			}
			$result->free();
                        return relist ($myconn,$loguser,$value,$type,$table,$unit,$quantity,$reason, $expdate);

                /* First time list value */
                case 0:
                        $result->free();
                        return addtolist ($myconn,$loguser,$value,$tables["$typedesc"],$unit,$quantity,$reason,$_);
        }
}


/*************** Functions to check if two net overlap each other ********************/

function ipRange ($range) {
/* List IP in range */
	return array_map('long2ip', range( ip2long($range[0]), ip2long($range[1]) ) );
}

function isIn($netA, $netB) {
/* TRUE if an IP of $netA is contained in netB */
	list($addressA,$maskA) = explode('/', $netA);
	list($addressB,$maskB) = explode('/', $netB);
	require_once 'vendor/autoload.php';
	$net = new \dautkom\ipv4\IPv4();
	$range = $net->address($addressA)->mask($maskA)->getRange();
	$ips = ipRange($range);
	foreach ( $ips as $ip )
		if ( $net->address($addressB)->mask($maskB)->has($ip) )
			return TRUE;
	return FALSE;
}

function netOverlap($myconn, $tabletype, $net, &$thisNet, $loguser) {
/* return TRUE if $net overlap an existing network into DB */
	$thisNet = NULL;
	if ($tabletype['field'] != 'network') {
		syslog(LOG_ERR, $loguser.': '.$tabletype['name'].' is not a network list.');
		return FALSE;
	}
	$result = searchentry ($myconn,'ALL',$tabletype);
        if ($result->num_rows) {
		while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
			$thisNet = long2ip($row['network']).'/'.long2ip($row['netmask']);
			if ( isIn($thisNet, $net) ) {
				$result->free();
				syslog(LOG_INFO, "$loguser: the net <$net> overlaps the existing network <$thisNet>.");
				return TRUE;
			}
		}
	}
	$result->free();
	return FALSE;
}

/*********************************************************************************************/


/* For miltermap */
function checkMilterConf($table) {
	if (isset($table['milter'])) {
        	if ($table['milter'] ===  TRUE) {
			switch ( $table['field'] ) {
				case 'network':
				case 'ip':
					return TRUE;
			}
		}
	}
	return FALSE;
}			

/*
function enterDBMilt($myconn,$tables,$loguser) {
        if (!($myconn->select_db($tables('name')))) {
                syslog(LOG_ERR, $loguser.': Can\'t enter into DB '.$tables('name'));
                return FALSE;
        }
	return TRUE;
}
*/

function milterTable($t) {
	/* Return the milter object table for type t  or FALSE on error */
        switch ($t) {
                case 'network':
                        return 'net';
                case 'ip':
                        return 'ips';
                default:
			syslog(LOG_EMERG, "ALERT: type <$t> not allowed in configuration. ");
                        return FALSE;
        }
}


function readMiltName($myconn,$loguser) {
	$milters=array();
	$query = 'SELECT `name` FROM `config`';

        $result = $myconn->query($query);
        if($result === false) {
                syslog(LOG_EMERG, "$loguser: ALERT: Query <$query> failed: ".$myconn->error);
		return FALSE;
	}
	if ($result->num_rows) {
		while ($milt = $result->fetch_array(MYSQLI_ASSOC))
			$milters[] = $milt['name'];
	}
	$result->free();
	return $milters;
}

function changeMilter ($myconn,$loguser,$miltVal,$table,$miltID) {
	$query = array();
	foreach ( $miltVal as $value => $action ) {
		switch ( $action ) {
			case 'keep':
				break;
			case 'add':
				$query[] = sprintf( "INSERT INTO `milt` (
                		        	`id` ,
                        			`name` 
                			)
                			VALUES (
                        			%d ,
						'%s'
					)",$miltID,$value);
				break;
			case 'del':
				$query[] = "DELETE FROM  `milt` WHERE (`id` = '$miltID' AND `name` = '$value')";
		}
	}
	if ( count($query) ) /* This "if" is redundant, because if I call this I already checked there is a change */
		/* I update datemod because the user couldn't change */
		$query[] = sprintf('UPDATE `%s` SET
						`user`=\'%s\',
						`datemod`= CURRENT_TIMESTAMP
					 WHERE `idmilt`=%d', $table, $loguser, $miltID);


	/* Start a safe transaction: it commits only if all queries happen */
	$myconn->autocommit(FALSE);
	$myconn->begin_transaction(MYSQLI_TRANS_START_READ_ONLY);
	$ok = TRUE;
	foreach ( $query as $q ) {
		if ($myconn->query($q) !== TRUE) {
			$ok = FALSE;
			syslog(LOG_ERR, "$loguser: Error: ".$myconn->error);
		}
	}
	if ( $ok ) {
		if ( $myconn->commit() )
			syslog(LOG_INFO, "$loguser: Milter setting changed successfully.");
		else {
			syslog(LOG_ERR, "$loguser: Milter setting NOT changed for an unpredictable COMMIT error.");
			if ( $myconn->rollback() )
				syslog(LOG_INFO, "$loguser: rollback succeeded.");
			else
				syslog(LOG_ERR, "$loguser: rollback failed. Your db could be compromized. Check it!");
			$ok = FALSE;
		}
	}
	else
		syslog(LOG_ERR, "$loguser: Error: Milter setting NOT changed. See at above errors.");
	return $ok;
		
}
	

function curl_get($url, array $get = NULL, array $options = array(), $loguser)
{
    $defaults = array(
        CURLOPT_URL => $url. (strpos($url, '?') === FALSE ? '?' : ''). http_build_query($get),
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_TIMEOUT => 4
    );

    $ch = curl_init();
    curl_setopt_array($ch, ($options + $defaults));
    if( ! $result = curl_exec($ch))
    {
        syslog(LOG_ERR, sprintf('%s: CURL Error: <%s>', $loguser, curl_error($ch)));
    }
    curl_close($ch);
    return $result;
}


function nsdom($dom) {
/* Return the first lowercase upper domain (or domain itself) with NS record */
/* checkdnsrr doesn't work with alias... use dns_get_record */
	if ( strpos ( rtrim($dom, '.'), '.' ) === false )
	/* if $dom doesn't contain dots, then it is a TLD. We don't list TLD. */
		return NULL;
	if (@dns_get_record ( $dom , DNS_NS ))
		return strtolower(rtrim($dom, '.'));
	if (@dns_get_record ( $dom , DNS_A )) 
		return nsdom( ltrim(strstr($dom, '.'), '.') );
	return NULL;
}

function isValid($dom) {
/* Return TRUE if domain has NS or A record */
	if (preg_match('/^(?!:\/\/)([a-zA-Z0-9-]+\.){0,5}[a-zA-Z0-9-][a-zA-Z0-9-]+\.[a-zA-Z]{2,64}?\.{0,1}$/i',$dom) === 1) {
		if (checkdnsrr ( $dom , 'NS' ))
			return TRUE;
		if (checkdnsrr ( $dom , 'A' ))
			return TRUE;
	}
	return FALSE;
}

/*
function checkEmailAddress($email) {
	if(preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $email))
		return true;
	return false;
}

function checkIP($ip)
{
	$cIP = ip2long($ip);
	$fIP = long2ip($cIP);
	if ($fIP == '0.0.0.0') return FALSE;
	return TRUE;
}
*/
?>

