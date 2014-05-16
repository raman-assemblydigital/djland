<?php

function get_include_contents($filename)
{ echo $filename;
   if (is_file($filename))
   {
       ob_start();
       include $filename;
       $contents = ob_get_contents();
       ob_end_clean();
       return $contents;
   }
   return false;
}	

function write_xml_to_tempfile($str)
{
	$temp_filename = tempnam("","");
	$temp_filehandle = fopen($temp_filename, "w");
	fwrite($temp_filehandle, $str);
	fclose($temp_filehandle);
	
	return $temp_filename;
}

function write_file_to_ftp($source_file, $dest_file, $ftp_host, $ftp_username, $ftp_password, $ftp_path)
{
	

	
	$log_me = 'burli podcaster - '.date('D, d M Y').' - <b>'.date(' g:i:s a').'</b>';
	echo '<br/>source file:'.$source_file;
	echo '<br/>dest_file:'.$dest_file;
	echo '<br/>ftp_host:'.$ftp_host;
	echo '<br/>ftp path:'.$ftp_path;
//	$log_me .= '<br/>source file:'.$source_file;


	$log_file = '../logs/log2.html';
	$log_file_contents = file_get_contents($log_file);
	file_put_contents ( $log_file , $log_me.$log_file_contents );
		
		
		/*
	$conn_id = ftp_connect($ftp_host);
	$login_result = ftp_login($conn_id, $ftp_username, $ftp_password);
	
	if ((!$conn_id) || (!$login_result)) 
	{
		echo "Error connecting<br>";
		return;
	}
			
	if ($ftp_path != "")
	{
		ftp_chdir($conn_id,$ftp_path);
	}
	
	echo "Uploading $dest_file to $ftp_host (Path: \"<i>$ftp_path</i>\", File: \"<i>$dest_file</i>\")...<br>";

	$upload = ftp_put($conn_id, $dest_file, $source_file, FTP_BINARY); 
			
	// check upload status
	if (!$upload)
	{
	   echo "FTP upload has failed!";
	} else
	{
	   echo "Uploaded $source_file to $ftp_host as $dest_file";
	}
	
	echo "<p>";
	
	
	*/
}

	define('POD_CONFIG', true);
	require('config.php');	
	
	global $Incoming_Channel_ID;
	
	Header("Content-Type: text/html;");	
	
	echo "<html><head>\r\n";
	echo "<meta http-equiv=\"refresh\" content=\"60\"";
	echo "</head>\r\n\r\n";
	
	$db2 = mysql_connect($db_host, $db_user, $db_pass);
	if( !$db2 )
	{
		die("Error connecting to the Server");
		exit;
	}
	$result = mysql_select_db($db_database, $db2);
	if( !$result )
	{
		die("Error selecting Database");
		exit;
	}	
	
    $query = "SELECT ChannelID, StringValue FROM `podcast_channels` WHERE FieldID = 1";

	$result = mysql_query($query,$db2);
	if( !$result )
    {
		die("Error executing query");
	}
	
	$channel_ID =
	$channel_title = $channel_subtitle = $channel_author = $channel_link = $channel_ftp_server = $channel_ftp_user = $channel_ftp_password = 
		$channel_ftp_audio_format = $channel_unc_path = $channel_purging_method = $channel_purging_param1 = "";	
		
	echo "<b>FTP uploader (revision date: 2006-04-17) ...</b><p>\r\n\r\n";
	
	 while( $row = mysql_fetch_array($result))
	 {
		$channel_ID =
		$channel_title = $channel_subtitle = $channel_author = $channel_link = $channel_ftp_server = $channel_ftp_user = $channel_ftp_password = 
		$channel_ftp_audio_format = $channel_unc_path = $channel_purging_method = $channel_purging_param1 = "";		 
		
		$channel_ftp_xml_server = $channel_ftp_xml_user = $channel_ftp_xml_password = "";
	 
	 	$channel_ID = $row[0];
		
		$query22 = "SELECT FieldID, IntValue, StringValue, TimeValue, BlobValue FROM `podcast_channels` WHERE ChannelID = $channel_ID";
	
		//echo "about to run query $query22 ($db2)...<br>\r\n";
		$result22 = mysql_query($query22,$db2);
		//echo "Ran query $query22...<br>\r\n";	
		if( !$result22 )
		{
			die($query22);
		}
		//echo "Continuing here...<p>\r\n\r\n";
				echo 'bunion';
		while( $pgm_row2 = mysql_fetch_array($result22))
		{		
			// echo "$pgm_row2[0] is $pgm_row2[2]<br>";		
			
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
				case 24:
					$channel_ftp_xml_server = $pgm_row2[2];
					break;
				case 25:
					$channel_ftp_xml_user = $pgm_row2[2];
					break;
				case 26:
					$channel_ftp_xml_password = $pgm_row2[2];
					break;					
					
					
			} // switch		
		print_r($pgm_row2);			
		} // which (each field)
		$target_okay = ( $channel_ftp_server != "" || $channel_ftp_xml_server != "");
		
		if (isset($xml_output_dir))
		{
			$target_okay = true;
		}
					
		if ($channel_upload_method == 0 && $target_okay && $channel_dest_xml_filename != "")
		{
			echo "<b>Updating $channel_title...</b><p><ul>";
			
			$Incoming_Channel_ID = $channel_ID;
			$string = get_include_contents('podcasts.php');	
			if (isset($xml_output_dir))
			{
				$target_xmlfile = $xml_output_dir.'\\'.$channel_dest_xml_filename;
				echo "Writing to $target_xmlfile";
				
				$temp_filehandle = fopen($target_xmlfile, "w");
				fwrite($temp_filehandle, $string);
				fclose($temp_filehandle);
				
				echo "</ul>\r\n\r\nhihihi ".$temp_filehandle;
				continue;
			}
			
			$output1 = write_xml_to_tempfile($string);
			
			// new 2006-04-17 -- check if xml ftp server info is being provided
			
			$path_to_use = "";
			
			if ($channel_ftp_xml_path != "")
			{
				$path_to_use = $channel_ftp_xml_path;
			}
			else
			{
				$path_to_use = $channel_ftp_path;
			}
			
			if ($channel_ftp_xml_server != "")
			{
			
				write_file_to_ftp(
				$output1,
				$channel_dest_xml_filename,
				$channel_ftp_xml_server,
				$channel_ftp_xml_user,
				$channel_ftp_xml_password,
				$path_to_use
				);
			
			}
			else
			{			
				write_file_to_ftp(
				$output1,
				$channel_dest_xml_filename,
				$channel_ftp_server,
				$channel_ftp_user,
				$channel_ftp_password,
				$path_to_use
				);
			}
			
			unlink($output1);
			
			echo "</ul>\r\n\r\n";

		}
		else
		{
			//echo "$channel_title is UNC upload method<p>";
		}

		mysql_free_result($result22);

	 } // for each channel

	 mysql_free_result($result);
     //mysql_close($db2);
		
?>
