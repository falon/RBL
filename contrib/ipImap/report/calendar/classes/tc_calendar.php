<?php
//*********************************************************************
// The php calendar component
// written by TJ @triconsole
//
// version 3.75 (1 June 2015)
//
//*********************************************************************
$AUTHOR = "Triconsole";
$WEB_SUPPORT = "http://www.triconsole.com/php/calendar_datepicker.php";
//*********************************************************************

require_once('tc_date.php');

class tc_calendar{
	public $version = "3.75";
	public $check_new_version = false;

	private $icon;
	private $objname;
	private $txt = "Select"; //display when no calendar icon found or set up
	private $date_format = 'd-M-Y'; //format of date shown in panel if $show_input is false
	private $year_display_from_current = 30;

	private $date_picker;
	private $path = '';

	private $day = 00;
	private $month = 00;
	private $year = 0000;

	private $width = 150;
	private $height = 205;

	public $year_start;
	public $year_end;

	public $year_start_input;
	public $year_end_input;

	private $startDate = 0; //0 (for Sunday) through 6 (for Saturday)

	public $time_allow1 = false;
	public $time_allow2 = false;
	private $show_not_allow = false;

	private $auto_submit = false;
	private $form_container;
	private $target_url;

	private $show_input = true;

	public $dsb_days = array(); //collection of days to disabled

	public $zindex = 1;

	private $v_align = "bottom";
	private $h_align = "right";
	private $line_height = 18; //for vertical align offset

	private $date_pair1 = "";
	private $date_pair2 = "";
	private $date_pair_value = "";

	private $sp_dates = array(array(), array(), array()); //array[0]=no recursive, array[1]=monthly, array[0]=yearly
	private $sp_type = 0; //0=disabled specify date, 1=enabled only specify date

	private $tc_onchanged = "";
	public $rtl;

	private $show_week = false;
	public $week_hdr = "";

	private $interval = 1; //date selected interval, default 1 day

	private $auto_hide = 1;
	private $auto_hide_time = 1000;

	private $mydate;
	public $warning_msgs = array();

	//Tooltips
	private $tt_dates = array(array(), array(), array()); //array[0]=no recursive, array[1]=monthly, array[0]=yearly
	private $tt_tooltips = array(array(), array(), array()); //array[0]=no recursive, array[1]=monthly, array[0]=yearly

	//Timezone
	//Leave blank will use server settings.
	//Please refer to the supported timezones here http://php.net/manual/en/timezones.php
	private $timezone = "";
	private $timezone_offset = 0;

	private $system_timezone = "";
	private $system_timezone_offset = 0;
	private $system_timezone_h = 0;
	private $system_timezone_i = 0;
	private $system_timezone_s = 0;

	private $theme = "default";

	//calendar constructor
	function __construct($objname, $date_picker = false, $show_input = true, $timezone = ""){
		$this->objname = $objname;
		//$this->year_display_from_current = 50;
		$this->date_picker = $date_picker;

		if($timezone != "") $this->setTimezone($timezone);

		//set default year display from current year
		$thisyear = date('Y');
		$this->year_start = $thisyear-$this->year_display_from_current;
		$this->year_end = $thisyear+$this->year_display_from_current;

		$this->show_input = $show_input;

		$this->mydate = new tc_date();
	}

	//check for leapyear
	function is_leapyear($year){
    	return ($year % 4 == 0) ?
    		!($year % 100 == 0 && $year % 400 <> 0)	: false;
    }

	//get the total day of each month in year
    function total_days($month,$year){
    	$days = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		if($month > 0 && $year > 0){
	    	return ($month == 2 && $this->is_leapYear($year)) ? 29 : $days[$month-1];
		}else return 31;
    }

	//Deprecate since v1.6
	function getDayNum($day){
		$headers = $this->getDayHeaders();
		return isset($headers[$day]) ? $headers[$day] : 0;
	}

	//get the day headers start from sunday till saturday
	function getDayHeaders(){
		$rtn_hdrs = array();
		$hdrs = array("0"=>"Su", "1"=>"Mo", "2"=>"Tu", "3"=>"We", "4"=>"Th", "5"=>"Fr", "6"=>"Sa");

		$startdate = $this->startDate;

		for($i=0; $i<=6; $i++){
			if($startdate >= sizeof($hdrs)) $startdate = 0;
			//if(isset($hdrs[(string)$startdate]))
				$rtn_hdrs[] = $hdrs[(string)$startdate];

			$startdate++;
		}

		return $rtn_hdrs;
	}

