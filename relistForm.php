<?php
        $typedesc=$_POST["typedesc"];
        $type = $tables["$typedesc"]['field'];
        $table = $tables["$typedesc"]['name'];
	$adm = unserialize($_POST["adm"]);
	if (in_array($_POST["user"],array_keys(array_filter($adm)))) $extopt = '<option value="MONTH">MONTHS</option><option value="YEAR">YEARS</option>';
	else $extopt = NULL;
?>


<td  colspan="9">
<form style="margin:0; text-align: right;" name='RelistButton<?php echo $_POST['type']; ?>' enctype="text/plain" method="post" target="_self" action="relist.php"  onSubmit="xmlhttpPost('relist.php', 'RelistButton<?php echo $_POST['type']; ?>', 'id<?php echo $_POST['ID']; ?>', '<img src=\'/include/pleasewait.gif\'>'); return false;" />

		Relist <?php  echo $_POST['type'].' '.$_POST['value']; ?> for <input class="input_text" name="type" type="hidden" value="<?php echo $_POST['type']; ?>" /><input class="input_text" name="value" type="hidden" value="<?php echo $_POST['value']; ?>" />
		<select class="input_text" name="quantity" size="1"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option></select><select class="input_text" name="unit" size="1"><option value="DAY">DAYS</option><option value="WEEK">WEEKS</option><?php echo $extopt; ?></select>  Reason:<input maxlength="128" name="reason" size="30" type="text" class="input_text" placeholder="Specify a reason" required title="Please, specify a reason!" />&nbsp;<input name="Relist" type="submit" value="Relist" class="button" id="bwarn" /></form></td>
