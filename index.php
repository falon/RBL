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
<h1> RBL Management System</h1> 
<?php
require_once('config.php');
require_once('function.php');

checkSSL();
if ( $require_auth )
	if ( username() == 'unknown' ) exit ("<p>You MUST configure your server to use authentication.</p>");


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
</select> <input class="input_text" maxlength="90" name="Value" size="30" type="text" value="ALL" required pattern="((((^|\.)((25[0-5])|(2[0-4]\d)|(1\d\d)|([1-9]?\d))){4})|(((^|\.)((25[0-5])|(2[0-4]\d)|(1\d\d)|([1-9]?\d))){4})\/(((255\.){3}(255|254|252|248|240|224|192|128|0+))|((255\.){2}(255|254|252|248|240|224|192|128|0+)\.0)|((255\.)(255|254|252|248|240|224|192|128|0+)(\.0+){2})|((255|254|252|248|240|224|192|128|0+)(\.0+){3}))|(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])|\b([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}\b|ALL)$" title="A valid key is required, or the word 'ALL'" /> <input name="Check" type="submit" value="Check"  />&nbsp;
<!-- <input name="Find" type="button"  value="Get Sender IP from source mail" onClick="xmlhttpPost('mailform.htm', 'check', 'Risultato', '<img src=\'/include/pleasewait.gif\'>'); return false;"> --></form>
END;

?>
<hr>
<DIV id='Risultato'></DIV>
<div style="clear: both">
<hr>
<p>This more than DNSBL service works with authomated listing mechanisms. RFC6471 compliants. HTML5 browser required.</p>
<h5 style="margin-top:1ex;margin-bottom:0;text-align: center">Just your Personal <i>RBL Tool System</i> - Version <?php echo $version; ?></h5>
</div>
</body>
</html>
