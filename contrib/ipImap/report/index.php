<html>
<head>
<title>I RBL You!</title>
<link rel="stylesheet" type="text/css" href="/include/style.css">
<link rel="SHORTCUT ICON" href="favicon.ico">
<script type="text/javascript">
<!-- // nascondi ai vecchi browser
window.defaultStatus='IRBLYou! -- A great extension of IQueueYou!';
-->
</script>
<script  src="/include/ajaxsbmt.js" type="text/javascript"></script>
<script src="https://ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/prototype.js" type="text/javascript" charset="utf-8"></script>
<script src="https://ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/scriptaculous.js?load=effects" type="text/javascript" charset="utf-8"></script>
</head>
<body>
<h1>Learn Tool Status</h1>
<form name="formSpam" id="formSpam" onSubmit="xmlhttpPost('result.php', 'formSpam', 'content', '<img src=\'/include/pleasewait.gif\'>'); return false;" enctype="text/plain" method="post" target="_self" class="left">

<?php
$reports = array();
foreach (glob("*.{htm,html}", GLOB_BRACE) as $filename) {
    $reports[$filename] = filemtime($filename);
}
arsort($reports);
$newest = new DateTime('@'.array_shift($reports));
$oldest = new DateTime('@'.array_pop($reports));
printf('<input type="date" name="calSpam" min="%s" max="%s" value="%s">',
	$oldest->format('Y-m-d'), $newest->format('Y-m-d'), $newest->format('Y-m-d'));
?>
<input type="submit" name="Check Reports" value="Engage" />
</form>
<div class="right" id="content">
</div>
</body>
</html>
