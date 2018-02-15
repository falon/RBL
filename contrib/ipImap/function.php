<?php
function getIP($header,$mxserver,$msa) {
/* Get submission server's IP from header's mail */
/* Each line must end with /r/n			 */
/* IP is the first one written by your mxserver	 */

	$ip = FALSE;
	$host = FALSE;
	$dateR = FALSE;
	if ( preg_match_all('/^Received:\sfrom(?:.|\r\n\s)*?[\[\(]\s*(?P<ip>\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})[\]\)](?:.|\r\n\s)+\s+by(?:\s|\r\n\s+)(?P<host>\S+).*(?:\s|\r\n\s\s)+.*;\s+(?P<date>.*)/m',$header,$received) ) {
		for ($i = count($received[0])-1;$i>=0;$i--) {
#			print "Examine ".$received[0][$i]."\n";
			if ( preg_match($msa,$received['host'][$i]) )
				$dateR = $received['date'][$i];
        		foreach ($mxserver as $mx) {
        			if (!$ip)
					if ($mx == $received['host'][$i]) {
						$host = $received['host'][$i];
						$ip = $received['ip'][$i];
                    			}
                	}
        	}
	}
	if ( preg_match ('/\r\nDate:\s(?P<date>.*)\r\n/',$header,$dateC) != 1)
		$dateC['date'] = 'Not found';
	if ( preg_match ('/\r\nMessage\-I(?:D|d):\s(?P<mid>.*)\r\n/',$header,$mid) != 1)
		$mid['mid'] = NULL;
	return array($ip,$host,$dateR,$dateC['date'],$mid['mid']);
}

function updateReport ($ip,$uid,$ipcount,$uidcount,$hostname,$dateC,$msgid,$dateL) {

	return sprintf ('<tr><td nowrap>%s</td><td nowrap>%s</td><td>%s</td><td>%s</td><td>%u</td><td>%u</td><td>%s</td><td>%s</td></tr>'."\n",$dateL,$dateC,$uid,$ip,$uidcount,$ipcount,$hostname,htmlentities($msgid) );
}

function updatebadReport ( $uid,$dateC,$msgid,$dateL,$text ) {
	return sprintf ('<tr><td nowrap>%s</td><td nowrap>%s</td><td>%s</td><td>%s</td><td nowrap>%s</td></tr>'."\n",$dateL,$dateC,$uid,htmlentities($msgid),$text );
}


function summaryBadReport ($uidvet) {
        $nuid = $uidvet['count'];
        if ( empty($uidvet) ) return NULL;
        $return = '<hr><h3>Statistics by UID</h3><table><tr><th>UID</th><th>Learned times</th></tr>'."\n";

        /* Remove count index */
        $uids = array_keys($uidvet['uid']);
	$totlearn = 0;

        foreach ( $uids as $uid ) {
		$totlearn += $uidvet['uid']["$uid"]['count'];; 
		$return .= sprintf ('<tr><td>%s</td><td>%u</td></tr>',$uid,$uidvet['uid']["$uid"]['count']);
	}
	$return .= sprintf ('<tr><th>%s</th><th>%u</th></tr></table>','TOT',$totlearn);
	$return .= sprintf ('<p>%s : %u</p>','Unique UID',$nuid);

	return $return;
}
	

function array_msort($array, $cols)
{
    $colarr = array();
    foreach ($cols as $col => $order) {
        $colarr[$col] = array();
        foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
    }
    $eval = 'array_multisort(';
    foreach ($cols as $col => $order) {
        $eval .= '$colarr[\''.$col.'\'],'.$order.',';
    }
    $eval = substr($eval,0,-1).');';
    eval($eval);
    $ret = array();
    foreach ($colarr as $col => $arr) {
        foreach ($arr as $k => $v) {
            $k = substr($k,1);
            if (!isset($ret[$k])) $ret[$k] = $array[$k];
            if (isset ($array[$k][$col])) $ret[$k][$col] = $array[$k][$col];
        }
    }
    return $ret;

}


