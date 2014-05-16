<?php
require("headers/showlib.php");
require("headers/security_header.php");
require("headers/function_header.php");

function getPlaylistViewer($numrows,$filter){
	//if we are filtering by a showname then filter our query.
	if($filter)
	{
	//check what show_id is for the showname
	$showid = mysqli_quert($db,"SELECT host_id FROM shows WHERE name = ".$filter;
	//query playlists for saved playlists with show id = to show we are filtering
	$result = mysqli_query($db,"SELECT * FROM playlists ORDER BY start_time DESC WHERE host_id =".$showid." LIMIT 2500";
	}
	else
	{
	//query playlists database for ALL saved playlists
	$result = mysqli_query($db,"SELECT * FROM playlists ORDER BY start_time DESC LIMIT 2500";
	}
	//print_r($result);
	$num_rows = mysqli_num_rows($result);
	foreach(); //blah blah finish somehow
	return blah;
	
	
	}
	


/*
	$result = mysqli_query($db,"SELECT * FROM playlists  ORDER BY start_time DESC");

//	print_r($result);
	$num_rows = mysqli_num_rows($result);

	$min = min($num_rows,2500);
	$count = 0;
	while($count < $min) {
		
		if(mysqli_result($result,$count,"status")==1) 
			$draft = "(draft)";
		else
			$draft = "";
		
		if(mysqli_result($result,$count,"star")==1)
			$star_ = "&#9733;";
		else
			$star_ = "";
		$date_unix = strtotime(mysqli_result($result,$count,"start_time")); 
		$theDate = 	date ( 'Y: M j, g:ia', $date_unix);
viewplaylistarray[i]=fsdfds;

//printf("<OPTION VALUE=\"%s\">%s - %s %s\n", mysqli_result($result,$count,"id"), $theDate, $fshow_name[mysqli_result($result,$count,"show_id")], $draft);
		print("<option value='".mysqli_result($result,$count,"id")."'>".$theDate." - ".$star_.$fshow_name[mysqli_result($result,$count,"show_id")].$star_." ".$draft);
		$count++;
	}
	printf("</SELECT><BR><button TYPE=submit VALUE=\"View Playsheet\" >View Playsheet</button>\n");
	
	*/
	?>