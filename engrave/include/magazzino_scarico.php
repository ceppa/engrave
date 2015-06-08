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
	
		$fattura_string="CONCAT(fatture.numero,'-',YEAR(fatture.data))";
		$ricevuta_string="CONCAT(ricevute.numero,'-',YEAR(ricevute.data))";
		$parti_string="GROUP_CONCAT(CONCAT(parti.codice,'←',parti.descrizione,'←',ABS(magazzino.qta),'←',um.um) SEPARATOR '↓')";
		$where="$qtype LIKE '%$q%'";
		
		$conn=opendb();

		$data_movimento="IFNULL( fatture.data , 
								IFNULL( ricevute.data , 
									magazzino_scarico.data_inserimento)
							)";
		$query="SELECT magazzino_scarico.data_inserimento AS data_inserimento, 
					$fattura_string AS numero_fattura, 
					$ricevuta_string AS numero_ricevuta, 
					magazzino_scarico_lost.description AS altra_destinazione, 
					$parti_string AS lista_parti,
					$data_movimento AS data_movimento  
			FROM magazzino_scarico
					LEFT JOIN magazzino ON magazzino_scarico.id=magazzino.id_scarico
					LEFT JOIN parti ON magazzino.id_parte=parti.id
					LEFT JOIN fatture ON magazzino_scarico.id_fattura=fatture.id
					LEFT JOIN ricevute ON magazzino_scarico.id_ricevuta=ricevute.id
					LEFT JOIN magazzino_scarico_lost ON magazzino_scarico.id_magazzino_scarico_lost=magazzino_scarico_lost.id 
					LEFT JOIN um ON parti.um_id=um.id 
			GROUP BY magazzino_scarico.id
			HAVING $where";

		$result=do_query($query,$conn);
		$total=$result->num_rows;
		$result->free();

		$query="SELECT magazzino_scarico.id,
					magazzino_scarico.data_inserimento AS data_inserimento, 
					$fattura_string AS numero_fattura, 
					$ricevuta_string AS numero_ricevuta, 
					magazzino_scarico_lost.description AS altra_destinazione, 
					$parti_string AS lista_parti,
					$data_movimento AS data_movimento 
				FROM magazzino_scarico 
					LEFT JOIN magazzino ON magazzino_scarico.id=magazzino.id_scarico
					LEFT JOIN parti ON magazzino.id_parte=parti.id
					LEFT JOIN fatture ON magazzino_scarico.id_fattura=fatture.id
					LEFT JOIN ricevute ON magazzino_scarico.id_ricevuta=ricevute.id
					LEFT JOIN magazzino_scarico_lost ON magazzino_scarico.id_magazzino_scarico_lost=magazzino_scarico_lost.id 
					LEFT JOIN um ON parti.um_id=um.id 					
				GROUP BY magazzino_scarico.id
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
				list($p_cod,$p_desc,$p_qta,$p_um)=explode("←",$parti_row);
				$parti_value.=sprintf("<tr class='detail_table_row'><td>%s - %s</td><td>%s %s</td></tr>",
					$p_cod,$p_desc,fixNumber($p_qta),$p_um);
			}
			$parti_value.="</table>";

			$data['rows'][] = array
			(
				'id' => $id,
				'cell' => array
				(
/*					$row['data_inserimento'], */
					$row['data_movimento'],
					$row['numero_fattura'], 
/*					$row['numero_ricevuta'], */
					$row['altra_destinazione'], 
					$parti_value
				)
			); 
		}
		echo json_encode($data);
		die();
	case "add":
		$fields=array
		(
			"id_utente"=>array("value"=>$_SESSION["id"],"type"=>"hidden"),
			"id_fattura"=>array("value"=>"","label"=>"Fattura","type"=>"input"),
/*			"id_ricevuta"=>array("value"=>"","label"=>"Ricevuta","type"=>"input"),*/
			"id_magazzino_scarico_lost"=>array("value"=>"","label"=>"Altra Destinazione","type"=>"input"),
			"id"=>array
			(
				"value"=>"",
				"label"=>"Dettagli",
				"details"=>array
				(
					"table"=>"magazzino",
					"id"=>"id_scarico",
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
							)
					)
				)
			)
		);
		require_once("forms.php");
		showForm($form_id,"magazzino_scarico",$fields);
		showFormAddon();

		die();
	case "details":
		$id_value=substr($_POST["id"],3);
		$conn=opendb();
		$query="SELECT magazzino_scarico.id as id,magazzino_scarico.data_inserimento,
					anag_clienti.denominazione as cliente_fattura,
					CONCAT(fatture.numero,'-',YEAR(fatture.data)) AS numero_fattura,
					ricevute.cliente as cliente_ricevuta,
					CONCAT(ricevute.numero,'-',YEAR(ricevute.data)) AS numero_ricevuta
				FROM magazzino_scarico LEFT JOIN fatture ON 
					magazzino_scarico.id_fattura=fatture.id
				LEFT JOIN anag_clienti ON fatture.id_cliente=anag_clienti.id_cliente
				LEFT JOIN ricevute ON magazzino_scarico.id_ricevuta=ricevute.id 
				WHERE magazzino_scarico.id='$id_value'";

		$result=do_query($query,$conn);
		$rows=result_to_array($result,true);
		$row=$rows[$id_value];



		$query="SELECT magazzino.qta,
					parti.codice,parti.descrizione,um.um 
				FROM magazzino_scarico LEFT JOIN magazzino ON 
					magazzino_scarico.id=magazzino.id_scarico 
					LEFT JOIN parti ON magazzino.id_parte=parti.id
					LEFT JOIN um ON parti.um_id=um.id
				WHERE magazzino_scarico.id='$id_value'";

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
			<?
			if(strlen($row["numero_fattura"]))
			{?>
				<tr>
					<td class="header">Cliente</td>
					<td class="row"><?=$row["cliente_fattura"]?></td>
				</tr>
				<tr>
					<td class="header">Numero fattura</td>
					<td class="row"><?=$row["numero_fattura"]?></td>
				</tr>
			<?}?>
			</table>
			<div class="left bold" style="padding: 5px 0px;">dettagli</div>

			<table class="form">
				<tr class="header">
					<td>codice</td>
					<td>descrizione</td>
					<td>qta</td>
				</tr>
			<?
				foreach($details as $detail)
				{
					?>
					<tr class="row">
						<td class="left"><?=$detail["codice"]?></td>
						<td class="left"><?=$detail["descrizione"]?></td>
						<td class="left"><?=fixNumber(-$detail["qta"])." ".$detail["um"]?></td>
					</tr>
				<?}?>
			</table>

		<?

		break;
	case "newLost":
		$conn=opendb();
		$id="";
		$description=str_replace("'","\'",trim($_POST["newLost"]));
		$query="SELECT id FROM magazzino_scarico_lost 
			WHERE description='$description'";
		$result=do_query($query,$conn);
		$rows=result_to_array($result,false);
		if(count($rows)>0)
			$id=$rows[0]["id"];
		elseif(strlen($description))
		{
			$query="INSERT INTO magazzino_scarico_lost(description)
				VALUES('$description')";
			$result=do_query($query,$conn);
			$id=mysql_insert_id();
		}
		echo $id;
		closedb($conn);
		break;
	default:
		break;
}

function showFormAddon()
{?>
	<input type="hidden" id="id_fattura_ricevuta" value="" />
<?
}

/*

DROP TRIGGER IF EXISTS `magazzino_scarico_insert`//

CREATE TRIGGER magazzino_scarico_insert BEFORE INSERT ON magazzino_scarico
FOR EACH ROW 
BEGIN
    IF NEW.id_fattura = '' THEN
        SET NEW.id_fattura = NULL;
    END IF;
    IF NEW.id_ricevuta = '' THEN
        SET NEW.id_ricevuta = NULL;
    END IF;
    IF NEW.id_magazzino_scarico_lost = '' THEN
        SET NEW.id_magazzino_scarico_lost = NULL;
    END IF;
END;
*/

?>