	function setIcon($icon){
		$this->icon = $icon;
	}

	function setText($txt){
		$this->txt = $txt;
	}

	//-----------------------------------------------------------
	//input the date format according to php date format
	// for example: 'd F y' or 'Y-m-d'
	//-----------------------------------------------------------
	function setDateFormat($format){
		$this->date_format = $format;
	}

	//set default selected date
	function setDate($day, $month, $year){
		//get system timezone before set the date
		$this->system_timezone = date_default_timezone_get();
		$this->system_timezone_offset = date('Z');
		$this->system_timezone_h = date("H");
		$this->system_timezone_i = date("i");
		$this->system_timezone_s = date("s");
		//echo("***".date('Y-m-d H:i:s').",".date("Y-m-d H:i:s", $this->system_timezone_time));
		//echo("system tz: ".$this->system_timezone.",".$this->system_timezone_offset);

		$this->day = $day;
		$this->month = $month;
		$this->year = $year;
	}

	function setDateYMD($date){
		list($year, $month, $day) = explode("-", $date, 3);
		$this->day = $day;
		$this->month = $month;
		$this->year = $year;
	}

	//specified location of the calendar_form.php
	function setPath($path){
		$last_char = substr($path, strlen($path)-1, strlen($path));
		if($last_char != "/") $path .= "/";
		$this->path = $path;
	}

	function writeScript(){
		$this->processScript();
	}

	function getScript(){
		return $this->processScript(true);
	}

	function processScript($buffer = false){
		$str = "";

		//check valid default date
		if(!$this->checkDefaultDateValid()){
			//unset default date
			$this->setDate(00, 00, 0000);
		}

		//check date set to the timezone
		if($this->year>0 && $this->month>0 && $this->day>0){
			//date has been set
			if($this->timezone != "" && $this->system_timezone != "" && $this->timezone != $this->system_timezone){
				//echo("<br />TZ! ".$this->system_timezone);
				//echo("<br />OFFSET: ".$this->system_timezone_offset.",".$this->timezone_offset);
				//timezone has been set and different from system timezone
				$a_date = $this->year."-".$this->month."-".$this->day." ".$this->system_timezone_h.":".$this->system_timezone_i.":".$this->system_timezone_s;
				//echo(", date: $a_date");

				if ((version_compare(PHP_VERSION, '5.3.0') <= 0 && checkdate($this->month, $this->day, $this->year)) || true) {
					//get the timezone difference
					$tz_sys_ms = $this->system_timezone_offset;
					$tz_new_ms = $this->timezone_offset;

					if($tz_sys_ms>=0 && $tz_new_ms<=0){
						$timezone_diff = 0-($tz_sys_ms+abs($tz_new_ms));
					}elseif($tz_sys_ms<=0 && $tz_new_ms>=0){
						$timezone_diff = abs($tz_sys_ms)+$tz_new_ms;
					}else{
						$timezone_diff = $tz_sys_ms-$tz_new_ms;
					}
					$timezone_diff_hr = $timezone_diff/3600;
					//echo("<br />Diff: ".$timezone_diff_hr);

					$a_time = strtotime($a_date);
					//echo("a_time: $a_time");
					$a_date_y = date("Y", $a_time);
					$a_date_m = date("m", $a_time);
					$a_date_d = date("d", $a_time);
					$a_date_h = date("H", $a_time);
					$a_date_i = date("i", $a_time);
					$a_date_s = date("s", $a_time);

					$n_time = mktime(($a_date_h+$timezone_diff_hr), $a_date_i, $a_date_s, $a_date_m, $a_date_d, $a_date_y);
					//echo("<br />n_time: $n_time");
					$this->year = date("Y", $n_time);
					$this->month = date("m", $n_time);
					$this->day = date("d", $n_time);
				}else{
					$date = new DateTime($a_date, new DateTimeZone($this->system_timezone));
					$date->setTimezone(new DateTimeZone($this->timezone));
					$this->year = $date->format('Y');
					$this->month = $date->format('m');
					$this->day = $date->format('d');
				}
				//echo("<br />".$this->year."-".$this->month."-".$this->day);
			}
		}

		$str .= $this->writeHidden();

		//check whether it is a date picker
		if($this->date_picker){
			$str .= "<div style=\"position: relative; z-index: ".$this->zindex."; display: inline-block; vertical-align: top;\" id=\"container_".$this->objname."\" onmouseover=\"javascript:focusCalendar('".$this->objname."');\" onmouseout=\"javascript:unFocusCalendar('".$this->objname."', ".$this->zindex.");\">";

			if($this->show_input){
				$str .= $this->writeDay();
				$str .= $this->writeMonth();
				$str .= $this->writeYear();
			}else{
				$str .= " <a href=\"javascript:toggleCalendar('".$this->objname."', ".$this->auto_hide.", ".$this->auto_hide_time.");\" class=\"tclabel\">";
				$str .= $this->writeDateContainer();
				$str .= "</a>";
			}

			$str .= " <a href=\"javascript:toggleCalendar('".$this->objname."', ".$this->auto_hide.", ".$this->auto_hide_time.");\">";
			if(is_file($this->icon)){
				$str .= "<img src=\"".$this->icon."\" id=\"tcbtn_".$this->objname."\" name=\"tcbtn_".$this->objname."\" border=\"0\" align=\"absmiddle\" style=\"vertical-align:middle;\" alt=\"".$this->txt."\" title=\"".$this->txt."\" />";
			}else $str .= $this->txt;
			$str .= "</a>";

			$str .= $this->writeCalendarContainer();

			$str .= "</div>";
		}else{
			$str .= $this->writeCalendarContainer();
		}

		if($buffer){
			return $str;
		}else{
			echo($str);
		}
	}