function summaryReportAndList ($cf,$myconn,$tables,$category,$vet,$key) {
	$nk = $vet['count'];

	if ( empty($vet) ) return NULL;
	
	$return = sprintf('<h3>Statistics by %s</h3><table><tr><th>%s</th><th>Learned by</th><th>Learned times</th><th title="This field doesn\'t say if this %s is currently listed, but it says if this %s has listed now!">Listed Now</th></tr>'."\n", strtoupper($key),strtoupper($key),$key,$key);
	
	$values = array_keys($vet["$key"]);

	foreach ( $values as $value ) {
		if ( $value == 'count' ) continue;
                $nlearn = $vet["$key"]["$value"]['count'];
                unset($vet["$key"]["$value"]['count']);
		$quantity = $cf["listing$key"]['quantity']["$category"]; /* In searchAndList this value is
										passed by reference and modified */
		$nuid = count($vet["$key"]["$value"]);
		if ( !$cf["listing$key"]['onlyReport']["$category"] ) {
			if ( ($nlearn >= $cf["listing$key"]['threshold']["$category"])&&($nuid >= $cf["listing$key"]['thresholduid']["$category"]) ) {
				$reason = sprintf(
				'The %s <%s> has been listed because was marked %u times as %s by %u different accounts during last %u days.',
				strtoupper($key),$value,$nlearn,$category,$nuid,$cf['imap']['oldestday']);
				$listed = searchAndList ($myconn,$cf['syslog']['user'],$tables,$cf["listing$key"]['list']["$category"],$value,$cf["listing$key"]['unit']["$category"],$quantity,$reason);
			}
			else $listed = FALSE;
		}
		else $listed = FALSE;
		$nowlist = array( TRUE =>  array(
					'style' => 'id=\'ipfound\'',
					'name'  => 'YES',
				  ),
				  FALSE => array(
					'style' => 'id=\'\'',
					'name' => 'No',
				  ),
				  NULL  => array(
					'style' => 'id=\'\'',
					'name' => 'No',
				  )
		);
		
		$return .='<tr><td rowspan="'.$nuid.'">'.$value.'</td>';
		$return .= sprintf ('<td>%s</td><td rowspan="'.$nuid.'">%u</td><td rowspan="'.$nuid.'" '.$nowlist["$listed"]['style'].'>%s</td></tr>',$vet["$key"]["$value"][0],$nlearn,$nowlist["$listed"]['name']);
		$rowuid=NULL;
                for ($j=1;$j<$nuid;$j++) $rowuid .= '<tr><td>%s</td></tr>';
		array_shift($vet["$key"]["$value"]);
                $return .= vsprintf ($rowuid,$vet["$key"]["$value"]);

	}
	$return .= sprintf ('<tr><th title="unique %s">%u</th><th title="unique uids">%u</th><th>%u</th></table>',$key,$vet["$key"]['count'],$vet['uid']['count'],$nk);


	/* Statistics by UID */
	/* Not used for listing purpose, but useful to you! */
	$return .= sprintf('<h3>Statistics by UID</h3><table><tr><th>UID</th><th>%s learned</th><th>Learned times</th></tr>'."\n",$key);
	$uids = array_keys($vet['uid']);
        foreach ( $uids as $uid ) {
		if ( $uid == 'count' ) continue;	
	        $nlearn = $vet['uid']["$uid"]['count'];
	        unset ( $vet['uid']["$uid"]['count'] );
		$nip = count($vet['uid']["$uid"]);
		$return .='<tr><td rowspan="'.$nip.'">'.$uid.'</td>';
		$return .= sprintf ('<td>%s</td><td rowspan="'.$nip.'">%u</td></tr>',$vet['uid']["$uid"][0],$nlearn);
                $rowuid=NULL;
                for ($j=1;$j<$nip;$j++) $rowuid .= '<tr><td>%s</td></tr>';
                array_shift($vet['uid']["$uid"]);
                $return .= vsprintf ($rowuid,$vet['uid']["$uid"]);

        }
        $return .= sprintf ('<tr><th title="unique uids">%u</th><th title="unique %s">%u</th><th>%u</th></table>',
			$vet['uid']['count'],$key,$vet["$key"]['count'],$nk);


	return $return;
}


