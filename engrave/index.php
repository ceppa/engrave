<?php
//require_once("include/datetime.php");
require_once("config.php");
require_once("include/util.php");


do_header();
do_footer();

function do_header()
{
	global $version,$siteName;
	$height=40;
	$width="33%";
	$ie=strstr($_SERVER["HTTP_USER_AGENT"],"MSIE");
	if($ie)
		echo '﻿<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
	else
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it">
	<head>
	<link rel="icon" href="favicon.png" />
	<title><?=$siteName?></title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="description" content="envysoft secure authentication" />
	<meta name="keywords" content="php,javascript,authentication,md5,hashing,php,javascript,authenticating,auth,AUTH,secure,secure login,security,php and javascript secure authentication,combat session fixation!" />
	<script type="text/javascript" src="js/md5.js"></script>
	<script type="text/javascript" src="js/datetime.js"></script>
	<script type="text/javascript" src="js/util.js"></script>
	<script type="text/javascript" src="js/jquery-1.8.2.js"></script>
	<script type="text/javascript" src="js/jquery-ui.js"></script>
	<script type="text/javascript" src="js/jquery-ui-timepicker-addon.js"></script>
	<script type="text/javascript" src="js/jquery.autocomplete.js"></script>
	<script type="text/javascript" src="js/flexigrid.js"></script>
	<script type="text/javascript" src="js/forms.js"></script>
	<script type="text/javascript" src="js/auth.js"></script>
	<script type="text/javascript" src="js/main.js"></script>
	<script type="text/javascript" src="js/menu.js"></script>
	<script type="text/javascript" src="js/users.js"></script>
	<script type="text/javascript" src="js/stampe.js"></script>
	<script type="text/javascript" src="js/clienti.js"></script>
	<script type="text/javascript" src="js/parti.js"></script>
	<script type="text/javascript" src="js/fornitori.js"></script>
	<script type="text/javascript" src="js/magazzino.js"></script>
	<script type="text/javascript" src="js/magazzino_carico.js"></script>
	<script type="text/javascript" src="js/magazzino_scarico.js"></script>
	<script type="text/javascript" src="js/fatture.js"></script>
	<script type="text/javascript" src="js/ricevute.js"></script>
	<script type="text/javascript" src="js/ddt.js"></script>
	<script type="text/javascript" src="js/cassa.js"></script>
	<script type="text/javascript" src="js/mailing_list.js"></script>
	<script type="text/javascript" src="js/preventivi.js"></script>
	<link rel="stylesheet" type="text/css" href="css/menu.css" />
	<link rel="stylesheet" type="text/css" href="css/jquery-ui.css" />
	<link rel="stylesheet" type="text/css" href="css/jquery-ui-timepicker-addon.css" />
	<link rel="stylesheet" type="text/css" href="css/style.css" title="envysheet" />
	<link rel="stylesheet" type="text/css" href="css/flexigrid.pack.css" />
	<link rel="stylesheet" type="text/css" href="css/autocomplete.css" />
	<link rel="stylesheet" type="text/css" href="css/dialog.css" />
	</head>
	<body>
	<div id="header" style="display:none">
		<table class="tab_header">
			<tr>
				<td style="width:25%;text-align:left">
					<img src="img/logo.png" alt="logo" height="<?=$height?>" />
				</td>
				<td id="titolo">
				</td>
				<td style="width:25%;height:<?=$height?>px;text-align:right; margin:0px; padding:0px; vertical-align: top;white-space: nowrap;">
					<input type="button" class="button" value="Esci" 
						name="logout" onclick="do_logout()"/>
				</td>
			</tr>
		</table>
	</div>
	<div id="admin_nav"	style="display:none">
	</div>
	<div id="content"></div>
	<div id="flexi" style="display:none"></div>
<?
}

function do_footer()
{?>
	<div id="messageBox" style="display:none">
	</div>
	</body>
	</html>
<?}

?>
