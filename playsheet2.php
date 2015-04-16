<?php		///	 playsheet.php - playlist.citr.ca

session_start();
require("headers/showlib.php");
require("headers/security_header.php");
require("headers/function_header.php");
require("headers/menu_header.php");
require("headers/socan_header.php");
$SOCAN_FLAG;
$showlib = new Showlib($db);

echo "<center>";
print_menu2();
echo "</center>";
$SOCAN_FLAG=socanCheck($db);

if (socanCheck($db) || $_GET['socan']=='true' ){


	$SOCAN_FLAG = true;
} else {
	$SOCAN_FLAG = false;
}

if($SOCAN_FLAG)
{
print ('<input type="hidden" id="socancheck" value="1">');
}
else
{
print ('<input type="hidden" id="socancheck" value="0">');
}


$newPlaysheet = false;
if (!isset($_POST['id'])) $newPlaysheet = true;
if (isset($_POST['id']) && $_POST['id']==0)
	$newPlaysheet = true;

$actionSet = isset($_GET['action']);
$action = $_GET['action'];	
// invisible form element that javascript updates

if(isset($_POST['numberOfRows'])){
	$playlist_entries = $_POST["numberOfRows"];
} else $playlist_entries = 5;

?>
<script type="text/javascript">
var socan=<?php echo json_encode($SOCAN_FLAG); ?>;
</script>

<html>
<head>
<meta name=ROBOTS content=\"NOINDEX, NOFOLLOW\">
<meta charset="utf-8">
<link rel=stylesheet href='css/style.css' type='text/css'>

<title>DJLAND | Playsheet</title>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="js/jquery.form.js"></script> 

<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" />
  <script src="http://code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
  
  <script>
  $(function() {
    $( ".datepicker" ).datepicker({ dateFormat: "yy-mm-dd" });
  });
  </script>

	<link rel="stylesheet" href='js/bootstrap/bootstrap.min.css'></script>


	<style type='text/css'>

	body {
		background-color: white;
		padding-left: 12px;
		color:white;
	}

	button{
		color:black;
		}
	.big{
		font-size:2em;
		cursor:default;
	}

	.big:hover{
		background-color:grey;

	}
	.nav, .pagination, .carousel, .panel-title a { cursor: pointer; }

	.podcast_date{
		width:10px;
	}
	.date_pick{

	}

	.play_episode{
		color:black;
		//	background-color:white;

	}
	.play_episode:hover{
		color:blue;
	}

	.timepicker{
		border-width:1px;
		border-style: solid;

		display:inline-block;
		padding:7px;

	}

	.summary{
		width:80%;
		min-height:200px;
		margin-top:5px;
		margin-bottom:5px;
		overflow: auto;
	}

	.subtitle{
		font-weight:bold;
	}
	.episode{
		width:800px;
		padding-left:35px;
	}

	.title{  margin-left:-15px;
	}
	input,textarea{
		border-style:solid;
		border-color:white;
		width:100%;
	}
	.editing{
		border-color:lightskyblue;
		border-width:1px;
		border-style:solid;
		background-color:#eef6ff;
	}

	.title-label{
		position:relative;
		bottom:-18px;
	}

	.previews{
		display:inline-block;
	}

	</style>

	</head>
<?php

if(oldIE())
      echo " <body class='ie'> ";
else
     echo "<body>";

