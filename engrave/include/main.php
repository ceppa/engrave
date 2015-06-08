<?
require_once("const.php");
require_once("../config.php");

function display_admin_nav()
{
	$cellWidth=91;
	$livello=$_SESSION["livello"];
	$ops=array
		(
			"toolbar_movimenti"=>array
			(
				"name"=>"movimenti",
				"level"=>0,
				"submenu"=>array(
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
					"toolbar_ddt"=>"DDT"
				)
			),
			"toolbar_anagrafica"=>array
			(
				"name"=>"anagrafica",
				"level"=>0,
				"submenu"=>array(
					"toolbar_clienti"=>"clienti",
					"toolbar_fornitori"=>"fornitori"
					)
			),
			"toolbar_stampe"=>array
			(
				"name"=>"stampe",
				"level"=>0
			),
			"toolbar_users"=>array
			(
				"name"=>"utenti",
				"level"=>3
			)
		);
	?>
		<table class="admin_nav">
		<tr>
			<td>
	<?
	$i=0;
	$submenus=array();
	$nc=count($ops);
	foreach($ops as $k=>$v)
	{
		$minlevel=$v["level"];
		if($livello>=$minlevel)
		{
			$name=$v["name"];
			if(!is_array($v["submenu"]))
			{
				$v["submenu"]=array();
				$class="menuitem";
			}
			else
			{
				$class="menuparent";
				$submenus[$k]["submenu"]=$v["submenu"];
				$submenus[$k]["menu_order"]=$i;
			}


	?>
			<div class="toolbar toolbar_mainmenu <?=$class?>" id="<?=$k?>" >
				<?=$name?>
			</div>

		<?
			$i++;
		}
	}?>
			</td>
		</tr>
	<?
	foreach($submenus as $module=>$submenu)
	{
		$items=$submenu["submenu"];
		$menu_order=$submenu["menu_order"];
		$left=(int)$menu_order * $cellWidth-1;
		?>
		
		<tr id="row_<?=$module?>" style="display:none">
			<td>
		<?
			if($menu_order>0)
			{?>
				<div class="menuitem_void" style="width:<?=$left?>px">
					&nbsp;
				</div>
			<?}?>
		<?
		$first=1;
		foreach($items as $k=>$name)
		{
			$addclass=($first?" toolbar_submenu_first":"");
			$first=0;
		?>
			<div class="toolbar toolbar_submenu menuitem<?=$addclass?>" id="<?=$k?>" >
				<?=$name?>
			</div>
		<?}
		?>
			</td>
		</tr>
	<?}?>
	
		</table>
		
	<?
}

?>
