<?php // CRTC REPORT REQUEST HANDLER

//***************************************************
//***********      REQUIREMENTS       ***************
//***************************************************

$cc_reg_req = 35; // mysqli_result($result,$count,"cc_req");
$cc_spec_req = 12;
$cc_spec_ethnic_req = 7;
$pl_req = 60;// mysqli_result($result,$count,"pl_req");
$fe_req = 35;// mysqli_result($result,$count,"fem_req");
$inst_req = 35;// mysqli_result($result,$count,"fem_req");
$hit_req = 10;// mysqli_result($result,$count,"fem_req");
$SOCAN_FLAG;
//***************************************************
//***************************************************
//***************************************************

require("../headers/db_header.php");
require("../headers/function_header.php");
require("../headers/showlib.php");
require("../adLib.php");
require("../headers/socan_header.php");
$showlib = new Showlib($db);
$adLib = new AdLib($mysqli_sam,$db);
$SOCAN_FLAG =socanCheck($db);
// CRTC Broadcast Day hours - 6:00am to midnight
if(isset($_POST['min_time']) && isset($_POST['max_time'])){
$min_time = $_POST['min_time'];
$max_time = $_POST['max_time'];
} else { // uncomment when know post loading works
// $min_time = 6;
// $max_time = 24;
}


if(isset($_POST['from'])){
$from = $_POST['from'];
$to = $_POST['to'] ;
}
$from = strtotime($from);
$to = strtotime($to)+ 24*60*60; //add one day to make the request include last day in range


$samFrom = date("Y-m-d H:i:s", $from);
$samTo = date("Y-m-d H:i:s", $to);  

$samPlays = array();
$adsLogged = array();

// see headers/function_header.php
// available fields: id, show_id, start_time, end_time, unix_time
$showList = grabPlaylists($from,$to, $db);
$plays = grabPlayitems($from,$to, $db);

// filter out shows that didn't air during broadcast day hours
// make two windows out of max and min to represent nighttime non-broadcast day hours
if ($max_time <= 24){
$min_first = 0;
$max_first = $min_time;

$min_second = $max_time;
$max_second = 24;
} else {
$min_first = $max_time - 24;
$max_first = $min_time;
$min_second = 24;
$max_second = 24;
}

$temp = array();
foreach($showList as $i => $v){
	$thisStartHr = explode(' ',$v['start_time']);
	$thisStartHr = explode(':',$thisStartHr[1]);
	$thisStartHr = $thisStartHr[0];
	
	$end_array = explode(':',$v['end_time']);
	$thisEndHr = $end_array[0];
	$thisEndMin = $end_array[1];
	
		if (	(	($thisStartHr >= $min_first)&&($thisStartHr <= $max_first)
				||	($thisStartHr >= $min_second)&&($thisStartHr <= $max_second)
				)&&
				(	($thisEndHr >= $min_first)&&($thisEndHr <= $max_first)
				||	($thisEndHr >= $min_second)&&($thisEndHr <= $max_second)
				)
			){ 	// both the start time and end time of the show fall inside 
				// a "non-broadcast day" period (so do not keep)
			} else {
			$temp []= $v;
			}
}
$showList = $temp;

//find minimum / earliest playsheet id (and latest)

$min = $showList[0]['id'];
$last = array_slice($showList,-1);
$max = $last[0]['id'];

// GRAB ADS THAT CORRESPOND WITH PLAYLIST RANGE
$query = "SELECT playsheet_id, name, played, sam_id FROM adlog WHERE playsheet_id >= '$min' AND playsheet_id <= '$max' AND LEFT(type,2) = 'AD'  ORDER BY playsheet_id ASC";

if( $result = $db->query($query)){
	while($row = $result->fetch_assoc()){
		$thisID = $row['playsheet_id'];
		$adsLogged[$thisID] []=$row;		
	}
} else {
echo "citr database problem :(";	
}

// TODO: implement this helper function also
// $adsLogged = grabAds(...)


$query = "SELECT songID, artist, title, date_played, duration, songtype FROM historylist WHERE date_played >= '$samFrom' AND date_played <= '$samTo' AND (songtype = 'A' OR songtype = 'I') ORDER BY date_played ASC";

if( $result = $mysqli_sam->query($query)){
	while($row = $result->fetch_assoc()){
		
		$row['date_unix'] = strtotime($row['date_played']); 
		$samPlays []=$row;		
	}
} else {
echo "SAM database problem :(";	
}