//
//
//
//
//
//
//
//          Task:   Saving a playsheet - whethere it's a new one or editing an existing one
//
//
//
//
//
//
//
if( (is_member("dj") || (is_member("editdj") && $newPlaysheet ) ) && $actionSet && $action == "submit") {

//	//print_r($_POST);

//	$show_id = fget_id($_POST['showtitle'], "shows", false);
	$show_id = $_POST['showtitle'];
	$host_id = htmlentities(fget_id($_POST['host'], "hosts", true));
	$create_name = get_username();
	$create_date = date('Y-m-d H:i:s');
	$edit_name = get_username();
	$show_date = fas($_POST['pl_date_year'] . "-" . $_POST['pl_date_month'] . "-" . $_POST['pl_date_day']);
	$start_time = fas($_POST['pl_date_year'] . "-" . $_POST['pl_date_month'] . "-" . $_POST['pl_date_day'] . " " . $_POST['pl_date_hour'] . ":" . $_POST['pl_date_min'] . ":" . "00");
	$end_time = fas($_POST['end_date_hour'] . ":" . $_POST['end_date_min'] . ":" . "00");
	$spokenword = htmlentities($_POST['spokenword']);
	$spokenword_h = $_POST['sw-time-hr'];
	$spokenword_m = $_POST['sw-time-min'];
	$unix_time = $_POST['unixTime'];
	$status = $_POST['status'];
	$star = $_POST['star'];
	$pl_crtc = $_POST['pl_crtc'];
	$pl_lang = $_POST['pl_lang'];
	$type = $_POST['type'];
	
	
	
	$spokenword_duration = 60*$spokenword_h + $spokenword_m;

	if($newPlaysheet) { // submitting a new playsheet		
		$ps_query = "INSERT INTO `playlists` (id, create_date, create_name) VALUES (NULL, '$create_date', '$create_name')";
		if (mysqli_query($db,$ps_query))
			$ps_id = mysqli_insert_id($db);
		else
			echo "create playsheet unsuccessful :(<br/>";
	}
	else {	// submitting a previously loaded playsheet (editing)
	
		//Delete all play items and logged ads
		$ps_id = $_POST['id'];
		mysqli_query($db,"DELETE FROM `playitems` WHERE playsheet_id='$ps_id'");
	//	mysqli_query($db,"DELETE FROM adlog WHERE playsheet_id='$ps_id'");

	}

	if(!$unix_time){
		$unix_time = 'NULL';
	}
	$error_occurred = 'false';
	$query = "UPDATE `shows` SET last_show='$start_time' WHERE id='$show_id' AND last_show < '$start_time'";
	$result = $db->query($query);
	
	if($result){
		$update_show_query = "UPDATE `playlists` SET show_id='$show_id', host_id='$host_id', edit_name='$edit_name', start_time='$start_time', end_time='$end_time', spokenword='$spokenword', spokenword_duration='$spokenword_duration', unix_time=".$unix_time.", status='$status', star='$star', crtc='$pl_crtc', lang='$pl_lang', type='$type' WHERE id='$ps_id'";
		$result2 = $db->query($update_show_query);
		if($result2){
			echo "<h3>thanks for submitting a playsheet!  Here is the music you played:</h3>";
		}else{
			$error[0] = mysqli_error($db);
			echo mysqli_error($db);
			$error[1] = $update_show_query;
			$error_occurred = 'true';
		}
	}else{
		$error[0] = mysqli_error($db);
		$error[1] = $update_show_query;
		$error_occurred='true';
	}
	if(error_occurred=='true'){
		$log_me = "<hr/> Error Logged at: ".$today."<br> Occured on page: ".$_SERVER['HTTP_REFERER']." <br/> Error: ".$error[0]."<br/>Query: ".$error[1]."<br/>Data: ".json_encode($_POST,true);
		$log_file = 'logs/log.html';
		if(file_put_contents ( 'logs/log.html' , $log_me, FILE_APPEND)){
			echo "<br/>The Error was Logged Sucessfully";
		}else{
			echo "<br/>The error could not be logged";
		}
	}

	
	if($SOCAN_FLAG)	{echo "<div class=playsheetSOCAN>";}
	else {echo "<div class=playsheetSOCAN>";}
	
	// NEED TO KNOW HOW MANY ROWS SOMEHOW!



if(!isset($show_id)){
	$show_id = 0;
}

	for($i=0; $i < $playlist_entries; $i++) {
		//		mysqli_query($db, "INSERT INTO `playitems` (playsheet_id, show_id, song_id, format_id, is_playlist, is_canadian, is_yourown, is_indy, is_fem, show_date) VALUES ('$ps_id', '$show_id', '".fget_song_id($_POST['artist'.$i], $_POST['title'.$i], $_POST['song'.$i])."', '".$fformat_id[$_POST['format'.$i]]."', '".(isset($_POST['pl'.$i])?1:0)."', '".(isset($_POST['cc'.$i])?1:0)."', '".(isset($_POST['yo'.$i])?1:0)."', '".(isset($_POST['indy'.$i])?1:0)."', '".(isset($_POST['fem'.$i])?1:0)."', '$show_date')");
		/*abcd*/		$cat = 12;	
		if($SOCAN_FLAG)
		{
		//$insert_song_start_day = $_POST['set_song_start_day'.$i];
		$insert_song_start_hour = $_POST['set_song_start_hour'.$i];
		$insert_song_start_minute = $_POST['set_song_start_minute'.$i];
		//$insert_song_start_second = $_POST['set_song_start_second'.$i];
		$insert_song_length_minute = $_POST['set_song_length_minute'.$i];
		$insert_song_length_second = $_POST['set_song_length_second'.$i];
		$insert_background = isset($_POST['background'.$i])?1:0;
		$insert_theme = isset($_POST['theme'.$i])?1:0;
		}

		$insert_artist = $_POST['artist'.$i];
		$insert_album = $_POST['album'.$i];
		$insert_song = $_POST['song'.$i];
		$insert_songID = fget_song_id($insert_artist,$insert_album,$insert_song);
		$insert_pl = isset($_POST['pl'.$i])?1:0;
		$insert_cc = isset($_POST['cc'.$i])?1:0;
		$insert_fem = isset($_POST['fem'.$i])?1:0;
		$insert_crtc = $_POST['crtc'.$i];
		$insert_lang = addslashes($_POST['lang'.$i]);
		$insert_part = isset($_POST['part'.$i])?1:0;
		$insert_inst = isset($_POST['inst'.$i])?1:0;
		$insert_hit = isset($_POST['hit'.$i])?1:0;

		if($SOCAN_FLAG)
		{
		$insert_composer = $_POST['composer'.$i];
		$update_query = "UPDATE songs SET composer = '$insert_composer' WHERE id='$insert_songID'";
		if(mysqli_query($db, $update_query))
		{
		} else 
		echo 'update composer unsuccessful<br/>';
		}

		if( isset($_POST['artist'.$i]) && isset($_POST['album'.$i]) && isset( $_POST['song'.$i])){
			if($SOCAN_FLAG){
			$insert_query = "INSERT INTO `playitems` ".
							"(playsheet_id, show_id, song_id, is_playlist, is_canadian, is_fem, show_date, crtc_category, lang, is_part, is_inst, is_hit, is_background, is_theme, insert_song_start_hour, insert_song_start_minute,  insert_song_length_minute, insert_song_length_second)".
					"VALUES ('$ps_id', '$show_id', '$insert_songID', '$insert_pl', '$insert_cc', '$insert_fem','$show_date', '$insert_crtc', '$insert_lang', '$insert_part', '$insert_inst', '$insert_hit', '$insert_background','$insert_theme','$insert_song_start_hour',  '$insert_song_start_minute', '$insert_song_length_minute',  '$insert_song_length_second')";
			}
			else
			{
			$insert_query = "INSERT INTO `playitems` ".
							"(playsheet_id, show_id, song_id, is_playlist, is_canadian, is_fem, show_date, crtc_category, lang, is_part, is_inst, is_hit)".
					"VALUES ('$ps_id', '$show_id', '$insert_songID', '$insert_pl', '$insert_cc', '$insert_fem','$show_date', '$insert_crtc', '$insert_lang', '$insert_part', '$insert_inst', '$insert_hit')";
			}
			$insert_result = $db->query($insert_query);
			if($insert_result){
				if($insert_cc==1) {
					echo "<font color=red>";
				}
				else {
					echo "<font color=white>";
				}
				if($SOCAN_FLAG){
					echo  html_entity_decode($insert_artist) . " - " . html_entity_decode($insert_song) . "-" . html_entity_decode($insert_album) . "-" . html_entity_decode($insert_composer) ;
				}
				else{
					echo  html_entity_decode($insert_artist) . " - " . html_entity_decode($insert_song) . "-" . html_entity_decode($insert_album) ;
				}
				echo "</font><br/>";
			}else { 
				echo "An Error Occurred!";
				$error[0] = mysqli_error($db);
				$error[1] = $insert_query;
				$log_me = "<hr/> Error Logged at: ".$today."<br> Occured on page: ".$_SERVER['HTTP_REFERER']." <br/> Error: ".$error[0]."<br/>Query: ".$error[1]."<br/>Data: ".json_encode($_POST,true);
				$log_file = 'logs/log.html';
				if(file_put_contents ( 'logs/log.html' , $log_me, FILE_APPEND)){
					echo "<br/>The Error was Logged Sucessfully";
				}else{
					echo "<br/>The error could not be logged";
				}
			}
		}

	}
	
	
	$ad_entries = $_POST["numberOfAdRows"];

	
	$ad_query = "UPDATE adlog SET playsheet_id = '".$ps_id."', played='0' WHERE time_block = '".$_POST['unixTime']."'"; // assume the ad is not played - set to 0
	if (	mysqli_query($db, $ad_query)){		
			} else {
				echo "ad query didn't work: ".$ad_query."<br/>";
				$log_me = 'playsheet.php - there was a problem with the ad update query '.date('D, d M Y').' - <b>'.date(' g:i:s a').'</b>';
				$log_me .= '<br/>POST: '.print_r($_POST,true).'<br>ad_query:'.$ad_query.'<hr>';
				$log_file = 'logs/log.html';
				file_put_contents ( 'logs/log.html' , $log_me, FILE_APPEND);
	}
	
	foreach($_POST as $postID => $postVal){
		if ( substr($postID,0,10) == "adplaydbid" ) {
			$brian = explode("_",$postID);
			$ad_row_db_id = $brian[1];
			$ad_query = "UPDATE adlog SET played = '1', playsheet_id = '".$ps_id."' WHERE id='".$ad_row_db_id."'"; // set the row to played
			if (mysqli_query($db, $ad_query)){

			}else{
				echo "ad query didn't work: <br/>".$ad_query."<br/>";
				$log_me = 'playsheet.php - there was a problem with the ad update query '.date('D, d M Y').' - <b>'.date(' g:i:s a').'</b>';
				$log_me .= '<br/>POST: '.print_r($_POST,true).'<br>ad_query:'.$ad_query.'<hr>';
				$log_file = 'logs/log.html';
				file_put_contents ( 'logs/log.html' , $log_me, FILE_APPEND);
			} 
		}
	}
	
	echo "</div>";
	echo "<br/><br/>format:<br/> artist - title (album) <br/> <font color=red>red means cancon</font> <br/><br/> feedback? email technicalservices@citr.ca<br/><br/>";
}