	function writeCalendarContainer(){
		$params = array();
		$params[] = "objname=".$this->objname;

		$param = $this->day;
		if($param != "") $params[] = "selected_day=".$param;

		$param = $this->month;
		if($param != "") $params[] = "selected_month=".$param;

		$param = $this->year;
		if($param != "") $params[] = "selected_year=".$param;

		$param = $this->year_start_input;
		if($param != "") $params[] = "year_start=".$param;

		$param = $this->year_end_input;
		if($param != "") $params[] = "year_end=".$param;

		$param = ($this->date_picker) ? 1 : 0;
		if($param != "") $params[] = "dp=".$param;

		$param = $this->time_allow1;
		if($param != "") $params[] = "da1=".$param;

		$param = $this->time_allow2;
		if($param != "") $params[] = "da2=".$param;

		$param = $this->show_not_allow;
		if($param != "") $params[] = "sna=".$param;

		$param = $this->auto_submit;
		if($param != "") $params[] = "aut=".$param;

		$param = $this->form_container;
		if($param != "") $params[] = "frm=".$param;

		$param = $this->target_url;
		if($param != "") $params[] = "tar=".$param;

		$param = $this->show_input;
		if($param != "") $params[] = "inp=".$param;

		$param = $this->date_format;
		if($param != "") $params[] = "fmt=".$param;

		$param = implode(",", $this->dsb_days);
		if($param != "") $params[] = "dis=".$param;

		$param = $this->date_pair1;
		if($param != "") $params[] = "pr1=".$param;

		$param = $this->date_pair2;
		if($param != "") $params[] = "pr2=".$param;

		$param = $this->date_pair_value;
		if($param != "") $params[] = "prv=".$param;

		$param = $this->path;
		if($param != "") $params[] = "pth=".$param;

		$param = htmlspecialchars($this->check_json_encode($this->sp_dates), ENT_QUOTES);
		if($param != "") $params[] = "spd=".$param;

		$param = $this->sp_type;
		if($param != "") $params[] = "spt=".$param;

		$param = rawurlencode($this->tc_onchanged);
		if($param != "") $params[] = "och=".$param;

		$param = $this->startDate;
		if($param != "") $params[] = "str=".$param;

		$param = $this->rtl;
		if($param != "") $params[] = "rtl=".$param;

		$param = $this->show_week;
		if($param != "") $params[] = "wks=".$param;

		$param = $this->interval;
		if($param != "") $params[] = "int=".$param;

		$param = $this->auto_hide;
		if($param != "") $params[] = "hid=".$param;

		$param = $this->auto_hide_time;
		if($param != "") $params[] = "hdt=".$param;

		$param = $this->timezone;
		if($param != "") $params[] = "tmz=".$param;

		//$param = $this->system_timezone;
		//if($param != "") $params[] = "stz=".$param;

		$param = $this->theme;
		if($param != "") $params[] = "thm=".$param;

		$paramStr = (sizeof($params)>0) ? "?".implode("&", $params) : "";

		if($this->date_picker){
			$div_display = "hidden";
			$div_position = "absolute";

			$line_height = $this->line_height;

			if(is_file($this->icon)){
				$img_attribs = getimagesize($this->icon);
				$line_height = $img_attribs[1]+2;
			}

			$div_align = "";

			//adjust alignment
			switch($this->v_align){
				case "top":
					$div_align .= "bottom:".$line_height."px;";
					break;
				case "bottom":
				default:
					$div_align .= "top:".$line_height."px;";
			}

			switch($this->h_align){
				case "left":
					$div_align .= "left:0px;";
					break;
				case "right":
				default:
					$div_align .= "right:0px;";
			}
		}else{
			$div_display = "visible";
			$div_position = "relative";
			$div_align = "";
		}

		$mout_str = ($this->auto_hide && $this->date_picker) ? " onmouseout=\"javascript:prepareHide('".$this->objname."', ".$this->auto_hide_time.");\"" : "";

		$mover_str = " onmouseover=\"javascript:cancelHide('".$this->objname."');\"";

		$str = "";
		//write the calendar container
		$str .= "<div id=\"div_".$this->objname."\" style=\"position:".$div_position."; visibility:".$div_display."; z-index:100;".$div_align."\" class=\"div_calendar calendar-border\" ".$mout_str.$mover_str.">";
		$str .= "<IFRAME id=\"".$this->objname."_frame\" src=\"".$this->path."calendar_form.php".$paramStr."\" frameBorder=\"0\" scrolling=\"no\" allowtransparency=\"true\" width=\"100%\" height=\"100%\" style=\"z-index: 100;\"></IFRAME>";
		$str .= "</div>";

		return $str;
	}

