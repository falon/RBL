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


function summaryReportAndList ($cf,$myconn,$tables,$category,$ipvet) {
	$nips = $ipvet['count'];

	if ( empty($ipvet) ) return NULL;
	$return = '<h3>Statistics by IP</h3><table><tr><th>IP</th><th>Learned by</th><th>Learned times</th><th title="This field doesn\'t say if this ip is currently listed, but it says if this IP has listed now!">Listed Now</th></tr>'."\n";
	
	$ips = array_keys($ipvet['ip']);

	foreach ( $ips as $ip ) {
		if ( $ip == 'count' ) continue;
                $nlearn = $ipvet['ip']["$ip"]['count'];
                unset($ipvet['ip']["$ip"]['count']);
		$quantity = $cf['quantity']["$category"]; /* In searchAndList this value is passed by reference and modified */
		$nuid = count($ipvet['ip']["$ip"]);
		if ( !$cf['onlyReport'] ) {
			if ( ($nlearn >= $cf['thresholdip']["$category"])&&($nuid >= $cf['thresholduid']["$category"]) ) {
				$reason = "The IP <$ip> has been listed because was marked $nlearn times as $category by $nuid different accounts during last ".$cf['oldestday'].' days.';
				$listed = searchAndList ($myconn,$cf['user'],$tables,$cf['list']["$category"],$ip,$cf['unit']["$category"],$quantity,$reason);
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
		
		$return .='<tr><td rowspan="'.$nuid.'">'.$ip.'</td>';
		$return .= sprintf ('<td>%s</td><td rowspan="'.$nuid.'">%u</td><td rowspan="'.$nuid.'" '.$nowlist["$listed"]['style'].'>%s</td></tr>',$ipvet['ip']["$ip"][0],$nlearn,$nowlist["$listed"]['name']);
		$rowuid=NULL;
                for ($j=1;$j<$nuid;$j++) $rowuid .= '<tr><td>%s</td></tr>';
		array_shift($ipvet['ip']["$ip"]);
                $return .= vsprintf ($rowuid,$ipvet['ip']["$ip"]);

	}
	$return .= sprintf ('<tr><th title="unique ips">%u</th><th title="unique uids">%u</th><th>%u</th></table>',$ipvet['ip']['count'],$ipvet['uid']['count'],$nips);


	/* Statistics by UID */
	/* Not used for listing purpose, but useful to you! */
	$return .= '<h3>Statistics by UID</h3><table><tr><th>UID</th><th>IP learned</th><th>Learned times</th></tr>'."\n";
	$uids = array_keys($ipvet['uid']);
        foreach ( $uids as $uid ) {
		if ( $uid == 'count' ) continue;	
	        $nlearn = $ipvet['uid']["$uid"]['count'];
	        unset ( $ipvet['uid']["$uid"]['count'] );
		$nip = count($ipvet['uid']["$uid"]);
		$return .='<tr><td rowspan="'.$nip.'">'.$uid.'</td>';
		$return .= sprintf ('<td>%s</td><td rowspan="'.$nip.'">%u</td></tr>',$ipvet['uid']["$uid"][0],$nlearn);
                $rowuid=NULL;
                for ($j=1;$j<$nip;$j++) $rowuid .= '<tr><td>%s</td></tr>';
                array_shift($ipvet['uid']["$uid"]);
                $return .= vsprintf ($rowuid,$ipvet['uid']["$uid"]);

        }
        $return .= sprintf ('<tr><th title="unique uids">%u</th><th title="unique ips">%u</th><th>%u</th></table>',$ipvet['uid']['count'],$ipvet['ip']['count'],$nips);	


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


function imapReport ($cf,$myconnArray,$splunkconn,$tables,$type) {
	$file = dirname(__FILE__) . '/' . $cf['reportFile']["$type"];
	$fileb= dirname(__FILE__) . '/' . $cf['badreportFile']["$type"];
	$m_mail = imap_open('{'.$cf['mailhost'].':143/imap/novalidate-cert/authuser='.$cf['authuser'].'}'.$cf['folder']["$type"], $cf['account'],$cf['authpassword'], OP_READONLY)
        	or syslog (LOG_EMERG, $cf['user'].': Error in IMAP connection to <'.$cf['mailhost'].'>: ' . imap_last_error());
	if ( !$m_mail ) exit(254);
		

	syslog (LOG_INFO,$cf['user'].': Successfully connected to <'.$cf['mailhost'].">; Reading $type messages of last ".$cf['oldestday'].' days...');
	//get all messages
	$dateTh = date ( "d-M-Y", strToTime ( '-'.$cf['oldestday'].' days' ) );
        $dateN  = date ( "d-M-Y", strToTime ( "now" ) );
        $m_search=imap_search ($m_mail, "SINCE \"$dateTh\" BEFORE \"$dateN\"" );


	// Order results starting from newest message
	if ( empty($m_search) ) {
		syslog (LOG_INFO,$cf['user'].": No mail found in $type folder. No reports written for $type.");
	        if ( $ierr = imap_errors() )
	                foreach ( $ierr as $thiserr )
	                        syslog (LOG_ERR, $cf['user'].": IMAP Error: $thiserr");
	        if ( $ierr = imap_alerts() )
	                foreach ( $ierr as $thiserr )
	                        syslog (LOG_ALERT, $cf['user'].": IMAP Alert: $thiserr");
		imap_close( $m_mail );
		if ( file_exists( $file ) ) unlink ($file);
		if ( file_exists( $fileb ) ) unlink ($fileb);
		return FALSE;
	}
	$nmes = count ($m_search);
	syslog (LOG_INFO,$cf['user'].": Found $nmes mail in $type folder.");
	if ($nmes>0) rsort($m_search);

	// Create report file

	$fp = fopen($file, 'w');
	$fpb= fopen($fileb, 'w');
	$lastup = "Last Update: " . date ("d F Y H:i", time());
	fwrite( $fp, file_get_contents(dirname(__FILE__) . '/' . $cf['reportTemplateHeader']) );
	fwrite( $fp,"<h1> Report of IP sending $type</h1><h5>$lastup</h5><h2>Detailed Report</h2>" );
	if ($cf['onlyReport']) fwrite( $fp,'<p>None of the below IP has been listed because listing is not active in configuration.</p>');
	fwrite( $fp,'<table><tr><th title="taken from Received header" nowrap>Date of Learn</th><th title="taken from Date header" nowrap>Date of Write</th><th nowrap>UID</th><th nowrap>IP</th><th title="How many times this uid learns">#UID</th><th title="Number of times this learned IP appears in different mails">#IP</th><th nowrap>Received by</th><th>Message-Id</th></tr>' );
	fwrite( $fpb,file_get_contents(dirname(__FILE__) . '/' . $cf['reportTemplateHeader']) );
	fwrite( $fpb,"<h1> Report of bad reported $type mails</h1><h5>$lastup</h5><h2>Detailed Report</h2>" );
	fwrite( $fpb,'<table><tr><th title="taken from Received header" nowrap>Date Learn</th><th title="taken from Date header" nowrap>Date Received</th><th nowrap>UID</th><th>Message-Id</th><th title="Why is this a bad report?">Reason</th></tr>' );

	$ipuid = array();
	$ipuid['count'] = 0;
	$ipuid['uid'] = array();
	$ipuid['ip'] = array();
	$ipuid['ip']['count'] = 0;
	$ipuid['uid']['count'] = 0;
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

	        list ($ip,$host,$dateReceived,$dateClient,$mid) =  getIP( $head,$cf['mx'],$cf['msalearn'] );
		if (empty($mid)) {
			$uid='NA';
			syslog (LOG_ERR, $cf['user'].": Error retrieving data for empty Message-ID.");
		} else {
			if ($dateReceived === FALSE) {
				$uid='unauthenticated';
				syslog (LOG_ERR, $cf['user'].": Error retrieving date for $mid. Maybe this mail was not submitted to Learner MSA");
			} else  
				if ( !($uid = splunksearch ($splunkconn, trim($mid,'<>'), $dateReceived)) ) {
					syslog (LOG_ERR, $cf['user'].": Error retrieving uid from Splunk log for $mid.");
					$uid='unknown';
				}
		}

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
	$uidbad['uid'] = array_msort( $uidbad['uid'], array('count'=>SORT_DESC) );
	
	fwrite($fp, '</table>');
	fwrite($fp, '<hr><h2>Summary Report</h2><h5>Listing policy: ip must be learned at least '.$cf['thresholdip']["$type"].' times from at least '.$cf['thresholduid']["$type"].' different valid uids.</h5>' );

        /* Make MYSQL connection */
	if ( $cf['onlyReport'] )
		$mysqli = NULL;
	else {
        	$mysqli = new mysqli($myconnArray['dbhost'], $myconnArray['userdb'], $myconnArray['pwd'], $myconnArray['db'], $myconnArray['dbport']);
        	if ($mysqli->connect_error) {
                	syslog (LOG_EMERG, $cf['user'].': Connect Error (' . $mysqli->connect_errno . ') '
                	. $mysqli->connect_error);
                	exit (254);
        	}
        	syslog(LOG_INFO, $cf['user'].': Successfully mysql connected to ' . $mysqli->host_info) ;
	}
	/***********************/

	fwrite($fp, summaryReportAndList ($cf,$mysqli,$tables,$type,$ipuid) );
	if ( !$cf['onlyReport'] )
		$mysqli->close();
	fwrite($fp,file_get_contents(dirname(__FILE__) . '/' . $cf['reportTemplateFooter']));
	fclose($fp);

	fwrite($fpb, '</table>');
	fwrite( $fpb,summaryBadReport( $uidbad ) );
	fwrite($fpb,file_get_contents(dirname(__FILE__) . '/' . $cf['reportTemplateFooter']));
	fclose($fpb);
	syslog (LOG_INFO,$cf['user'].': Report files written. Listing job for '.$type.' terminated.');

	if ( $ierr = imap_errors() )
		foreach ( $ierr as $thiserr )
			syslog (LOG_ERR, $cf['user'].": IMAP Error: $thiserr");
	if ( $ierr = imap_alerts() )
                foreach ( $ierr as $thiserr )
                        syslog (LOG_ALERT, $cf['user'].": IMAP Alert: $thiserr");
	imap_close($m_mail);
}
?>
