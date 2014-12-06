<?php

session_start();
require("headers/security_header.php");
require("headers/function_header.php");
require("headers/menu_header.php");

function fieldComplete($arr, $curr) {
	// status codes
	// -1: not started
	// 0: currently all fields unset
	// 1: currently all fields set with whitespace
	// 2: currently all fields set with content
	// 3: error
	$status = -1;
	foreach ($arr as $value) {
		if (isset($_POST[$value.$curr])) {
			if ($status == 0) {
				$status = 3;
				break;
			}
			if ($_POST[$value.$curr] == "") { // -1,1,2
				if ($status == 2) { // -1,1
					$status = 3;
					break;
				}
				else {
					$status = 1;
				}
			}
			else {
				if ($status == 1) { // -1, 2
					$status = 3;
					break;
				}
				else {
					$status = 2;
				}
			}
		}
		else {
			if($status == 1 || $status == 2) {
				$status = 3;
				break;
			}
			else {
				$status = 0;
			}
		}
	}
	return $status;
}
function processFields($arr) {
	$count = 1;
	$end = false;
	$o_arr = array();
	while (!$end) {
		$status = fieldComplete($arr, $count);
		switch($status) {
			case 2: // Fields in row complete w/ content
				foreach ($arr as $value) {
					$o_arr[$count][$value]=fas($_POST[$value.$count]);
				}
				$count += 1;
				break;
			case 1: // Fields in row all empty string
				$count += 1;
				break;
			case 0: // Fields in row all unset
				$end = true; // Exit loop
				break;
			default: // Error
				$o_arr = -1;
				$end = true;
		}
	}
	return $o_arr;
}

// Used to populate time schedule
$alt_val = array(0=>"None",1=>"Week 1",2=>"Week 2");

// Option generating code
$str_hour = "";
for($i=0; $i <= 23; $i++) { // Generates hours
	$str_hour .= sprintf("<option>%02d</option>", $i);
}
$str_min = "<option>00</option><option>30</option>";
$str_dow = "";
foreach($dow as $key_name => $var_name) { // Generates days of week
		$str_dow .= sprintf("<option value=\"%s\">%s</option>", $key_name, $var_name);
}

// Echos HTML head
echo "<html><head><meta name=ROBOTS content=\"NOINDEX, NOFOLLOW\">
<link rel=\"stylesheet\" href=\"css/style.css\" type=\"text/css\">
<title>DJ LAND | Shows</title>";
if (!(isset($_GET['action']) && ($_GET['action'] == 'edit'||$_GET['action'] == 'add'))) {
	echo "</head><body>";
	print_menu2();
}

// -------- POST handling code ---------------------------------