function splunksearch ($service,$message_id,$date) {

	// Run a blocking search
	$searchQueryBlocking = 'search (message_id="'. addslashes( $message_id ) .
				'" OR sasl_username) | transaction message_id queue_id maxspan=3m maxpause=2m | search sasl_username message_id=* | table sasl_username';

	/* Doesn't work on Splunk 6.6 for HTTP exceptions
	// A blocking search returns the job when the search is done
	$job = $service->getJobs()->create($searchQueryBlocking, array(
	    'exec_mode' => 'blocking',
	    'earliest_time' => date("c",strtotime ($date)-120),
	    'latest_time' => date("c",strtotime ($date)+60)
	));

	if ($job['resultCount'] == 0) return FALSE;

	// Get job results
	$resultSearch = $job->getResults();
	*/

	// A one shot search
        $searchParams = array(
                'earliest_time' => date("c",strtotime ($date)-120),
                'latest_time' => date("c",strtotime ($date)+60)
        );

        // Run a oneshot search that returns the job's results
        $resultsStream = $service->oneshotSearch($searchQueryBlocking, $searchParams);
        $resultSearch = new Splunk_ResultsReader($resultsStream);

	// Use the built-in XML parser to display the job results
	foreach ($resultSearch as $result)
	  {
	    if ($result instanceof Splunk_ResultsFieldOrder)
	    {
	      // More than one field attribute returned by search
	      // You must redefine the search
	      if ( count($result->getFieldNames()) > 1 ) return FALSE;
	    }
	    else if ($result instanceof Splunk_ResultsMessage)
	    {
	      // I don't want messages in my search
	      return FALSE;
	    }
	    else if (is_array($result))
	    {
	      // Process a row
	      foreach ($result as $key => $valueOrValues)
	        {
	         if (is_array($valueOrValues))
	          {
	            return FALSE;
	          }
	         else
	          {
	            return $valueOrValues;
	            #print "  {$key} => {$value}\r\n";
	          }
	        }
	    }
	    else
	    {
	      #print "Unknow result type";
	      return FALSE;
	    }
	  }
}

/*
        Function to read body taken from
        https://www.electrictoolbox.com/php-imap-message-body-attachments/
*/

function flattenParts($messageParts, $flattenedParts = array(), $prefix = '', $index = 1, $fullPrefix = true) {

        foreach($messageParts as $part) {
                $flattenedParts[$prefix.$index] = $part;
                if(isset($part->parts)) {
                        if($part->type == 2) {
                                $flattenedParts = flattenParts($part->parts, $flattenedParts, $prefix.$index.'.', 0, false);
                        }
                        elseif($fullPrefix) {
                                $flattenedParts = flattenParts($part->parts, $flattenedParts, $prefix.$index.'.');
                        }
                        else {
                                $flattenedParts = flattenParts($part->parts, $flattenedParts, $prefix);
                        }
                        unset($flattenedParts[$prefix.$index]->parts);
                }
                $index++;
        }

        return $flattenedParts;

}

function getPart($connection, $messageNumber, $partNumber, $encoding) {

        $data = imap_fetchbody($connection, $messageNumber, $partNumber);
        switch($encoding) {
                case 0: return $data; // 7BIT
                case 1: return $data; // 8BIT
                case 2: return $data; // BINARY
                case 3: return base64_decode($data); // BASE64
                case 4: return quoted_printable_decode($data); // QUOTED_PRINTABLE
                case 5: return $data; // OTHER
        }


}
/***********************************/

