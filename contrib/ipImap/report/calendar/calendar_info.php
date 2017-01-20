<?php
require_once("classes/tc_calendar.php");
require_once("classes/tc_date.php");

if(!isset($show_calendar_info)) $show_calendar_info = true;
if(!isset($show_fb_info)) $show_fb_info = true;
if(!isset($show_servertime_info)) $show_servertime_info = true;

$tobj = new tc_calendar("");
$version = $tobj->version;
$check_version = $tobj->check_new_version;

$tdate = new tc_date();

if(!isset($timezone)) $timezone = date_default_timezone_get();

$wan_enabled = 0;
$new_version = 0;
if($wan_enabled = @fsockopen("www.google.com", 80, $errno, $errstr, 1)){
	if($check_version && $wan_enabled){
		if(function_exists("file_get_contents")){
			$new_version = @file_get_contents("http://www.triconsole.com/php/tc_calendar_version.php?v=".$version);
		}
	}
}
elseif(function_exists("file_get_contents")){
	$ctx = stream_context_create(array('http' => array('timeout' => 1)));
	$wan_enabled = @file_get_contents("http://www.google.com",null,$ctx,0,1);
	if($check_version && $wan_enabled){
		if(function_exists("file_get_contents")){
#			$new_version = @file_get_contents("http://www.triconsole.com/php/tc_calendar_version.php?v=".$version);
		}
	}
}

define("L_ABOUT", "<b>PHP Datepicker Calendar</b><br />Version: <b>".strval($version)."</b>".($new_version ? "<br /><b><font color=\"red\">Update available <a href=\"$WEB_SUPPORT\" target=\"_blank\">here</a> !</font></b>" : "").($wan_enabled ? ($show_fb_info ? "<br /><div class=\"fb-like\" data-href=\"https://www.facebook.com/DatePicker\" data-send=\"false\" data-layout=\"button_count\" data-show-faces=\"false\" data-font=\"tahoma\" ref=\"std_about_info\"></div>" : "") : "")."<br />&copy;2006-".$tdate->getDate("Y")." <b><a href=\"$WEB_SUPPORT\" target=\"_blank\" title=\"http://triconsole.com\">$AUTHOR</a></b>".($show_servertime_info ? "<br />Server Timezone:".($timezone ? "<br />$timezone" : "")."<br /><span id=\"timecontainer\">".$tdate->getDate("Y-m-d H:i:s")."</span>" : ""));

?>
<?php if($wan_enabled && $show_fb_info){ ?>
<link href="calendar_info.css" rel="stylesheet" type="text/css" />
    <div id="fb-root"></div>
    <script>
        window.fbAsyncInit = function(){
            FB.init({
                appId: '674148172599839',
                xfbml: true
            });
        };
        (function() {
            var e = document.createElement('script'); e.async = true;
            e.src = document.location.protocol +
                '//connect.facebook.net/en_US/all.js';
            document.getElementById('fb-root').appendChild(e);
        }());
    </script>
<?php } ?>
<div style="float: left;" id="info">
    <img src="images/<?php echo($new_version ? "version_info.gif" : "about.png"); ?>" width="9" height="9" border="0" id="info_icon" />
    <div id="about"><?php echo(L_ABOUT); ?></div>
    <script type="text/javascript" src="calendar_servertime.js"></script>
    <script type="text/javascript">
       new showLocalTime("timecontainer", "server-php", 0, "long")
    </script>
    <script type="text/javascript">
    <!--
    var timeoutID = new Array();

    var obj = document.getElementById("info_icon");
    obj.onmouseover = function(){ displayAbout(); }
    obj.onmouseout = function(){ hideAbout(); }

    var obj = document.getElementById("about");
    obj.onmouseover = function(){ displayAbout(true); }
    obj.onmouseout = function(){ hideAbout(); }

    function displayAbout(flag){
        var obj = document.getElementById("about");

        var this_height = obj.style.height;

        if(typeof(flag) == "undefined" || (flag === true && (this_height != "1px" && this_height != ""))){
            cancelTimer();

            //obj.style.display = "block";
            obj.style.height = "auto";
            obj.style.border = "1px solid #191970";
            obj.style.backgroundColor = "#F8F8FF";
        }
    }
    function hideAbout(){
        var obj = document.getElementById("about");

        this.timeoutID[this.timeoutID.length] = window.setTimeout(function(){
            obj.style.border = "none";
            //obj.style.display = "none";
            obj.style.height = "1px";
            obj.style.backgroundColor = "";
            }
            , 500);
    }
    function cancelTimer(){
        for(i=0; i<this.timeoutID.length; i++){
            var timers = this.timeoutID[i];
            clearTimeout(timers);
        }
        this.timeoutID = new Array();
    }
    //-->
    </script>
</div>
