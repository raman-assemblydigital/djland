/**
 * Trigger a popup window to display info of the current song
 */
function songinfo(songID)
{
	var songwin = window.open("songinfo.php?songID="+songID, "songinfowin", "location=no,status=no,menubar=no,scrollbars=yes,height=400,width=650");
	songwin.focus();
}

/**
 * Trigger a popup window to open a Audiorealm player for the current station
 */
function player(stationID)
{
	var playerwin = window.open("http://player.spacialnet.com/players/player.html?stationID="+stationID, "playerWindow", "location=no,status=no,menubar=no,scrollbars=no,resizeable=no");
	playerwin.focus();
}

/**
 * Handle requests on this same webserver
 */
function requestPrivate(songID)
{
	reqwin = window.open("request.php?songID="+songID, "_AR_request", "location=no,status=no,menubar=no,scrollbars=yes,resizeable=yes,height=420,width=668");
	reqwin.focus();
}

/**
 * Use the AudioRealm.com request handler
 */
function requestAudioRealm(songID, samhost, samport)
{
	var path = "http://request.audiorealm.com/req/";
	reqwin = window.open(path+"req.html?songID="+songID+'&samport='+samport+'&samhost='+samhost, "_AR_request", "location=no,status=no,menubar=no,scrollbars=yes,resizeable=yes,height=400,width=550");
	reqwin.focus();
}

/**
 * Hide pictures if show = false
 */
function showPicture(obj, show)
{
	if(show)
	{
		$(obj).show();
	}
	else
	{
		$(obj).hide();
	}
}