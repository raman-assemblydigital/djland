<?php

//define('POD_CONFIG', true);

require_once('config.php');

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
	
	if ( (!isset($_SERVER['PHP_AUTH_USER'])) or ($_SERVER['PHP_AUTH_USER'] != "administrator") or ($_SERVER['PHP_AUTH_PW'] != $pgm_admin_pw ) )
	{
	   header('WWW-Authenticate: Basic realm="CiTRPodcastEditor"');
	   header('HTTP/1.0 401 Unauthorized');
	   echo 'Text to send if user hits Cancel button';
	   exit;
	}	
	
	$security_query =
		"SELECT A.StringValue, B.StringValue
		FROM podcast_channels AS A 
		LEFT JOIN podcast_channels AS B ON A.ChannelID = B.ChannelID
		WHERE A.FieldID = 19 
		AND B.FieldID = 31
		ORDER BY A.StringValue";
			
		
	$result_sec = mysql_query($security_query,$db);
	if( !$result_sec )
	{
		die("Error executing security query");
	}
	
	echo "<html><title>Podcast passwords dump</title>\r\n\r\n";
	echo "<table border=1>\r\n";
	
	echo "<h1>Passwords dump</h1>\r\n\r\n";
		
	while ($row_max = mysql_fetch_array($result_sec))
	{
		echo "<tr><td width=20%>".substr($row_max[0], 0, -4)."<td width=20%>$row_max[1]\r\n";
	}


?>