	//write the select box of days
	function writeDay(){
		$total_days = $this->total_days($this->month, $this->year);

		$str = "";
		$str .= "<select name=\"".$this->objname."_day\" id=\"".$this->objname."_day\" onChange=\"javascript:tc_setDay('".$this->objname."', this[this.selectedIndex].value);\" class=\"tcday\"".($this->rtl ? " dir=\"rtl\"" : "").">";
		$str .= "<option value=\"00\"".($this->rtl ? " dir=\"rtl\"" : "").">Day</option>";
		for($i=1; $i<=$total_days; $i++){
			$selected = ((int)$this->day == $i) ? " selected='selected'" : "";
			$str .= "<option value=\"".str_pad($i, 2 , "0", STR_PAD_LEFT)."\"".$selected.($this->rtl ? " dir=\"rtl\"" : "").">".$i."</option>";
		}
		$str .= "</select> ";

		return $str;
	}

	//write the select box of months
	function writeMonth(){
		$str = "";
		$str .= "<select name=\"".$this->objname."_month\" id=\"".$this->objname."_month\" onChange=\"javascript:tc_setMonth('".$this->objname."', this[this.selectedIndex].value);\" class=\"tcmonth\"".($this->rtl ? " dir=\"rtl\"" : "").">";
		$str .= "<option value=\"00\"".($this->rtl ? " dir=\"rtl\"" : "").">Month</option>";

		$monthnames = $this->getMonthNames();
		for($i=1; $i<=sizeof($monthnames); $i++){
			$selected = ((int)$this->month == $i) ? " selected='selected'" : "";
			$str .= "<option value=\"".str_pad($i, 2, "0", STR_PAD_LEFT)."\"".$selected.($this->rtl ? " dir=\"rtl\"" : "").">".$monthnames[$i-1]."</option>";
		}
		$str .= "</select> ";

		return $str;
	}

	//write the year textbox
	function writeYear(){
		$str = "";
		//echo("<input type=\"textbox\" name=\"".$this->objname."_year\" id=\"".$this->objname."_year\" value=\"$this->year\" maxlength=4 size=5 onBlur=\"javascript:tc_setYear('".$this->objname."', this.value, '$this->path');\" onKeyPress=\"javascript:if(yearEnter(event)){ tc_setYear('".$this->objname."', this.value, '$this->path'); return false; }\"> ");
		$str .= "<select name=\"".$this->objname."_year\" id=\"".$this->objname."_year\" onChange=\"javascript:tc_setYear('".$this->objname."', this[this.selectedIndex].value);\" class=\"tcyear\"".($this->rtl ? " dir=\"rtl\"" : "").">";
		$str .= "<option value=\"0000\"".($this->rtl ? " dir=\"rtl\"" : "").">Year</option>";

		$year_start = $this->year_start;
		$year_end = $this->year_end;

		//check year to be selected in case of time_allow is set
		if(!$this->show_not_allow && ($this->time_allow1 || $this->time_allow2)){
			if($this->time_allow1 && $this->time_allow2){
				$year_start = $this->mydate->getDate("Y", $this->time_allow1);
				$year_end = $this->mydate->getDate("Y", $this->time_allow2);
			}elseif($this->time_allow1){
				//only date 1 specified
				$year_start = $this->mydate->getDate("Y", $this->time_allow1);
			}elseif($this->time_allow2){
				//only date 2 specified
				$year_end = $this->mydate->getDate("Y", $this->time_allow2);
			}
		}

		for($i=$year_end; $i>=$year_start; $i--){
			$selected = ((int)$this->year == $i) ? " selected='selected'" : "";
			$str .= "<option value=\"".$i."\"".$selected.($this->rtl ? " dir=\"rtl\"" : "").">".$i."</option>";
		}
		$str .= "</select> ";

		return $str;
	}