function getDomains ($text,$exclude) {
	/* Pattern from https://mathiasbynens.be/demo/url-regex */
	/* Current choice: @gruber */
	$pattern = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#iS';
	$ret = array();
	$num_found = preg_match_all($pattern, $text, $out);
	if ( ($num_found !== FALSE) && ($num_found>0) ) {
		foreach ($out[0] as $url) {
			$dom=parse_url($url, PHP_URL_HOST);
			if (!( empty($dom) || in_array($dom,$exclude) ))
				$ret[] = $dom;
		}
	}
	print_r($out[0]);
	return array_values(array_unique($ret));
}

function parseURL ($connection,$messageNumber, $exclusionList) {
	$message = '';
	$structure = imap_fetchstructure($connection, $messageNumber);
	if (isset($structure->parts)) {
		$flattenedParts = flattenParts($structure->parts);
		foreach($flattenedParts as $partNumber => $part) {

			switch($part->type) {
		
				case 0:
					// the HTML or plain text part of the email
					$message .= getPart($connection, $messageNumber, $partNumber, $part->encoding);
				break;
		
				case 1:
					// multi-part headers, can ignore
		
				break;
				case 2:
					// attached message headers, can ignore
				break;
			
				case 3: // application
				case 4: // audio
				case 5: // image
				case 6: // video
				case 7: // other
				break;
	
			}
	
		}
	}
	else
		$message = getPart($connection, $messageNumber, 1, $structure->encoding);

	if ( !empty($message) )
		return getDomains($message, $exclusionList);
	return array();
}

function humanKey($key) {
	switch($key) {
		case 'ip':
			return 'ips';
		case 'dom':
			return 'domains';
	}
	return $key;
}

function writeFileHeader($f,$conf,$key,$type,$rtime) {
        fwrite( $f, file_get_contents(dirname(__FILE__) . '/' . $conf['report']['reportTemplateHeader']) );
        fwrite( $f,sprintf('<h1> Report of %s %s</h1><h5>%s</h5><h2>Detailed Report</h2>',$type, strtoupper(humanKey($key)),$rtime) );
        if ($conf["listing$key"]['onlyReport']["$type"]) {
                fwrite( $f,sprintf('<p>None of the below %s have been listed because listing is not active in configuration.</p>',
		strtoupper(humanKey($key))) );
		syslog(LOG_INFO, sprintf('%s: Report only for %s %s: no listing activated in configuration.',
			$conf['syslog']['user'],$type,humanKey($key))
		);
	}
        fwrite( $f,sprintf('<table><tr><th title="taken from Received header" nowrap>Date of Learn</th><th title="taken from Date header" nowrap>Date of Write</th><th nowrap>UID</th><th nowrap>%s</th><th title="How many times this uid learns">#UID</th><th title="Number of times this learned %s appears in different mails">#%s</th><th nowrap>Received by</th><th>Message-Id</th></tr>',
	strtoupper($key),strtoupper($key),strtoupper($key)) );
}


