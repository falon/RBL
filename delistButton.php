<?php
$form = <<<END
<form style="margin:0" name='DelistButton$id' enctype="text/plain" method="post" target="_self" action="delist.php" accept-charset="utf-8" onSubmit="xmlhttpPost('delist.php', 'DelistButton$id', 'id$id', '<img src=\'/include/pleasewait.gif\'>'); return false;" />
END;

$button = <<<END
$form<input name="type" type="hidden" value="$typedesc" /><input name="value" type="hidden" value="$value" /><input name="ID" type="hidden" value="$id" /><input class="button" id="bwarn" name="Delist" type="submit" value="Delist"  /></form>
END;

return $button;
?>
