<html>
<head>
<title>DNSBL Lookup</title>
<link rel="stylesheet" type="text/css" href="/include/style.css">
<link rel="SHORTCUT ICON" href="favicon.ico">
<script  src="/include/ajaxsbmt.js" type="text/javascript"></script>
<script src="https://ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/prototype.js" type="text/javascript" charset="utf-8"></script>
<script src="https://ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/scriptaculous.js?load=effects" type="text/javascript" charset="utf-8"></script>
</head>
<body>
<h1> DNSBL Lookup</h1> 
<?php

$_POST['Value'] = str_replace('_','.',array_keys($_GET)[0]);
$_POST['genere'] = 'Spam IP';
require_once('result.php');
?>
<hr>
<p>This DNSBL service works with authomated listing mechanisms. RFC6471 compliants. HTML5 browser required.</p>
</div>
</body>
</html>