function imapReport ($cf,$myconnArray,$splunkconn,$tables,$type) {
	$file = dirname(__FILE__) . '/' . $cf['report']['reportFile']["$type"];
	$filed = dirname(__FILE__) . '/' . $cf['report']['reportDomFile']["$type"];
	$fileb= dirname(__FILE__) . '/' . $cf['report']['badreportFile']["$type"];
	$m_mail = imap_open('{'.$cf['imap']['mailhost'].':143/imap/novalidate-cert/authuser='.$cf['imap']['authuser'].'}'.$cf['imap']['folder']["$type"], $cf['imap']['account'],$cf['imap']['authpassword'], OP_READONLY)
        	or syslog (LOG_EMERG, $cf['syslog']['user'].': Error in IMAP connection to <'.$cf['imap']['mailhost'].'>: ' . imap_last_error());
	if ( !$m_mail ) exit(254);
		

	syslog (LOG_INFO,$cf['syslog']['user'].': Successfully connected to <'.$cf['imap']['mailhost'].">; Reading $type messages of last ".$cf['imap']['oldestday'].' days...');
	//get all messages
	$dateTh = date ( "d-M-Y", strToTime ( '-'.$cf['imap']['oldestday'].' days' ) );
        $dateN  = date ( "d-M-Y", strToTime ( "now" ) );
        $m_search=imap_search ($m_mail, "SINCE \"$dateTh\" BEFORE \"$dateN\"" );

	// Order results starting from newest message
	if ( empty($m_search) ) {
		syslog (LOG_INFO,$cf['syslog']['user'].": No mail found in $type folder. No reports written for $type.");
	        if ( $ierr = imap_errors() )
	                foreach ( $ierr as $thiserr )
	                        syslog (LOG_ERR, $cf['syslog']['user'].": IMAP Error: $thiserr");
	        if ( $ierr = imap_alerts() )
	                foreach ( $ierr as $thiserr )
	                        syslog (LOG_ALERT, $cf['syslog']['user'].": IMAP Alert: $thiserr");
		imap_close( $m_mail );
		if ( file_exists( $file ) ) unlink ($file);
		if ( file_exists( $filed ) ) unlink ($filed);
		if ( file_exists( $fileb ) ) unlink ($fileb);
		return FALSE;
	}
	$nmes = count ($m_search);
	syslog (LOG_INFO,$cf['syslog']['user'].": Found $nmes mail in $type folder.");
	if ($nmes>0) rsort($m_search);

	// Create report file

	$fp = fopen($file, 'w');
	$fpd= fopen($filed, 'w');
	$fpb= fopen($fileb, 'w');
	$lastup = "Last Update: " . date ("d F Y H:i", time());
	writeFileHeader($fp,$cf,'ip',$type,$lastup);
	writeFileHeader($fpd,$cf,'dom',$type,$lastup);

	fwrite( $fpb,file_get_contents(dirname(__FILE__) . '/' . $cf['report']['reportTemplateHeader']) );
	fwrite( $fpb,"<h1> Report of bad reported $type mails</h1><h5>$lastup</h5><h2>Detailed Report</h2>" );
	fwrite( $fpb,'<table><tr><th title="taken from Received header" nowrap>Date Learn</th><th title="taken from Date header" nowrap>Date Received</th><th nowrap>UID</th><th>Message-Id</th><th title="Why is this a bad report?">Reason</th></tr>' );

	$ipuid = array();
	$ipuid['count'] = 0;
	$ipuid['uid'] = array();
	$ipuid['ip'] = array();
	$ipuid['ip']['count'] = 0;
	$ipuid['uid']['count'] = 0;
	$domuid = array();
	$domuid['count'] = 0;
	$domuid['dom'] = array();
	$domuid['dom']['count'] = 0;
	$domuid['uid'] = array();
	$domuid['uid']['count'] = 0;
	$uidbad = array();
	$uidbad['count'] = 0;
	$uidbad['uid'] = array();

        // loop for each message
	foreach ($m_search as $onem) {

	        //get imap header info for obj thang
	        //$headers = imap_headerinfo($m_mail, $onem);
	        //$head = imap_fetchheader($m_mail, $headers->Msgno);
		$head = imap_fetchheader($m_mail, $onem );
	        //$obj = imap_rfc822_parse_headers( $head);

	        list ($ip,$host,$dateReceived,$dateClient,$mid) =  getIP( $head,$cf['mx_hostname']['mx'],$cf['msa']['msalearn'] );
		if (empty($mid)) {
			$uid='NA';
			syslog (LOG_ERR, $cf['syslog']['user'].": Error retrieving data for empty Message-ID.");
		} else {
			if ($dateReceived === FALSE) {
				$uid='unauthenticated';
				syslog (LOG_ERR, $cf['syslog']['user'].": Error retrieving date for $mid. Maybe this mail was not submitted to Learner MSA");
			} else  
				if ( !($uid = splunksearch ($splunkconn, trim($mid,'<>'), $dateReceived)) ) {
					syslog (LOG_ERR, $cf['syslog']['user'].": Error retrieving uid from Splunk log for $mid.");
					$uid='unknown';
				}
		}

		/* Extract domains url in body */
		$domains = parseURL ($m_mail,$onem,$cf['listingdom']['exclude']);

	        /* Update count of each ip */
	        if ($host and ($uid!='NA') and ($uid!='unauthenticated') and ($uid!='unknown')) { /* IP is received by MX servers  and learned by valid uid */
			$ipuid['count']++;					//number of right messages

	                if (in_array($uid,array_keys($ipuid['uid']))) {
				$ipuid['uid']["$uid"]['count']++;		//number of learn by this uid
				if (!in_array($ip,$ipuid['uid']["$uid"])) 
					$ipuid['uid']["$uid"][]=$ip;		//ips learned by this uid
			}
			else {
				$ipuid['uid']["$uid"]['count'] = 1;
				$ipuid['uid']["$uid"][]=$ip;
				$ipuid['uid']['count']++;                	//number of unique uids
			}

                        if (in_array($ip,array_keys($ipuid['ip']))) {
                                $ipuid['ip']["$ip"]['count']++;			//number of time this ip appears in different messages
				if (!in_array($uid,$ipuid['ip']["$ip"]))
					$ipuid['ip']["$ip"][]=$uid;		//uids that learned this ip
			}
                        else {
                                $ipuid['ip']["$ip"]['count'] = 1;
				$ipuid['ip']["$ip"][]=$uid;
				$ipuid['ip']['count']++;			//number of unique ips
                        }

			foreach ($domains as $dom) {
				$domuid['count']++;
				if (in_array($uid,array_keys($domuid['uid']))) {
					$domuid['uid']["$uid"]['count']++;               //number of learn by this uid
					if (!in_array($dom,$domuid['uid']["$uid"]))
						$domuid['uid']["$uid"][]=$dom;		//domains learned by this uid
				}
				else {
					$domuid['uid']["$uid"]['count'] = 1;
					$domuid['uid']["$uid"][]=$dom;
					$domuid['uid']['count']++;			//number of unique uids
				}

				if (in_array($dom,array_keys($domuid['dom']))) {
					$domuid['dom']["$dom"]['count']++;	//number of learn with this domain
					if (!in_array($uid,$domuid['dom']["$dom"]))
						$domuid['dom']["$dom"][]=$uid;	//uids that learned this domain
				}
				else {
					$domuid['dom']["$dom"]['count'] = 1;
					$domuid['dom']["$dom"][]=$uid;
					$domuid['dom']['count']++;		//number of unique domains
				}

				fwrite($fpd,
					updateReport (
						$dom,$uid,$domuid['dom']["$dom"]['count'],
						$domuid['uid']["$uid"]['count'],$host,
						$dateClient,$mid,$dateReceived
					)
				);
			}

	        	/* Update HTML report */
	        	fwrite($fp,updateReport ( $ip,$uid,$ipuid['ip']["$ip"]['count'],$ipuid['uid']["$uid"]['count'],$host,$dateClient,$mid,$dateReceived) );
		}
	        else {	/* Bad learn */
			
                        if (in_array($uid,array_keys($uidbad['uid']))) 
                                $uidbad['uid']["$uid"]['count']++;               //number of bad learn by this uid
                        else {
                                $uidbad['uid']["$uid"]['count'] = 1;
				$uidbad['uid']["$uid"][]=$uid;
                                $uidbad['count']++;                       //numeber of unique bad uids
                        }
			/* The reason of bad report */
			if ($host === FALSE) $reason = 'This mail was not received by recognized MX host';
			if ($dateReceived === FALSE) $reason = 'This mail was not submitted to recognized MSA for learn';
			if ($uid=='unknown') $reason = 'The uid of this mail was not found in splunk log';
			if (!isset($reason)) $reason = '?';
				
			fwrite( $fpb,updatebadReport ( $uid,$dateClient,$mid,$dateReceived,$reason ) );
		}
	}


	//close report file and mailbox

	/* Summary Report */
	$ipuid['ip'] = array_msort( $ipuid['ip'], array('count'=>SORT_DESC) );
	$ipuid['uid'] = array_msort( $ipuid['uid'], array('count'=>SORT_DESC) );
	$domuid['dom'] = array_msort( $domuid['dom'], array('count'=>SORT_DESC) );
	$domuid['uid'] = array_msort( $domuid['uid'], array('count'=>SORT_DESC) );
	$uidbad['uid'] = array_msort( $uidbad['uid'], array('count'=>SORT_DESC) );
	
	fwrite($fp, '</table>');
	fwrite($fpd, '</table>');
	fwrite($fp, '<hr><h2>Summary Report</h2><h5>Listing policy: ip must be learned at least '.$cf['listingip']['threshold']["$type"].' times from at least '.$cf['listingip']['thresholduid']["$type"].' different valid uids.</h5>' );
	fwrite($fpd, '<hr><h2>Summary Report</h2><h5>Listing policy: domains must be learned at least '.$cf['listingdom']['threshold']["$type"].' times from at least '.$cf['listingdom']['thresholduid']["$type"].' different valid uids.</h5>' );

        /* Make MYSQL connection */
	if ( $cf['listingip']['onlyReport']["$type"] && $cf['listingdom']['onlyReport']["$type"] )
		$mysqli = NULL;
	else {
        	$mysqli = new mysqli($myconnArray['dbhost'], $myconnArray['userdb'], $myconnArray['pwd'], $myconnArray['db'], $myconnArray['dbport']);
        	if ($mysqli->connect_error) {
                	syslog (LOG_EMERG, $cf['syslog']['user'].': Connect Error (' . $mysqli->connect_errno . ') '
                	. $mysqli->connect_error);
                	exit (254);
        	}
        	syslog(LOG_INFO, $cf['syslog']['user'].': Successfully mysql connected to ' . $mysqli->host_info) ;
	}
	/***********************/

	fwrite($fp, summaryReportAndList ($cf,$mysqli,$tables,$type,$ipuid, 'ip') );
	fwrite($fp,file_get_contents(dirname(__FILE__) . '/' . $cf['report']['reportTemplateFooter']));
	fclose($fp);

	fwrite($fpd, summaryReportAndList ($cf,$mysqli,$tables,$type,$domuid, 'dom') );
	fwrite($fpd,file_get_contents(dirname(__FILE__) . '/' . $cf['report']['reportTemplateFooter']));
	fclose($fpd);
	
	if ( !($cf['listingip']['onlyReport']["$type"] && $cf['listingdom']['onlyReport']["$type"]) )
		$mysqli->close();

	fwrite($fpb, '</table>');
	fwrite( $fpb,summaryBadReport( $uidbad ) );
	fwrite($fpb,file_get_contents(dirname(__FILE__) . '/' . $cf['report']['reportTemplateFooter']));
	fclose($fpb);
	syslog (LOG_INFO,$cf['syslog']['user'].': Report files written. Listing job for '.$type.' terminated.');

	if ( $ierr = imap_errors() )
		foreach ( $ierr as $thiserr )
			syslog (LOG_ERR, $cf['syslog']['user'].": IMAP Error: $thiserr");
	if ( $ierr = imap_alerts() )
                foreach ( $ierr as $thiserr )
                        syslog (LOG_ALERT, $cf['syslog']['user'].": IMAP Alert: $thiserr");
	imap_close($m_mail);
}
?>
