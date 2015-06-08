<?
require_once('mysql.php');
require_once('const.php');
require_once('util.php');
require_once('../config.php');


$op=$_REQUEST["op"];
switch($op)
{
	case "list":
		$page=$_POST["page"];
		
		$rp=$_POST["rp"];
		$q=$_POST["query"];
		$qtype=$_POST["qtype"];
		$sortname=$_POST["sortname"];
		$sortorder=$_POST["sortorder"];
		$limit=sprintf("%d,%d",(int)($page-1)*$rp,(int)$rp);
	
		$parti_string="GROUP_CONCAT(CONCAT(parti.codice,'←',
						parti.descrizione,'←',magazzino.qta,'←',
						magazzino.prezzo,'←',um.um) SEPARATOR '↓')";
		$where="$qtype LIKE '%$q%'";
		
		$conn=opendb();
		$query="SELECT
				anag_fornitori.denominazione AS fornitore,
				magazzino_carico.data_inserimento,
				magazzino_carico.numero_fattura AS numero_fattura,
				magazzino_carico.data_fattura AS data_fattura,
				$parti_string AS lista_parti 
			FROM magazzino_carico
					LEFT JOIN magazzino ON magazzino_carico.id=magazzino.id_carico
					LEFT JOIN anag_fornitori 
						ON magazzino_carico.id_fornitore=anag_fornitori.id_fornitore
					LEFT JOIN parti ON magazzino.id_parte=parti.id 
					LEFT JOIN um ON parti.um_id=um.id
			GROUP BY magazzino_carico.id
			HAVING $where";
//		$fp = fopen('error.txt', 'w');
//		fwrite($fp, "$qtype\n$query\n");
//		fclose($fp);

		$result=do_query($query,$conn);
		$total=$result->num_rows;
		$result->free();
	
		$query="SELECT magazzino_carico.id,magazzino_carico.data_inserimento,
					anag_fornitori.denominazione AS fornitore,
					magazzino_carico.numero_fattura AS numero_fattura,
					magazzino_carico.data_fattura AS data_fattura,
					$parti_string AS lista_parti 
				FROM magazzino_carico
						LEFT JOIN magazzino ON magazzino_carico.id=magazzino.id_carico
						LEFT JOIN anag_fornitori 
							ON magazzino_carico.id_fornitore=anag_fornitori.id_fornitore
						LEFT JOIN parti ON magazzino.id_parte=parti.id 
						LEFT JOIN um ON parti.um_id=um.id
				GROUP BY magazzino_carico.id
				HAVING $where
				ORDER BY $sortname $sortorder LIMIT $limit";

		$result=do_query($query,$conn);
		$rows=result_to_array($result,true);
	
		closedb($conn);
		$data=array();
		$data['page'] = $page;
		$data['total'] = $total;
		foreach($rows as $id=>$row)
		{
			$parti=$row['lista_parti'];
			$parti_value="<table class='detail_table'>";
			$parti_rows=explode("↓",$parti);
			foreach($parti_rows as $parti_row)
			{
				list($p_cod,$p_desc,$p_qta,$p_prezzo,$p_um)=explode("←",$parti_row);
				$parti_value.=sprintf("<tr class='detail_table_row'><td>%s - %s</td>
					<td>%s %s</td><td>%s€</td></tr>",
					$p_cod,$p_desc,fixNumber($p_qta),$p_um,$p_prezzo);
			}
			$parti_value.="</table>";
			$data['rows'][] = array
			(
				'id' => $id,
				'cell' => array
				(
//					$row['data_inserimento'], 
					$row['fornitore'], 
					$row['numero_fattura'], 
					$row['data_fattura'], 
					$parti_value
				)
			); 
		}
		echo json_encode($data);
		die();
	case "edit":
		$id_value=substr($_POST["id"],3);
		$id_field="id";
		$form_id="$id_field,$id_value";
	case "add":
		$fields=array
		(
			"id_utente"=>array("value"=>$_SESSION["id"],"type"=>"hidden"),
			"id_fornitore"=>array
			(
				"value"=>"",
				"label"=>"Fornitore",
				"link"=>array
				(
					"table"=>"anag_fornitori",
					"id"=>"id_fornitore",
					"text"=>"denominazione"
				)
			),
			"numero_fattura"=>array("value"=>"","label"=>"Numero Fattura"),
			"data_fattura"=>array("value"=>"","label"=>"Data Fattura"),
			"id"=>array
			(
				"value"=>"",
				"label"=>"Dettagli",
				"details"=>array
				(
					"table"=>"magazzino",
					"id"=>"id_carico",
					"fields"=>array
					(
						array(
							"label"=>"articolo",
							"field"=>"id_parte",
							"link"=>array
							(
								"table"=>"parti",
								"id"=>"id",
								"text"=>"codice"
							),
							"length"=>"30"
							),
						array(
							"label"=>"qta",
							"field"=>"qta",
							"length"=>"3",
							"value"=>"1"
							),
						array(
							"label"=>"prezzo",
							"field"=>"prezzo",
							"length"=>"10"
							)
					)
				)
			)
		);
		require_once("forms.php");
		showForm($form_id,"magazzino_carico",$fields);
		showNewPartForm();
		die();
	case "add_part":
		$codice=str_replace("'","\'",$_POST["newpart_codice"]);
		$descrizione=str_replace("'","\'",$_POST["newpart_descrizione"]);
		$um_id=$_POST["newpart_um"];
		$id_fornitore=$_POST["newpart_fornitore"];
		$id_tipo_materiale=$_POST["newpart_id_tipo_materiale"];
		$scorta_minima=$_POST["newpart_scorta_minima"];
		$query="INSERT INTO parti(
			codice,
			descrizione,
			um_id,
			id_fornitore,
			id_tipo_materiale,
			scorta_minima)
			VALUES(
				'$codice',
				'$descrizione',
				'$um_id',
				'$id_fornitore',
				'$id_tipo_materiale',
				'$scorta_minima'
			)";
		$conn=opendb();
		$result=do_query($query,$conn);
		$id_new_part=$conn->insert_id;
		echo (int)$id_new_part;

		closedb($conn);
		
		die();
	case "details":
		$id_value=substr($_POST["id"],3);
		$conn=opendb();
		$query="SELECT magazzino_carico.id as id,magazzino_carico.data_inserimento,
					anag_fornitori.denominazione as fornitore,
					magazzino_carico.numero_fattura,
					magazzino_carico.data_fattura
				FROM magazzino_carico LEFT JOIN anag_fornitori ON 
					magazzino_carico.id_fornitore=anag_fornitori.id_fornitore
				WHERE magazzino_carico.id='$id_value'";

		$result=do_query($query,$conn);
		$rows=result_to_array($result,true);
		$row=$rows[$id_value];

		$query="SELECT magazzino.qta,magazzino.prezzo,
					parti.codice,parti.descrizione,um.um 
				FROM magazzino_carico LEFT JOIN magazzino ON 
					magazzino_carico.id=magazzino.id_carico 
					LEFT JOIN parti ON magazzino.id_parte=parti.id
					LEFT JOIN um ON parti.um_id=um.id
				WHERE magazzino_carico.id='$id_value'";

		$result=do_query($query,$conn);
		$details=result_to_array($result,false);


		closedb($conn);
			?>
			
			<table class="form">
				<tr>
					<td colspan="2" class="left">
						<input type="button" class="cancel" name="cancel" value="cancel" id="cancel" />
					</td>
				</tr>
				<tr>
					<td class="header">Data inserimento</td>
					<td class="row"><?=$row["data_inserimento"]?></td>
				</tr>
				<tr>
					<td class="header">Fornitore</td>
					<td class="row"><?=$row["fornitore"]?></td>
				</tr>
				<tr>
					<td class="header">Numero fattura</td>
					<td class="row"><?=$row["numero_fattura"]?></td>
				</tr>
				<tr>
					<td class="header">Data fattura</td>
					<td class="row"><?=$row["data_fattura"]?></td>
				</tr>
			</table>
			<div class="left bold" style="padding: 5px 0px;">dettagli</div>

			<table class="form">
				<tr class="header">
					<td>codice</td>
					<td>descrizione</td>
					<td>qta</td>
					<td>prezzo</td>
				</tr>
			<?
				foreach($details as $detail)
				{?>
					<tr class="row">
						<td class="left"><?=$detail["codice"]?></td>
						<td class="left"><?=$detail["descrizione"]?></td>
						<td class="left"><?=fixNumber($detail["qta"])." ".$detail["um"]?></td>
						<td class="left"><?=$detail["prezzo"]."€"?></td>
					</tr>
				<?}?>
			</table>

		<?

		break;
	default:
		break;
}

