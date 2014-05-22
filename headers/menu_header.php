<?php
//MENU HEADER


function print_menu() {

	printf("<table class=menu border=0 align=center><tr>");

	if(is_member("member") && get_username() != "citrdjs") {
		printf("<td class=menu><a href=useradd.php?action=list>&nbsp;Users&nbsp;</a></td>");
	}
	if(is_member("membership")) {
		printf("<td class=menu><a href=\"membership.php\">&nbsp;Membership&nbsp;</a></td>");
	}
	if(is_member("library")) {
		printf("<td class=menu><a href=\"library.php\">&nbsp;Library&nbsp;</a></td>");
	}
	if(is_member("editlibrary")) {
		printf("<td class=menu><a href=\"library.php?action=add\">&nbsp;Update Library&nbsp;</a></td>");
	}
	if(is_member("addshow")) {
		echo "<td class=menu><a href=\"shows.php?action=list\">&nbsp;Shows&nbsp;</a></td>";
		echo "<td class=menu><a href=\"adscheduler.php\">&nbsp;Ad Scheduler&nbsp;</a></td>";
		echo "<td class=menu><a href=\"adreport.php\">&nbsp;Ad Report&nbsp;</a></td>";
		echo "<td class=menu><a href=\"charting.php\">&nbsp;Charts&nbsp;</a></td>";
		echo "<td class=menu><a href=\"crtcreport.php\">&nbsp;CRTC Report&nbsp;</a></td>";
	}
	if(is_member("dj")) {
		printf("<td class=menu><a href=\"playsheet.php?action=list\">&nbsp;Playsheets&nbsp;</a></td>");
		printf("<td class=menu><a href=\"playsheet.php\">&nbsp;New Playsheet&nbsp;</a></td>");
		//printf("<td class=menu><a href=\"playsheet.php?action=report\">&nbsp;Report&nbsp;</a></td>");
		print("<td class=menu><a href='report.php'>Show Report</a></td>");
		print("<td class=menu><a href='help.php' target='_blank'> Help </a></td>");
	}
	printf("<td class=menu><A HREF=\"index.php?action=logout\">&nbsp;Log Out&nbsp;</a></td>");

	printf("</tr></table>");

	if(isset($_SESSION['sv_login_fails']) && $_SESSION['sv_login_fails']) {
		printf("<br>");
		printf("<BR><center>WARNING!<BR>%s Login Failures</center>\n", $_SESSION['sv_login_fails']);
	}
}
?>

<?php
function print_menu2(){
?>


	<div class="container">
		<div class="navbar navbar-default" role="navigation">
			<div class="container-fluid">
				<div class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
					<?php if(is_member("member") && get_username() != "citrdjs"): ?>
						<li><a href=useradd.php?action=list>Users</a></li>
					<?php endif; 
					if(is_member("membership")) : ?>
						<li><a href="membership.php">Membership</a></li>	
					<?php endif; 
					if(is_member("library")) : ?>
						<li><a href="library.php"> View Library</a></li>
					<?php endif;
					if(is_member("editlibrary")) : ?>
						<li><a href="library.php?action=add">Update Library</a></li>
					<?php endif;
					if(is_member("addshow")) : ?>
						<li><a href="shows.php?action=list">Shows</a></li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">Manage Ads<b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li><a href="adscheduler.php">Ad Scheduler</a></li>
								<li class="divider"></li>
								<li><a href="adreport.php">Ad Reporting</a></li>
							</ul>
						</li>
						<li><a href="charting.php">Charts</a></li>
						
					<?php endif; ?>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">Reports<b class="caret"></b></a>
							<ul class="dropdown-menu">
								<?php if(is_member("dj")) : ?> 
									<li><a href="report.php">Show Report</a></li> 
								<?php endif;
								if(is_member("addshow")) : ?>
									<li class="divider"></li>
									<li><a href="crtcreport.php">CRTC Report</a></li> 
								<?php endif; ?>
							</ul>
						</li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">Playsheets<b class="caret"></b></a>
							<ul class="dropdown-menu">
								<?php if(is_member("dj")) : ?> 
									<li><a href="playsheet.php?action=list">Open a Playsheet</a></li>
									<li><a href="playsheet.php">New Playsheet</a></li> 
								<?php endif; ?>
							</ul>
						</li>
					
					
					
					<?php if(is_member("dj")) : ?>
						<li><a href="help.php" target="_blank"> Help </a></li>
					<?php endif; ?>
				
					</ul>
					<ul class="nav navbar-nav navbar-right">
					<li><a href="index.php?action=logout">Log Out</a></td>
					</ul>
				</div>
			</div>
		</div>
	</div>
<?php
}
?>