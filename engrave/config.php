<?
    ini_set('include_path',get_include_path().PATH_SEPARATOR.'/home/engravel/php');
    ini_set("error_reporting",E_ALL & ~E_NOTICE & ~E_STRICT);

    date_default_timezone_set("Europe/Rome");
	$siteName="http://www.engravelab.it/";
	$siteAddress="http://www.engravelab.it/";
	ini_set ('session.name', '$siteName');
	session_start();
?>