function showNewPartForm()
{
	$conn=opendb();
	$query="SELECT id,um FROM um";
	$result=do_query($query,$conn);
	$ums=result_to_array($result,true);
	$ums[0]=array("um"=>"---");
	asort($ums);
	$query="SELECT id,description FROM tipo_materiale";
	$result=do_query($query,$conn);
	$tipo_materiales=result_to_array($result,true);
	$tipo_materiales[0]=array("description"=>"---");
	asort($tipo_materiales);
	closedb($conn);
?>
	<div id="dialog-form" class="dialog_form" title="Nuova Parte">
	<p class="validateTips">Tutti i campi sono obbligatori</p>

	<form>
	<fieldset id="newpart_form" >
		<input type="hidden" id="newpart_fornitore" name="newpart_fornitore">
		<input type="hidden" id="newpart_row" name="newpart_row">
		<label for="newpart_codice">Codice</label>
		<input type="text" name="newpart_codice" id="newpart_codice" class="text ui-widget-content ui-corner-all">
		<label for="newpart_descrizione">Descrizione</label>
		<input type="text" name="newpart_descrizione" id="newpart_descrizione" value="" class="text ui-widget-content ui-corner-all">
		<label for="newpart_um">Unit&aacute; di misura</label>
		<select name="newpart_um" id="newpart_um" value="" 
			class="text ui-widget-content ui-corner-all">
		<?
			foreach($ums as $id=>$um)
			{?>
				<option value="<?=$id?>">
					<?=$um["um"]?>
				</option>
			<?}
		?>
		</select>
		<label for="newpart_id_tipo_materiale">Tipo materiale</label>
		<select name="newpart_id_tipo_materiale" id="newpart_id_tipo_materiale" 
			value="" class="text ui-widget-content ui-corner-all">
		<?
			foreach($tipo_materiales as $id=>$tipo_materiale)
			{?>
				<option value="<?=$id?>">
					<?=$tipo_materiale["description"]?>
				</option>
			<?}
		?>
		</select>
		<label for="newpart_scorta_minima">Scorta minima</label>
		<input type="text" name="newpart_scorta_minima" id="newpart_scorta_minima" class="text ui-widget-content ui-corner-all" value="0">
	</fieldset>
	</form>
</div>
<?
}
?>