//
//
//
//
//        Task:      List playsheets
//
//
//
//
//
else if($actionSet && $action == 'list' && !isset($_GET['delete'])) {
//echo ('list playsheets');


	printf("<CENTER><FORM METHOD=\"GET\" ACTION=\"%s\" name=\"the_form\">\n", $_SERVER['SCRIPT_NAME']);

	//echo "<CENTER><FORM METHOD='GET' name='the_form'>";
	
	printf("<INPUT type=hidden name=action value=edit>");
	printf("<SELECT class='selectps' NAME=\"id\" SIZE=25>\n");
	$get_playlists = "SELECT p.start_time AS start_time,p.id AS id, s.name AS name, p.star AS star, p.status AS status FROM playlists AS p INNER JOIN shows AS s ON s.id=p.show_id   ORDER BY start_time DESC";
	if($result = $db->query($get_playlists)){


		while($row = mysqli_fetch_array($result)){
			$time = date( 'Y: M j, g:ia' ,strtotime($row['start_time']));
			echo "<option value='".$row[id]."'>".$time." - ".$row[name].($row['status'] == 1 ? " - (draft)":"").($row["star"] == 1 ? " &#9733":"")."</option>";
		}
	}

	echo "</SELECT><BR><button TYPE=submit VALUE='View Playsheet' class='bigbutton' >View Playsheet</button>";
	echo "<br/><br/><button type=submit name=socan value='true' >Load as SOCAN playsheet</button>";



	if((is_member("addshow"))){
	echo "<br/><br/><button type=delete name=delete value=delete>delete selected playsheet</button>";
	echo '<br/><br/><a href="setSocan.php">Set a Socan Period Here</a>';
	
	}
	echo "</FORM></CENTER>";
} else if ( (isset($_GET['delete']) && ( $_GET['delete'] == 'delete') ) && (is_member("addshow"))){
	
	$delete_query = "DELETE FROM playlists WHERE id='".$_GET['id']."'";
	$delete_playitems = "DELETE FROM playitems WHERE playsheet_id = '".$_GET['id']."'";

	if( $result = mysqli_query($db,$delete_query)) {

		if($result2 = mysqli_query($db,$delete_playitems)){
				echo '<center><br/><br/>you just deleted playsheet id #'.$_GET['id'];
				echo '<br/><a href="/playsheet.php?action=list">back to list</a></center>';
		}
	} else {
		echo 'could not delete the playsheet';
		echo '<hr/>'.$delete_query;
	}
}
/* moved to an external file report.php
else if(is_member("member") && isset($_GET['action']) && $_GET['action'] == 'report' ) {
}*/

//
//
//
//
//
//
//
//            Task:      Edit/New Playsheet
//
//
//
//
//
//

