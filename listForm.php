<?php
if (in_array($user,array_keys(array_filter($adm)))) $extopt = '<option value="MONTH">MONTHS</option><option value="YEAR">YEARS</option>';
else $extopt = NULL;

if ( $tables["$typedesc"]['milter'] ) {
	if ( checkMilterConf($tables["$typedesc"]) ) 
		print '<p>This is a milter table. Please, first add your item as usual. Then you will be able to customize the milter settings.</p>';
	else {
		$err = "Error in config about <$typedesc>. Check at your config.php.";
		syslog (LOG_EMERG, $err);
		printf ('<p>%s</p>', htmlspecialchars($err) );
	}	
}

?>
<form style="margin:0; text-align: left;" accept-charset="utf-8" name='ListButton<?php echo $type ?>' enctype="text/plain" method="post" target="_self" action="list.php"  onSubmit="xmlhttpPost('list.php', 'ListButton<?php echo $type ?>', 'Risultato', '<img src=\'/include/pleasewait.gif\'>'); return false;" />

		List <?php  echo $typedesc.' &lt;'.htmlentities($value).'&gt;'; ?> for <input name="type" type="hidden" value="<?php echo $typedesc; ?>" /><input name="value" type="hidden" class="input_text" value="<?php echo $value; ?>" />
		<select name="quantity" class="input_text" size="1"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="20">20</option></select><select class="input_text" name="unit" size="1"><option value="DAY">DAYS</option><option value="WEEK">WEEKS</option><?php echo $extopt;?></select>  Reason:<input maxlength="128" name="reason" size="30" type="text" class="input_text" /><input name="List" class="button" id="bwarn" type="submit" value="List"/></form>