	function eHidden($suffix, $value) {
		if(trim($value) != ""){
			if($suffix) $suffix = "_".$suffix;
			return "<input type=\"hidden\" name=\"".$this->objname.$suffix."\" id=\"".$this->objname.$suffix."\" value=\"".$value."\" />";
		}
	}

	//write hidden components
	function writeHidden(){
		$str = "";

		$str .= $this->eHidden('', $this->getDate());
		$str .= $this->eHidden('dp', $this->date_picker);
		$str .= $this->eHidden('year_start', $this->year_start);
		$str .= $this->eHidden('year_end', $this->year_end);

		$str .= $this->eHidden('da1', $this->time_allow1);
		$str .= $this->eHidden('da2', $this->time_allow2);
		$str .= $this->eHidden('sna', $this->show_not_allow);
		$str .= $this->eHidden('aut', $this->auto_submit);
		$str .= $this->eHidden('frm', $this->form_container);
		$str .= $this->eHidden('tar', $this->target_url);
		$str .= $this->eHidden('inp', $this->show_input);
		$str .= $this->eHidden('fmt', $this->date_format);
		$str .= $this->eHidden('dis', implode(",", $this->dsb_days));
		$str .= $this->eHidden('pr1', $this->date_pair1);
		$str .= $this->eHidden('pr2', $this->date_pair2);
		$str .= $this->eHidden('prv', $this->date_pair_value);
		$str .= $this->eHidden('pth', $this->path);

		$str .= $this->eHidden('spd', htmlspecialchars($this->check_json_encode($this->sp_dates), ENT_QUOTES));
		$str .= $this->eHidden('spt', $this->sp_type);

		$str .= $this->eHidden('och', rawurlencode($this->tc_onchanged));
		$str .= $this->eHidden('str', $this->startDate);
		$str .= $this->eHidden('rtl', $this->rtl);
		$str .= $this->eHidden('wks', $this->show_week);
		$str .= $this->eHidden('int', $this->interval);

		$str .= $this->eHidden('hid', $this->auto_hide);
		$str .= $this->eHidden('hdt', $this->auto_hide_time);

		//Tooltips
		$str .= $this->eHidden('ttd', htmlspecialchars($this->check_json_encode($this->tt_dates), ENT_QUOTES));
		$str .= $this->eHidden('ttt', htmlspecialchars($this->check_json_encode($this->tt_tooltips), ENT_QUOTES));

		$str .= $this->eHidden('tmz', $this->timezone);
		//$str .= $this->eHidden('stz', $this->system_timezone);
		$str .= $this->eHidden('thm', $this->theme);

		return $str;
	}

	//set width of calendar
	//---------------------------
	// Deprecated since version 2.9
	// Auto sizing is applied
	//---------------------------
	function setWidth($width){
		if($width) $this->width = $width;
	}

	//set height of calendar
	//---------------------------
	// Deprecated since version 2.9
	// Auto sizing is applied
	//---------------------------
	function setHeight($height){
		if($height) $this->height = $height;
	}

	function setYearInterval($start, $end){
		$this->year_start_input = $start;
		$this->year_end_input = $end;

		if(!$start) $start = $this->year_start;
		if(!$end) $end = $this->year_end;

		if($start < $end){
			$this->year_start = $start;
			$this->year_end = $end;
		}else{
			$this->year_start = $end;
			$this->year_end = $start;
		}
	}

	function getMonthNames(){
		return array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	}