// sample sam play:
/* Array ( [songID] => 67917 [artist] => SLED [title] => June 22 2013 [date_played] => 2013-05-29 19:10:44 [date_unix] => 1369879844 ) 
*/
$totalSpokenWord = 0;

foreach($showList as $i => $v){		
		$thisSpokenWord = $v['spokenword_duration'];
		$totalSpokenWord = $totalSpokenWord + $thisSpokenWord;
}



echo "<h2><font color=black>CRTC Report - CiTR 101.9fm Vancouver, BC, Canada - citr.ca</font></h2>";
echo "<div id='report-summary'>";
echo "<h2>Summary</h2>";
echo "	from: ".date("D, F jS, Y",$from)."<br/>
		to: ".date("D, F jS, Y",$to-1)."<br/>";
echo "Broadcast hours: ".$min_time.":00 to ".$max_time.":00";
echo "<br/>";

echo "Total spoken word (cat 12): ".floor($totalSpokenWord/60)." hours and ".($totalSpokenWord%60)." minutes.";

$total_items = count($plays);
$total_reg = 0;
$total_spec = 0;
$total_pl = 0;
$total_cc_reg = 0;
$total_cc_spec = 0;
$total_fe = 0;
$total_inst = 0;
$total_hit = 0;

foreach($plays as $i => $play){
			// for each show, no need to load the show's CRTC requirements
	$show_id = mysqli_result($result,$count,"id");
	
	if($play['is_playlist']==1) $total_pl++;
	if(($play['is_canadian']==1)&&($play['crtc_category']==20)) $total_cc_reg++;
	if(($play['is_canadian']==1)&&($play['crtc_category']==30)) $total_cc_spec++;
	if($play['is_fem']==1) $total_fe++;
	if($play['is_inst']==1) $total_inst++;
	if($play['is_hit']==1) $total_hit++;
	
	if($play['crtc_category']==20) $total_reg++;
	if($play['crtc_category']==30) $total_spec++;
	
	
}
echo "<br/><br/>";

echo "	<table class='report-table'><tr class='report-header'><td>category</td><td>count</td><td>percentage</td><td>requirement</td>
			<tr>
				<td>
					total playlist
				</td>
				<td>".
					$total_pl.
				"</td>
				<td>".
					round((($total_pl / $total_items)*100),2)."%".
				"</td>
				<td>".
					$pl_req."%".
				"</td>
			</tr>
			<tr>
				<td>
					total femcon
				</td>
				<td>".
					$total_fe.
				"</td>
				<td>".
					round((($total_fe / $total_items)*100),2)."%".
				"</td>
				<td>".
					$fe_req."%".
				"</td>
			</tr>
			<tr>
				<td>
					total instrumental
				</td>
				<td>".
					$total_inst.
				"</td>
				<td>".
					round((($total_inst / $total_items)*100),2)."%".
				"</td><td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					total hits
				</td>
				<td>".
					$total_hit.
				"</td>
				<td>".
					round((($total_hit / $total_items)*100),2)."%".
				"</td>
				<td>".
					$hit_req."% (max)".
				"</td>
			</tr>
			<tr class='report-header'>
				<td>
					total number of songs
				</td>
				<td>".
					$total_items.
				"</td><td>&nbsp;</td><td>&nbsp;</td>
			</tr><tr><td colspan=4>&nbsp;</td></tr>
			<tr class='report-header'><td colspan=4>cancon summary</td>
			</tr>
			<tr>
				<td>
					total cancon (cat 2)
				</td>
				<td>".
					$total_cc_reg.
				"</td>
				<td>".
					round((($total_cc_reg / $total_reg)*100),2)."%".
				"</td>
				<td>".
					$cc_reg_req."%".
				"</td>
			</tr>
			<tr class='report-header'>
				<td>
					total songs (cat 2)
				</td>
				<td>".
					$total_reg.
				"</td><td>&nbsp;</td><td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					total cancon (cat 3)
				</td>
				<td>".
					$total_cc_spec.
				"</td>
				<td>".
					round((($total_cc_spec / $total_spec)*100),2)."%".
				"</td>
				<td>".
					$cc_spec_req."%".
				"</td>
			</tr>
			<tr class='report-header'>
				<td>
					total songs (cat 3)
				</td>
				<td>".
					$total_spec.
				"</td><td>&nbsp;</td><td>&nbsp;</td>
			</tr>
		</table>
			
			";
			
						
