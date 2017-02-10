<?php

$version='1.9d';

function username() {
	if (isset ($_SERVER['REMOTE_USER'])) $user = $_SERVER['REMOTE_USER'];
        	else if (isset ($_SERVER['USER'])) $user = $_SERVER['USER'];
                	else $user='unknown';
	return $user;
}


function addtolist ($myconn,$user,$value,$type,$table,$expUnit,$expQ,$myreason) {
// See MySQL manual for $expQ and $expUnit at
// https://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_timestampadd

	$result=FALSE;

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

function relist ($myconn,$user,$value,$type,$table,$expUnit,$expQ,$myreason) {

	$result=FALSE;

        switch ($type) {
	  case 'ip':
                $query= sprintf("UPDATE `$table` SET
			`active` = '1',
			`user` = '%s',
			`exp` = TIMESTAMPADD(%s,%d,CURRENT_TIMESTAMP),
			`nlist` = `nlist` + 1,
			`reason` = '%s'
			WHERE `$table`.`$type` = INET_ATON('%s') LIMIT 1" ,$user,$expUnit,$expQ,$myreason,$value);
		break;
          case 'network':
		list($sub['net'],$sub['mask'])=explode('/',$value);
                $query= sprintf("UPDATE `$table` SET
                        `active` = '1',
                        `user` = '%s',
                        `exp` = TIMESTAMPADD(%s,%d,CURRENT_TIMESTAMP),
                        `nlist` = `nlist` + 1,
                        `reason` = '%s'
                        WHERE (`$table`.`$type` = INET_ATON('%s') AND `$table`.`netmask` = INET_ATON('%s')) LIMIT 1" ,$user,$expUnit,$expQ,$myreason,$sub['net'],$sub['mask']);
		break;
	  default:
                $query= sprintf("UPDATE `$table` SET
                        `active` = '1',
                        `user` = '%s',
                        `exp` = TIMESTAMPADD(%s,%d,CURRENT_TIMESTAMP),
                        `nlist` = `nlist` + 1,
                        `reason` = '%s'
			WHERE `$table`.`$type` = '%s' LIMIT 1" ,$user,$expUnit,$expQ,$myreason,$value);
	}

        if ($myconn->query($query) === TRUE) {
            syslog(LOG_INFO, "$user: relist $type <$value> on <$table> for $expQ $expUnit.");
		$result=TRUE;
        }
        else syslog (LOG_ERR, "$user: Error: ". $myconn->error);
	return $result;
}

function remove ($myconn,$user,$value,$type,$table) {
        $result=FALSE;

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
	$desc = array_keys($tables);
	foreach ($desc as $tdesc) { 
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


function ask($myconn,$id,$what,$alltables,$typedesc,$value,$lock,$user,$adm) {

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
        $table = $tablelist['name'];

        if ($value == 'ALL') $query = 'select * from '.$table;
        else {
                switch ($type) {
                  case 'ip':
                        $query= "select * from $table where $type =  INET_ATON('$value')";
                        break;
                  case 'network':
                        list($sub['net'],$sub['mask'])=explode('/',$value);
                        $query= 'select * from '.$table.' where (((inet_aton(\''.$sub['net'].'\') | (~ inet_aton(\''.$sub['mask'].'\'))) & netmask) = network)';
                        break;
                  default:
                        $query= "select * from $table where $type = '$value'";
                }
        }

	$result = $myconn->query($query);
	if($result === false)
		syslog(LOG_EMERG, "ALERT: Query <$query> failed: ".$myconn->error);
        return $result;
}

function countListed ($myconn,$table) {
/* Return number of current listed items into a rbl table */
	$number = 0;
	$query = "SELECT COUNT(*) as `count` FROM `$table` WHERE (`active`=1 AND TIMESTAMPDIFF(MICROSECOND,NOW(),`exp`)>0) GROUP BY `active` ORDER BY `count` DESC LIMIT 1";
	$row = $myconn->query($query);
	$number = $row->fetch_array(MYSQLI_ASSOC);
	$number = $number['count'];
	$row->free();
	return $number;
}


function isFull($myconn,$typedesc,$alltables) {
        if (isset($alltables["$typedesc"]['limit'])) {
                if ( countListed($myconn,$alltables["$typedesc"]['name']) >= $alltables["$typedesc"]['limit'] ) 
                        return TRUE;
        }
	return FALSE;
}

function rlookup ($myconn,$user,$adm,$value,$typedesc,$tables) {

	$type = $tables["$typedesc"]['field'];
	$table = $tables["$typedesc"]['name'];

	$result = searchentry ($myconn,$value,$tables["$typedesc"]);
	if ($result) {
		printf("<pre>Your request for $type &lt;$value&gt; returned %d items.\n</pre>", $result->num_rows);

        /* Check for limit in number of listed items */
	$full = isFull($myconn,$typedesc,$tables);
	if ($full) print '<p>'.htmlspecialchars("$typedesc has reached maximum value of ".$tables["$typedesc"]['limit'].' listed items.').'</p>';

		if ($result->num_rows) {
			print '<table><thead><tr><th>'.$type.'</th><th title="The date this object has been listed for the first time">DateAdd</th><th>DateMod</th><th>Exp</th><th>Status</th><th title="Number of times this object has been listed">#List</th><th>Authored by</th><th width="250">Reason</th><th>Action</th></tr></thead><tfoot><tr></tr></tfoot><tbody>'."\n";
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

                		printf ("<tr id=id$i><td id='status$listed'>%s</td><td id='status$listed'>%s</td><td id='status$listed'>%s</td><td id='status$listed'>%s</td><td id='status$listed'>%s</td><td id='status$listed'>%s</td><td id='status$listed'>%s</td><td id='status$listed'>%s</td><td>%s</td></tr>\n", $element, $riga['date'], $riga['datemod'], $riga['exp'], $riga['active'], $riga['nlist'], $riga['user'],htmlspecialchars($riga['reason']),ask($myconn,$i,$listed,$tables,$typedesc,$element,$full,$user,$adm));
				$i++;
        		}
			print '</tbody></table>';
		}
		else {
			print "<pre>$type &lt;$value&gt; is not listed!\n</pre>";
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
	$messageID = md5(uniqid($now,1)) . '@' . $_SERVER["HOSTNAME"];
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
	$params = NULL;
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


function searchAndList ($myconn,$loguser,$tables,$typedesc,$value,$unit,&$quantity,$reason) {

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
                        /* Entry already listed */
                        if ( isListed($thisentry) ) {
                                syslog(LOG_INFO, $loguser.': '.$value.' already listed. Nothing to do.');
                                $result->free();
                                return FALSE;
                        }

                        /* Entry delisted */
                        $result->free();
			$quantity *= $thisentry['nlist'];
                        return relist ($myconn,$loguser,$value,$type,$table,$unit,$quantity,$reason);


                /* First time list value */
                case 0:
                        $result->free();
                        return addtolist ($myconn,$loguser,$value,$type,$table,$unit,$quantity,$reason);
        }
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

