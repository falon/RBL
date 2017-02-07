<?php

$theDate = isset($_REQUEST["calSpam"]) ? $_REQUEST["calSpam"] : "";
$dir = glob("*-$theDate.html"); // put all files in an array


print '<p>List of available Reports for '.date('l, d M Y', strtotime($theDate)).'</p><ul>';
foreach($dir as $file)
{
        if ( basename($file) != basename(__FILE__) ) {
		$modalDiv = 'openModal'.basename($file);
		echo '<li><a href="#'.$modalDiv.'">'.str_replace("-$theDate.html",'',basename($file)).'</a></li>';
#                echo '<li><pre><a href="'.basename($file).'" title="'.str_replace("-$theDate.html",'',basename($file))." of $theDate".'" onClick="Modalbox.show(this.href, {title: this.title, height: 600}); return false;">'.str_replace("-$theDate.html",'',basename($file)).'</a></pre></li>';
		print <<<MODAL
<div id="$modalDiv" class="overlay">
	<div class="popup">
		<a href="#close" title="Close" class="close">X</a>
MODAL;
		readfile(basename($file));
		echo	'</div></div>';
	}
}
echo '</ul>';
?>