	//-------------------------------
	// Deprecated since version 3.61
	// override by startDate()
	//-------------------------------
	function startMonday($flag){
		//$this->startMonday = $flag;

		//change it so that it will not cause an error after version 3.61
		if($flag) $this->startDate = 1;
	}

	function startDate($num){
		if(is_numeric($num) && $num >= 0 && $num <= 6)
			$this->startDate = $num;
	}

	function dateAllow($from = "", $to = "", $show_not_allow = true){
		$time_from = $this->mydate->validDate($from) ? $from : null;
		$time_to = $this->mydate->validDate($to) ? $to : null;

		// sanity check, ensure time_from earlier than time_to
		if($time_from != null && $time_to != null && $this->mydate->dateAfter($time_to, $time_from, true)){
			$tmp = $time_from;
			$time_from = $time_to;
			$time_to = $tmp;
		}

		if ($time_from != null) {
			$this->time_allow1 = $time_from;
			$y = $this->mydate->getDate('Y', $time_from);
			if($this->year_start && $y > $this->year_start) $this->year_start = $y;

			//setup year end from year start
			if($time_to == null && !$this->year_end) $this->year_end = $this->year_start + $this->year_display_from_current;
		}

		if ($time_to>0) {
			$this->time_allow2 = $time_to;
			$y = $this->mydate->getDate('Y', $time_to);
			if($this->year_end && $y < $this->year_end) $this->year_end = $y;

			//setup year start from year end
			if($time_from == null && !$this->year_start) $this->year_start = $this->year_end - $this->year_display_from_current;
		}

		$this->show_not_allow = $show_not_allow;
	}

	function autoSubmit($auto, $form_name, $target = ""){
		$this->auto_submit = $auto;
		$this->form_container = $form_name;
		$this->target_url = $target;
	}

	function getDate(){
		return str_pad($this->year, 4, "0", STR_PAD_LEFT)."-".str_pad($this->month, 2, "0", STR_PAD_LEFT)."-".str_pad($this->day, 2, "0", STR_PAD_LEFT);
	}

	function showInput($flag){
		$this->show_input = $flag;
	}

	function writeDateContainer(){
		if($this->day && $this->month && $this->year){
			$dd = $this->mydate->getDate($this->date_format, $this->year."-".$this->month."-".$this->day);
		}else $dd = "Select Date";

		return "<div id=\"divCalendar_".$this->objname."_lbl\" class=\"date-tccontainer\">$dd</div>";
	}

	//------------------------------------------------------
	// This function disable day column as specified value
	// day values : Sun, Mon, Tue, Wed, Thu, Fri, Sat
	//------------------------------------------------------
	function disabledDay($day){
		$day = strtolower($day); //make it not case-sensitive
		if(in_array($day, $this->dsb_days) === false)
			$this->dsb_days[] = $day;
	}

	function setAlignment($h_align, $v_align){
		$this->h_align = $h_align;
		$this->v_align = $v_align;
	}

	function setDatePair($calendar_name1, $calendar_name2, $pair_value = "0000-00-00 00:00:00"){
		if($calendar_name1 != $this->objname){
			$this->date_pair1 = $calendar_name1;
			if($pair_value != "0000-00-00 00:00:00")
				$this->date_pair_value = $pair_value;
		}elseif($calendar_name2 != $this->objname){
			$this->date_pair2 = $calendar_name2;
			if($pair_value != "0000-00-00 00:00:00")
				$this->date_pair_value = $pair_value;
		}
	}

	function setSpecificDate($dates, $type=0, $recursive=""){
		if(is_array($dates)){
			$recursive = strtolower($recursive);

			//change specific date to time
			foreach($dates as $sp_date){
				if($this->mydate->validDate($sp_date)){
					switch($recursive){
						case "month": //add to monthly
							if(!in_array($sp_date, $this->sp_dates[1]))
								$this->sp_dates[1][] = $sp_date;
							break;
						case "year": //add to yearly
							if(!in_array($sp_date, $this->sp_dates[2]))
								$this->sp_dates[2][] = $sp_date;
							break;
						default: //add to no recursive
							if(!in_array($sp_date, $this->sp_dates[0]))
								$this->sp_dates[0][] = $sp_date;
					}
				}
			}

			$this->sp_type = ($type == 1) ? 1 : 0; //control data type for $type
		}
	}
	/*
	//old method use timestamp
	function setSpecificDate($dates, $type=0, $recursive=""){
		if(is_array($dates)){
			$recursive = strtolower($recursive);

			//change specific date to time
			foreach($dates as $sp_date){
				$sp_time = $this->mydate->getTimestamp($sp_date);

				if($sp_time > 0){
					switch($recursive){
						case "month": //add to monthly
							if(!in_array($sp_time, $this->sp_dates[1]))
								$this->sp_dates[1][] = $sp_time;
							break;
						case "year": //add to yearly
							if(!in_array($sp_time, $this->sp_dates[2]))
								$this->sp_dates[2][] = $sp_time;
							break;
						default: //add to no recursive
							if(!in_array($sp_time, $this->sp_dates[0]))
								$this->sp_dates[0][] = $sp_time;
					}
				}
			}

			$this->sp_type = ($type == 1) ? 1 : 0; //control data type for $type
		}
	}
	*/

