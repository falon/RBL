<?php

function imapFolder($cf, $username) {
	$return = array();
	$open='{'.$cf['mailhost'].':143/imap/novalidate-cert/authuser='.$cf['authuser'].'}';
        $m_mail = imap_open($open, $username, $cf['authpassword'], OP_READONLY)
                or syslog (LOG_EMERG, $cf['user'].': Error in IMAP connection to <'.$cf['mailhost'].'>: ' . imap_last_error());
        if ( !$m_mail ) exit(254);


        syslog (LOG_INFO,$cf['user'].': Successfully connected to <'.$cf['mailhost'].'>; Listing folders of account <'.$username.'>...');
        //get all folder
	$list = imap_list($m_mail, $open, "*");
	imap_close($m_mail);
	if (is_array($list))
		foreach ($list as $mbox)
			$return[] = explode($open,$mbox,2)[1];
	else
		syslog (LOG_INFO,$cf['user'] . ': imap_list failed: ' . imap_last_error() );
	return $return;
}

function imapFind ($cf, $username, $folder) {
	$head=array();
	$m_mail = imap_open('{'.$cf['mailhost'].':143/imap/novalidate-cert/authuser='.$cf['authuser'].'}'.$folder, $username,$cf['authpassword'], OP_READONLY)
        	or syslog (LOG_EMERG, $cf['user'].': Error in IMAP connection to <'.$cf['mailhost'].'>: ' . imap_last_error());
	if ( !$m_mail ) exit(254);
		

	syslog (LOG_INFO,$cf['user'].': Successfully connected to <'.$cf['mailhost'].">; Reading <$folder> messages of last ".$cf['oldestday'].' days on account <'.$username.'>...');
	//get all messages
	$dateTh = date ( "d-M-Y", strToTime ( '-'.$cf['oldestday'].' days' ) );
	$m_search=imap_search ($m_mail, "SINCE \"$dateTh\" TEXT \"Authentication-Results: \"" );


	// Order results starting from newest message
	if ( empty($m_search) ) {
		syslog (LOG_INFO,$cf['user'].": No suitable mail found in <$folder> folder.");
	        if ( $ierr = imap_errors() )
	                foreach ( $ierr as $thiserr )
	                        syslog (LOG_ERR, $cf['user'].": IMAP Error: $thiserr");
	        if ( $ierr = imap_alerts() )
	                foreach ( $ierr as $thiserr )
	                        syslog (LOG_ALERT, $cf['user'].": IMAP Alert: $thiserr");
		imap_close( $m_mail );
		return FALSE;
	}
	$nmes = count ($m_search);
	syslog (LOG_INFO,$cf['user'].": Found $nmes mail in <$folder> folder.");
	if ($nmes>0) rsort($m_search);

        // loop for each message
	foreach ($m_search as $onem) 
		$head[] = imap_fetchheader($m_mail, $onem );
	imap_close($m_mail);
	return $head;
}

function dspamLevel($prob, $conf) {
/* Calculate DSPAM Level as the Spamassassin Plugin */
	if (is_null($prob) or is_null($conf)) return '-';
	$t_prob = abs((($prob - 0.5) * 2) * 100);
	return round(($t_prob + ($conf*100)) / 2);
}

function dspamType($classSpam) {
	switch($classSpam) {
		case 'HAM':
			return 'Innocent';
		case 'SPAM':
			return 'Spam';
		default:
			/* this should never happens */
			return $classSpam;
	}
}

