<?php

require ('config.php');

$_ = htmlentities(serialize($adm));
$button = <<<END
<form style="margin:0; display:inline;" name="RelistButton$id" enctype="text/plain" method="post" target="_self" action="relistForm.php" onSubmit="xmlhttpPost('relistForm.php', 'RelistButton$id', 'id$id', '<img src=\'/include/pleasewait.gif\'>'); return false;" />
END;

$button .= <<<END
<input name="type" type="hidden" value="$typedesc" /><input name="value" type="hidden" value="$value" /><input name="user" type="hidden" value="$user"><input name="adm" type="hidden" value="$_"><input name="ID" type="hidden" value="$id" /><input class="button" name="Relist" type="submit" value="Relist" /></form>
END;

if ($tables["$typedesc"]['field'] == 'network') {
	$button .= <<<END
<form style="margin:0; display:inline;" name='RemoveButton$id' enctype="text/plain" method="post" target="_self" action="remove.php" onSubmit="xmlhttpPost('remove.php', 'RemoveButton$id', 'id$id', '<img src=\'/include/pleasewait.gif\'>'); return false;" /><input name="type" type="hidden" value="$typedesc" /><input name="value" type="hidden" value="$value" /><input name="ID" type="hidden" value="$id" /><input class="button" id="bwarn" name="Remove" type="submit" value="Remove" /></form>
END;
}


return $button;
?>