	function checkDefaultDateValid($reset = true){
		$date_str = $this->year."-".str_pad($this->month, 2, "0", STR_PAD_LEFT)."-".str_pad($this->day, 2, "0", STR_PAD_LEFT);
		//$default_datetime = $this->mydate->getTimestamp($date_str);
/*
		//reset year if set to 2038 and later
		if(!$this->mydate->compatible && $this->year >= 2038){
			return false;
		}
*/
		//check if set date is in year interval
		$start_interval = $this->year_start."-01-01";
		$end_interval = $this->year_end."-12-31";

		//check if set date is before start_interval
		if($this->mydate->dateBefore($start_interval, $date_str)){
			return false;
		}

		//check if set date is after end_interval
		if($this->mydate->dateAfter($end_interval, $date_str)){
			return false;
		}

		//check with allow date
		if($this->time_allow1 && $this->time_allow2){
			if($this->mydate->dateBefore($this->time_allow1, $date_str, false) || $this->mydate->dateAfter($this->time_allow2, $date_str, false)){
				return false;
			}
		}elseif($this->time_allow1){
			if($this->mydate->dateBefore($this->time_allow1, $date_str, false)) return false;
		}elseif($this->time_allow2){
			if($this->mydate->dateAfter($this->time_allow2, $date_str, false)) return false;
		}

		//check with specific date
		if(is_array($this->sp_dates) && sizeof($this->sp_dates) > 0){
			//check if it is current date
			$sp_found = false;

			if(isset($this->sp_dates[2])){
				foreach($this->sp_dates[2] as $sp_time){
					$this_md = $this->mydate->getDate("md", $date_str);
					$sp_time_md = $this->mydate->getDate("md", $sp_time);
					if($sp_time_md == $this_md){
						$sp_found = true;
						break;
					}
				}
			}

			if(isset($this->sp_dates[1]) && !$sp_found){
				foreach($this->sp_dates[1] as $sp_time){
					$sp_time_d = $this->mydate->getDate("d", $sp_time);
					if($sp_time_d == $this->day){
						$sp_found = true;
						break;
					}
				}
			}

			if(isset($this->sp_dates[0]) && !$sp_found){
				$sp_found = in_array($date_str, $this->sp_dates[0]);
			}

			switch($this->sp_type){
				case 0:
				default:
					//disabled specific and enabled others
					if($sp_found) return false;
					break;
				case 1:
					//enabled specific and disabled others
					if(!$sp_found) return false;
					break;
			}
		}

		if(is_array($this->dsb_days) && sizeof($this->dsb_days) > 0){
			$day_txt = $this->mydate->getDate("D", $date_str);
			if(in_array(strtolower($day_txt), $this->dsb_days) !== false){
				return false;
			}
		}

		return true;
	}

	function check_json_encode($obj){
		//try customize to get it work, should replace with better solution in the future
		if(is_array($obj)){
			if(function_exists("json_encode") && false){
				return json_encode($obj);
			}else{
				//only array is assumed for now
				$return_arr = array();
				foreach($obj as $arr){
					if(is_array($arr) && sizeof($arr)>0)
						$return_arr[] = "[\"".implode("\",\"", $arr)."\"]";
					else $return_arr[] = "[]";
				}
				return "[".implode(",", $return_arr)."]";
			}
		}else return "";
	}