if(is_member("addshow")) {
	
	// DELETING SHOWS --------
	if(isset($_GET['action']) && $_GET['action'] == "delete") {
		echo "<center><h1>Show Deleted</h1>";

		if(isset($_POST['id']) && $_POST['id']) {
			$ed = $_POST['id'];
		}
		else {
			$ed = 0;
		}
		mysqli_query($db, "DELETE FROM `playitems` WHERE show_id='$ed'");
		mysqli_query($db, "DELETE FROM `playlists` WHERE show_id='$ed'");
		mysqli_query($db, "DELETE FROM `shows` WHERE id='$ed'");
		mysqli_query($db, "DELETE FROM `social` WHERE show_id='$ed'");
		mysqli_query($db, "DELETE FROM `show_times` WHERE show_id='$ed'");
	}
	// SUBMITTING SHOWS -------
	else if(isset($_GET['action']) && $_GET['action'] == "submit") {
		echo "<center><h1>Show Added</h1>";

		$show_name = fas($_POST['showtitle']);
		$host_id = fget_id(fas($_POST['host']), "hosts", true);
		$weekday = fas($_POST['weekday']);
		$create_name = get_username();
		$create_date = date('Y-m-d H:i:s');
		$edit_name = get_username();
		$start_time = fas($_POST['pl_date_hour'] . ":" . $_POST['pl_date_min'] . ":" . "00");
		$end_time = fas($_POST['end_date_hour'] . ":" . $_POST['end_date_min'] . ":" . "00");
		$active = (isset($_POST['c_active'])&&$_POST['c_active']==1) ? 1:0;
		$crtc_default = $_POST['r_crtc_default'] == '20' ? 20 : 30;
		$lang_default = fas($_POST['t_lang_default']);
		$website = fas($_POST['t_website']);
		$rss = fas($_POST['t_rss']);
		$genre = fas($_POST['t_genre']);
		$show_desc = fas($_POST['t_show_desc']);
		echo '<hr/> results<br/>';
		$notes = fas($_POST['t_notes']);
		$show_img = fas($_POST['t_show_img']);
		$sponsor_name = fas($_POST['t_sponsor_name']);
		$sponsor_url = fas($_POST['t_sponsor_url']);
		
		$times = processFields(array("sd","sh","sm","ed","eh","em","alt"));
		$socials = processFields(array("socialName","socialURL"));

		$p_xml = fas($_POST['t_xml']);
		$p_subtitle = fas($_POST['t_subtitle']);
		$p_description = fas($_POST['t_description']);
		$p_keywords = fas($_POST['t_keywords']);
		$p_link = fas($_POST['t_link']);
		$p_image = fas($_POST['t_podcast_img']);
		$podcast_channel_id = fas($_POST['t_pod_id']);
		
		if(isset($_POST['id']) && $_POST['id']) {
			$ed = $_POST['id'];
		}
		else {

			$insert_channel_q = "INSERT INTO `podcast_channels` (id) VALUES (NULL)";
			if (mysqli_query($db,$insert_channel_q) ) echo "podcast channel created <br/>";
				else echo "there was an error creating the podcast channel <br/>";
//			echo "inserted: ".$insert_q;
			$podcast_channel_id = mysqli_insert_id($db);

			$insert_q = "INSERT INTO `shows` (id, create_date, create_name, podcast_channel_id) VALUES (NULL, '$create_date', '$create_name', '$podcast_channel_id')";
			if (mysqli_query($db,$insert_q) ) echo "show created <br/>";
				else echo "there was an error creating the show <br/>";
//			echo "inserted: ".$insert_q;
			$ed = mysqli_insert_id($db);

		}
		
		if ($times == -1) { // Error has occured when processing time fields
			echo '<p style="color:red">ERROR: Time fields incomplete (Not Saved)</p>';
		}
		else {
			mysqli_query($db,"DELETE FROM `show_times` WHERE show_id=$ed");
			foreach ($times as $s_arr) {

				$sd = $s_arr['sd'];
				$st = $s_arr['sh'].":".$s_arr['sm'].":00";
				$endd = $s_arr['ed'];
				$et = $s_arr['eh'].":".$s_arr['em'].":00";
				$alt = $s_arr['alt'];
				mysqli_query($db,"INSERT INTO `show_times` (show_id, start_day, start_time, end_day, end_time, alternating) VALUES ($ed, '$sd', '$st', '$endd', '$et', '$alt')");
			}
		}
		if ($socials == -1) { // Error has occured when processing social fields
			echo '<p style="color:red">ERROR: Social fields incomplete (Not Saved)</p>';
		}
		else {
			mysqli_query($db,"DELETE FROM `social` WHERE show_id=$ed");
			foreach ($socials as $key => $s_arr) {
				$name = $s_arr['socialName'];
				$url = $s_arr['socialURL'];
				if (isset($_POST['socialShortName'.$key])) {
					$sn = $_POST['socialShortName'.$key];
				}
				else {
					$sn = "";
				}
				if (isset($_POST['unlink'.$key]) && $_POST['unlink'.$key] == 1) {
					$unlink = 1;
				}
				else {
					$unlink = 0;
				}
				mysqli_query($db,"INSERT INTO `social` (show_id, social_name, social_url, short_name, unlink) VALUES ($ed, '$name', '$url', '$sn', $unlink)");
			}
		}
		if (!$weekday) $weekday = 0;
		$update_q = "UPDATE `shows` SET name='$show_name', host_id='$host_id', weekday='$weekday', edit_name='$edit_name', crtc_default=$crtc_default, lang_default='$lang_default', active=$active, genre='$genre', website='$website', rss='$rss', show_desc='$show_desc', notes='$notes', show_img='$show_img', sponsor_name='$sponsor_name', sponsor_url='$sponsor_url' WHERE id='$ed'";
		
		$update_podcast_q = "UPDATE `podcast_channels` SET subtitle='$p_subtitle', summary='$p_description', keywords='$p_keywords', link='$p_link', image_url='$p_image', xml='$p_xml' WHERE id='$podcast_channel_id'";
		
		if( mysqli_query($db, $update_q) ) {
			echo "show successfully edited<br/>";
			write_new_showlist_file();
		} else {
			echo "error updating show info<br/>";
		}

		if( mysqli_query($db, $update_podcast_q)){

			if (mysqli_affected_rows($db) >= 1){
				echo "show podcast channel successfully edited<br/>";
			} else {
				echo 'did not edit a podcast channel';
			}
		} else {
			echo "error updating podcast channel.<br/>query is ".$update_podcast_q;
		}
//		echo "updated: $update_q </center>";

		require_once('headers/showlib.php');
		
		$showlib = new Showlib($db);
		
		
	}




	// ADD OR EDIT SHOWS -------
	// ADD OR EDIT SHOWS -------
	// ADD OR EDIT SHOWS -------
	// ADD OR EDIT SHOWS -------
	// ADD OR EDIT SHOWS -------
	// ADD OR EDIT SHOWS -------
	// ADD OR EDIT SHOWS -------
	// ADD OR EDIT SHOWS -------
	// ADD OR EDIT SHOWS -------
	// ADD OR EDIT SHOWS -------
	// ADD OR EDIT SHOWS -------
	// ADD OR EDIT SHOWS -------


	else if(isset($_GET['action']) && ($_GET['action'] == 'edit'||$_GET['action'] == 'add')) {
		if($_GET['action'] == 'edit') {
			$ed = fas($_GET['id']);

			$result = mysqli_query($db,"SELECT *,HOUR(end_time) AS end_hour, MINUTE(end_time) AS end_min, HOUR(start_time) AS start_hour, MINUTE(start_time) AS start_min FROM shows WHERE id='$ed'");
			
			$show_data = mysqli_fetch_array($result); 


		}
		else {
			$ed = 0;
		}

		$times = mysqli_query($db,"SELECT *, HOUR(start_time) AS sh, MINUTE(start_time) AS sm, HOUR(end_time) AS eh, MINUTE(end_time) AS em FROM `show_times` WHERE show_id=$ed");
		$timeRows = mysqli_num_rows($times);
		$socials = mysqli_query($db,"SELECT * FROM `social` WHERE show_id=$ed");
		$socialRows = mysqli_num_rows($socials);

//		$show_name = $ed ? mysqli_result_dep($result, 0, "name") : "";
		$show_name = $ed ? $show_data["name"] : "";
		$host_name = $ed ? $fhost_name[$show_data["host_id"]] : "";
		$weekday = $ed ? $show_data["weekday"] : date('w');
//		echo "weekday is ".$weekday;
		$start_hour = $ed ? $show_data["start_hour"] : date('H');
		$start_min = $ed ? $show_data["start_min"] : date('i');
		$end_hour = $ed ? $show_data["end_hour"] : date('H');
		$end_min = $ed ? $show_data["end_min"] : date('i');
		$active = $ed ? $show_data["active"] : 1;
		$crtc_num = $ed ? $show_data["crtc_default"] : "";
		$crtc_default = $crtc_num == 20 ? 20 : 30;
		$lang_default = $ed ? $show_data["lang_default"] : "";
		$genre = ($ed && !is_null($show_data["genre"])) ? $show_data["genre"] : "";
		$website = ($ed && !is_null($show_data["website"])) ? $show_data["website"] : "";
		$rss = ($ed && !is_null($show_data["rss"])) ? $show_data["rss"] : "";
		$show_desc = ($ed && !is_null($show_data["show_desc"])) ? $show_data["show_desc"] : "";
		$sponsor_name = ($ed && !is_null($show_data["sponsor_name"])) ? $show_data["sponsor_name"] : "";
		$sponsor_url = ($ed && !is_null($show_data["sponsor_url"])) ? $show_data["sponsor_url"] : "";
		$notes = ($ed && !is_null($show_data["notes"])) ? $show_data["notes"] : "";
		$show_img = ($ed && !is_null($show_data["show_img"])) ? $show_data["show_img"] : "";

		$podcast_channel_id = $ed ? $show_data['podcast_channel_id'] : "";
		


			$podcast_query = 'SELECT * from podcast_channels where id = "'.$podcast_channel_id.'";';
			
			if ($podcast_result = mysqli_query($db, $podcast_query)){

				$podcast_data = mysqli_fetch_array($podcast_result);
				print_r($podcast_data);
			} else {

			}


		$p_xml = $podcast_channel_id ? $podcast_data['xml'] : "" ;
		$p_subtitle = $podcast_channel_id ? $podcast_data['subtitle'] : "" ;
		$p_description = $podcast_channel_id ? $podcast_data['summary'] : "" ;
		$p_keywords= $podcast_channel_id ? $podcast_data['keywords'] : "" ;
		$p_link = $podcast_channel_id ? $podcast_data['link'] : "" ;
		$p_image = $podcast_channel_id ? $podcast_data['image_url'] : "" ;

		$p_episode_default_title = $podcast_channel_id ? $podcast_data['episode_default_title'] : "" ;
		$p_episode_default_subtitle = $podcast_channel_id ? $podcast_data['episode_default_subtitle'] : "" ;
		$p_episode_default_author = $podcast_channel_id ? $podcast_data['episode_default_author'] : "CiTR 101.9fm" ;

		// Special HTML head (for javascript functions)
		$weeks_elapsed = floor((time() - 1341100800)/(7*24*60*60));
		$week_num = ($weeks_elapsed%2) + 1;
		echo '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>';
		echo '<script type="text/javascript">';

		// Schedule Functions
		if ($timeRows > 0) {
			echo "timeRows=$timeRows;";
		}
		else {
			echo "timeRows=1;";
		}
		echo "str_dow = '$str_dow';";
		echo "str_hour = '$str_hour';";
		echo "str_min = '$str_min';";
		echo 'function addTimeRow() {var row = $("#time"+timeRows);$("#time"+timeRows+" .controls").css("display", "none");timeRows += 1;row.after(genTimeRow(timeRows));}';
		echo 'function minusTimeRow() {$("#time"+timeRows).remove();timeRows -= 1;$("#time"+timeRows+" .controls").css("display", "inline")}';
		echo "function genTimeRow(num) {
		var output = '<div id=\"time'+num+'\">'+num+'. ';
		// Start
		output += '<select name=\"sd'+num+'\">'+str_dow+'</select>';
		output += '<span><select name=\"sh'+num+'\">'+str_hour+'</select>:<select name=\"sm'+num+'\">'+str_min+'</select></span>';
		// Spacing
		output += '<span></span>';
		// End
		output += '<select name=\"ed'+num+'\">'+str_dow+'</select>';
		output += '<span><select name=\"eh'+num+'\">'+str_hour+'</select>:<select name=\"em'+num+'\">'+str_min+'</select></span>';
		// Alternating
		output += '<span></span><select name=\"alt'+num+'\"><option value=\"0\">None</option><option value=\"1\">Week 1</option><option value=\"2\">Week 2</option></select>';
		// Buttons
		output += '<span class=\"controls\"><button class=\"minus\" type=\"button\" onclick=\"minusTimeRow()\">-</button><button class=\"plus\" type=\"button\" onclick=\"addTimeRow()\">+</button></span></div>';
		return output;}";
		
		// Social Functions
		if ($socialRows > 0) {
			echo "socialRows=$socialRows;";
		}
		else {
			echo "socialRows=1;";
		}
		echo 'function addSocialRow() {var row = $("#social"+socialRows);$("#social"+socialRows+" .controls").css("display", "none");socialRows += 1;row.after(genSocialRow(socialRows));}';
		echo 'function minusSocialRow() {$("#social"+socialRows).remove();socialRows -= 1;$("#social"+socialRows+" .controls").css("display", "inline")}';
		echo "function genSocialRow(num) {
		var output = '<div id=\"social'+num+'\"><span>'+num+'. ';
		// Start
		output += '<input type=\"text\" name=\"socialName'+num+'\" maxlength=\"90\" size=\"10\" /></span>';
		output += '<span><input type=\"text\" name=\"socialURL'+num+'\" maxlength=\"190\" size=\"40\" /></span>';
		output += '<span><input type=\"text\" name=\"socialShortName'+num+'\" maxlength=\"90\" size=\"10\" /></span>';
		output += '<span><input type=\"checkbox\" name=\"unlink'+num+'\" value=\"1\" /></span>';
		// Buttons
		output += '<span class=\"controls\"><button class=\"minus\" type=\"button\" onclick=\"minusSocialRow()\">-</button><button class=\"plus\" type=\"button\" onclick=\"addSocialRow()\">+</button></span></div>';
		return output;}";
		echo '</script>';
		echo "</head><body>";
		print_menu2();
		// End of head

		printf("<br><div class=\"editform\"><h1>%s Show</h1>", ($ed ? "Edit" : "Add New"));

		echo "<br><FORM METHOD=\"POST\" ACTION=\"{$_SERVER['SCRIPT_NAME']}?action=submit\" name=\"the_form\">\n";
		if($ed) {
			echo "<INPUT type=hidden name=id value=$ed>";
		}
		// Start of table section
		echo "<div class='table'>";
		printf("<p><span>Show Title: </span><input name='showtitle' type='text' size=35 value=\"%s\"></p>", $show_name);
		printf("<p><span>Host/Op: </span><input name='host' type=text size=35 value=\"%s\"></p>", $host_name);
		printf("<p><span>Genre: </span><input name=\"t_genre\" type=\"text\" maxlength=\"255\" size=\"35\" value=\"%s\"></p>", $genre);
		printf("<p><span>Show Description: </span><textarea name=\"t_show_desc\" cols=\"40\" rows=\"6\">%s</textarea></p>", $show_desc);
		printf("<br/><p><span>Show Image URL: </span><input name=\"t_show_img\" type=\"text\" maxlength=\"255\" size=\"35\" value=\"%s\"></p>", $show_img);
		printf("<p><span>Website: </span><input name=\"t_website\" type=\"text\" maxlength=\"255\" size=\"35\" value=\"%s\"></p>", $website);
		printf("<p><span>Language: </span><input name=\"t_lang_default\" type='text' size='35' value=\"%s\"></p>", $lang_default);
		
		




		echo "<p><span>CRTC Default: </span>20<input name=\"r_crtc_default\" type='radio' value=\"20\" ".($crtc_num == 20 ? "checked='checked'" : "")." /> 30<input name=\"r_crtc_default\" type='radio' value=\"30\" ".($crtc_num == 30 ? "checked='checked'" : "")." /></p>";
		echo "<p><span></span><span style=\"font-size:0.77em\">(Eg. sponsor1; sponsor2 - put sponsors with no links at the end)</span>";
		printf("<p><span>Sponsor Name(s): </span><input name=\"t_sponsor_name\" type=\"text\" maxlength=\"255\" size=\"35\" value=\"%s\"></p>", $sponsor_name);
		echo "<p><span></span><span style=\"font-size:0.77em\">(Eg. url1; url2 - separate with semicolons)</span>";
		printf("<p><span>Sponsor Url: </span><input name=\"t_sponsor_url\" type=\"text\" maxlength=\"255\" size=\"35\" value=\"%s\"></p>", $sponsor_url);

		/*printf("<p><span>Weekday: </span><select name=\"weekday\">");
		if ($ed) printf("<option value=\"%s\">%s</option>", $weekday, $dow[$weekday]);
		echo $str_dow;
		echo "</select></p>
		<p><span>Start Time: </span>[";
		printf("<SELECT NAME=pl_date_hour>\n<option>%02d</option>", $start_hour);
		echo $str_hour;
		printf("</SELECT>:");
		printf("<SELECT NAME=pl_date_min>\n<option>%02d</option>", $start_min);
		echo $str_min;
		printf("</SELECT>]</p>");

		printf("<p><span>End Time: </span>[");
		printf("<SELECT NAME=end_date_hour>\n<option>%02d</option>", $end_hour);
		echo $str_hour; 
		printf("</SELECT>:");
		printf("<SELECT NAME=end_date_min>\n<option>%02d</option>", $end_min);
		echo $str_min;
		printf("</SELECT>]</p>");*/
		if ($active == 1) {
			echo "<p><span>Active: </span><input type='checkbox' name='c_active' value='1' checked=\"checked\" /></p>";
		}
		else {
			echo "<p><span>Active: </span><input type='checkbox' name='c_active' value='1' /></p>";
		}
		echo "</div>";




		echo '<div id=show_edit_podcast >';

		echo "<p><span>Feedburner: </span><input name='t_rss' type='text' maxlength='255' size='80' value='{$rss}'></p>";
		echo "<p><span>Local XML: </span><input name='t_xml' type='text' maxlength='255' size='80' value='{$p_xml}'></p>";
		echo "<p><span>Keywords: </span><input name='t_keywords' type='text' maxlength='255' size='80' value='{$p_keywords}'></p>";
		echo "<p><span>Subtitle: </span><input name='t_subtitle' type='text' maxlength='255' size='80' value='{$p_subtitle}'></p>";
		echo "<p><span>Summary: </span><br/><textarea name='t_description' type='text' maxlength='255' rows='15' cols='40' value=''>{$p_description}</textarea></p>";
		echo "<p><span>Link: </span><input name='t_link' type='text' maxlength='255' size='80' value='{$p_link}'></p>";
		
		echo "<p><span>channel id: <input name='t_pod_id' readonly value ='{$podcast_channel_id}'></p>";
		

		/*
		if(isset($podcast_channel_id)){

			$pod_query = 'SELECT * from podcast_channels where id = "'.$podcast_channel_id.'";';
			if ($pod_result = mysqli_query($db, $pod_query) ){
				
				$podcast_info = mysqli_fetch_array($pod_result);

				echo "<p><span>Subtitle: </span><input name='p_subtitle' type='text' maxlength='255' size='35' value='subtitle'></p>";
		
			}  else {}

		} else {

				echo 'no podcast found';
				
				//error
			}
*/

		?>
