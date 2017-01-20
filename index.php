<html>
<head>
<title>I RBL You!</title>
<link rel="stylesheet" type="text/css" href="/include/style.css">
<link rel="SHORTCUT ICON" href="favicon.ico">
<script  src="/include/ajaxsbmt.js" type="text/javascript"></script>
<script src="https://ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/prototype.js" type="text/javascript" charset="utf-8"></script>
<script src="https://ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/scriptaculous.js?load=effects" type="text/javascript" charset="utf-8"></script>
</head>
<body>
<h1> RBL System Management</h1> 
<?php
require_once('config.php');
require_once('function.php');


if ( $require_auth ) if ( username() == 'unknown' ) exit ("<p>You MUST configure your server to use authentication.</p>");


if ( $imapListActive )
	print ' <p style="text-align: right"><a href="/spamreport" target="_new">SPAM Learn Observer</a></p>';

print <<<END
<form name="check" action="result.php" onSubmit="xmlhttpPost('result.php', 'check', 'Risultato', '<img src=\'/include/pleasewait.gif\'>'); return false;" enctype="text/plain" method="post" target="_self">
                        Lookup&nbsp;<select class="input_text" name="genere" size="1">
END;


$option=NULL;
$desc = array_keys($tables);
foreach ($desc as $description) { 
	$disabled = $tables["$description"]['active']==TRUE ? '' : ' disabled';  
	$option .= '<option value="'.$description."\"$disabled>$description</option>";
	}

print <<<END
$option
</select> <input class="input_text" maxlength="90" name="Value" size="30" type="text" value="ALL" required pattern="((((^|\.)((25[0-5])|(2[0-4]\d)|(1\d\d)|([1-9]?\d))){4})|ALL)$" title="A valid IP address is required, or word 'ALL'" /> <input name="Check" type="submit" value="Check"  />&nbsp;
<!-- <input name="Find" type="button"  value="Get Sender IP from source mail" onClick="xmlhttpPost('mailform.htm', 'check', 'Risultato', '<img src=\'/include/pleasewait.gif\'>'); return false;"> --></form>
END;

?>
<hr>
<DIV id='Risultato'></DIV>
<div style="clear: both">
<hr>
<p>This DNSBL service works with authomated listing mechanisms. RFC6471 compliants (I hope!). HTML5 browser required.</p>
<h5 style="margin-top:1ex;margin-bottom:0;text-align: center">Just your Personal <i>RBL Tool System</i> for MTA - Version <?php echo $version; ?></h5>
</div>
</body>
</html>