function imapInfo($user,$header,$ARhosts,$dpl=false, $learn=false) {
/* Get relevant Info from header's mail */
/* Each line must end with /r/n         */

	$result = array(
                'date' => NULL,
                'from' => NULL,
                'messageid' => NULL,
		'dmarc' => array(
			'result' => NULL,
			'dom'	=> NULL
			),
		'spf' => array(
                        'result' => NULL,
                        'dom'   => NULL
                        ),
		'dkim' => array(
                        'result' => NULL,
                        'dom'   => NULL
                        ),
                'spam' => array(
                        'status' => NULL,
                        'score' => NULL,
                        'th'    => NULL,
                        ),
                'dspam' => array(
                        'type' => NULL,
                        'level' => NULL,
			'learn' => NULL
                        ),
		'warn' => NULL
        );
		

        if ( preg_match_all ('/^Authentication\-Results:\s+(?<host>[\w\.]+);(?:\s+|\r\n\s+)dmarc=(?<dmarc>\w+)\s+\(p=\w+\s+dis=\w+\)\s+header\.from=(?<DMARCfrom>[\w\.]+)/m',$header,$received) ) {
		$k=0;
                for ($i = count($received[0])-1;$i>=0;$i--) {
	                foreach ($ARhosts as $mx) {
				if ($mx == $received['host'][$i]) {
					/* This is a trusted AR result */
					$result['dmarc']['result']=$received['dmarc'][$i];
					$result['dmarc']['dom'] = $received['DMARCfrom'][$i];
					$k++;
				}
                	}
		}
        }
	$received=NULL;
	if ($k>1) $result['warn'][] = 'The trusted DMARC AR Headers are present more than once. Something wrong.';

        if ( preg_match_all('/^Authentication\-Results:\s+(?<host>[\w\.]+);(?:\s+|\r\n\s+)spf=(?<spf>\w+)\s+smtp\.(?:mailfrom|helo)=(?<SPFfrom>[\w\.]+)/m',$header,$received) ) {
		$k=0;
		for ($i = count($received[0])-1;$i>=0;$i--) {
			foreach ($ARhosts as $mx) {
				if ($mx == $received['host'][$i]) {
					/* This is a trusted AR result */
					$result['spf']['result']=$received['spf'][$i];
					$result['spf']['dom'] = $received['SPFfrom'][$i];
					$k++;
                        	}
                	}
        	}
	}
	$received=NULL;
	if ($k>1) $result['warn'][] = 'The trusted SPF AR Headers are present more than once. Something wrong.';

	$k=0;
        if ( preg_match_all('/^Authentication\-Results:\s+(?<host>[\w\.]+);(?:\s+|\r\n\s+)dkim=(?<dkim>\w+)\s+[\w\s\(\)\-]+header\.d=(?<DKIMdom>[\w\.]+)/m',$header,$received) ) {
		for ($i = count($received[0])-1;$i>=0;$i--) {
	                foreach ($ARhosts as $mx) {
        	                if ($mx == $received['host'][$i]) {
                	                /* This is a trusted AR result */
                        	        $result['dkim']['result']=$received['dkim'][$i];
                                	$result['dkim']['dom'] = $received['DKIMdom'][$i];
					$k++;
                        	}
                	}
        	}
	}
	$received=NULL;
	if ($k>1) $result['warn'][] = 'The trusted DKIM AR Headers are present more than once. Something wrong.';

	if ($dpl) { /* Use Spamassassin Plugin */
		if ( preg_match_all('/^X\-Spam\-Status:\s(?P<spamstatus>\w+)\,(?:\s+|\r\n\s+)score=(?P<score>[\-\.\d]+)(?:\s+|\r\n\s+)tagged_above=\-{0,1}\d+(?:\s+|\r\n\s+)required=(?P<th>[\-\.\d]+)(?:\s+|\r\n\s+)tests=\[(?:.|\r\n\s+)*DSPAM_(?P<dtype>SPAM|HAM)_(?P<dlevel>\d\d)(?:.|\r\n\s+)*\]/m',$header,$received) ) {
			$result['spam']['status']=$received['spamstatus'][0];
                	$result['spam']['score'] = $received['score'][0];
			$result['spam']['th'] = $received['th'][0];
			$result['dspam']['type'] = dspamType($received['dtype'][0]);
			$result['dspam']['level'] =$received['dlevel'][0];
        	}
        	if (count($received[0])>1) $result['warn'][] = 'The Spamassassin Headers are present more than once. I consider only the last one.';
	}
	else { /* Parse apart all DSPAM Header and calculate a level */
		if ( preg_match_all('/^X\-Spam\-Status:\s(?P<spamstatus>\w+)\,(?:\s+|\r\n\s+)score=(?P<score>[\-\.\d]+)(?:\s+|\r\n\s+)tagged_above=\-{0,1}\d+(?:\s+|\r\n\s+)required=(?P<th>[\-\.\d]+)(?:\s+|\r\n\s+)tests=\[(?:.|\r\n\s+)*\]/m',$header,$received) ) {
                        $result['spam']['status']=$received['spamstatus'][0];
                        $result['spam']['score'] = $received['score'][0];
                        $result['spam']['th'] = $received['th'][0];
			if (count($received[0])>1)
				$result['warn'][]= 'The Spamassassin Headers are present more than once. I consider only the last one.';
		}
		if ( preg_match ('/\r\nX\-DSPAM\-Result:\s(?P<result>.*)\r\n/',$header,$received) != 1)
	                $result['warn'] = 'DSPAM Result invalid, not present or present more than once.';
	        else
                	$result['dspam']['type']=$received['result'];
		$prob = NULL;
		$conf = NULL;
                if ( preg_match ('/\r\nX\-DSPAM\-Probability:\s(?P<prob>.*)\r\n/',$header,$received) != 1)
                        $result['warn'][] = 'DSPAM Probability invalid, not present or present more than once.';
		else
			$prob = $received['prob'];
		if ( preg_match ('/\r\nX\-DSPAM\-Confidence:\s(?P<conf>.*)\r\n/',$header,$received) != 1)
                        $result['warn'][] = 'DSPAM Confidence invalid, not present or present more than once.';
		else
			$conf = $received['conf'];
		$result['dspam']['level'] = dspamLevel($prob,$conf);
	}
	$received=NULL;
	if ( preg_match ('/\r\nFrom:\s(?P<from>.*)\r\n/',$header,$received) != 1)
                $result['warn'][] = 'From header invalid or not present';
        else
                $result['from'] = $received['from'];

        if ( preg_match ('/\r\nDate:\s(?P<date>.*)\r\n/',$header,$received) != 1)
                $result['warn'][] = 'Date header invalid or not present';
	else
		$result['date'] = $received['date'];

	$received=NULL;
        if ( preg_match ('/\r\nMessage\-I(?:D|d):\s(?P<mid>.*)\r\n/',$header,$received) != 1)
                $result['warn'][] = 'Message-ID invalid, not present or present more than once.';
	else
		$result['messageid']=$received['mid'];

        $received=NULL;

        switch ($learn) {
		case 'dspamc':
        		if ( preg_match ('/\r\nX\-DSPAM\-Signature:\s(?P<sig>.*)\r\n/',$header,$received) != 1)
				$result['warn'] = 'DSPAM Signature invalid, not present or present more than once.';
			else
				$result['dspam']['learn']=$received['sig'];			
			break;
		case false:
			break;
		default:
			syslog (LOG_INFO,$user.': Error in "learn" imap configuration value. Please, set "dspamc" or "false".');
	}
	
        return $result;
}




