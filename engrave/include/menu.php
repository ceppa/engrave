<?
require_once("../config.php");

$op = $_REQUEST['op'];
if($op=="display_admin_nav")
{
	display_admin_nav();
	die();
}

function display_admin_nav()
{
	$ops=array
		(
			"toolbar_magazzino"=>array
			(
				"name"=>"magazzino",
				"level"=>0,
				"submenu"=>array(
					"toolbar_magazzino"=>"situazione",
					"toolbar_parti"=>"parti",
					"toolbar_carico"=>"carico",
					"toolbar_scarico"=>"scarico"
				)
			),
			"toolbar_documenti"=>array
			(
				"name"=>"documenti",
				"level"=>0,
				"submenu"=>array(
					"toolbar_fatture"=>"fatture",
					"toolbar_ricevute"=>"ricevute",
					"toolbar_ddt"=>"DDT",
					"toolbar_preventivi"=>"preventivi",
					"toolbar_acquisti"=>"acquisti"
				)
			),
			"toolbar_anagrafica"=>array
			(
				"name"=>"anagrafica",
				"level"=>0,
				"submenu"=>array(
					"toolbar_clienti"=>"clienti",
					"toolbar_fornitori"=>"fornitori",
					"toolbar_mailing_list"=>"mailing list"
					)
			),
			"toolbar_cassa"=>array
			(
				"name"=>"cassa",
				"level"=>0
			),
			"toolbar_stampe"=>array
			(
				"name"=>"stampe",
				"level"=>0,
				"submenu"=>array(
					"toolbar_print_warehouse"=>"magazzino",
					"toolbar_print_carichi"=>"carichi",
					"toolbar_print_scarichi"=>"scarichi",
					"toolbar_print_giacenze_critiche"=>"giacenze critiche",
					"toolbar_select_fatture"=>"fatture",
					"toolbar_select_cassa"=>"cassa",
					"toolbar_corrispettivi"=>"registro corrisp."
					)
			),
			"toolbar_users"=>array
			(
				"name"=>"utenti",
				"level"=>2
			)
		);


	echo '<ul id="nav" class="drop">';


	$livello=$_SESSION["livello"];
	foreach($ops as $k=>$v)
	{
		$minlevel=$v["level"];
		if($livello>=$minlevel)
		{
			$name=$v["name"];
			if(isset($v["submenu"])&&is_array($v["submenu"]))
			{
				echo '<li><span>'.$v["name"].'</span>';
				echo '<ul>';
				foreach($v["submenu"] as $module=>$text)
					echo '<li onclick="'.$module.'()"><span>'.$text.'</span></li>';
				echo '</ul>';
				echo '</li>';
			}
			else
				echo '<li onclick="'.$k.'()"><span>'.$v["name"].'</span></li>';

		}
	}
	echo '</ul>';
}
?>
