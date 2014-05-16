<?php

define('POD_CONFIG', true);

require_once('config.php');

function get_param($name, $type = "string", $default = null, $bTrimFirst = true)
{
	$value = $default;

	if(isset($_GET[$name]))
		$value = $_GET[$name];

	if(isset($_POST[$name]))
		$value = $_POST[$name];

	if($bTrimFirst)
	{
		$value = trim($value);
	}

	settype($value, $type);
	return $value;
}

function str2time($strStr, $strPattern = null)
{
   // an array of the valide date characters, see: http://php.net/date#AEN21898
   $arrCharacters = array(
       'd', // day
       'm', // month
       'y', // year, 2 digits
       'Y', // year, 4 digits
       'H', // hours
       'i', // minutes
       's'  // seconds
   );
   // transform the characters array to a string
   $strCharacters = implode('', $arrCharacters);

   // splits up the pattern by the date characters to get an array of the delimiters between the date characters
   $arrDelimiters = preg_split('~['.$strCharacters.']~', $strPattern);
   // transform the delimiters array to a string
   $strDelimiters = quotemeta(implode('', array_unique($arrDelimiters)));

   // splits up the date by the delimiters to get an array of the declaration
   $arrStr    = preg_split('~['.$strDelimiters.']~', $strStr);
   // splits up the pattern by the delimiters to get an array of the used characters
   $arrPattern = preg_split('~['.$strDelimiters.']~', $strPattern);

   // if the numbers of the two array are not the same, return false, because the cannot belong together
   if (count($arrStr) !== count($arrPattern)) {
       return false;
   }

   // creates a new array which has the keys from the $arrPattern array and the values from the $arrStr array
   $arrTime = array();
   for ($i = 0;$i < count($arrStr);$i++) {
       $arrTime[$arrPattern[$i]] = $arrStr[$i];
   }

   // gernerates a 4 digit year declaration of a 2 digit one by using the current year
   if (isset($arrTime['y']) && !isset($arrTime['Y'])) {
       $arrTime['Y'] = substr(date('Y'), 0, 2) . $arrTime['y'];
   }

   // if a declaration is empty, it will be filled with the current date declaration
   foreach ($arrCharacters as $strCharacter) {
       if (empty($arrTime[$strCharacter])) {
           $arrTime[$strCharacter] = date($strCharacter);
       }
   }

   // checks if the date is a valide date
   if (!checkdate($arrTime['m'], $arrTime['d'], $arrTime['Y'])) {
       return false;
   }

   // generates the timestamp
   $intTime = mktime($arrTime['H'], $arrTime['i'], $arrTime['s'], $arrTime['m'], $arrTime['d'], $arrTime['Y']);
   // returns the timestamp
   return $intTime;
}

// expecting 2006-10-20 -- then convert to a time
function getdatefromYYYYMMDD($s)
{
	$tok = strtok($s, "-");
	
	if ($tok == false)
	{
		return 0;
	}
	
	$year = $tok;
	
	$tok = strtok("-");
	
	if ($tok == false)
	{
		return 0;
	}
	
	$month = $tok;
	
	$tok = strtok("-");	
	
	if ($tok == false)
	{
		return 0;
	}
	
	$day = $tok;
	
	$year = 2006;
	$month = 10;
	$day = 30;
	
	if ($theresult = mktime(0,0,0,$month,$day,$year) === false)
	{
		die("Some problem!");
	}
	
	//die(date("M-d-Y",$theresult));
	
	return mktime(0,0,0,$month,$day,$year);
}

// returns a string in the format HH:MM:SS
// accepts strings like "10pm", "10 pm", "10:15PM", 10:02:15 pM", etc.
// returns string beginning with ERROR if there is a problem
function gettimefromstring($s)
{
	if (strlen($s) < 3)
	{
		return "ERROR: String too short";
	}
	
	$ampmlower = strtolower(substr($s, -2));
	
	$am = ($ampmlower == "am") ? TRUE : FALSE;
	$pm = ($ampmlower == "pm") ? TRUE : FALSE;
	
	if ($am == false && $pm == false)
	{
		return "ERROR: invalid am/pm designator";
	}
	
	$remaining = str_replace(" ", "", substr($s, 0, -2));
	
	$tok = strtok($remaining, ":");
	
	$hh = -1;
	$mm = -1;
	$ss = -1;
	
	while ($tok !== false)
	{
		$theval = $tok;
		$tok = strtok(":"); // evaluate it now so we can loop easier in following code
		
		if ($hh == -1)
		{
			$hh = $theval;
			if ($hh < 1 or $hh > 12)
			{
				return "ERROR: hour out of range";
			}
			
			if ($pm == true and $hh < 12)
			{
				$hh += 12;
			}
			
			if ($am == true and $hh == 12)
			{
				$hh = 0;
			}
			
			continue;
		}
		
		if ($mm == -1)
		{
			$mm = $theval;	
			
			if ($mm < 0 or $mm > 59)
			{
				return "ERROR: minute out of range";
			}			
			
			continue;
		}
		
		if ($ss == -1)
		{
			$ss = $theval;
			
			if ($ss < 0 or $ss > 59)
			{
				return "ERROR: minute out of range";
			}
		}
	}

	
	if ($hh < 0)
	{
		return "ERROR: hour is invalid";
	}
	
	if ($mm < 0)
	{
		$mm = 0;
	}
	
	if ($ss < 0)
	{
		$ss = 0;
	}
			
	return str_pad($hh, 2, "0", STR_PAD_LEFT).':'.str_pad($mm, 2, "0", STR_PAD_LEFT).':'.str_pad($ss, 2, "0", STR_PAD_LEFT);
	//int strcasecmp ( string str1, string str2 )
}


function gettimestamp()
{
	global $nYear, $nMonth, $nDay;
	return mktime(0, 0, 0, $nMonth, $nDay, $nYear);
}

function quotequotes(&$s)
{
	$s = str_replace("&", "&amp;", $s);	
	$s = str_replace("\"", "&quot;", $s);
}