function printTableHeader($title,$content,$footer=FALSE,$fcontent) {
        print <<<END
	<caption>$title</caption>
	<thead>
	<tr>
END;
	$kcontent = array_keys($content);
        $cols = count($kcontent);
        for ($i=0; $i<$cols; $i++) {
		$key = $kcontent[$i];
                printf ('<th colspan="%d" rowspan="%d">%s</th>',
			!is_array($content[$key]) ?:
			count(array_keys($content[$key])) ?: '1',
			!is_array($content[$key]) ?:
			empty(array_keys($content[$key])) ? '2' : '1',
			$kcontent[$i]);
	}
	print '</tr><tr>';
	for ($i=0; $i<$cols; $i++) {
		$key = $kcontent[$i];
		if (is_array($content[$key])&&($hs = array_keys($content[$key]))) {
			foreach ($hs as $h)
				printf('<th>%s</th>',$h);
		}
	}
		
        print '</tr></thead>';
        if ($footer) {
                print '<tfoot><tr>';
                print "<th colspan=\"$cols\">".$fcontent.'</th>';
                print '</tr></tfoot>';
        }
        return TRUE;
}


function formatVal($val, $learn) {
	foreach (array_keys($val) as $key) {
		if (is_array($val["$key"]) and ($key!='warn'))
			$val["$key"] = formatVal($val["$key"], $learn);
		else {
			switch ($key) {
				case 'warn':
					if (empty($val["$key"]))
						$val["$key"] = '-';
					else 
						$val["$key"] = sprintf('<div title="%s">Y</div>',implode($val["$key"],"\n"));
					break;
				case 'learn':
					$val["$key"] = formLearn($learn, $val);
					break;
				default:
					$val["$key"] = htmlentities($val["$key"]);
			}
		}
	}
	return $val;
}

function formLearn($type, $par) {
	$return = NULL;
	switch ($type) {
		case 'dspamc':
			$classes = array('Spam', 'Innocent');
			foreach ($classes as $class) {
				$par['class'] = $class;
				$val["$class"] = sprintf('dspamc --user dspam --deliver=summary --class=%s --source=error --signature=%s',
							strtolower($class), $par['learn']);
				if (($class != $par['type'])||($par['level']<99))
					$return .= sprintf(file_get_contents('formLearnDSPAM.htm'),
						$class,$class,$val["$class"],base64_encode(json_encode($par)),$class);
			}
		default:
			return $return;
	}
}

function printTableRow($row, $learn, $init=true) {
	$color = 'inherit';
	if ($init) 
		$row=formatVal($row,$learn);
	foreach( $row as $key => $val) {
		if (is_array($val))
			printTableRow($val, $learn, false);
		else {
			/* DSPAM format */
			if (isset($row['type']))
				switch($row['type']) {
					case 'Innocent':
					case 'HAM':
						$color = 'rgba(0,255,0, %.1f)';
						break;
					case 'Spam':
					case 'SPAM':
						$color = 'rgba(255,0,0,%.1f)';
			}
			/* DMARC, DKIM, SPF format */
			if (isset($row['result']))
				switch($row['result']) {
					case 'pass':
						$color = 'rgba(0,255,0, %.1f)';
						break;
					case 'fail':
						$color = 'rgba(255,0,0,%.1f)';
				}
			/* Spamassassin format */
			if (isset($row['status']))
	                        switch($row['status']) {
					case 'No':
						$color = 'rgba(0,255,0, %.1f)';
						break;
					case 'Yes':
						$color = 'rgba(255,0,0,%.1f)';
				}	
				
			$alpha = (is_numeric($val)AND($key=='type')) ? round($val/100,1) : 1.0;
			$bg = sprintf(" style=\"background-color: $color\"", $alpha);		
			printf ('<td class="cellfix"%s>%s</td>',$bg, $val);
		}
	}
}
?>
