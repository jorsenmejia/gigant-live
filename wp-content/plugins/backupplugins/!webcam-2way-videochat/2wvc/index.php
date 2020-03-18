<?php
$r=$_GET['r'];
include_once("incsan.php");
sanV($r);

$swfurl="2wvc.swf?room=" . urlencode($r). "&extension=_none_&prefix=" . urlencode("../../../../wp-admin/admin-ajax.php?action=v2wvc&task=");
$bgcolor="#333333";
$baseurl="";
$wmode="transparent";
?>
<html lang="en">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<title>2 Way Video Chat by VideoWhisper.com</title>
<style type="text/css">
<!--
BODY
{
margin:0px;
	background-color: #333;
}

#2wayvideochat
{
width:100%;
height:700px;
	z-index:0;
	vertical-align: middle;
	text-align: center;
}
-->
</style>
<SCRIPT language="JavaScript">
//the code below allows activating certain functions from javascript
function getFlashMovie(movieName) {
	
   if (navigator.appName.indexOf("Microsoft") != -1) {
        //alert("IE");
        if (typeof (window[movieName].videowhisperToActionscript) == 'function') {
            // alert("< IE9");
            movie = window[movieName];
        }
        else if (typeof (document[movieName].videowhisperToActionscript) == 'function') {
            // alert(">= IE9");
            movie = document[movieName];
        }
    }
    else {
        // alert("NON IE");
        movie = document[movieName];
    }

    return movie;
}

//flash = flash html object name (ie "videowhisper_chat")
//action = next / snapshot / snapshot_self / buzz / p2p_toggle
function videowhisperCallActionscript(flash, action) 
{
    var movie = getFlashMovie(flash);
	if (movie == null || movie == undefined) window.alert("Flash element not found:" + flash + " :" + movie);
	else movie.videowhisperToActionscript(action);
}
</SCRIPT>
</head>
<BODY>
<CENTER>

<div id="2wayvideochat">

<object id="videowhisper_chat" width="1000" height="700" type="application/x-shockwave-flash" data="<?=$swfurl?>">
<param name="movie" value="<?=$swfurl?>" /><param name="bgcolor" value="<?=$bgcolor?>" /><param name="salign" value="lt" /><param name="scale" value="noscale" /><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /> <param name="base" value="<?=$baseurl?>" /> <param name="wmode" value="<?=$wmode?>" /> 
</object>

<noscript>
<p align="center"><strong>This content requires the Adobe Flash Player:
<a href="http://get.adobe.com/flashplayer/">Get Latest Flash</a></strong>!</p>
</noscript>

</div>
</CENTER>
</BODY>
</html>