function checksecurity()
{
	require('config.php');
}

	$restricted_view = false;

	$db = mysql_connect($db_host, $db_user, $db_pass);
	if( !$db )
	{
		die("Error connecting to the Server");
		exit;
	}
	$result = mysql_select_db($db_database, $db);
	if( !$result )
	{
		die("Error selecting Database");
		exit;
	}
	
	if (isset($pgm_manager) and $pgm_manager == true)
	{
		$restricted_view = true; // unless otherwise specified
		
		if (!isset($_SERVER['PHP_AUTH_USER']))
		{
		   header('WWW-Authenticate: Basic realm="CiTRPodcastEditor"');
		   header('HTTP/1.0 401 Unauthorized');
		   echo "Sorry, you can't get in";
		   exit;
		} else
		{
		
			if ( $_SERVER['PHP_AUTH_USER'] == "administrator")
			{							
				if ($_SERVER['PHP_AUTH_PW'] == $pgm_admin_pw)
				{
					$restricted_view = false;
				}
			}
			
			//echo "<p>Hello {$_SERVER['PHP_AUTH_USER']}.</p>";
			//echo "<p>You entered {$_SERVER['PHP_AUTH_PW']} as your password.</p>";
			//die('abc');
		}

	}
	
    $Requested_Action = get_param("action", "string", "");

	if ($restricted_view)
	{
		//$Channel_ID	 = 51; // for now		
		
		$security_query =
			"SELECT A.ChannelID FROM podcast_channels AS A 
			LEFT JOIN podcast_channels AS B ON A.ChannelID = B.ChannelID
			WHERE A.FieldID = 19 AND A.StringValue LIKE '%{$_SERVER['PHP_AUTH_USER']}%'
			AND B.FieldID = 31 AND B.StringValue = '{$_SERVER['PHP_AUTH_PW']}'";
			
		//die($security_query);
		
		$result_sec = mysql_query($security_query,$db);
		if( !$result_sec )
		{
			die("Error executing security query");
		}
		
		if ($row_max = mysql_fetch_array($result_sec))
		{
			$Channel_ID = $row_max[0];
		}
		else
		{
		   header('WWW-Authenticate: Basic realm="CiTRPodcastEditor"');
		   header('HTTP/1.0 401 Unauthorized');
		   echo 'Sorry that you are unable to login';
		   exit;
		}
				
		
	}
	else
	{
		$Channel_ID = get_param("ChannelID", "string", "");    
	}
    
    if ($Requested_Action == "createchannel")
    {
    	$Create_Channel = $_POST['newchannel']; // get_param("newchannel","string","");
    	
		if ($Create_Channel == "") // new 2006-03-27
		{
			die("Error -- must provide a title for new channel");
		}
    	    	
    	//echo "User wants to create channel named $Create_Channel<p>";

		$query_max = "SELECT MAX(ChannelID) FROM `podcast_channels`";
		
    	//echo "$query_max<p>";    
    			
		$result_max = mysql_query($query_max,$db);
		if( !$result_max )
		{
			die("Error executing max query");
		}
		
		$next_id = 0;
		
		if ($row_max = mysql_fetch_array($result_max))
		{
			$next_id = $row_max[0];
		}
		
		$next_id++;				
			
		$query_add = "INSERT INTO podcast_channels (ChannelID, FieldID, StringValue) VALUES($next_id,1,\"$Create_Channel\")";
		
		//echo "$query_add";
	
		$result_add = mysql_query($query_add,$db);
		if( !$result_add )
		{
			die($query_add);
		}	
		
		$Channel_ID = $next_id;
		
		// now, let's initialize the channel by copying the values from the other specified channel
		
		$Channel_copy_source = get_param("ch_create_basedon", "string", "");
		
		if ($Channel_copy_source >= 0)
		{
			// in next line, we are ignoring the blob field, for now
			// get all source fields EXCEPT the title value, which we already inserted (above)
			$query_copy_source = "SELECT FieldID, IntValue, StringValue, TimeValue FROM `podcast_channels` WHERE ChannelID = $Channel_copy_source AND FieldID != 1";
			
			//echo "$query_copy_source<br>";
			
			$result_copy = mysql_query($query_copy_source,$db);		
			
			if($result_copy )
			{
				 while( $row_copy = mysql_fetch_array($result_copy))
				 {
					$val_int = ($row_copy[1] == NULL) ? "NULL" : $row_copy[1];
							
					$val_date = ($row_copy[3] == 0) ? "0000-00-00T00:00:00" : date("c",strtotime($row_copy[3]));
					
					//echo "$val_date<br>";
					
					$query_copy_target = "INSERT INTO podcast_channels (ChannelID, FieldID, IntValue, StringValue, TimeValue) VALUES($Channel_ID,$row_copy[0],$val_int,\"$row_copy[2]\",'$val_date')";
					//echo "$query_copy_target<br>";											
					
					$result_paste = mysql_query($query_copy_target,$db);
					
					if (!$result_paste)
					{
						die($query_copy_target);
					}
				 }			
			}
			else
			{
				die($query_copy_source);
			}		
		
		} // if user wants to base new channel on an existing one
		
    }
    
    if ($Requested_Action == "editchannel")
    {
	
		$ch_title = get_param("ch_title", "string", ""); // 1
		$ch_subtitle = get_param("ch_subtitle", "string", ""); // 2
		$ch_author = get_param("ch_author", "string", ""); // 3
		$ch_link = get_param("ch_link", "string", ""); // 4
		$ch_image = get_param("ch_image", "string", ""); // 5
		
		$ch_ftp_server = get_param("ch_ftp_server", "string", ""); // 6
		$ch_ftp_user = get_param("ch_ftp_user", "string", ""); // 7
		$ch_ftp_password = get_param("ch_ftp_password", "string", ""); // 8
		$ch_ftp_audio_path = get_param("ch_ftp_audio_path", "string", ""); // 9
		
		$ch_audio_format = get_param("ch_audio_format", "string", ""); // 10
		
		$ch_unc_path = get_param("ch_unc_path", "string", ""); // 11		
		
		$ch_purging_method = get_param("ch_purging_method", "integer", ""); // 12
		
		$ch_purging_param1 = get_param("ch_purging_param1", "integer", ""); // 13
		
		$ch_upload_method = get_param("ch_upload_method", "integer", "0"); // 14
		
		$ch_type = get_param("ch_type", "integer", "0"); // 15
		
		$ch_dest_audio_filename = get_param("ch_dest_audio_filename", "string", ""); // 16
		
		$ch_audio_url_prefix = get_param("ch_audio_url_prefix", "string", ""); // 17	
		
		// 18 is 'dirty' status.  If 1, podcast_ftp.php will refresh it		

		$ch_dest_xml_filename = get_param("ch_dest_xml_filename", "string", ""); // 19
		
		$ch_ftp_xml_path = get_param("ch_dest_xml_path","string",""); // 20	
		
		// below are new
		
		$ch_episode_default_title = get_param("ch_episode_title","string",""); // 21
		
		$ch_episode_default_subtitle = get_param("ch_episode_subtitle","string",""); // 22
		
		$ch_episode_default_author = get_param("ch_episode_author","string",""); // 23
		
		// below are new 2006-04-17
		
		$ch_ftp_xml_server = get_param("ch_ftp_xml_server", "string", ""); // 24

		$ch_ftp_xml_user = get_param("ch_ftp_xml_user", "string", ""); // 25

		$ch_ftp_xml_password = get_param("ch_ftp_xml_password", "string", ""); // 26
		
		$ch_keywords = get_param("ch_keywords", "string", ""); // 27
		
		$ch_summary = get_param("ch_summary", "string", ""); // 28 -- new on 2006-05-19
		
		$ch_ownername = get_param("ch_ownername", "string", ""); // 29

		$ch_owneremail = get_param("ch_owneremail", "string", ""); // 30		
		
		$ch_password = get_param("ch_password", "string", ""); // 31
					
		// ---------
		
		$ch_delete = get_param("ch_delete","string","");
		
		if ($ch_delete == "DELETE")
		{
			// user wants to delete the entire channel
			
			$query_delete_episodes = "DELETE FROM `podcast_audiofiles` WHERE ChannelID=$Channel_ID";						
			$result_delete_episodes = mysql_query($query_delete_episodes,$db);
			if( !$query_delete_episodes)
			{
				die($query_delete_episodes);
			}			
			//mysql_free_result($result_delete_episodes);
			
			$query_delete_channel = "DELETE FROM `podcast_channels` WHERE ChannelID=$Channel_ID";						
			$result_delete_channel = mysql_query($query_delete_channel,$db);
			if( !$query_delete_channel)
			{
				die($query_delete_channel);
			}			
			//mysql_free_result($result_delete_channel);
			
			$Channel_ID = "";
		
		}
		else
		{	
			$query_removeexisting = "DELETE FROM `podcast_channels`WHERE ChannelID=$Channel_ID AND FieldID <= 31";
			
			$result_removeexisting = mysql_query($query_removeexisting,$db);
			if( !$result_removeexisting)
			{
				die($query_removeexisting);
			}
	
			$query_editchannel = "INSERT INTO podcast_channels (ChannelID, FieldID, IntValue, StringValue, BlobValue) 
				VALUES 
				($Channel_ID, 1, NULL, \"$ch_title\", \"\"),
				($Channel_ID, 2, NULL, \"$ch_subtitle\", \"\"),
				($Channel_ID, 3, NULL, \"$ch_author\", \"\"),
				($Channel_ID, 4, NULL, \"$ch_link\", \"\"),
				($Channel_ID, 5, NULL, \"$ch_image\", \"\"),
				
				($Channel_ID, 6, NULL, \"$ch_ftp_server\", \"\"),
				($Channel_ID, 7, NULL, \"$ch_ftp_user\", \"\"),
				($Channel_ID, 8, NULL, \"$ch_ftp_password\", \"\"),
				($Channel_ID, 9, NULL, \"$ch_ftp_audio_path\", \"\"),
				
				($Channel_ID, 10, NULL, \"$ch_audio_format\", NULL)	,	
				($Channel_ID, 11, NULL, \"$ch_unc_path\", \"\"),
				
				($Channel_ID, 12, $ch_purging_method, NULL, \"\"),
				($Channel_ID, 13, $ch_purging_param1, NULL, \"\"),
				
				($Channel_ID, 14, $ch_upload_method, NULL, \"\"),
				
				($Channel_ID, 15, $ch_type, NULL, \"\"),
				
				($Channel_ID, 16, NULL, \"$ch_dest_audio_filename\", \"\"),
				
				($Channel_ID, 17, NULL, \"$ch_audio_url_prefix\", \"\"),
				
				($Channel_ID, 18, 1, NULL, \"\"),
				
				($Channel_ID, 19, NULL, \"$ch_dest_xml_filename\", \"\"),
				
				($Channel_ID, 20, NULL, \"$ch_ftp_xml_path\", \"\"),				
				
				($Channel_ID, 21, NULL, \"$ch_episode_default_title\", \"\"),
				
				($Channel_ID, 22, NULL, \"$ch_episode_default_subtitle\", \"\"),
				
				($Channel_ID, 23, NULL, \"$ch_episode_default_author\", \"\"),
				
				($Channel_ID, 24, NULL, \"$ch_ftp_xml_server\", \"\"),
				
				($Channel_ID, 25, NULL, \"$ch_ftp_xml_user\", \"\"),
				
				($Channel_ID, 26, NULL, \"$ch_ftp_xml_password\", \"\"),
				
				($Channel_ID, 27, NULL, \"$ch_keywords\", \"\"),
				
				($Channel_ID, 28, NULL, \"\", \"$ch_summary\"),
				
				($Channel_ID, 29, NULL, \"$ch_ownername\", \"\"),
				
				($Channel_ID, 30, NULL, \"$ch_owneremail\", \"\"),
				
				($Channel_ID, 31, NULL, \"$ch_password\", \"\")				
				";					
				
			$result_editchannel = mysql_query($query_editchannel,$db);
			if( !$result_editchannel)
			{
				die($query_editchannel);
			}
		}
    }    
    
    if ($Requested_Action == "editepisode" or $Requested_Action == "addepisode")
    {
		//echo "<h2>Still in beta! -- editing of channel </h2>\r\n";
		
		$ep_takeonline = get_param("item_takeonline", "string", "");
		$ep_takeoffline = get_param("item_takeoffline", "string", "");
				
		$ep_title = get_param("item_title", "string", "");
		$ep_subtitle = get_param("item_subtitle", "string", "");
		$ep_summary = get_param("item_summary", "string", "");
			
		$ep_author = get_param("item_author", "string", "");		
		
		$Channel_ID = get_param("channel", "string", "");
		$Episode_ID = get_param("episode", "string", "");		
		
		//echo "<h3>Channel is $Channel_ID, Episode is $Episode_ID: $ep_active, $ep_title, $ep_subtitle</h3>";
		
		// we will replace meta-data for title, subtitle, and author
		
		if ($Requested_Action == "editepisode") // e.g. it's not 'addepisode'
		{
				
			$query_removeexisting = "DELETE FROM `podcast_audiofiles`WHERE ChannelID=$Channel_ID AND EpisodeID=$Episode_ID AND FieldID IN (1, 2, 3, 103, 105)";
			
			// 1 is title, 2 is subtitle, 3 is author, 103 is status, and 105 is the item summary
			
			$result_removeexisting = mysql_query($query_removeexisting,$db);
			if( !$result_removeexisting)
			{
				die($query_removeexisting);
			}
			
			$ep_newstatus = 2; // let's assume it's only unless we set otherwise (see below)
				
			if ($ep_takeonline == "on")
			{			
				$ep_newstatus = 2;
				//echo "<h1>Setting ns to 2</h1>";
			}
			
			if ($ep_takeoffline == "on")
			{
				$ep_newstatus = 0;
				//echo "<h1>Setting ns to 0</h1>";
			}
					
			$query_editepisode = "INSERT INTO podcast_audiofiles (ChannelID, EpisodeID, FieldID, IntValue, StringValue, BlobValue) 
			VALUES 
			($Channel_ID, $Episode_ID, 1, NULL, \"$ep_title\", \"\")
			,($Channel_ID, $Episode_ID, 2, NULL, \"$ep_subtitle\", \"\")
			,($Channel_ID, $Episode_ID, 3, NULL, \"$ep_author\", \"\")
			,($Channel_ID, $Episode_ID, 103, $ep_newstatus, NULL, \"\")					
			,($Channel_ID, $Episode_ID, 105, NULL, \"\", \"$ep_summary\")	
			";
			
			
		}
		else
		{
			$ep_startstring = "";
			$ep_endstring = "";
		
			$start_time = get_param("startmarker"); 
			$end_time = get_param("endmarker");
								
			if ($start_time == 0 and $end_time == 0)
			{					
				$ep_broadcastdate = get_param("startdate");
				
				$ep_timefrom_clean = gettimefromstring(get_param("timefrom"));						
				if (strstr($ep_timefrom_clean, "ERROR"))
				{
					die('Error with starting time string:<p>'.$ep_timefrom_clean);
				}
				
				$ep_timeto_clean = gettimefromstring(get_param("timeto"));
				if (strstr($ep_timeto_clean, "ERROR"))
				{
					die('Error with ending time string:<p>'.$ep_timeto_clean);
				}				
							
				$start_time = str2time($ep_broadcastdate." ".$ep_timefrom_clean,"Y-m-d H:i:s");
				$end_time =   str2time($ep_broadcastdate." ".$ep_timeto_clean,"Y-m-d H:i:s");
				
				if ($end_time < $start_time) // then we assume it's wrapping over midnight, so add one day to end time
				{
					$end_time = strtotime("+1 day",$end_time);
				}
			}
					
			// now, let's do some valication on the times
			
			if ($start_time >= $end_time)
			{
				die("The podcast must start before it finishes.  Go back and fix the times");			
			}
			
			//die("Start = ".date("Y-m-d H:i:s",$start_time)." and end = ".date("Y-m-d H:i:s",$end_time));
		
			if (isset($max_podcast_length)) // expressed in minutes
			{						
				if ( ($end_time-$start_time) > $max_podcast_length*60)
				{
					die("Podcast must be no longer than ".$max_podcast_length." minutes.  Go back and fix the length");
				}			
			}
			
			// first, check if podcast doesn't exceed the current time minus 30 seconds
			
			if ( $end_time >= (time()-30) )
			{
				die("Podcast cannot span current time");
			}
			
			//die("Start = ".date("Y-m-d H:i:s",$start_time)." and end = ".date("Y-m-d H:i:s",$end_time));
			
			$ep_startstring = date("Y-m-d\TH:i:s",$start_time);
			$ep_endstring = date("Y-m-d\TH:i:s",$end_time);
				
			$ep_title = str_replace("[%DATE]",date("d-M-Y",$start_time),$ep_title);
			
			//die($ep_title);
				
			$ep_newstatus = 2;
			
			$query_max = "SELECT MAX(EpisodeID) FROM `podcast_audiofiles` WHERE ChannelID=".$Channel_ID;
			
			$result_max = mysql_query($query_max,$db);
			if( !$result_max )
			{
				die("Error executing max query");
			}
			
			$next_id = 0;
			
			if ($row_max = mysql_fetch_array($result_max))
			{
				$next_id = $row_max[0];
			}
			
			$next_id++;							
			
			$Episode_ID = $next_id;	
						
			$query_editepisode = "INSERT INTO podcast_audiofiles (ChannelID, EpisodeID, FieldID, IntValue, StringValue, TimeValue, BlobValue) VALUES 
			($Channel_ID, $Episode_ID, 1, 0, '".$ep_title."', NULL, '')\r\n
			,($Channel_ID, $Episode_ID, 2, 0, '".$ep_subtitle."', NULL, '')\r\n
			,($Channel_ID, $Episode_ID, 103, ".$ep_newstatus.", NULL, '', '')\r\n
			,($Channel_ID, $Episode_ID, 104, 0, '', '".$ep_startstring."', '')\r\n
			,($Channel_ID, $Episode_ID, 105, 0, '', NULL, '".$ep_summary."')\r\n
			,($Channel_ID, $Episode_ID, 106, 0, '', '".$ep_startstring."', '')\r\n
			,($Channel_ID, $Episode_ID, 107, 0, '', '".$ep_endstring."', '')\r\n
			";
			
		}
		

			
		//if ($Requested_Action == "addepisode")
		//{
		//	die($query_editepisode);
		//}
							
		$result_editepisode = mysql_query($query_editepisode,$db);
		if( !$result_editepisode)
		{
			die($query_editepisode);
		}		
			
    }
    
	$query_check_podcast_channels = "SHOW COLUMNS FROM podcast_channels";
	
	$result_check_channels = mysql_query($query_check_podcast_channels,$db);
	if(!$result_check_channels)
    {   
		$query_create_podcast_channels = 
			"CREATE TABLE `podcast_channels` (
			`ChannelID` int(11) NOT NULL default '0',
			`FieldID` int(11) NOT NULL default '0',
			`IntValue` int(11) default '0',
			`StringValue` varchar(255) default '',
			`TimeValue` datetime default '0000-00-00T00:00:00',
			`BlobValue` longtext,
			PRIMARY KEY  (`ChannelID`,`FieldID`)
			) TYPE=MyISAM";
			
		$result_create_podcast_channels = mysql_query($query_create_podcast_channels,$db);
		
		if( !$result_create_podcast_channels)
		{
			die($query_create_podcast_channels);
		}	
	}
	
	$query_check_podcast_audiofiles= "SHOW COLUMNS FROM podcast_audiofiles";
	
	$result_check_audiofiles = mysql_query($query_check_podcast_audiofiles,$db);
	if(!$result_check_audiofiles)
    {   
		$query_create_podcast_audiofiles = 
			"CREATE TABLE `podcast_audiofiles` (
			`ChannelID` int(11) NOT NULL default '0',
			`EpisodeID` int(11) NOT NULL default '0',
			`FieldID` int(11) NOT NULL default '0',
			`IntValue` int(11) NOT NULL default '0',
			`StringValue` varchar(255) NOT NULL default '',
			`TimeValue` datetime NOT NULL default '0000-00-00T00:00:00',
			`BlobValue` longtext NOT NULL,
			PRIMARY KEY  (`ChannelID`,`EpisodeID`,`FieldID`)
			) TYPE=MyISAM";
			
		$result_create_podcast_audiofiles = mysql_query($query_create_podcast_audiofiles,$db);
		
		if( !$result_create_podcast_audiofiles)
		{
			die($query_create_podcast_audiofiles);
		}	
	}		

//    $Prog_ID = get_param("programid", "string", "");

	Header("Content-Type: text/html;");

	echo "<html>\r\n";
	
	echo "<head>\r\n";
	echo "<title>Burli Podcast Editor</title>\r\n";
	
	echo "</head>\r\n";
	
	echo "<center>";
	echo "<h2>Podcast channel editor</h2>";
	echo "<font size=2>Form revision date: 2006-09-27</font>";
	echo "</center>\r\n";
	
	if ($restricted_view)
	{


	}
	else
	{
	
		echo "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\r\n";
		
		echo "<FIELDSET>\r\n";
		echo "<LEGEND>Channel chooser</LEGEND>";
		
		echo "<table>\r\n";
		
		echo "<tr>\r\n";
		echo "<td>\r\n";
		echo "Choose a channel to edit:<br>\r\n";
		  
		$query_channels = "SELECT ChannelID, StringValue FROM `podcast_channels` WHERE FieldID = 1";
	
		$result = mysql_query($query_channels,$db);
		if( !$result )
		{
			die("Error executing query:<p>$query_channels");
		}
		
		echo "<ul>\r\n\r\n";
		
		while( $row = mysql_fetch_array($result))
		{
		
			$title_to_display = $row[1];
			
			if ($title_to_display == "") // new 2006-03-27 -- if no title, at least display something
			{
				$title_to_display = "(untitled channel)";
			}
			
			echo "<li><a href=\"".$_SERVER['PHP_SELF']."?ChannelID=".$row[0]."\">".$title_to_display."</a>";
			//$nrow++;
		}
		 
		echo "</ul><p>\r\n\r\n";
			
		echo "<tr>\r\n";
		echo "<td>\r\n";
		echo "... or create a new channel:<p>\r\n";
		
		echo "<tr>\r\n";
		echo "<td>\r\n";	
		
		echo "<tr>\r\n";
		echo "<td>\r\n";
		
		echo "Title:\r\n";	
		echo "<td>\r\n";			
			
		echo "<input type=\"text\" name=\"newchannel\" size=25 />\r\n";
		
		echo "<tr>\r\n";
		echo "<td>\r\n";
		
		echo "Copy settings from ";
		
		echo "<td>\r\n";
		
		echo "<SELECT name=\"ch_create_basedon\">\r\n";
		
		$result = mysql_query($query_channels,$db);
		if( !$result )
		{
			die("Error executing query:<p>$query_channels");
		}
		
		echo "<OPTION value=\"-1\">(No channel)</OPTION>\r\n";	
		
		while( $row = mysql_fetch_array($result))
		{
			$title_to_display = $row[1];
			
			if ($title_to_display == "") // new 2006-03-27 -- if no title, at least display something
			{
				$title_to_display = "(untitled channel)";
			}		
			
			echo "<li><a href=\"".$PHP_SELF."?ChannelID=".$row[0]."\">".$title_to_display."</a>\r\n";
	
			echo "<OPTION value=\"$row[0]\">$title_to_display</OPTION>\r\n";
		}
	
		echo "</SELECT>\r\n";
			
		echo "<input type=\"hidden\" name=\"action\" value=\"createchannel\" />\r\n";
		
		echo "<tr>\r\n";
		echo "<td>\r\n";
		echo "<td>\r\n";
		
		echo "<input type=\"submit\" value=\"Create channel\" />\r\n";
		
		echo "</table>\r\n";
		
		
		echo "</FIELDSET>\r\n";
		
		echo "</FORM>\r\n";
	} // if not pgm manager
	
	if ($Channel_ID != "")
	{
	
		$query2 = "SELECT FieldID, IntValue, StringValue, TimeValue, BlobValue FROM `podcast_channels` WHERE ChannelID = '".$Channel_ID."'";
	
		$result2 = mysql_query($query2,$db);
		if( !$result2 )
		{
			die($query2);
		}		
		
		$channel_password = '';
		
		while( $pgm_row2 = mysql_fetch_array($result2))
		{			
			switch ($pgm_row2[0])
			{
				case 1:
					$channel_title = $pgm_row2[2];
					break;
				case 2:
					$channel_subtitle = $pgm_row2[2];
					break;
				case 3:
					$channel_author = $pgm_row2[2];
					break;
				case 4:
					$channel_link = $pgm_row2[2];
					break;
				case 5:
					$channel_image = $pgm_row2[2];				
					break;
				case 6:
					$channel_ftp_server = $pgm_row2[2];
					break;
				case 7:
					$channel_ftp_user = $pgm_row2[2];
					break;
				case 8:
					$channel_ftp_password = $pgm_row2[2];
					break;
				case 9:
					$channel_ftp_audio_path = $pgm_row2[2];
					break;	
				case 10:
					$channel_audio_format = $pgm_row2[2];
					break;
				case 11:
					$channel_unc_path = $pgm_row2[2];
					break;
				case 12:
					$channel_purging_method = $pgm_row2[1];
					break;
				case 13:
					$channel_purging_param1 = $pgm_row2[1];				
					break;
				case 14:
					$channel_upload_method = $pgm_row2[1];
					break;
				case 15:
					$channel_type = $pgm_row2[1];
					break;
				case 16:
					$channel_dest_audio_filename = $pgm_row2[2];
					break;
				case 17:
					$channel_audio_url_prefix = $pgm_row2[2];
					break;
				case 19:
					$channel_dest_xml_filename = $pgm_row2[2];
					break;
				case 20:
					$channel_ftp_xml_path = $pgm_row2[2];
					break;
				case 21: // new
					$episode_default_title = $pgm_row2[2];
					break;
				case 22: // new
					$episode_default_subtitle = $pgm_row2[2];
					break;
				case 23: // new 2006-04-17
					$episode_default_author = $pgm_row2[2];
					break;
				case 24: // new 2006-04-17
					$channel_ftp_xml_server = $pgm_row2[2];
					break;
				case 25: // new 2006-04-17
					$channel_ftp_xml_user = $pgm_row2[2];
					break;
				case 26: // new 2006-04-17
					$channel_ftp_xml_password = $pgm_row2[2];
					break;
				case 27: // new 2006-04-17:
					$channel_keywords = $pgm_row2[2];
					break;
				case 28: // new 2006-05-19
					$channel_summary = $pgm_row2[4]; // note this is 4 (for the longtext field)
					break;
				case 29:
					$channel_ownername = $pgm_row2[2];
					break;
				case 30:
					$channel_owneremail = $pgm_row2[2];
					break;
				case 31:
					$channel_password = $pgm_row2[2];
					break;
			}
			
			//6: ftp server
			//7: ftp user
			//8: ftp password
			//9: ftp path			
			
			//$channel_author = $pgm_row[2];
			//$channel_subtitle = $pgm_row[3];
			//$channel_link = $pgm_row[5];

		}
	
		echo "<table>\r\n\r\n";
		
		echo "<tr><td>";
		
		if ($channel_image != "")
		{
			echo "<img src=\"$channel_image\" width=150 height=150>";
		}
		
		echo "<td>";
		
		echo "<h2>Editing channel: ".$channel_title."</h2>\r\n";
		
		//die($_SERVER['PHP_SELF']);
		$logouturl = "http://log:out@".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
		
		echo "<a href=".$logouturl.">Click here to login as a different user</a><p>\r\n";
			
		if ( isset($pgm_manager) and ($pgm_manager == true) )
		{	
			if (isset($xml_url_prefix) and isset($channel_dest_xml_filename) )
			{			
				echo "<a href=\"".$xml_url_prefix.$channel_dest_xml_filename."\" target=\"_blank\">Click here to view XML</a><p>\r\n";
			}
		}
		else
		{
			echo "<a href=\"podcasts.php?ChannelID=$Channel_ID\" target=\"_blank\">Click here to test XML</a>\r\n";
		}
		
		echo "</table>\r\n";
	 	
		if ($restricted_view) 
		{	
				// don't display any of the channel fields
		}		
		else
		{
		
			echo "<FORM action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\r\n";
			
			echo "<input type=\"hidden\" name=\"action\" value=\"editchannel\" />\r\n";
			echo "<input type=\"hidden\" name=\"ChannelID\" value=\"".$Channel_ID."\" />\r\n";		
			
			echo "<FIELDSET>\r\n";
			echo "<LEGEND>Channel details</LEGEND>\r\n";	
			
			echo "<table>\r\n";
			
			echo "<tr>\r\n";
			echo "<td>\r\n";
			echo "Channel type\r\n";
			echo "<td>\r\n";
			echo "<SELECT name=\"ch_type\">\r\n";
			
			$option_one = ($channel_type == 0) ? " selected" : "";
			$option_two = ($channel_type == 1) ? " selected" : "";	
			
			echo "<OPTION $option_one value=\"0\">Podcast channel</OPTION>\r\n";		
			echo "<OPTION $option_two value=\"1\">Basic audio upload</OPTION>\r\n";
			echo "</SELECT>\r\n";
			echo " (Submit modifications now to access available upload fields)";			
			
			echo "<tr>\r\n";
			echo "<td>\r\n";
			echo "<LABEL for=\"ch_title\">Channel Title: </LABEL>\r\n";
			echo "<td>\r\n";
			echo "<INPUT type=\"text\" name=\"ch_title\" size=80 value=\"".$channel_title."\">\r\n";			
			
			if ($channel_type == 0) // podcast
			{				
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"ch_subtitle\">Channel Subtitle: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_subtitle\" size=80 value=\"".$channel_subtitle."\">\r\n";		
				
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"ch_summary\">Channel Summary: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_summary\" size=80 maxlength=4000 value=\"".$channel_summary."\">\r\n";					
			
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"ch_author\">Channel Author: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_author\" size=80 value=\"".$channel_author."\">\r\n";
				
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"ch_keywords\">Channel Keywords: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_keywords\" size=80 value=\"".$channel_keywords."\">\r\n";			
	
	
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"ch_ownername\">Channel Owner Name: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_ownername\" size=80 value=\"".$channel_ownername."\">\r\n";			
				
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"ch_owneremail\">Channel Owner Email: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_owneremail\" size=80 value=\"".$channel_owneremail."\">\r\n";						
				
				// next ones added 28-October-2005
					
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"ch_episode_title\">Episode's Default Title: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_episode_title\" size=80 value=\"$episode_default_title\">\r\n";			
		
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"ch_episode_subtitle\">Episode's Default Subtitle: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_episode_subtitle\" size=80 value=\"$episode_default_subtitle\">\r\n";
				
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"ch_episode_author\">Episode's Default Author: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_episode_author\" size=80 value=\"$episode_default_author\">\r\n";			
				
				// ...........
	
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"ch_link\">Link: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_link\" size=80 value=\"".$channel_link."\">\r\n";
				
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"ch_image\">Image URL: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_image\" size=80 value=\"$channel_image\">\r\n";
				
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"ch_audio_url_prefix\">Audio URL prefix: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_audio_url_prefix\" size=80 value=\"$channel_audio_url_prefix\">\r\n";
						
			}
			
			echo "<tr>\r\n";
			echo "<td>\r\n";
			echo "<LABEL for=\"channel_audio_format\">Audio format: </LABEL>\r\n";
			echo "<td>\r\n";
			echo "<INPUT type=\"text\" name=\"ch_audio_format\" size=80 value=\"".$channel_audio_format."\">\r\n";		
			
			echo "<tr>\r\n";
			echo "<td>\r\n";
			echo "<LABEL for=\"ch_dest_audio_filename\">Destination audio filename: </LABEL>\r\n";
			echo "<td>\r\n";
			echo "<INPUT type=\"text\" name=\"ch_dest_audio_filename\" size=80 value=\"".$channel_dest_audio_filename."\">\r\n";			
			
			echo "<tr>\r\n";
			echo "<td>\r\n";
			echo "Upload method\r\n";
			echo "<td>\r\n";
			echo "<SELECT name=\"ch_upload_method\">\r\n";
			
			$option_one = ($channel_upload_method == 0) ? " selected" : "";
			$option_two = ($channel_upload_method == 1) ? " selected" : "";	
			
			echo "<OPTION $option_one value=\"0\">FTP</OPTION>\r\n";
			echo "<OPTION $option_two value=\"1\">UNC</OPTION>\r\n";
			echo "</SELECT>\r\n";
			echo " (Submit modifications now to access available upload fields)";
	
			if ($channel_upload_method == 0)
			{
				// new 2006-04-17
				echo "<tr><td><td><center>---- Audio upload details----</center>\r\n";
						
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"channel_ftp_server\">FTP server: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_ftp_server\" size=80 value=\"".$channel_ftp_server."\">\r\n";
				
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"channel_ftp_user\">FTP username: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_ftp_user\" size=80 value=\"".$channel_ftp_user."\">\r\n";
				
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"ch_ftp_password\">FTP password: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_ftp_password\" size=80 value=\"".$channel_ftp_password."\">\r\n";
				
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"ch_ftp_audio_path\">FTP path: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_ftp_audio_path\" size=80 value=\"".$channel_ftp_audio_path."\">\r\n";
				
				// new 2006-04-17
				echo "<tr><td><td><center>---- XML upload details----<br>(<i>Leave blank to use same settings as audio upload</i>)</center>\r\n";
				
				// new 2006-04-17 -- new vars: $channel_ftp_xml_server, $channel_ftp_xml_user, $channel_ftp_xml_password
				
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"channel_ftp_xml_server\">FTP server: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_ftp_xml_server\" size=80 value=\"".$channel_ftp_xml_server."\">\r\n";
				
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"channel_ftp_xml_user\">FTP username: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_ftp_xml_user\" size=80 value=\"".$channel_ftp_xml_user."\">\r\n";
				
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"ch_ftp_xml_password\">FTP password: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_ftp_xml_password\" size=80 value=\"".$channel_ftp_xml_password."\">\r\n";			
				
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"ch_dest_xml_path\">FTP path: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_dest_xml_path\" size=80 value=\"".$channel_ftp_xml_path."\">\r\n";					
				
				echo "<tr><td><td><center>-------<br></center>\r\n";
			
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"ch_dest_xml_filename\">Destination XML filename: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_dest_xml_filename\" size=80 value=\"".$channel_dest_xml_filename."\">\r\n";

				if ($pgm_manager)
				{
					echo "<tr>\r\n";
					echo "<td>\r\n";
					echo "<LABEL for=\"ch_password\">Channel Password: </LABEL>\r\n";
					echo "<td>\r\n";
					echo "<INPUT type=\"text\" name=\"ch_password\" size=80 value=\"".$channel_password."\">\r\n";
				}
			
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "Keep\r\n";
				echo "<td>\r\n";
				echo "<SELECT name=\"ch_purging_method\">\r\n";
				
				$option_one = ($channel_purging_method == 0) ? " selected" : "";
				$option_two = ($channel_purging_method == 1) ? " selected" : "";
				$option_three = ($channel_purging_method == 2) ? " selected " : "";
				
				echo "<OPTION $option_one value=\"0\">All available episodes</OPTION>\r\n";
				echo "<OPTION $option_two value=\"1\">Latest [n] available episodes</OPTION>\r\n";
				echo "<OPTION $option_three value=\"2\">Since [n] hours</OPTION>\r\n";
				echo "</SELECT>\r\n";
				
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "[n] parameter\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_purging_param1\" size=10 value=\"$channel_purging_param1\">\r\n";
	
			}
			else
			if ($channel_upload_method == 1)
			{
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"ch_unc_path\">UNC path: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"ch_unc_path\" size=80 value=\"".$channel_unc_path."\">\r\n";
			}						
			
			echo "<tr>\r\n";
			echo "<td>\r\n";
			echo "<LABEL for=\"ch_delete\">Delete channel: </LABEL>\r\n";
			echo "<td>\r\n";
			echo "<INPUT type=\"text\" name=\"ch_delete\" size=6>\r\n";	
						
			echo "<tr>\r\n";
			echo "<td>\r\n";
			echo "<td>\r\n";
			echo "<INPUT type=\"submit\" value=\"Submit channel modifications\">\r\n";
			
			echo "</table>\r\n";
			
			echo "</FIELDSET>\r\n";
			
			echo "</FORM>\r\n";
		}
		
		if ($restricted_view)
		{	
		
			echo "<FIELDSET>\r\n";
			echo "<LEGEND>Add new episode</LEGEND>";
	
			echo "<FORM action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\r\n";
			
			echo "<input type=\"hidden\" name=\"action\" value=\"addepisode\" />\r\n";
			echo "<input type=\"hidden\" name=\"channel\" value=\"".$Channel_ID."\" />\r\n";
			echo "<INPUT type=\"hidden\" name=\"item_author\" value=\"author-goes-here\" />\r\n";
			echo "<INPUT type=\"hidden\" name=\"item_title\" value=\"title-goes-here\" />\r\n";
				
			echo "Broadcast date/time using <a href=\"addmarker.php\">time markers</a><br>\r\n";	
		
			// let's build an array of recent time markers
			
			$sqlgetmarkers = "SELECT marker FROM podcast_timemarkers ORDER BY marker DESC LIMIT 10";
			
			$result_getmarkers = mysql_query($sqlgetmarkers,$db);
					
			if ($result_getmarkers)
			{
				$values = array();			
			
				while( $rowmarker = mysql_fetch_array($result_getmarkers))
				{				
					array_unshift($values,strtotime($rowmarker[0]));
				}	
				
				echo "From ";
			
				echo "<SELECT name=\"startmarker\">\r\n";

				echo "<OPTION value=\"0\">(select...)</OPTION>\r\n";
				
				foreach ($values as &$value)
				{			
					echo "<OPTION value=\"".$value."\">".date("l @ g:i:s a",$value)."</OPTION>\r\n";
				}			
				echo "</SELECT>\r\n";
							
				echo "Until\r\n";
				
				echo "<SELECT name=\"endmarker\">\r\n";
				
				echo "<OPTION value=\"0\">(select...)</OPTION>\r\n";
				
				foreach ($values as &$value)
				{			
					echo "<OPTION value=\"".$value."\">".date("l @ g:i:s a",$value)."</OPTION>\r\n";
				}			
				echo "</SELECT>\r\n";
				
				echo "<br>\r\n";				
				echo "------OR------<br>\r\n";
			}
					
			echo "Broadcast date/time specified manually<br>";	
			echo "<font size=-1>(use this format: 10pm, 9:20am, 3:14:34 pm)</font><br>\r\n";							
								
			//echo "<INPUT type=\"text\" name=\"startdate\" size=10 value=\"\">\r\n";
			echo "<SELECT name=\"startdate\">\r\n";
			
			$timevar = time();
			
			$daysleft = 3;
			
			while ($daysleft > 0)
			{			
				echo "<OPTION value=\"".date("Y-m-d",$timevar)."\">".date("D d-M-Y",$timevar)."</OPTION>\r\n";
				
				$timevar -= 86400;		
				$daysleft--;
			}
			echo "</SELECT>\r\n";			
		
			echo "<LABEL for=\"timefrom\">From </LABEL>\r\n";
			//echo "<td>\r\n";
			echo "<INPUT type=\"text\" name=\"timefrom\" size=12 value=\"\">\r\n";
			
			echo "<LABEL for=\"timeto\"> until: </LABEL>\r\n";
			//echo "<td>\r\n";
			echo "<INPUT type=\"text\" name=\"timeto\" size=12 value=\"\">\r\n";
			
			
			echo "<p>";
							
			//echo "<tr>\r\n";
			//echo "<td>\r\n";
			echo "<LABEL for=\"item_title\">Title: </LABEL> <font size=-1>[%DATE] will be replaced with the broadcast date, e.g 12-Sep-2006 </font><br>\r\n";
			//echo "<td>\r\n";
			echo "<INPUT type=\"text\" name=\"item_title\" size=80 value=\"Broadcast on [%DATE]\">\r\n<br>";
			
			//echo "<tr>\r\n";
			//echo "<td>\r\n";
			echo "<LABEL for=\"item_subtitle\">Subtitle: </LABEL><br>\r\n";
			//echo "<td>\r\n";
			echo "<INPUT type=\"text\" name=\"item_subtitle\" size=80 value=\"\">\r\n<br>";		
				
			echo "<LABEL for=\"item_summary\">Summary: </LABEL><br>\r\n";
			echo "<TEXTAREA name=\"item_summary\" rows=\"10\" cols=\"60\">";			
			echo "</TEXTAREA>";
			
			echo "<p>\r\n";
			
			echo "<INPUT type=\"submit\" value=\"Add new episode\">\r\n";
			echo "<INPUT type=\"reset\" value=\"Reset form\">\r\n";			
					
			echo "</FORM>\r\n";
			
			echo "</FIELDSET>";		
			

		}				
				
		$query3 = "SELECT EpisodeID FROM `podcast_audiofiles` WHERE ChannelID = $Channel_ID GROUP BY EpisodeID ORDER BY EpisodeID DESC";
			
		$result3 = mysql_query($query3,$db);
		if( !$result3 )
		{
			die("Error executing query");
		}
		
		//$episode_id = 0;
	
		while( $row3 = mysql_fetch_array($result3))
		{
			// for each episode, load its data
			
			$episode_id = $row3[0];
					
			$query4 = "SELECT FieldID, IntValue, StringValue, TimeValue, BlobValue FROM podcast_audiofiles WHERE EpisodeID = $episode_id AND ChannelID = $Channel_ID";
			
			$result4 = mysql_query($query4,$db);
			
			if( !$result4 )
			{
				die("Error executing sub-query for episodes");
			}
			
			$episode_title = $episode_subtitle = $episode_author = $episode_filename = "";
			$episode_summary = "";
			$episode_filelength = 0;
			$episode_status = 0;
			
			$episode_start_time = 0;
			$episode_stop_time = 0;
			
			$episode_pubdate_nicestring = "(Unknown pubdate)";
					
			while( $row4 = mysql_fetch_array($result4))
			{
				switch ($row4[0])
				{
					case 1:
						$episode_title = $row4[2];
						break;
					case 2:
						$episode_subtitle = $row4[2];
						break;
					case 3:
						$episode_author = $row4[2];
						break;
					case 100:
						$episode_filename = $row4[2];
						break;
					case 101:
						$episode_filelength = $row4[1];
						break;
					case 103:
						$episode_status = $row4[1];
						break;
					case 104:
						$Start_Date = date("Ymd",strtotime($row4[3]));
						$Start_Time = date("His",strtotime($row4[3]));
						$episode_pubdate_nicestring = date("r",strtotime($row4[3]));
						break;
					case 105: // episode summary
						$episode_summary = $row4[4];
						break;
					case 106: // start time: (from logger)
						$episode_start_time = $row4[3];
						break;
					case 107: // stop time: (from logger)
						$episode_stop_time = $row4[3];
						break;
				}
			}
			
			quotequotes($episode_title);
			quotequotes($episode_subtitle);
			quotequotes($episode_author);
			quotequotes($episode_summary);
			
			echo "<FORM action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\r\n";
			
			echo "<input type=\"hidden\" name=\"action\" value=\"editepisode\" />\r\n";
			echo "<input type=\"hidden\" name=\"channel\" value=\"".$Channel_ID."\" />\r\n";
			echo "<input type=\"hidden\" name=\"episode\" value=\"$row3[0]\" />\r\n";			
			
			echo "<FIELDSET>\r\n";
			echo "<LEGEND>Episode: $episode_pubdate_nicestring</LEGEND>\r\n";
			
			echo "<table>\r\n";
			
			echo "<tr>\r\n";
			echo "<td>\r\n";
			echo "Filename:\r\n";
			echo "<td>\r\n";
			
			if ($episode_filename != "")
			{
				echo "<a href=\"$channel_audio_url_prefix$episode_filename\">$channel_audio_url_prefix$episode_filename</a>\r\n";
			}
			else
			{
				echo "n/a";
			}
			
			echo "<tr>\r\n";
			echo "<td>\r\n";
			echo "File length:\r\n";
			echo "<td>\r\n";
			echo $episode_filelength."\r\n";
			
			if ($restricted_view) 
			{
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "Start time:\r\n";
				echo "<td>\r\n";
				
				echo "$episode_start_time\r\n";
				
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "Stop time:\r\n";
				echo "<td>\r\n";
				
				echo "$episode_stop_time\r\n";
			}
			
			echo "<tr>\r\n";
			echo "<td valign=top>\r\n";
			echo "Status:\r\n";
			echo "<td>\r\n";
			
			//echo "episiode status for episode $episode_id is $episode_status";
			
			if ($episode_status != 2)
			{
				echo "<font color=red>This espisode is currently unavailable</font><br>\r\n";
				echo "<INPUT name=\"item_takeonline\" type=\"checkbox\">Click here to make this episode available\r\n";
			}
			else
			{
				echo "<font color=green>This episode is currently available</font><br>\r\n";
				echo "<INPUT name=\"item_takeoffline\" type=\"checkbox\">Click here to make this episode unavailable\r\n";
			}
			
			if ($restricted_view == false)
			{			
				echo "<tr>\r\n";
				echo "<td>\r\n";
				echo "<LABEL for=\"item_author\">Author: </LABEL>\r\n";
				echo "<td>\r\n";
				echo "<INPUT type=\"text\" name=\"item_author\" size=80 value=\"$episode_author\">\r\n";			
			}			
			
			echo "<tr>\r\n";
			echo "<td>\r\n";
			echo "<LABEL for=\"item_title\">Title: </LABEL>\r\n";
			echo "<td>\r\n";
			echo "<INPUT type=\"text\" name=\"item_title\" size=80 value=\"$episode_title\">\r\n";
			
			echo "<tr>\r\n";
			echo "<td>\r\n";
			echo "<LABEL for=\"item_subtitle\">Subtitle: </LABEL>\r\n";
			echo "<td>\r\n";
			echo "<INPUT type=\"text\" name=\"item_subtitle\" size=80 value=\"$episode_subtitle\">\r\n";
						
			echo "<tr>\r\n";
			echo "<td valign=top>\r\n";
			echo "<LABEL for=\"item_summary\">Summary: </LABEL>\r\n";
			echo "<td>\r\n";
			echo "<TEXTAREA name=\"item_summary\" rows=\"10\" cols=\"60\">";
			echo "$episode_summary";
			echo "</TEXTAREA>\r\n";
			
			echo "<tr>\r\n";
			echo "<td>\r\n";
			echo "<td>\r\n";
			echo "<INPUT type=\"submit\" value=\"Submit episode modifications\">\r\n";
			
			echo "</table>\r\n";
			
			echo "</FIELDSET>\r\n";
			
			echo "</FORM>\r\n";
			
		} // for each episode
		

		
	
	} // if channel ID specified
	
	echo "</html>\r\n";

?>