else if(is_member("dj")){
require_once('adLib.php');
$adLib = new AdLib($mysqli_sam,$db);
			

	
	// Existing Playsheet
	if(  $actionSet && $action == 'edit' || $action == 'datadump'){



	//LOADING A SAVED PS
		$ps_id = fas($_GET['id']);
		//echo " you are editing playsheet id number ".$ps_id;
		if ($result = mysqli_query($db,"SELECT *,UNIX_TIMESTAMP(start_time) AS good_date, HOUR(end_time) AS end_hour, MINUTE(end_time) AS end_min FROM playlists WHERE id='$ps_id'")){
		$curr_id = mysqli_result_dep($result,0,"show_id");
		$currshow = $showlib->getShowByID($curr_id);
		$pl_date_year = date('Y', mysqli_result_dep($result, 0, "good_date"));
		$pl_date_month = date('m', mysqli_result_dep($result, 0, "good_date"));
		$pl_date_day = date('d', mysqli_result_dep($result, 0, "good_date"));
		$pl_date_hour = date('H', mysqli_result_dep($result, 0, "good_date"));
		$pl_date_min = date('i', mysqli_result_dep($result, 0, "good_date"));
		$end_date_hour = mysqli_result_dep($result, 0, "end_hour");
		$end_date_min = mysqli_result_dep($result, 0, "end_min");
		$unix_start_time = mktime($pl_date_hour, $pl_date_min, 0, $pl_date_month, $pl_date_day, $pl_date_year);
		$host_name = $fhost_name[mysqli_result_dep($result, 0, "host_id")];
		$show_name = $fshow_name[mysqli_result_dep($result, 0, "show_id")];
		$show_id = mysqli_result_dep($result, 0, "show_id");
		$loaded_spokenword = mysqli_result_dep($result,0,"spokenword");
		$loaded_sw_duration = mysqli_result_dep($result, 0, "spokenword_duration");
		$loaded_status = mysqli_result_dep($result, 0, "status");
		$loaded_crtc = mysqli_result_dep($result, 0, "crtc");
		
		$loaded_lang = mysqli_result_dep($result, 0, "lang");
		$loaded_type = mysqli_result_dep($result, 0, "type");

		// podcast stuff


		$adTable = $adLib->loadTableForSavedPlaysheet($ps_id);
		} else {
			// db query didn't work :|
				$pl_date_year =  date('Y');
				$pl_date_month =  date('m');
				$pl_date_day =  date('d');
				$pl_date_hour =  date('H');
				$pl_date_min =  date('i');
				$end_date_hour = date('H');
				$end_date_min = date('i');
			
				$host_name =  "";
				$show_name =  "";
				$show_id =  "";
			
				$loaded_spokenword =  "";
				$loaded_sw_duration =  "";
				$loaded_crtc = "";
				$loaded_lang = "";
		}
	}
	else {
		// making a new PS
		
			if(isset($_GET['time'])){	
				$unix_start_time = $_GET['time'];	
			
				//check to see if this unix time already has a playsheet saved - if so, load that one with action=edit
				
				$check_query = "SELECT id FROM playlists WHERE unix_time='".$unix_start_time."'";
				if ($check = mysqli_query($db, $check_query)){
					$checked = mysqli_fetch_assoc($check);
					if($yesnumber = $checked['id']){
						header( "Location: ./playsheet.php?action=edit&id=".$yesnumber);
					}
				} else{
				}
				
				//MAKING A NEW PS THAT IS IN PAST (OR FUTURE)
				$currshow = $showlib->getShowByTime($unix_start_time);
				
				$pl_date_year = date('Y',$unix_start_time);
				$pl_date_month = date('m',$unix_start_time);
				$pl_date_day = date('d',$unix_start_time);
				$pl_date_hour = date('H', $unix_start_time);
				$pl_date_min = date('i', $unix_start_time);
				
				$show_end = strtotime($currshow->times[0]['end_time']);
				$end_date_hour = date('H', $show_end);
				$end_date_min = date('i', $show_end);
				
			}
			else{
				
				// MAKING NEW PS THAT IS RIGHT NOW (default)
				$currshow = $showlib->getCurrentShow();
				$showtime = $currshow->getMatchingTime($showlib->getCurrentTime());


				if (count($showtime)) {
					$pl_date_hour = date('H', strtotime($showtime['start_time']));
					$pl_date_min = date('i', strtotime($showtime['start_time']));
					$end_date_hour = date('H', strtotime($showtime['end_time']));
					$end_date_min = date('i', strtotime($showtime['end_time']));
				//	echo "  ".$pl_date_hour.":".$pl_date_min;
				}
				$pl_date_year =  date('Y');
				$pl_date_month =  date('m');
				$pl_date_day =  date('d');
				
				$unix_start_time = mktime($pl_date_hour, $pl_date_min, 0, $pl_date_month, $pl_date_day, $pl_date_year);
		
			}
			$showtype = $currshow->showtype; 
			$ps_id = 0;			
			$host_name = $currshow->host;
			$show_name = $currshow->name;
			$show_id = $currshow->id;
			$lang_default = $currshow->lang_default;
			$crtc_default = $currshow->crtc_default;
			
			if($lang_default == ''){
				$lang_default = 'eng';
			}
			if($crtc_default == ''){
				$crtc_default = 20;
			}
						
			$loaded_spokenword =  "";
			$loaded_sw_duration =  "";
			$adTable = $adLib->generateTable($unix_start_time,'dj', false);
	}
	
		if($loaded_crtc)
			$crtc_pl = $loaded_crtc;
		else $crtc_pl = $crtc_default;
		
		if($loaded_lang)
			$lang_pl = $loaded_lang;
		else $lang_pl = $lang_default;
		echo "<div id=loaded_crtc_test style='display:none'>".$crtc_pl."</div>";	
	if($ps_id && $_GET['action'] != 'datadump') {
		// VIEW IS NOT RAW DATA
		printf("<br><div class=buttonContainer>");
		printf("<div class=nav><ul><li><a href=\"playsheet.php?action=datadump&id=%s\">&nbsp;View Tracklist&nbsp;</a></li></ul></div></div>",$ps_id);
	}	
	else if ($ps_id){
		// VIEW IS RAW DATA
		printf("<br><div class=buttonContainer>");
		printf("<div class=nav><ul><li><a href=\"playsheet.php?action=edit&id=%s\">&nbsp;View Playsheet&nbsp;</a></li></ul></div></div>",$ps_id);
	}	


// WINDOWS INTERNET EXPLORER CHECK
preg_match('/MSIE (.*?);/', $_SERVER['HTTP_USER_AGENT'], $matches);

if (count($matches)>1){
  //Then we're using IE
  $version = $matches[1];

  switch(true){
    case ($version<=8):
      print(' 	<div align="right"><button id="SamTab" class="panel-button">SAM plays</button>
				<button id="buttonLoadTimes" class="panel-button">SAM period </button>
				<button id="autosaver" class="panel-button">save<br/>draft</button></div> ');
      break;

    default:
      print("");
  }
}

	
	// Raw Data view
	printf("<br>");
	if($SOCAN_FLAG) {printf("<div class=playsheetSOCAN>");}
	else {printf("<div class=playsheet>");}
	if($_GET['action'] == 'datadump') {


		if($ps_id) {
			$result = mysqli_query($db,"SELECT * FROM playitems WHERE playsheet_id='$ps_id' ORDER BY id");
			$num_rows = mysqli_num_rows($result);
		}
		else {
			$num_rows = 0;
		}
     
        

        echo "<table >";
        echo "<tr><td colspan=2 ><br/>playsheet tracklist <br/>artist - song (album) <br/><br/></td></tr>";
        echo "<tr>";
        
		if($ps_id) {
			$result = mysqli_query($db,"SELECT * FROM playitems WHERE playsheet_id='$ps_id' ORDER BY id");
			$num_rows = mysqli_num_rows($result);
		}
		else {
			$num_rows = 0;
		}
		for($i=0; $i < $num_rows; $i++) {
            
			$result2 = mysqli_query($db,"SELECT * FROM songs WHERE id='".mysqli_result_dep($result,$i,"song_id")."'");
            echo "<tr>";
            echo "<td class=\"rawdata\">";
			echo html_entity_decode(mysqli_result_dep($result2,0,"artist"));
			echo " - ";
			echo html_entity_decode(mysqli_result_dep($result2,0,"song"));
			echo " (";
			echo html_entity_decode(mysqli_result_dep($result2,0,"title"));
			echo ")<br/>";
			if($SOCAN_FLAG)
			{
				echo " - ";
				echo html_entity_decode(mysqli_result_dep($result2,0,"composer"));
				echo "<br/>";
			}
           echo "</td></tr>";
            
		}
            echo "</table>";
		}
	else {
//
//
//
//
//
//
//              PLAYSHEET EDITING VIEW ( same for new playsheet or old playsheet )   
//
//
//
//
//
//
//		echo ('playsheet edit view. ID is '.$ps_id.'<br/>timestamp: '.$unix_start_time);
//		echo '.  Date: '.date( 'D, M j, g:ia', $unix_start_time);
		



		if($SOCAN_FLAG){
			printf("<FORM METHOD=POST ACTION=\"%s?action=submit&socan=true\" name=\"playsheet\" id='playsheetForm' >", $_SERVER['SCRIPT_NAME']);
		} else {
			printf("<FORM METHOD=POST ACTION=\"%s?action=submit\" name=\"playsheet\" id='playsheetForm' >", $_SERVER['SCRIPT_NAME']);
		}
		?>
		<INPUT type=hidden id='psid' name=id value= <?php echo $ps_id; ?>>
		<center><h1>DJ PLAYSHEET</h1></center>
		<table border=0 align=center width=100%%><tr><td>Show Type:
		<?php if( isset($loaded_type) && ($loaded_type != null) ){ ?>
		<select id='type' name='type' value=".$loaded_type."><option selected><?php echo $loaded_type; ?></option>
		<?php } 
		else if(isset($showtype) && ($showtype != null)){ ?>
		<select id='type' name='type' value=".$showtype."><option selected> <?php echo $showtype; ?> </option>		
		<?php }
		else{ ?>
		<select id='type' name='type' ><option selected>Live</option>
		<?php
		}
		?>
			<option>Live</option>
			<option>Syndicated</option>
			<option>Rebroadcast</option>
			<option>Simulcast</option>
			<option>Pre-Recorded</option>
			<option>Other</option>
		</select>
		<?php
		
		$playsheet_list = getRecentPlaylists($db,500);
		
		echo "<br/><select style='height:25px' class=invisible id='select-playsheet' >" ;
		$query = "SELECT s.id AS id, s.name AS name, p.id AS playsheet_id, p.start_time AS start_time  FROM shows AS s INNER JOIN playlists AS p ON s.id = p.show_id";
		if($result = $db->query($query)){
			while($row = mysqli_fetch_array($result)){
				echo "<option value='".$row['playsheet_id']."' data='".$row['start_time']."'>".$row['start_time']." - ".$row['name']."</option>";
			}
		}
		$result->close();



		/*foreach($playsheet_list as $i => $a_playsheet){
	 
			$ps_list_date_unix = strtotime($a_playsheet['start_time']); 
			$ps_list_Date = date ( 'M j, g:ia', $ps_list_date_unix);
		 
			echo '<option value='.$a_playsheet['id'].' data='.$ps_list_Date.' name='.$fshow_name[$a_playsheet['show_id']].' >'.$ps_list_Date.' - '.$fshow_name[$a_playsheet['show_id']].'</option>';
			
	 
		}*/
		echo '</select>';
		echo '<button id="load-playsheet" type="button" class="invisible">Select This Playsheet</button></tr>';
		
		echo "<span id='ps_header'>";
		
		printf("<tr><td> Show: <select id='showSelector' name=\"showtitle\">");

		if ($ps_id || $show_name) printf("<option value='%s' selected='selected'>%s",$show_id, $show_name);
		
		$query = "SELECT id,name FROM shows WHERE active=1 ORDER BY name";
		if($result = $db->query($query)){
			while($row = mysqli_fetch_array($result)){
				echo "<option value='".$row[id]."'>".$row[name]."</option>";
			}
		}
		$result->close();

		/*foreach($fshow_name_active as $x => $var_name) {
			if($var_name != '!DELETED' || $ps_id) printf("<option "."value='".$x."'>%s", $var_name);			
		}*/
		printf("</select></td>");

		printf("<td align=right>Host/Op: <input id='host' name=\"host\" type=text size=30 value=\"%s\"  ></td></table>", $host_name);

		//Playlist Date
		echo "<table width=100%% border=0 align=center><tr><td>Date: ";
		echo "(<SELECT id=playsheet-year NAME=pl_date_year  ><OPTION>".$pl_date_year;
		for($i=2002; $i <= 2013; $i++) echo "<OPTION>".$i; 
		echo "</SELECT>-";
		echo "<SELECT id=playsheet-month NAME=pl_date_month  >\n<OPTION>".sprintf("%02d",$pl_date_month);
		for($i=1; $i <= 12; $i++) echo "<OPTION>".sprintf("%02d", $i); 
		echo "</SELECT>-";
		echo "<SELECT id=playsheet-day NAME=pl_date_day  >\n<OPTION>".sprintf("%02d", $pl_date_day);
		for($i=1; $i <= 31; $i++) echo "<OPTION>".sprintf("%02d", $i);  
		echo "</SELECT>) <i>set date and show first</i>";

		printf("</td><td align=right>Start Time: [");
		printf("<SELECT id=pl_date_hour NAME=pl_date_hour  >\n<OPTION>%02d", $pl_date_hour);
		for($i=0; $i <= 23; $i++) printf("<OPTION value=%02d>%02d",$i, $i); 
		printf("</SELECT>:");
		printf("<SELECT id=pl_date_min NAME=pl_date_min  >\n<OPTION >%02d", $pl_date_min);
		for($i=0; $i <= 59; $i++) printf("<OPTION value=%02d>%02d", $i,$i); 
		printf("</SELECT>]");

		printf("</td><td align=right>End Time: [");
		printf("<SELECT id=end_date_hour NAME=end_date_hour  >\n<OPTION>%02d", $end_date_hour);
		for($i=0; $i <= 23; $i++) printf("<OPTION value=%02d >%02d", $i, $i); 
		printf("</SELECT>:");
		printf("<SELECT id=end_date_min NAME=end_date_min  >\n<OPTION>%02d", $end_date_min);
		for($i=0; $i <= 59; $i++)printf("<OPTION value=%02d>%02d", $i, $i); 
		?>
		</SELECT>]
		</td></tr><tr align=center width=400px>
		<td>CRTC Category:<input type='text' id=pl_crtc name=pl_crtc value=<?php echo $crtc_pl; ?>>
		</td>
		
		<td align=right colspan=2>Language:<input type='text' id=pl_lang name=pl_lang value=<?php echo $lang_pl ?>>		
		<td/><tr/></table>
		
		<img src='images/loading.gif' id='ps-loading-image'>
		</span>
		
		<!-- main interface table -->
		<span id='draft'><?php if($loaded_status==1):?>(draft)<?php endif;?></span>
	
		<br><table class='dragrows' id='playsheet-table'>
		<input type='text' id='numberOfRows' name='numberOfRows' class='invisible' value='<?php echo $playlist_entries; ?>'>
		<input type='text' id='numberOfAdRows' name='numberOfAdRows' class='invisible'>
		<input type='text' id='unixTime' name='unixTime' class='invisible' value='<?php echo $unix_start_time; ?>'>
		<input type='text' id='status' name='status' class='invisible' >
		<input type='text' id='star' name='star' class='invisible' >	
		<h2>Music</h2>	
		
		<?php if($SOCAN_FLAG): ?>
			<td >Time</td>
			<td >Duration</td>
			<td>Composer</td>
		<?php endif; ?>
		<!-- helpboxes declaration -->
		<div id='helpboxARTIST'></div>
		<div id='helpboxSONG'></div>
		<div id='helpboxALBUM'></div>
		<div id'=helpboxPL'></div>
		<div id='helpboxCC'></div>
		<div id='helpboxFE'></div>
		<div id='helpboxINST'></div>
		<div id='helpboxPART'></div>
		<div id='helpboxHIT'></div>
		<div id='helpboxTHEME'></div>
		<div id='helpboxBACKGROUND'></div>
		<div id='helpboxCRTC'></div>
		<div id='helpboxLANG'></div>
		<div id='helpboxTOOLS'></div>
		<div id='helpboxGUEST'></div>
		<div id='helpboxAD'></div>
		<!--Banner with Icons-->
		


		<?php
		if($SOCAN_FLAG){
		print('<div class="bannerforsortSOCAN">'); }
		else {
		print('<div class="bannerforsort">');}
		
		print('<div class="numbering"><span>#</span></div>');
		
		
		if($SOCAN_FLAG) {
		print("<div class='inputboxesSOCAN'><span class=popup id=ppartist>Artist</span></div>");
		print("<div class='inputboxesSOCAN'><span class=popup id=ppalbum>Album</span></div>");
		print("<div class='inputboxesSOCAN'><span class=popup id=ppsong>Song</span></div>");
		print("<div class='inputboxesSOCAN'><span class=popup id=ppcomp>Composer</span></div>");
		print("<div class='timeBox'><div class='timeBoxHalf'><span class=popup id=pptime1>Time Start (H:M)</span></div>");
		print("<div class='timeBoxHalf'><span class=popup id=pptime2>Duration (M:S)</span></div></div>");
		}
		else{
		print("<div class='inputboxes'><span class=popup id=ppartist>Artist</span></div>");
		print("<div class='inputboxes'><span class=popup id=ppalbum>Album</span></div>");
		print("<div class='inputboxes'><span class=popup id=ppsong>Song</span></div>");
		}
		?>
		<div class="CRTCicons"><span class=popup id=pppl ><img src="images/pl.png"></span></div>
		<div class="CRTCicons"><span class=popup id=ppcc ><img src="images/cc.png"></span></div>
		<div class="CRTCicons"><span class=popup id=ppfe ><img src="images/fe.png"></span></div>
		<div class="CRTCicons"><span class=popup id=ppinst ><img src="images/inst.png"></span></div>
		<div class="CRTCicons"><span class=popup id=pppart ><img src="images/part.png"></span></div>
		<div class="CRTCicons"><span class=popup id=pphit ><img src="images/hit.png"></span></div>
		<?php
		if($SOCAN_FLAG){
		echo '<div class="CRTCicons"><span class=popup id=pptheme ><img src="images/THEME.png"></span></div>';
		echo '<div class="CRTCicons"><span class=popup id=ppbackground ><img src="images/BACKGROUND.png"></span></div>';
		}
		?>
		
		
		<div class="CRTCradios"><span class=popup id=ppcrtc >CRTC</span></div>
		<div class="CRTCtext"><span class=popup id=pplang >Lang</span></div>
		<div class="CRTCtools"><span class=popup id=pptools >Tools</span></div>
		</div>
		
		<?php
		if($ps_id) {
			$result = mysqli_query($db,"SELECT * FROM playitems WHERE playsheet_id='$ps_id' ORDER BY id");
			$num_rows = mysqli_num_rows($result);
//			echo 'found a ps id, so did a query. here is the result:';
//			print_r($result);
		}
		else {
			$num_rows = 5;
		}
		if($num_rows==0)
		{
		$num_rows=1;
		}
		
		
		if($SOCAN_FLAG){print('<ul id="sortable" list-styletype="none">');}
		else {print('<ul id="sortable" list-styletype="none">');}
		
		for($i=0; $i <= ($num_rows); $i++) {		

				if($ps_id){ // if $ps_id is set then its a loaded playsheet
//				$set_lang = htmlentities(mysqli_result_dep($result,$i,"lang"), ENT_QUOTES);
				$set_lang = mysqli_result_dep($result,$i,"lang");
				}
				$set_part = mysqli_result_dep($result,$i,"is_part") ? " checked" : "";
				$set_inst = mysqli_result_dep($result,$i,"is_inst") ? " checked" : "";			
				$set_hit = mysqli_result_dep($result,$i,"is_hit") ? " checked" : "";
				$set_pl = mysqli_result_dep($result,$i,"is_playlist") ? " checked" : "";
				$set_cc = mysqli_result_dep($result,$i,"is_canadian") ? " checked" : "";
				$set_yo = mysqli_result_dep($result,$i,"is_yourown") ? " checked" : "";
				$set_indy = mysqli_result_dep($result,$i,"is_indy") ? " checked" : "";
				$set_fem = mysqli_result_dep($result,$i,"is_fem") ? " checked" : "";
			
				if($SOCAN_FLAG){
				$set_theme = mysqli_result_dep($result,$i,"is_theme") ? " checked" : "";
				$set_background = mysqli_result_dep($result,$i,"is_background") ? " checked" : "";	
				
				$set_song_start_hour = mysqli_result_dep($result,$i,"insert_song_start_hour");
				$set_song_start_minute = mysqli_result_dep($result,$i,"insert_song_start_minute");
				$set_song_length_minute = mysqli_result_dep($result,$i,"insert_song_length_minute");
				$set_song_length_second = mysqli_result_dep($result,$i,"insert_song_length_second");
			
				}
				
				$crtc_num = mysqli_result_dep($result,$i,"crtc_category");
				
				if(!(isset($crtc_num) && ($crtc_num=="20" || $crtc_num=="30"))){
					$crtc_num = $crtc_pl;
					}
				if(!isset($set_lang)){
						$set_lang = $lang_pl;
				}
				
				
//				if (!$ps_id && $lang_default) $crtc_num = $crtc_default;
			
				// the following queries are from songs
				$result2 = mysqli_query($db,"SELECT * FROM songs WHERE id='".mysqli_result_dep($result,$i,"song_id")."'");

//				$set_artist = htmlentities(mysqli_result_dep($result2,0,"artist"), ENT_QUOTES);
//				$set_title = htmlentities(mysqli_result_dep($result2,0,"title"), ENT_QUOTES);
//				$set_song = htmlentities(mysqli_result_dep($result2,0,"song"), ENT_QUOTES);

				$set_artist = html_entity_decode(mysqli_result_dep($result2,0,"artist"));
				$set_title = html_entity_decode(mysqli_result_dep($result2,0,"title"));
				$set_song = html_entity_decode(mysqli_result_dep($result2,0,"song"));
				if($SOCAN_FLAG)
				{
				$set_composer = html_entity_decode(mysqli_result_dep($result2,0,"composer"));
				}
				
				
			// last iteration counts as an invisible template row
			if ($i == ($num_rows)) {
				$i = "template";	
			printf("<li id='row%s' name='row%s' class='invisible'>",$i,$i);
			}
			else{	

				if($SOCAN_FLAG){
				printf("<li class='playsheetrow playsheetrowSOCAN' id='row%s' name='row%s'>",$i,$i);}
				else{
				printf("<li class='playsheetrow' id='row%s' name='row%s'>",$i,$i);}
				}

				
			print("<div class='numbering'><span class=rowLabel id='rowLabel".$i."'></span></div>");
			
			//SOCAN elements
			if($SOCAN_FLAG)
			{
			print("<span class='inputboxesSOCAN'><input class='inputboxesinnerSOCAN req' id=artist".$i." name=artist".$i." type=text size=18 value='".$set_artist."'  ></span>");
			print("<span class='inputboxesSOCAN'><input class='inputboxesinnerSOCAN req' id=album".$i." name=album".$i." type=text size=18 value='".$set_title."'  ></span>");
			print("<span class='inputboxesSOCAN'><input class='inputboxesinnerSOCAN req' id=song".$i." name=song".$i." type=text size=18 value='".$set_song."'  ></span>"); 
			print("<span class='inputboxesSOCAN'><input class='inputboxesinnerSOCAN req' id=composer".$i." name=composer".$i." type=text size=18 value='".$set_composer."'  ></span>"); 
			print("<span class='timeBox'>");
			
			//start time
			print("<span class='timeBoxHalf'>");	
			if(!$set_song_start_hour)
			{ $set_song_start_hour='00';}
			if(!$set_song_start_minute)
			{ $set_song_start_minute='00';}
			
			if(!$set_song_length_minute)
			{ $set_song_length_minute='00';}
			if(!$set_song_length_second)
			{ $set_song_length_second='00';}
			print("<SELECT class=timeInner id=set_song_start_hour".$i." name=set_song_start_hour".$i."  > <OPTION value='".$set_song_start_hour."'>".$set_song_start_hour."</OPTION>");
			for($j=0; $j <= 23; $j++) { print("<OPTION value='".$j."'>".sprintf("%02d",$j)."</OPTION>"); } 
			print("</SELECT>");
			print("<SELECT class=timeInner id=set_song_start_minute".$i." name=set_song_start_minute".$i."  ><OPTION value='".$set_song_start_minute."'>" .$set_song_start_minute."</OPTION>");
			for($j=0; $j <= 59; $j++) { print("<OPTION value='".$j."'>".sprintf("%02d",$j)."</OPTION>"); }
			print("</SELECT>");
			print("<SELECT style='display:none;' class=timeInner id=set_song_start_second".$i." name=set_song_start_second".$i."  ><OPTION value='".$set_song_start_second."'>".$set_song_start_second."</OPTION>");
			for($j=0; $j <= 59; $j++) { print("<OPTION value='".$j."'>".$j."</OPTION>"); }
			print("</SELECT>");
			print("<button type=button id='current_time_start".$i."' name='current_time_start".$i."' class='nowButton getStartTime'><b>CUE</b></button>");
			print("</span>");

			//duration
			print("<span class='timeBoxHalf'>");
			print("<SELECT class=timeInner  id=set_song_length_minute".$i." name=set_song_end_minute".$i." ><OPTION value='".$set_song_length_minute."'>".$set_song_length_minute."</OPTION>");
			for($j=0; $j <= 59; $j++) { print("<OPTION value='".$j."'>".sprintf("%02d",$j)."</OPTION>"); }
			print("</SELECT>");
			print("<SELECT class=timeInner id=set_song_length_second".$i." name=set_song_end_second".$i."  ><OPTION value='".$set_song_length_second."'>".$set_song_length_second."</OPTION>");
			for($j=0; $j <= 59; $j++) { print("<OPTION value='".$j."'>".sprintf("%02d",$j)."</OPTION>"); }
			print("</SELECT>");
			print("<button type=button id='current_time_end".$i."' name='current_time_end".$i."' class='nowButton getEndTime'><b>END</b></button>");
			print("</span>");
			
			print("</span>");
			
			}
			else
			{
			print("<span class='inputboxes'><input class='inputboxesinner req' id=artist".$i." name=artist".$i." type=text size=18 value='".$set_artist."'  ></span>");
			print("<span class='inputboxes'><input class='inputboxesinner req' id=album".$i." name=album".$i." type=text size=18 value='".$set_title."'  ></span>");
			print("<span class='inputboxes'><input class='inputboxesinner req' id=song".$i." name=song".$i." type=text size=18 value='".$set_song."'  ></span>"); 
			}
			print("<span class='CRTCicons'><input class='mousedragclick' type=checkbox id=pl".$i." name=pl".$i.$set_pl."   ></span>");
			print("<span class='CRTCicons'><input class='mousedragclick' type=checkbox id=cc".$i." name=cc".$i.$set_cc."   ></span>");
			print("<span class='CRTCicons'><input class='mousedragclick' type=checkbox id=fem".$i." name=fem".$i.$set_fem."   ></span>");
			print("<span class='CRTCicons'><input class='mousedragclick' type=checkbox id=inst".$i." name=inst".$i.$set_inst."   ></span>");
			print("<span class='CRTCicons'><input class='mousedragclick' type=checkbox id=part".$i." name=part".$i.$set_part."   ></span>");
			print("<span class='CRTCicons'><input class='mousedragclick' type=checkbox id=hit".$i." name=hit".$i.$set_hit."   ></span>");
			if($SOCAN_FLAG){
			print("<span class='CRTCicons'><input class='mousedragclick' type=checkbox id=theme".$i." name=theme".$i.$set_theme."   ></span>");
			print("<span class='CRTCicons'><input class='mousedragclick' type=checkbox id=background".$i." name=background".$i.$set_background."   ></span>");
			}
			
			
			print("	
					 <span class='CRTCradios'>".
					 "<span class='CRTCradios2'>".
					 "<label for='crtcTwo".$i."'class='CRTCicons3' >".
					 "2".
					 "</label>".
					 "<input class='radio mousedragclick CRTCicons3' type='radio' id='crtcTwo".$i."' name='crtc".$i."' value='20' ".($crtc_num == '20' ? "checked='checked'" : " ")." />".
					 "</span>".
					 "<span class='CRTCradios2'>".
					 "<label for='crtcThree".$i."' class='CRTCicons3' >".
					 "3".
					 "</label>".
					 "<input class='radio mousedragclick CRTCicons3' type='radio' id='crtcThree".$i."' name='crtc".$i."' value='30' ".($crtc_num == '30' ? "checked='checked'" : " ")."/>".
					 "</span></span>" );
			print("<span class='CRTCtext'><input class='langInput' id=lang".$i." name=lang".$i." type=text size=3 value='".$set_lang."'></span>");
			print("<span class='CRTCicons2'> <button type=button id=del".$i." class=delRow><b>-&nbsp</b></button></span>&nbsp;&nbsp;");
			print("<span class='CRTCicons2'><button type=button id=add".$i." class=addRow><b>+</b></button></span>");
			//print("<span class='CRTCicons2'><button type=button id=copy".$i." class=copyRow> copy </button></span>");
			print("<span class='CRTCicons2'><span class='dragHandle'>&nbsp;&nbsp;&#x21D5;</span></span>");
			print("<span></span>");
			
			print('</li>');
			
			// print("<td id='move' class='dragHandle'>&#x21D5;</td>");
			//print("</tr>");
			
			if ($i==="template") $i = ($num_rows);
		}
		print('</ul>');
		
		echo "<span style='width:100%; display:inline-block;'><span id='addfive'> add 5 more rows </span> </span> <br/>";


		// ADS SECTION
		
		
	?>

	<hr/>

<div id='spokenword'>
<h2>Spoken Word</h2>	


<span class='left' id='ads'>
<span class='popup' id='ppAds'><b>Ads / PSA / IDs</b></span>
<?php 


echo $adTable;
			
echo "</span> <span class='right'  id='swcontent'>";

echo "<span class='popup' id='ppGuests'><b>Guests, Interviews, Topics</b></span>";

echo "<br/>Description:<br/>";
echo "	<textarea id='spokenword' name='spokenword' >";
echo 	$loaded_spokenword;							
echo "</textarea><br/>";
echo "Total Overall Duration:<br/>";
				
					
		printf("<SELECT NAME='sw-time-hr' id='sw-time-hr'  >\n<OPTION>");
		
		if($loaded_sw_duration>0){
			$hours = floor($loaded_sw_duration/60);
			$minutes = $loaded_sw_duration%60;
			echo $hours;
			} else echo "00";
		for($i=0; $i < 24; $i++) printf("<OPTION>%02d", $i);
		printf("</SELECT> Hours <br/>");
		printf("<SELECT NAME='sw-time-min' id='sw-time-min'  >\n<OPTION>");
		if($loaded_sw_duration>0){
		echo $minutes;
		} else echo "00";
		for($i=0; $i < 60; $i++) printf("<OPTION>%02d", $i);
		printf("</SELECT> Minutes");
	?>
<!--		</td>
			
	</tr>
		
</table> -->
</span>

</div>
<br/><br/><br/><br/><br/><br/><hr/>

<?php if($enabled['podcast_tools']){ ?>
<div id='podcast-tools'>
<h2>Podcast Tools</h2>
<center>
<span class='custom_button' id='podcastStart' >podcast START</span>
<span class='custom_button' id='podcastEnd' >podcast END</span>

<span id='podcastTime'></span>	</center>
</div>

<div id="podcast" ng-app='podcastEditor'>
	<div ng-controller='newCtrl as new' >
		<div ng-controller='episodeCtrl as episode' class=episode >
			<ng-include src="'podcasting/podcast-episode.html'">

			{{episode.title}}
			</ng-include>
		</div>
	</div>

</div>

	<script type="text/javascript" src="js/soundmanager2/script/soundmanager2.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.3.5/angular.js"></script>
	<script type='text/javascript' src='js/bootstrap/bootstrap.js'></script>
	<script type='text/javascript' src='js/bootstrap/ui-bootstrap-tpls-0.12.0-withseconds.js'></script>

	<script type='text/javascript' src='podcasting/angular-podcasting.js'>

	</script>

	<script type='text/javascript'>
		podcastEditor.controller('newCtrl', ['$scope', function ($scope){
			console.warn('hi');
			var date = new Date();
			var unixDate = date.getTime() / 1000;
			$scope.episode = {
				date_unix:unixDate,
				duration:1600,
				something:'anything',
				title:'new podcast',
				subtitle:'subtitle',
				summary:'summary'};

			sm = new SoundManager();
		}]);

		podcastEditor.value('timezone_offset',<?php echo date_offset_get(new DateTime); ?>); //timezone offset is in seconds
		podcastEditor.value('channel_id','<?php echo $currshow->pod_chan_id; ?>');
		podcastEditor.value('edit_all',false);

	</script>


<hr>
	<?php




		}// end of podcast tools creation block
		
			if(!$ps_id || is_member("editdj")) {
			printf("<center><br/><span id='submitMsg'>This is an incomplete playsheet. <br/>Please fill in all music fields: <b>artist</b>, <b>album</b> (release title), and <b>song</b>. Also delete all empty rows by clicking the '-' button.<br/>You may temporarily save a draft and resume at another time by clicking 'Save draft' in the top right corner</span><br/><button id=submit type=submit value=\"Save Playsheet\">Submit Playsheet</button></center><br/><br/><br/>
			<div></div>");
		}
		printf("</FORM>");
		// echo'




	
		print("<div class='bugsAndTopChart'>");
		if (isset($station_info['tech_email'])){
		echo "<div class='bugs'>For support, email:<br/> <a href='mailto:".$station_info['tech_email']."'>".$station_info['tech_email']."</a><br/><br/> Or visit the<a href='help.php' target='_blank'> Q&A </a>page</div>";
		}
		print("<div class='topChart'>");
		print("Note: a song is a 'hit' if it has ever been in the top 40 of any of these charts:<br/>");
		print("<a target='none' href='http://www.billboard.com/charts/hot-100'>Billboard Hot 100</a><br/>");
		print("<a target='none' href='http://www.billboard.com/charts/canadian-hot-100'>Billboard Canadian Hot 100</a><br/>");
		print("<a target='none' href='http://www.billboard.com/charts/country-songs'>Billboard Hot Country</a>");
		print("</div></div>");
		print("<br/><br/><br/>");
	}

echo "
<div id='cancon'>
<span class='stars'></span>
<b>Cancon 2:</b> <!--<span id='CCType2Num'>0</span> /<span id='Type2Total'>0</span> =-->  <span id='CCType2Ratio' class='compliance'>0%</span>  (min 35%)
 

<b>Cancon 3:</b><!--<span id='CCType3Num'>0 </span> / <span id='Type3Total'>0</span> =-->   <span id='CCType3Ratio' class='compliance'>0%</span>  (min 12%) 
 
 
<b>Hits:</b><!--<span id='hitNum'>0</span> / <span id='total'>0</span> =--> <span id='hitRatio' class='compliance'>0%</span>  (max 10%) 

<b>Femcon:</b><span id='femRatio' class='compliance'>0%</span>   (min 35%) 

<b>Playlist/New:</b><span id='plRatio' class='compliance'>0%</span>  (min 15%)

<span class='stars'></span>
</div>";


}
?>



<!--
<div id="submitForm">SUBMIT FORM<div>
-->
<div id='autosave'></div>
<div id="debug">debug:</div>

<?php

if($_GET['action']!='list')
{
require_once('playsheet-ajax.php'); 
}
?>




</table>


<div id='highlightoverlay'></div>

<script type="text/javascript" src="./js/playsheet-functions.js"></script> 
<script type="text/javascript" src="./js/playsheet-setup.js"></script> 
<script type="text/javascript" src="./js/playsheet-initialize.js"> </script>
<script type="text/javascript">
var enabled = {};
enabled = <?php echo json_encode($enabled);?>;
</script>
</body>

<?php
//close db connection to citr db

//$db->close();

?>
</html>