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



function gettimestamp()
{
	global $nYear, $nMonth, $nDay;
	return mktime(0, 0, 0, $nMonth, $nDay, $nYear);
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
		die("Error selecting Database");
		exit;
	}

// code checking here
//

    $Requested_Action = get_param("action", "string", "");
    
    //$Channel_ID = get_param("ChannelID", "string", "");    
    
//    $Prog_ID = get_param("programid", "string", "");

	Header("Content-Type: text/html;");

	echo "<html>\r\n";
	
	echo "<head>\r\n";
	echo "<title>Burli Podcast Editor -- Time Event Adder</title>\r\n";
	
	echo "</head>\r\n";
	
	echo "<center>";

	$sqlcreate = "CREATE TABLE IF NOT EXISTS `podcast_timemarkers` (
	  `marker` datetime NOT NULL default '0000-00-00 00:00:00'
		) TYPE=MyISAM; ";
		
	$result_create = mysql_query($sqlcreate,$db);
	
    if ($Requested_Action == "addevent")
    {
		$add_time = time();
		$add_time_as_string = date("Y-m-d\TH:i:s",$add_time);
		$add_query = "INSERT INTO `podcast_timemarkers` (`marker`) VALUES ('$add_time_as_string')";
		//echo "<h3>$add_query</h3>";
		
		$result_add = mysql_query($add_query,$db);		
			
		if($result_add)
		{
			echo "<h4>Success!</h4>";
		}
		else
		{
			echo "<h4>Failure!</h4>";		
		}

    }
	
	echo "<h2>Podcast engine -- add time marker</h2>";
	echo "<font size=2>Form revision date: 2006-07-11</font><p>";
	
	echo "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\r\n";	
	echo "<input type=\"hidden\" name=\"action\" value=\"addevent\" />\r\n";
	echo "<input type=\"submit\" value=\"Add a time marker right now\" />\r\n";	
	echo "</FIELDSET>\r\n";
	echo "</FORM>\r\n";
	
	echo "<a href=\"edit.php\">Return to Podcast editor</a>\r\n";
	
	echo "</html>\r\n";
	
	echo "</center>\r\n";	

?>