//			$total_items = ($total_items) ? $total_items / 100 : 1;
//			printf("<tr><td>%s</td><td>%2.0f%%</td><td>%2.0f%%</td><td>%2.0f%%</td><td>%2.0f%%</td><td>%2.0f%%</td><td>%2.0f%%</td></tr>", "Total", $total_pl/$total_items, $total_cc_reg/$total_items,$total_cc_spec/$total_items, $total_yo/$total_items, $total_in/$total_items, $total_fe/$total_items);
//			printf("</table><br>");





							echo "<hr><h2>Content Breakdown by Show</h2>Legend:<br/><img src='images/pl.png' class=report_img>: New<br/>";
							echo "<img src='images/CAN.png' class=report_img>: Canadian Content<br/>";
							echo "<img src='images/fe.png' class=report_img>: Female Content<br/>";
							echo "<img src='images/inst.png' class=report_img>: Instrumental<br/>";
							echo "<img src='images/part.png' class=report_img>: Partial Play<br/>";
							echo "<img src='images/hit.png' class=report_img>: Hit<br/>";
							echo "<img src='images/background.png' class=report_img>: Background Music<br/>";
							echo "<img src='images/theme.png' class=report_img>: Theme Song<br/>";



echo "</div>";







foreach($showList as $i => $v){
		$thisID = $v['id'];
		$thisHost = $v['host_id'];
		
		$thisSpokenWord = $v['spokenword_duration'];
		$totalSpokenWord = $totalSpokenWord + $thisSpokenWord;
		
	
// OPTION 1 - get start time from playsheet's unix time
//		$start_unix = $v['unix_time'];
// OPTION 2 - get start time from playsheet's declared start time from form	
		$start_unix = strtotime($v['start_time']);
		$end_unix = strtotime($v['end_time'],$start_unix);
		
		
		if ($end_unix < $start_unix) // late night show that ends next day
		{	$end_unix += 60*60*24;	}
		
		if($SOCAN_FLAG){ echo '<div id="reportShowSOCAN">'; }
		else { echo '<div id="reportShow">'; }
			echo '<h3> ';
			$showObj = $showlib->getShowById($v['show_id']);
			echo $showObj->name;
			echo ' (hosted by '.$fhost_name[$thisHost].')';
			echo '</h3>';
			echo'<h4>'.date("D M j"."<b\\r/>"." g:i a ",$start_unix);
			echo ' - '.date("g:i a ",$end_unix);
			echo "</h4>";
			echo '<h5>'.$showObj->show_desc."</h5>";
			echo '<h4>CRTC category: '.$v['crtc'].'<br/>Language: '.$v['lang'].'</h4>';
			echo "<div class='songreport'>";
			echo "<h4>Music Played:</h4>";
			
			
			
			$total_pl = 0;
			$total_cc_reg = 0;
			$total_cc_spec = 0;
			$total_fe = 0;
			$total_inst = 0;
			$total_hit = 0;
			
			$count = 0;
			$total_reg = 0;
			$total_spec = 0;
			
			foreach($plays as $in => $pl){
				
				
					if	( $pl['playsheet_id']== $thisID) 
					{
						
					$count++;
					if($pl['crtc_category']==20) $total_reg++; else{}
					if($pl['crtc_category']==30) $total_spec++; else{}
					
					
					echo 	'<div class=dotted-underline><div class=report-entry>'.$pl['artist'].' - '.$pl['song'].
							' <span class=entry-lang>(Lang: '.$pl['lang'].') (Composer: '.$pl['composer'].') (Time Played: '.$pl['insert_song_start_hour'].':'.$pl['insert_song_start_minute'].') (Duration: '.$pl['insert_song_length_minute'].'m '.$pl['insert_song_length_second'].'s) </span></div>';
					echo '<span class=report-icons>'."<a>(".$pl['crtc_category'].")&nbsp;</a>";
						if ($pl['is_playlist']==1) {
							echo "<img src='images/pl.png' class=report_img>";
							$total_pl++;
							}
							else echo "<img src='images/nothing.png' class=report_img>";
						
						if ($pl['is_canadian']==1) {
							echo "<img src='images/CAN.png' class=report_img>";
							
							if($pl['crtc_category']==20) $total_cc_reg++; else{}
							if($pl['crtc_category']==30) $total_cc_spec++; else{}
						}
							else echo "<img src='images/nothing.png' class=report_img>";
							
						if ($pl['is_fem']==1) {
							echo "<img src='images/fe.png' class=report_img>";
							$total_fe++;
						}
							else echo "<img src='images/nothing.png' class=report_img>";
							
							
						if ($pl['is_inst']==1) {
							echo "<img src='images/inst.png' class=report_img>";
							$total_inst++;
						}
							else echo "<img src='images/nothing.png' class=report_img>";		
							
						if ($pl['is_part']==1) {
							echo "<img src='images/part.png' class=report_img>";
						}
							else echo "<img src='images/nothing.png' class=report_img>";	
							
						if ($pl['is_hit']==1) {
							echo "<img src='images/hit.png' class=report_img>";
							$total_hit++;
						}
						else echo "<img src='images/nothing.png' class=report_img>";	
						
						if($SOCAN_FLAG){
						if ($pl['is_theme']==1) {
							echo "<img src='images/background.png' class=report_img>";
						}
						else echo "<img src='images/nothing.png' class=report_img>";
						
						if ($pl['is_background']==1) {
							echo "<img src='images/theme.png' class=report_img>";
						}
						else echo "<img src='images/nothing.png' class=report_img>";
						}
						
						echo '</span></div>';
					//	echo '<br/>';
					}
				}
				
				echo "<br/>
					<div id='report-show-compliance'>";
				echo $count." songs (".$total_reg." regular and ".$total_spec." specialty):<br/>
								cancon 2: ".$total_cc_reg." / ".$total_reg."<span id='show-percent'>";
									if(($total_cc_reg==0)&&($total_reg==0)){
										echo "--</span><br/>";
								}	else{
										echo "(".round((100*$total_cc_reg/$total_reg),2)."%)</span> <br/>";
								}
							echo "cancon 3: ".$total_cc_spec." / ".$total_spec."<span id='show-percent'>";
									if(($total_cc_spec==0)&&($total_spec==0)){
										echo "--</span><br/>";
								}	else{
										echo "(".round((100*$total_cc_spec/$total_spec),2)."%)</span> <br/>";
								}
							echo "playlist: &nbsp;&nbsp;&nbsp;&nbsp;".$total_pl." / ".$count." <span id='show-percent'>(".round((100*$total_pl/$count),2)."%)</span> <br/>
								femcon: &nbsp;&nbsp;&nbsp;".$total_fe." / ".$count." <span id='show-percent'>(".round((100*$total_fe/$count),2)."%)</span> <br/>
								hits:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								".$total_hit." / ".$count." <span id='show-percent'>(".round((100*$total_hit/$count),2)."%)</span> <br/>
					</div>
				</div>";
		
				echo "<div class='crtcadreport'><h4>Spoken Word:</h4>";
					echo 'Tracked Plays:<br/>';
				
				foreach($samPlays as $j => $w){
				
				if(	( $w['date_unix']>= $start_unix) && 
					( $w['date_unix']<= $end_unix) ) {
					$time_played = explode(' ',$w['date_played']);
					$time_played = date("g:i a ",strtotime($time_played[1]));
					$duration = round(($w['duration']/1000),0);
					$type = $w['songtype'];
					if ($type=='I') $type = 'Station ID (43)'; else{}
					if ($type=='A') $type = 'Advertisement (51)'; else{}
					echo $time_played.' - '.$type.' - "'.$w['artist'].' '.$w['title'].'" ('.$duration.' secs)<br/>';
					}
				}
				
				echo '<br/>Station IDs:<br/>';
				
				
				//		returns array($times,$types,$names,$playeds);
					$jackson = $adLib->loadAdsForReport($v['id']);
					
					
					$times_list = $jackson[0];
					$types_list = $jackson[1];
					$names_list = $jackson[2];
					$playeds = $jackson[3];
					
				foreach( $times_list as $x => $time_val){
					
					if( $playeds[$x] && $types_list[$x]=='Station ID'){
						echo $times_list[$x].': Station ID<br/>';
					}
				}
				
				
				
				
				
//				print_r($v);
				
				if ($thisSpokenWord>0){
				echo "<br/>This show's spoken word (cat 12) duration: ";
				echo $thisSpokenWord." minutes";
				}
				
			echo "</div>";
		echo "</div>";
		
}// end of for each show



?>