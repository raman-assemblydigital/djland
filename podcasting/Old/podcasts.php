<?php

//define('POD_CONFIG', true); // remmed 2006-03-28 (see config.php -- no longer require this definition)

require('config.php');

	if(!defined('POD_FUNCTIONS_DEFINED'))
	{
		// we are called by FTP_UPLOAD.PHP, so make sure functions are defined only once

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
		
		function echowrite($thestr, $handle)
		{
			echo $thestr;
			
			if ($handle != 0)
			{
				fwrite($handle, $thestr);
			}
		}
				
		function gettimestamp()
		{
			global $nYear, $nMonth, $nDay;
			return mktime(0, 0, 0, $nMonth, $nDay, $nYear);
		}
		
	
		function htmlprep(&$s)
		{
			$s = str_replace("&", "&amp;", $s);	
			$s = str_replace("\"", "&quot;", $s);			
			
			$s = str_replace("<","&lt;", $s);
			$s = str_replace(">", "&gt;", $s);
		}		
		
		define('POD_FUNCTIONS_DEFINED', true);
	
	}

	$db = mysql_connect($db_host, $db_user, $db_pass);
	if( !$db )
	{
		die("Error connecting to the Server");
		exit;
	}
	$result = mysql_select_db($db_database, $db);
	if( !$result )
	{
		die("Error selecting Database: $db_database");
		exit;
	}

	global $Incoming_Channel_ID;

	if ($Incoming_Channel_ID)
	{
		$Channel_ID = $Incoming_Channel_ID;
	}
	else
	{
		$Channel_ID = get_param("ChannelID", "string", "");
	}

    if ($Channel_ID == "")
    {
		Header("Content-Type: text/html;");

	    $query = "SELECT ChannelID, StringValue FROM `podcast_channels` WHERE FieldID = 1";

	    $result = mysql_query($query,$db);
	     if( !$result )
	     {
	        die("Error executing query");
	     }

		echo "<html><title>List of available channels</title>\r\n";

	     while( $row = mysql_fetch_array($result))
	     {
			echo "<a href=\"podcasts.php?ChannelID=".$row[0]."\">".$row[1]."</a>";
			echo "<p>";

	        //$nrow++; <--- this is not needed
	     }

		echo "</html>\r\n";


    }
    else
    {
//	    Let's get some basic info about this 'program'

	    $pgm_query = "SELECT FieldID, IntValue, StringValue, TimeValue, BlobValue FROM `podcast_channels` WHERE ChannelID = $Channel_ID";
		
		$pgm_result = mysql_query($pgm_query,$db);
	     	if( !$pgm_result )
		{
	        	die($pgm_query);
		}

// 1: full title
// 2: sub-title
// 3: artist
// 4: link
// 5: image

		$channel_purge_method = $changel_purge_param1 = 0;
		
		$channel_ownername = $channel_owneremail = "";

		while( $pgm_row = mysql_fetch_array($pgm_result))
		{			
			switch ($pgm_row[0])
			{
				case 1:
					$channel_title = $pgm_row[2];
					break;
				case 2:
					$channel_subtitle = $pgm_row[2];
					break;
				case 3:
					$channel_author = $pgm_row[2];
					break;
				case 4:
					$channel_link = $pgm_row[2];
					break;
				case 5:
					$channel_image = $pgm_row[2];
					break;
				case 12: // NEW
					$channel_purge_method = $pgm_row[1];
					break;
				case 13: // NEW
					$channel_purge_param1 = $pgm_row[1];
					break;
				case 17:
					$channel_audio_user_prefix = $pgm_row[2];
					break;	
				case 27:
					$channel_keywords = $pgm_row[2];
					break;	
				case 28:
					$channel_summary = $pgm_row[4]; // note -- we get from field 4 (longtext)
					break;
				case 29:
					$channel_ownername = $pgm_row[2];
					break;	
				case 30:
					$channel_owneremail = $pgm_row[2];
					break;						
			}
			
			//$channel_author = $pgm_row[2];
			//$channel_subtitle = $pgm_row[3];
			//$channel_link = $pgm_row[5];

		}
		
		//echo "Title is $channel_title, Subtitle is $channel_subtitle, author is $channel_author, link is $channel_link<p>";		

		if ($channel_title == "")
		{
			$channel_title = $Provider.": ".$Prog_ID;
		}

//		echo "<title>CiTR PodCast: ".$Prog_ID."</title>\n";

		$query = "SELECT EpisodeID FROM `podcast_audiofiles` WHERE ChannelID = $Channel_ID GROUP BY EpisodeID"; // by default

		switch ($channel_purge_method)
		{
			case 1:
				//$query = "SELECT EpisodeID FROM `podcast_audiofiles` WHERE ChannelID = $Channel_ID AND FieldID = 104 ORDER BY TimeValue DESC LIMIT $channel_purge_param1";
				
				$query =
					"SELECT T1.EpisodeID, T1.TimeValue FROM `podcast_audiofiles` AS T1, `podcast_audiofiles` AS T2 
					WHERE T1.ChannelID = $Channel_ID AND T2.ChannelID = $Channel_ID
					AND T1.EpisodeID = T2.EpisodeID 
					AND T1.FieldID = 104
					AND T2.FieldID = 103 AND T2.IntValue = 2
					ORDER BY TimeValue DESC
					LIMIT $channel_purge_param1";
				
				//echo "$query";

				break;
			case 2:
				$earliest_time = time() - ($channel_purge_param1 * 60 * 60);
				$earliest_time_as_string = date("c",$earliest_time);				
				
				$query =
					"SELECT T1.EpisodeID, T1.TimeValue FROM `podcast_audiofiles` AS T1, `podcast_audiofiles` AS T2 
					WHERE T1.ChannelID = $Channel_ID AND T2.ChannelID = $Channel_ID 
					AND T1.EpisodeID = T2.EpisodeID AND T1.FieldID = 104 AND T1.TimeValue >= '$earliest_time_as_string' 
					AND T2.FieldID = 103 AND T2.IntValue = 2 ORDER BY TimeValue DESC";
				break;
		}
		
		//$query = "SELECT EpisodeID FROM `podcast_audiofiles` WHERE ChannelID = $Channel_ID GROUP BY EpisodeID";
		
		$result = mysql_query($query,$db);
		if( !$result )
		{
			die("Error executing query:<p>$query");
		}

		if (!$Incoming_Channel_ID)
		{
			Header("Content-Type: application/xml;");
		}
		
		//$write_handle = fopen("c:\\temp\\test-rss-1.xml","a+");
		
		echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>\r\n";
		
		if (isset($xml_stylesheet))
		{
			echo "<?xml-stylesheet title=\"XSL_formatting\" type=\"text/xsl\" href=\"".$xml_stylesheet."\"?>\r\n";
		}
		
		//fclose($write_handle);
		
		htmlprep($channel_summary);
		htmlprep($channel_title);		
		htmlprep($channel_subtitle);
		htmlprep($channel_keywords);
		htmlprep($channel_author);		
		htmlprep($channel_ownername);
		htmlprep($channel_owneremail);		
		
		echo "<rss xmlns:itunes=\"http://www.itunes.com/DTDs/Podcast-1.0.dtd\" version=\"2.0\" >\n";
		echo "<channel>\n";
		//		echo "<title>CiTR PodCast: ".$Prog_ID."</title>\n";
		echo "<title>".$channel_title."</title>\r\n";
		
		if ($channel_summary != "") // 2006-05-19 -- new, now we populate the description and itunes:summary field for the channel
		{

			echo "<description>".$channel_summary."</description>\r\n";		
			echo "<itunes:summary>".$channel_summary."</itunes:summary>\r\n";
		}
		
		echo "<itunes:author>".$channel_author."</itunes:author>\r\n";	
		echo "<itunes:subtitle>".$channel_subtitle."</itunes:subtitle>\r\n";
		
		// new 2006-04-17
		if ($channel_keywords != "")
		{
			echo "<itunes:keywords>$channel_keywords</itunes:keywords>\r\n";
		}	
		
		if ($channel_ownername != "" OR $channel_owneremail != "" )
		{			
			echo "<itunes:owner>\r\n";
			echo "<itunes:name>$channel_ownername</itunes:name>\r\n";
			echo "<itunes:email>$channel_owneremail</itunes:email>\r\n";			
			echo "</itunes:owner>\r\n";
		}			
		
		if ($channel_image != "")
		{
			echo "<itunes:image href=\"$channel_image\" />\r\n";
			
			echo "<itunes:link rel=\"image\" type=\"video/jpeg\" href=\"$channel_image\">$channel_title</itunes:link>\r\n\r\n";
			
			//echo "<itunes:image>$channel_image</itunes:image>\r\n";
			
			echo "<image>\r\n";
			echo "<link>$channel_link</link>\r\n";
			echo "<url>$channel_image</url>\r\n";
			echo "<title>$channel_title</title>\r\n";
			echo "</image>\r\n\r\n";
		}
		
		echo "<link>".$channel_link."</link>\n";
		//echo "<lastBuildDate></lastBuildDate>\n";
		echo "<generator>BurliSoftwareInc-2006-09-27</generator>\r\n\r\n";

		while( $row = mysql_fetch_array($result))
		{
			$query2 = "SELECT FieldID, IntValue, StringValue, TimeValue, BlobValue FROM podcast_audiofiles WHERE EpisodeID = $row[0] AND ChannelID = $Channel_ID";
			
			$result2 = mysql_query($query2,$db);
			
			if( !$result2 )
			{
				die("Error executing sub-query for episodes");
			}
			
			$episode_title = $episode_subtitle = $episode_author = $episode_filename = "";
			$episode_summary = "";
			$episode_filelength = 0;
			$episode_status = 0;
			
			$episode_pubdate = gettimeofday();
			
			while( $row2 = mysql_fetch_array($result2))
			{
				switch ($row2[0])
				{
					case 1:
						$episode_title = $row2[2];
						break;
					case 2:
						$episode_subtitle = $row2[2];
						break;
					case 3:
						$episode_author = $row2[2];
						break;
					case 100:
						$episode_filename = $row2[2];
						break;
					case 101:
						$episode_filelength = $row2[1];
						break;
					case 103:
						$episode_status = $row2[1];
						break;
					case 104:
						$episode_pubdate = date("r",strtotime($row2[3]));
						//$Start_Date = date("Ymd",strtotime($row2[3]));
						//$Start_Time = date("His",strtotime($row2[3]));
						break;
					case 105:
						$episode_summary = $row2[4];
						break;
				}
			}	
			
			htmlprep($episode_title);
			htmlprep($episode_subtitle);		
			htmlprep($episode_summary);		
			
			if ($episode_status < 2)
			{
				continue;
			}
			
			// following line added to assist with logger-to-podcast logic
			if ($episode_filelength == 0)
			{
				continue;
			}			
			
			//die ("Title is $episode_title, subtitle is $episode_subtitle, author is $episode_author, filename is $episode_filename, filelength is $episode_filelength<p>");
			
			mysql_free_result($result2);
				
			echo "<item>\n";
			echo "<title>$episode_title</title>\r\n";
			
			//		echo "<description>".$row[8]."</description>\r\n";
			
			// <pubDate>Mon, 05 Sep 2005 11:01:00 -0700</pubDate>
			echo "<pubDate>$episode_pubdate</pubDate>\r\n"; 
			
			if ($episode_author != "")
			{
				echo "<itunes:author>".$episode_author."</itunes:author>\r\n";
			}
			
			if ($episode_subtitle != "") // fixed on 2006-03-28
			{
				// echo "<itunes:summary>$episode_subtitle</itunes:summary>\r\n"; // now, this is moved to the episode_summary section
				echo "<description>$episode_subtitle</description>\r\n";
				echo "<itunes:subtitle>$episode_subtitle</itunes:subtitle>\r\n";
			}
			
			if ($episode_summary != "") // new on 2006-05-19
			{
				echo "<itunes:summary>$episode_summary</itunes:summary>\r\n";
			}			
			
			// <itunes:duration>58:00</itunes:duration>
			//		echo "<itunes:duration>14:21</itunes:duration>\r\n";
			
			echo "<enclosure url=\"";
			
			$finalprefix = $channel_audio_user_prefix;
			
			if (isset($audio_url_prefix_override))
			{
				$finalprefix = $audio_url_prefix_override;
			}
			
			$finalurl = $finalprefix.$episode_filename;
			
			echo $finalurl;
			echo "\" length=\"$episode_filelength\" type=\"audio/mpeg\"/>\r\n";
			
			//		echo "<media:content url=\"http://cmgtoronto.ca/Newsletters/newsletter070905_3.pdf\" fileSize=\"412182\" type=\"application/pdf\">\r\n";
			//		echo "</media:content>\r\n";
			
			echo "<guid ispermalink=\"true\">".$finalurl."</guid>";
			
			echo "</item>\r\n\r\n";
			
				//$nrow++; <--- this is not needed
	     }

		echo "</channel>\r\n";
		echo "</rss>\r\n";
		
		mysql_free_result($pgm_result);		
     }

     mysql_free_result($result);
     //mysql_close($db);
?>
