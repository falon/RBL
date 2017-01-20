<html>
<head>
<title>IP finder</title>
<link rel="stylesheet" type="text/css" href="/include/style.css">
<link rel="SHORTCUT ICON" href="favicon.ico">
</head>
<body>
<div id="ipfound">
<?php
system('/var/www/html/RBL/modrblfilter --ip < '.$_FILES['mailfile']['tmp_name']);
?>
</div>
<body>
</html>