	function &check_json_decode($str){
		//should replace with better solution in the future

		if(function_exists("json_decode") && false){
			return json_decode($str);
		}else{
			//only array is assume for now
			$str = stripslashes(rawurldecode($str));
			$str = trim($str);

			if($str && strlen($str) > 2){
				$str = substr($str, 1, strlen($str)-2);

				if($str && strlen($str) > 2){
					$str = substr($str, 1, strlen($str)-2);

					$return_arr = array();

					$offset = 0;

					$arr = explode("],[", $str);
					for($i=0; $i<sizeof($arr); $i++){
						$this_v = $arr[$i];
						if($this_v == "")
							$return_arr[] = array();
						else{
							$this_arr = explode(",", $this_v);

							for($j=0; $j<sizeof($this_arr); $j++){
								if(substr($this_arr[$j], 0, 1)=="\"" && substr($this_arr[$j], strlen($this_arr[$j])-1, 1)=="\""){
									$this_arr[$j] = substr($this_arr[$j], 1, strlen($this_arr[$j])-2);
								}
							}

							$return_arr[] = $this_arr;
						}
					}
					return $return_arr;
				}else return array();
			}else return array();
		}
	}

	function setOnChange($value){
		$this->tc_onchanged = $value;
	}

	function showWeeks($flag){
		$this->show_week = $flag;
	}

	function setAutoHide($auto, $time = ""){
		$this->auto_hide = ($auto) ? 1 : 0;
		if($time != "" && $time >= 0){
			$this->auto_hide_time = $time;
		}
	}

	//*****************
	// Validate the today date of calendar
	//*****************
	function validTodayDate(){
		$today = $this->mydate->getDate();

		//check if today is year 2038 and later
		if(!$this->mydate->compatible && $this->mydate->getDate("Y") >= 2038){
			return false;
		}

		//check if today is in range of date allow
		if($this->time_allow1 != ""){
			//check valid if today is after date_allow1
			if($this->mydate->validDate($this->time_allow1) && !$this->mydate->dateAfter($this->time_allow1, $today))
				return false;
		}

		if($this->time_allow2 > 0){
			//check valid if today is before date_allow2
			if($this->mydate->validDate($this->time_allow2) && !$this->mydate->dateBefore($this->time_allow2, $today))
				return false;
		}
		return true;
	}

	//Tooltips
	function setToolTips($dates, $tooltip="", $recursive=""){

		if(is_array($dates)){
			$recursive = strtolower($recursive);

			//change specific date to time
			foreach($dates as $tt_date){
				$tt_time = $tt_date;
//				if($tt_time > 0){
				switch($recursive){
					case "year": //add to yearly
						if(!in_array($tt_time, $this->tt_dates[2])){
							$this->tt_dates[2][] = $tt_time;
							$this->tt_tooltips[2][] = $tooltip;
						}
						else{
							$tt_key = array_search($tt_time, $this->tt_dates[2]);
							$this->tt_tooltips[2][$tt_key] = $this->tt_tooltips[2][$tt_key]."\n".$tooltip;
						}
						break;
					case "month": //add to monthly
						if(!in_array($tt_time, $this->tt_dates[1])){
							$this->tt_dates[1][] = $tt_time;
							$this->tt_tooltips[1][] = $tooltip;
						}
						else{
							$tt_key = array_search($tt_time, $this->tt_dates[1]);
							$this->tt_tooltips[1][$tt_key] = $this->tt_tooltips[1][$tt_key]."\n".$tooltip;
						}
						break;
					default: //add to no recursive
						if(!in_array($tt_time, $this->tt_dates[0])){
							$this->tt_dates[0][] = $tt_time;
							$this->tt_tooltips[0][] = $tooltip;
						}
						else{
							$tt_key = array_search($tt_time, $this->tt_dates[0]);
							$this->tt_tooltips[0][$tt_key] = $this->tt_tooltips[0][$tt_key]."\n".$tooltip;
						}
				}
//				}
			}
		}
	}

	function setTimezone($tz){
		$this->timezone = $tz;
		@date_default_timezone_set($tz);
		$this->timezone_offset = date('Z');
		//echo("new timezone: ".$this->timezone);
	}

	function setTheme($theme){
		$this->theme = $theme;
	}

	function getThemes(){
		$themes = array();
		$themesDirectory = dir('./css/');
		while($thname = $themesDirectory->read())
		{
			if(is_dir('./css/'.$thname) && file_exists('./css/'.$thname.'/calendar.css') && !preg_match("/^[\.]/", $thname))
			{
				$themes[$thname] = "./css/".$thname."/calendar.css";
			};
		};
		natsort($themes);
		$themesDirectory->close();
		return $themes;
	}

	function getThemePath($theme){
		$all_themes = $this->getThemes();
		return isset($all_themes[$theme]) ? $all_themes[$theme] : "";
	}
}
?>
