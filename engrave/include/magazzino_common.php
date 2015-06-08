<?
function computeWarehouse($to_date,$where,$order_by,$noecho=1,$qtaMagZero=true)
{
/*	$data_movimento="IF( magazzino_carico.data_fattura IS NULL , 
						(
							IF( fatture.data IS NULL , 
							(
								IF( ricevute.data IS NULL , 
									magazzino_scarico.data_inserimento, 
									ricevute.data )
							) , fatture.data )
						), magazzino_carico.data_fattura
					)";*/
	$data_movimento="IFNULL(magazzino_carico.data_fattura, 
								IFNULL( DATE_ADD(fatture.data,INTERVAL 12 HOUR) , 
									IFNULL( DATE_ADD(ricevute.data,INTERVAL 12 HOUR) , 
										DATE_ADD(DATE(magazzino_scarico.data_inserimento),INTERVAL 12 HOUR))
								)
							)";
	$query="SELECT parti.id,$data_movimento AS data_movimento, 
					anag_fornitori.denominazione AS fornitore, 
					tipo_materiale.description AS tipo, 
					parti.codice, parti.descrizione, parti.scorta_minima,
					magazzino.qta, 
					magazzino.prezzo, 
					um.um,
					magazzino.qta * magazzino.prezzo AS totale
			FROM magazzino
				LEFT JOIN magazzino_carico ON magazzino.id_carico = magazzino_carico.id
				LEFT JOIN parti ON magazzino.id_parte = parti.id
				LEFT JOIN um ON parti.um_id = um.id
				LEFT JOIN tipo_materiale ON parti.id_tipo_materiale = tipo_materiale.id
				LEFT JOIN anag_fornitori ON parti.id_fornitore = anag_fornitori.id_fornitore
				LEFT JOIN magazzino_scarico ON magazzino.id_scarico = magazzino_scarico.id
				LEFT JOIN fatture ON magazzino_scarico.id_fattura = fatture.id
				LEFT JOIN ricevute ON magazzino_scarico.id_ricevuta = ricevute.id
			WHERE DATE($data_movimento)<='$to_date' ".(strlen($where)?"AND $where":"")." 
			ORDER BY $order_by";

	$conn=opendb();
	$result=do_query($query,$conn);
	$rows=result_to_array($result,false);
	closedb($conn);

	$issues=array();
	$array=array();
	foreach($rows as $row)
	{
		if($row["qta"]>0)
		{
			if(!isset($array[$row["id"]]))
				$array[$row["id"]]=array();
			$array[$row["id"]][]=$row;
		}
		else
		{
			if((!isset($array[$row["id"]]))&&(!$noecho))
				$issues[]=$row["id"];
			else
			{
				$qta=abs($row["qta"]);
				if(!isset($array[$row["id"]]))
					$array[$row["id"]]=array();
				foreach($array[$row["id"]] as $id=>$linea)
				{
					$linea["qta"]=round((float)$linea["qta"],3);

					if($linea["qta"]>=$qta)
					{
						$linea["qta"]-=$qta;
						$array[$row["id"]][$id]["qta"]=$linea["qta"];
						$qta=0;
					}
					else
					{
						$qta-=$linea["qta"];
						$linea["qta"]=0;
						$array[$row["id"]][$id]["qta"]=$linea["qta"];
					}
					if($qta==0)
						break;
				}
				if(($qta!=0)&&(!$noecho))
					$issues[]=$row["id"];
			}
		}
	}
	$array2=array();
	foreach($array as $id_parts=>$linee)
	{
		$array2[$id_parts]=array();
		$prezzo=0;
		foreach($linee as $linea)
		{
			$qta=$linea["qta"];
			if((!$qtaMagZero) || ($qta>0))
			{
				$prezzo=$linea["prezzo"];
				if(isset($array2[$id_parts][$prezzo]))
					$array2[$id_parts][$prezzo]["qta"]+=$qta;
				else
				{
					$array2[$id_parts][$prezzo]=$linea;
				}
			}
		}
	}
	if(count($issues))
		echoIssues($issues);
	return $array2;
}
function echoIssues($issues)
{
	require_once("mysql.php");
	require_once("util.php");
	$query=sprintf("SELECT parti.codice,parti.descrizione,
		IFNULL(magazzino_carico.data_fattura,IFNULL(fatture.data,IFNULL(ricevute.data,magazzino_scarico.data_inserimento)))	AS data,
			magazzino_carico.data_fattura as data_fattura_in,
			fatture.data as data_fattura_out,
			ricevute.data as data_ricevuta_out,
			magazzino_scarico.data_inserimento,
			magazzino.qta
		FROM `magazzino`
		join parti on magazzino.id_parte=parti.id
		left join magazzino_carico on magazzino.id_carico=magazzino_carico.id
		left join magazzino_scarico on magazzino.id_scarico=magazzino_scarico.id
		left join fatture on magazzino_scarico.id_fattura=fatture.id
		left join ricevute on magazzino_scarico.id_ricevuta=ricevute.id
		WHERE parti.id in (%s)",implode(",", $issues));
	$conn=opendb();
	$result=do_query($query,$conn);
	$rows=result_to_array($result,false);
	closedb($conn);

	$columns=array
			(
				"codice"=>array("title"=>"Codice","align"=>"center"),
				"descrizione"=>array("title"=>"Descrizione","align"=>"center"),
				"data"=>array("title"=>"Data","align"=>"center"),
				"data_fattura_in"=>array("title"=>"Data fattura in","align"=>"center"),
				"data_fattura_out"=>array("title"=>"Data fattura out","align"=>"center"),
				"data_ricevura_out"=>array("title"=>"Data ricevuta out","align"=>"center"),
				"data_inserimento"=>array("title"=>"Data inserimento","align"=>"center"),
				"qta"=>array("title"=>"Qta","align"=>"center")
			);
	drawTable($rows,$columns);
			
}
?>
