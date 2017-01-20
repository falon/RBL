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
<script language="javascript" src="calendar/calendar.js"></script>
<link href="calendar/calendar.css" rel="stylesheet" type="text/css" />
</head>
<body>
<h1>Learn Tool Status</h1>
<form name="formSpam" id="formSpam" onSubmit="xmlhttpPost('result.php', 'formSpam', 'Risultato', '<img src=\'/include/pleasewait.gif\'>'); return false;" enctype="text/plain" method="post" target="_self" class="left">

<?php
$reports = array();
foreach (glob("*.{htm,html}", GLOB_BRACE) as $filename) {
    $reports[$filename] = filemtime($filename);
}
arsort($reports);
$newest = array_shift($reports);
$oldest = array_pop($reports);

require_once('calendar/classes/tc_calendar.php');
/* Write Calendar */
	  $myCalendar = new tc_calendar("calSpam");
	  $myCalendar->setIcon("calendar/images/iconCalendar.gif");
	  $myCalendar->setDate(date('d',$newest), date('m',$newest), date('Y',$newest));
	  $myCalendar->setPath("calendar/");
	  $myCalendar->setYearInterval(date('Y',$oldest), date('Y', $newest));
	  $myCalendar->dateAllow(date('Y-m-d', $oldest), date('Y-m-d', $newest));
	  $myCalendar->setDateFormat('j F Y');
	  $myCalendar->startDate(1);
	  $myCalendar->setAlignment('left', 'bottom');
#	  $myCalendar->setSpecificDate(array("2011-06-01"), 0, '');
#	  $myCalendar->autoSubmit(true, 'formSpam', 'result.php'); 
	  $myCalendar->setTheme('theme1');
	  $myCalendar->writeScript();
/*********************************/
?>
<input type="submit" name="Check Reports" value="Submit" />
</form>
<div class="left" id="Risultato">
</div>
</body>
</html>
