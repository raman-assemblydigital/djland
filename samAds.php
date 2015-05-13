<?php 	
	session_start();
	require_once("headers/security_header.php");
	require_once("headers/function_header.php");
	require_once("headers/menu_header.php");
if( permission_level() >= $djland_permission_levels['workstudy']){
?>
<html>
	<head>
		<meta name=ROBOTS content=\"NOINDEX, NOFOLLOW\">
		<meta charset="utf-8">
		<link rel=stylesheet href='css/style.css' type='text/css'>

		<title>DJLAND | Sam Ads</title>

		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script src="js/jquery.form.js"></script> 
		<script type='text/javascript' src='js/samAds.js'></script>
		<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" />
		<script src="http://code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
		<script>
			$(function() {
			$( ".datepicker" ).datepicker({ dateFormat: "yy-mm-dd" });
			});
		</script>
	</head>
	<body>
		<?php print_menu();
		$today = date('m/d/Y');?>
		<center><br/><br/><br/><br/>
		<input id='adname' onfocus="this.value=''" value='Enter an ad name' maxlength='15'></input>
		<label for="from">Start Date: </label>
		<input type="text" id="from" name="from" value="<?=$today ?>"/>
			
		<label for="to">End Date: </label>
		<input type="text" id="to" name="to" value="<?=$today ?>"/>
		<button id="submitDates">View Sam Ads</button>
		<img src='images/loading.gif' id='loadbar' style='display:none;'>
		
		<div id=samAds style='display:none;'>
            <div class=samtitleh>Ad Name</div>
            <div class=samplayedh>Time Played</div>
		</div>
	</body>
</html>
<?php
}else{
    header("Location: main.php");
}?>