<!--
		<pre>

		<span class='custom_button' id='write_rss'>write rss file</span>

		podcast url:

		</pre>

		<script type='text/javascript'>

		$('#write_rss').click(function(){
				$.ajax('podcasting/writexml.php', {

				});
		});
		</script>
		-->
		<?php

echo '</div>';


		
		// Times section
		echo "<p style=\"text-decoration:underline\">Times (current week is Week $week_num):</p>";
		echo '<div class="table">
		<span class="head">Start Day</span><span class="head">Start Time</span><span class="head">&nbsp;&nbsp;&nbsp;</span><span class="head">End Day</span><span class="head">End Time</span><span class="head">&nbsp;&nbsp;&nbsp;</span><span class="head">Alternating</span>';
		
		$count = 1;
		while($row = mysqli_fetch_assoc($times)) {
			echo "<div id=\"time$count\">$count. <select name=\"sd$count\"><option value=\"{$row['start_day']}\">{$dow[$row['start_day']]}</option>$str_dow</select>&nbsp;";//Start of table div
			echo "<span><select name=\"sh$count\">",sprintf("<option>%02d</option>",$row['sh']),"$str_hour</select>:<select name=\"sm$count\">",sprintf("<option>%02d</option>",$row['sm']),"$str_min</select></span>";
			echo "<span></span>";// Spacing
			echo "<select name=\"ed$count\"><option value=\"{$row['end_day']}\">{$dow[$row['end_day']]}</option>$str_dow</select>&nbsp;";//End times
			echo "<span><select name=\"eh$count\">",sprintf("<option>%02d</option>",$row['eh']),"$str_hour</select>:<select name=\"em$count\">",sprintf("<option>%02d</option>",$row['em']),"$str_min</select></span>";
			echo "<span></span>";// Spacing
			echo "<select name=\"alt$count\"><option value=\"{$row['alternating']}\">{$alt_val[$row['alternating']]}</option><option value=\"0\">None</option><option value=\"1\">Week 1</option><option value=\"2\">Week 2</option></select>"; // Alternating
			if ($count == $timeRows) {
				echo '<span class="controls">';
			}
			else {
				echo '<span class="controls" style="display:none">';
			}
			if ($count > 1) { // Echo minus button if more than one row
				echo '<button class="minus" type="button" onclick="minusTimeRow()">-</button>';
			}
			echo '<button class="plus" type="button" onclick="addTimeRow()">+</button></span></div>'; // Buttons
			$count++;
		}
		
		if ($timeRows == 0) {
			echo "<div id=\"time1\">1. <select name=\"sd1\"><option value=\"",date("w"),"\">",$dow[date("w")],"</option>$str_dow</select>&nbsp;";//Start of table div
			echo "<span><select name=\"sh1\">",sprintf("<option>%02d</option>",date("H")),"$str_hour</select>:<select name=\"sm1\">$str_min</select></span>";
			echo "<span></span>";// Spacing
			echo "<select name=\"ed1\"><option value=\"",date("w"),"\">",$dow[date("w")],"</option>$str_dow</select>&nbsp;";//End times
			echo "<span><select name=\"eh1\">",sprintf("<option>%02d</option>",date("H")),"$str_hour</select>:<select name=\"em1\">$str_min</select></span>";
			echo "<span></span>";// Spacing
			echo '<select name="alt1"><option value="0">None</option><option value="1">Week 1</option><option value="2">Week 2</option></select>'; // Alternating
			echo '<span class="controls"><button class="plus" type="button" onclick="addTimeRow()">+</button></span></div>'; // Buttons
		}
		echo "</div>";
		
		// Social section
		echo '<p style="text-decoration:underline">Social + Contact Info</p>';
		echo '<div class="table">
		<span class="head">Service Name</span><span class="head">Address (include http/https if not email)</span><span class="head">Short Name</span><span class="head">Unlink</span>'; //start of table div
		$count = 1;
		while ($row = mysqli_fetch_assoc($socials)) { // Generate social rows
			echo "<div id=\"social$count\"><span>$count. <input type=\"text\" name=\"socialName$count\" value=\"{$row['social_name']}\" maxlength=\"90\" size=\"10\" /></span>";
			echo "<span><input type=\"text\" name=\"socialURL$count\" value=\"{$row['social_url']}\" maxlength=\"190\" size=\"40\" /></span>";
			echo "<span><input type=\"text\" name=\"socialShortName$count\" value=\"{$row['short_name']}\" maxlength=\"90\" size=\"10\" /></span>";
			if ($row['unlink'] == 1) {
				echo "<span><input type='checkbox' name='unlink$count' checked='checked' value='1' /></span>";
			}
			else {
				echo "<span><input type='checkbox' name='unlink$count' value='1' /></span>";
			}
			if ($count == $socialRows) {
				echo '<span class="controls">';
			}
			else {
				echo '<span class="controls" style="display:none">';
			}
			if ($count > 1) { // Echo minus button if more than one row
				echo '<button class="minus" type="button" onclick="minusSocialRow()">-</button>';
			}
			echo '<button class="plus" type="button" onclick="addSocialRow()">+</button></span></div>';
			$count++;
		}
		
		if ($socialRows == 0) { // Generate fields if no database entry
			echo "<div id=\"social1\"><span>1. <input type=\"text\" name=\"socialName1\" maxlength=\"90\" size=\"10\" /></span>";
			echo "<span><input type=\"text\" name=\"socialURL1\" maxlength=\"190\" size=\"40\" /></span>";
			echo "<span><input type=\"text\" name=\"socialShortName1\" maxlength=\"90\" size=\"10\" /></span>";
			echo "<span><input type='checkbox' name='unlink1' value='1' /></span>";
			echo '<span class="controls">';
			echo '<button class="plus" type="button" onclick="addSocialRow()">+</button></span></div>';
		}
		echo "</div>";
		
		printf("<p>Notes:</p><textarea name=\"t_notes\" cols=\"78\" rows=\"14\">%s</textarea>", $notes);
		echo "<br><p style=\"float:left\"><input type=submit value=\"Save Show\"></p>
		</form>";

		if($ed) {
			printf("<FORM METHOD=\"POST\" ONSUBMIT=\"return confirm('PERMANENTLY DELETE this show and associated playsheets?')\" ACTION=\"%s?action=delete\" name=\"the_form\">\n", $_SERVER['SCRIPT_NAME']);
			printf("<p style=\"float:right\"><INPUT type=hidden name=id value=%s>", $ed);
			echo "warning: deleting a show will delete all of the show's playsheets ever made";
			printf("<input type=submit value=\"Delete Show\"></p>");
			printf("</form>");
		}
		echo "</div>";
	}
	// LISTING INACTIVE SHOWS --------
	else if(isset($_GET['action']) && $_GET['action'] == 'list' ) {
	?>
		<div class=buttonContainer>
			<div class=nav>
				<ul>
					<li><a href=?action=add>Add New Show</a></li>
				</ul>
			</div>
		</div>
		<div class=buttonContainer>
			<div class=nav>
				<ul>
					<li><a href=shows.php>Hide Inactive</a></li>
				</ul>
			</div>
		</div>
	<?php 
		echo "<CENTER><FORM METHOD=\"GET\" ACTION=\"{$_SERVER['SCRIPT_NAME']}\" name=\"the_form\">\n
		<INPUT type=hidden name=action value=edit>";
	?>	
	<h2>All Shows:</h2>
	<select name='id' size=20>
	<?php 
			$query = "SELECT id,name FROM shows ORDER BY name";
		if($result = $db->query($query)){
			while($row = mysqli_fetch_array($result)){
				echo "<option value='".$row[id]."'>".$row[name]."</option>";
			}
		}
	?>
	</select>
	<BR/>
	<input type=submit value="Edit Show">
		</FORM></CENTER>
	<?php

	}
	// DEFAULT ACTION: LISTING ONLY ACTIVE SHOWS --------
	else {
	//	echo "<br><table class=menu border=0 align=center><tr>
	//	<td class=menu><a href=\"?action=add\">&nbsp;Add New Show&nbsp;</a></td></tr><tr><td class=\"menu\"><a href=\"?action=listi\">&nbsp;Show Inactive Shows&nbsp;</a></td></tr></table>";
		?>
		<div class=buttonContainer>
					<div class=nav>
						<ul>
							<li><a href=?action=add>Add New Show</a></li>
						</ul>
					</div>
				</div>
				<div class=buttonContainer>
					<div class=nav>
						<ul>
							<li><a href=?action=list>Show Inactive Shows</a></li>
						</ul>
					</div>
				</div>
		<?php


		echo "<CENTER><FORM METHOD=\"GET\" ACTION=\"{$_SERVER['SCRIPT_NAME']}\" name=\"the_form\">\n
		<INPUT type=hidden name=action value=edit>
		<h2>Active shows:</h2>
		<SELECT NAME=\"id\" SIZE=20>\n";

		$result = mysqli_query($db,"SELECT * FROM shows WHERE active=1 ORDER BY name");
		$num_rows = mysqli_num_rows($result);
		$count = 0;
		while($count < $num_rows) {
			printf("<OPTION VALUE=\"%s\">%s\n", mysqli_result_dep($result,$count,"id"), $fshow_name[mysqli_result_dep($result,$count,"id")]);
			$count++;
		}
		echo "</SELECT><BR><INPUT TYPE=submit VALUE=\"Edit Show\">\n
		</FORM></CENTER>\n";

	}
}
echo "</body></html>";



function write_new_showlist_file(){

	$showlist = " ";
	for($count = 0; $count < 3; $count++){
		$showList = file_get_contents('http://djland.citr.ca/showlist-handler.php');
		if(strlen($showList) > 1000){
			file_put_contents ('static/theShowList.html' , $showList, LOCK_EX );
			sleep(5);
			break;
		}
	}
	if(strlen($showList) < 1000){
		echo "<br/>Error updating the show list <br/>";
	}
	else{
		echo "<br/>Show list successfully updated <br/>";
	}
}
?>
