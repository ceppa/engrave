<?
require_once("const.php");
require_once("util.php");
require_once("mysql.php");
require_once("../config.php");


$op=$_REQUEST["op"];

switch($op)
{
	case "select_fatture_print":
		select_fatture_print();
		break;
	case "select_cassa_print":
		select_cassa_print();
		break;
	case "select_corrispettivi_print":
		select_corrispettivi_print();
		break;
	case "stampa_fatture_mese":
	case "stampa_fatture_anno":
	case "stampa_fatture_non_incassate":
		stampa_fatture_mese_anno();
		break;
	case "stampa_cassa_mese":
		stampa_cassa_mese();
		break;
	case "stampa_corrispettivi":
		stampa_corrispettivi();
		break;
	case "print_warehouse":
		?>
		magazzino a data: <input type="text" name="date_to" id="date_to" class="date_class" />
		<div id="div_result"></div>
		<?
		break;
	case "print_carichi":
		$conn=opendb();
	
		$data_inizio=sprintf("%04d-%02d-01",$anno,$mese);
		$query="SELECT DISTINCT YEAR(data_fattura) AS anno 
			FROM magazzino_carico
			ORDER BY anno";
		$result=do_query($query,$conn);
		$rows=result_to_array($result,false);
		$conn=null;
		?>
		seleziona anno: <select name="anno_carichi" id="anno_carichi" class="date_class">
				<option value="0">tutti</option>
		<?
			foreach($rows as $row)
			{
				$anno=$row["anno"];
				$selected=($anno==date("Y")-1?' selected="selected"':'');
			?>
				<option value="<?=$anno?>"<?=$selected?>><?=$anno?></option>
			<?}
		?>
		</select>
		<div id="div_result"></div>
		<?
		break;
	case "print_scarichi":
		$conn=opendb();

		$data_inizio=sprintf("%04d-%02d-01",$anno,$mese);
		$data_movimento="IFNULL( fatture.data , 
								IFNULL( ricevute.data , 
									magazzino_scarico.data_inserimento)
							)";
		$query="SELECT DISTINCT YEAR($data_movimento) anno 
			FROM magazzino_scarico
					LEFT JOIN fatture ON magazzino_scarico.id_fattura=fatture.id
					LEFT JOIN ricevute ON magazzino_scarico.id_ricevuta=ricevute.id
			ORDER BY anno";

		$result=do_query($query,$conn);
		$rows=result_to_array($result,false);
		$conn=null;
		?>
		seleziona anno: <select name="anno_scarichi" id="anno_scarichi" class="date_class">
				<option value="0">tutti</option>
		<?
			foreach($rows as $row)
			{
				$anno=$row["anno"];
				$selected=($anno==date("Y")-1?' selected="selected"':'');
			?>
				<option value="<?=$anno?>"<?=$selected?>><?=$anno?></option>
			<?}
		?>
		</select>
		<div id="div_result"></div>
		<?
		break;
	case "print_giacenze_critiche":
		print_giacenze_critiche();
		break;
	case "status":
		print_warehouse();
		break;
	case "print_carichi_update":
		print_carichi();
		break;
	case "print_scarichi_update":
		print_scarichi();
		break;
	default:
		die();
}

function print_giacenze_critiche()
{
	require_once("magazzino_common.php");

	$sortname="tipo_materiale.description,anag_fornitori.denominazione,codice";
	$sortorder="asc";
	$where="1=1";
	require_once("magazzino_common.php");

	$array2=computeWarehouse("9999-99-99",$where,"$sortname $sortorder,data_movimento",1,false);

	$data=array();
	foreach($array2 as $id=>$linee)
	{
		if(count($linee))
		{
			$qta=0;
			foreach($linee as $linea)
			{
				$qta+=$linea['qta'];
				$row=$linea;
			}
			$qta=fixNumber($qta);
			if($qta<=$row["scorta_minima"])
				$data['rows'][] = array
				(
					"tipo"=>$row['tipo'], 
					"fornitore"=>$row['fornitore'], 
					"codice"=>$row['codice'], 
					"descrizione"=>$row['descrizione'], 
					"um"=>$row['um'], 
					"qta"=>$qta,
					"scorta_minima"=>$row['scorta_minima']
				);
		}
	}
	if(count($data)==0)
	{
		echo "Nessuna giacenza critica";
		return;
	}
?>
<table>
<tr class="header">
	<td>tipo materiale</td>
	<td>fornitore</td>
	<td>codice</td>
	<td>descrizione</td>
	<td>um</td>
	<td>qta</td>
	<td>scorta minima</td>
</tr>
<?
	foreach($data["rows"] as $linea)
	{
?>
<tr class="left">
	<td><?=$linea["tipo"]?></td>
	<td><?=$linea["fornitore"]?></td>
	<td><?=$linea["codice"]?></td>
	<td><?=$linea["descrizione"]?></td>
	<td><?=$linea["um"]?></td>
	<td><?=$linea["qta"]?></td>
	<td><?=$linea["scorta_minima"]?></td>
</tr>
<?
	}
?>
</table>
<?

}

function print_carichi()
{
	$anno=$_POST["anno"];
	if($anno>0)
	{
		$where="WHERE YEAR(magazzino_carico.data_fattura)='".$anno."'";
		$order="ORDER BY data_fattura";
	}
	else
	{
		$where="";
		$order="ORDER BY codice,data_fattura";
	}
	$conn=opendb();
	$query="SELECT magazzino_carico.id,magazzino_carico.data_inserimento,
				anag_fornitori.denominazione AS fornitore,
				magazzino_carico.numero_fattura AS numero_fattura,
				magazzino_carico.data_fattura AS data_fattura,
				tipo_materiale.description AS tipo_materiale,
				magazzino.prezzo,magazzino.qta,um.um,
				parti.descrizione,
				parti.codice
			FROM magazzino_carico
					LEFT JOIN magazzino ON magazzino_carico.id=magazzino.id_carico
					LEFT JOIN anag_fornitori 
						ON magazzino_carico.id_fornitore=anag_fornitori.id_fornitore
					LEFT JOIN parti ON magazzino.id_parte=parti.id 
					LEFT JOIN um ON parti.um_id=um.id
					LEFT JOIN tipo_materiale ON parti.id_tipo_materiale=tipo_materiale.id 
			$where
			$order";
	
	$result=do_query($query,$conn);
	$rows=result_to_array($result,false);
	$conn=null;
?>
<table>
<tr class="header">
	<td>fornitore</td>
	<td>numero_fattura</td>
	<td>data_fattura</td>
	<td>tipo_materiale</td>
	<td>codice</td>
	<td>descrizione</td>
	<td>qta</td>
	<td>um</td>
	<td>prezzo</td>
</tr>
<?
	$id=0;
	foreach($rows as $linea)
	{
/*		if($linea["id"]==$id)
		{
			$linea["fornitore"]="";
			$linea["numero_fattura"]="";
			$linea["data_fattura"]="";
		}
		else
			$id=$linea["id"];*/
?>
<tr class="left">
	<td><?=$linea["fornitore"]?></td>
	<td><?=$linea["numero_fattura"]?></td>
	<td><?=$linea["data_fattura"]?></td>
	<td><?=$linea["tipo_materiale"]?></td>
	<td><?=$linea["codice"]?></td>
	<td><?=$linea["descrizione"]?></td>
	<td><?=$linea["qta"]?></td>
	<td><?=$linea["um"]?></td>
	<td><?=$linea["prezzo"]?></td>
</tr>
<?
	}
?>
</table>
<?
}



function print_scarichi()
{
	$anno=$_POST["anno"];
	$data_movimento="IFNULL( fatture.data , 
							IFNULL( ricevute.data , 
								magazzino_scarico.data_inserimento)
						)";
	$fattura_string="IFNULL(CONCAT(fatture.numero,'-',YEAR(fatture.data)),IFNULL(CONCAT(ricevute.numero,'-',YEAR(ricevute.data)),magazzino_scarico_lost.description))";

	if($anno>0)
	{
		$where="WHERE YEAR($data_movimento)='".$anno."'";
		$order="ORDER BY data_movimento";
	}
	else
	{
		$where="";
		$order="ORDER BY codice,data_movimento";
	}
	$conn=opendb();
	$query="SELECT magazzino_scarico.data_inserimento AS data_inserimento, 
				$fattura_string AS fattura, 
				parti.codice,parti.descrizione,
				ABS(magazzino.qta) AS qta,
				tipo_materiale.description AS tipo_materiale,
				$data_movimento AS data_movimento,
				um.um  
		FROM magazzino_scarico
				LEFT JOIN magazzino ON magazzino_scarico.id=magazzino.id_scarico
				LEFT JOIN parti ON magazzino.id_parte=parti.id
				LEFT JOIN fatture ON magazzino_scarico.id_fattura=fatture.id
				LEFT JOIN ricevute ON magazzino_scarico.id_ricevuta=ricevute.id
				LEFT JOIN magazzino_scarico_lost ON magazzino_scarico.id_magazzino_scarico_lost=magazzino_scarico_lost.id 
				LEFT JOIN um ON parti.um_id=um.id 
				LEFT JOIN tipo_materiale ON parti.id_tipo_materiale=tipo_materiale.id 
		$where
		$order";

	$result=do_query($query,$conn);
	$rows=result_to_array($result,false);	
	$conn=null;
?>
<table>
<tr class="header">
	<td>data</td>
	<td>fattura / altro</td>
	<td>tipo_materiale</td>
	<td>codice</td>
	<td>descrizione</td>
	<td>qta</td>
	<td>um</td>
</tr>
<?
	foreach($rows as $linea)
	{
?>
<tr class="left">
	<td><?=$linea["data_movimento"]?></td>
	<td><?=$linea["fattura"]?></td>
	<td><?=$linea["tipo_materiale"]?></td>
	<td><?=$linea["codice"]?></td>
	<td><?=$linea["descrizione"]?></td>
	<td><?=$linea["qta"]?></td>
	<td><?=$linea["um"]?></td>
	<td><?=$linea["prezzo"]?></td>
</tr>
<?
	}
?>
</table>
<?
}


function select_cassa_print()
{
	$self=$_SERVER["PHP_SELF"];
	global $mesi;
	$lastMonth=strtotime("-1 month");
	$mese=date("n",$lastMonth);
	$anno=date("Y",$lastMonth);
?>
	<div class="inline" style="clear:left">
		<form action="<?=$self?>" target="_blank" method="post" name="stampa_cassa_mese">
			<input type="hidden" name="op" value="stampa_cassa_mese">
			<table class="stampeselect">
				<tr>
					<td colspan="2">
						<div class="centra">
							<p class="title">Per mese</p>
						</div>
					</td>
				</tr>
				<tr>
					<td>Mese</td>
					<td>
						<select class="input" name="mese">
	<?
	foreach($mesi as $mese_id=>$mese_text)
	{?>
							<option value="<?=($mese_id+1)?>"<?=($mese_id+1==$mese?" selected":"")?>>
								<?=$mese_text?>
							</option>
	<?}?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Anno</td>
					<td>
						<select class="input" name="anno">
	<?
	for($anno_id=2010;$anno_id<=2030;$anno_id++)
	{?>
							<option value="<?=$anno_id?>"<?=($anno_id==$anno?" selected":"")?>>
								<?=$anno_id?>
							</option>
	<?}?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="centra">
							<input type="submit" name="submit_mese" 
								value="stampa">
						</div>
					</td>
				</tr>
			</table>
		</form>
	</div>
<?
}

function select_corrispettivi_print()
{
	$conn=opendb();

	$query="SELECT DISTINCT YEAR(data) AS anno,MONTH(data) AS mese 
		FROM ricevute
		WHERE data>'2014-12-31'
		ORDER BY anno,mese";
	$result=do_query($query,$conn);
	$rows=result_to_array($result,false);
	$conn=null;

	
	$self=$_SERVER["PHP_SELF"];
	global $mesi;
	$lastMonth=strtotime("-1 month");
	$mesesel=date("n",$lastMonth);
	$annosel=date("Y",$lastMonth);
?>
	<div class="inline" style="clear:left">
		<form action="<?=$self?>" target="_blank" method="post" name="stampa_corrispettivi">
			<input type="hidden" name="op" value="stampa_corrispettivi">
			<table class="stampeselect">
				<tr>
					<td colspan="2">
						<div class="centra">
							<p class="title">Per mese</p>
						</div>
					</td>
				</tr>
				<tr>
					<td>Mese</td>
					<td>
						<select class="input" name="mese">
	<?
	foreach($rows as $row)
	{
		$mese_id=$row["mese"];
		$anno_id=$row["anno"];
		$mese_text=$mesi[$mese_id-1];
		$value=sprintf("%04d%02d",$anno_id,$mese_id);
		$selected=($mese_id==$mesesel)&&($anno_id==$annosel);
	?>
							<option value="<?=$value;?>"<?=($selected?" selected":"")?>>
								<?=sprintf("%04d %s",$anno_id,$mese_text)?>
							</option>
	<?}?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="centra">
							<input type="submit" name="submit_mese" 
								value="stampa">
						</div>
					</td>
				</tr>
			</table>
		</form>
	</div>
<?
}


function select_fatture_print()
{
	$self=$_SERVER["PHP_SELF"];
	global $mesi;
	$lastMonth=strtotime("-1 month");
	$mese=date("n",$lastMonth);
	$anno=date("Y",$lastMonth);
?>
	<div class="inline" style="clear:left">
		<form action="<?=$self?>" target="_blank" method="post" name="stampa_fatture_mese">
			<input type="hidden" name="op" value="stampa_fatture_mese">
			<table class="stampeselect">
				<tr>
					<td colspan="2">
						<div class="centra">
							<p class="title">Per mese</p>
						</div>
					</td>
				</tr>
				<tr>
					<td>Mese</td>
					<td>
						<select class="input" name="mese">
	<?
	foreach($mesi as $mese_id=>$mese_text)
	{?>
							<option value="<?=($mese_id+1)?>"<?=($mese_id+1==$mese?" selected":"")?>>
								<?=$mese_text?>
							</option>
	<?}?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Anno</td>
					<td>
						<select class="input" name="anno">
	<?
	for($anno_id=2010;$anno_id<=2030;$anno_id++)
	{?>
							<option value="<?=$anno_id?>"<?=($anno_id==$anno?" selected":"")?>>
								<?=$anno_id?>
							</option>
	<?}?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="centra">
							<input type="submit" name="submit_mese" 
								value="stampa">
						</div>
					</td>
				</tr>
			</table>
		</form>
	</div>


	<div class="inline">
		<form action="<?=$self?>" target="_blank" method="post" name="stampa_fatture_anno">
			<input type="hidden" name="op" value="stampa_fatture_anno">
			<table class="stampeselect">
				<tr>
					<td colspan="2">
						<div class="centra">
							<p class="title">Per anno</p>
						</div>
					</td>
				</tr>
				<tr>
					<td>Anno</td>
					<td>
						<select class="input" name="anno">
	<?
	$anno=date("Y")-1;
	for($anno_id=2010;$anno_id<=2030;$anno_id++)
	{?>
							<option value="<?=$anno_id?>"<?=($anno_id==$anno?" selected":"")?>>
								<?=$anno_id?>
							</option>
	<?}?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="centra">
							<input type="submit" name="submit" 
								value="stampa">
						</div>
					</td>
				</tr>
			</table>
		</form>
	</div>



	<div class="inline">
		<form action="<?=$self?>" target="_blank" method="post" name="stampa_fatture_mese">
			<input type="hidden" name="op" value="stampa_fatture_non_incassate">
			<table class="stampeselect">
				<tr>
					<td colspan="2">
						<div class="centra">
							<p class="title">Non incassate</p>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="centra">
							<input type="submit" name="submit" 
								value="stampa">
						</div>
					</td>
				</tr>
			</table>
		</form>
	</div>
<?
}


function print_warehouse()
{
	require_once("magazzino_common.php");
	$to_date=$_POST["to_date"];
	$array2=computeWarehouse($to_date,"","anag_fornitori.denominazione, parti.codice,data_movimento",0);
?>
<table>
<tr class="header">
	<td>fornitore</td>
	<td>tipo materiale</td>
	<td>codice</td>
	<td>decrizione</td>
	<td>qta</td>
	<td>um</td>
	<td>prezzo</td>
	<td>totale</td>
</tr>
<?
	foreach($array2 as $linee)
	{
		foreach($linee as $linea)
		{
?>
<tr>
	<td><?=$linea["fornitore"]?></td>
	<td><?=$linea["tipo"]?></td>
	<td><?=$linea["codice"]?></td>
	<td><?=$linea["descrizione"]?></td>
	<td><?=fixNumber($linea["qta"])?></td>
	<td><?=$linea["um"]?></td>
	<td><?=fixNumber($linea["prezzo"])?></td>
	<td><?=fixNumber($linea["qta"]*$linea["prezzo"])?></td>
</tr>
<?
		}
	}
?>
</table>


<?
}

function stampa_cassa_mese()
{
	require_once('File/PDF.php');
	require_once("pdf.php");
	require_once("mysql.php");

	$anno=$_POST["anno"];
	$mese=$_POST["mese"];

	$nomefile=sprintf("cassa_%d_%02d",$anno,$mese);

	$conn=opendb();

	$data_inizio=sprintf("%04d-%02d-01",$anno,$mese);
	$query="SELECT sum(t.importo) AS saldo_prec 
		FROM 
		(
			select sum(importo) as importo 
			FROM cassa WHERE cassa.data<'$data_inizio'
			UNION ALL
			select sum(importo) as importo
			FROM ricevute 
			WHERE ricevute.data>'2014-12-31' AND ricevute.data<'$data_inizio' AND ricevute.contanti=1 AND ricevute.data_incasso IS NOT NULL
		) t
		";
	$result=do_query($query,$conn);
	$rows=result_to_array($result,false);
	$row=$rows[0];
	$saldo_prec=floatval($row["saldo_prec"]);
	$query="SELECT t.data, t.descrizione, t.importo FROM
			(
				SELECT data, descrizione, importo
				FROM cassa
				WHERE month(data)='$mese' AND year(data)='$anno'
				UNION ALL
				select data,CONCAT('Ric. ',LPAD(numero,2,'0'),'/',YEAR(data),' - ',ricevute.cliente) AS descrizione,importo
				FROM ricevute 
				WHERE data>'2014-12-31' AND month(ricevute.data)='$mese' AND year(ricevute.data)='$anno' AND ricevute.contanti=1 AND ricevute.data_incasso IS NOT NULL
			) t
			ORDER BY data";
	$result=do_query($query,$conn);
	$rows=result_to_array($result,false);
	closedb($conn);


	class My_File_PDF extends File_PDF 
	{
		public $mese;
		public $anno;
		public $saldo_prec;
		public $cellswidth=array(25,92,22,22,22);

		function header()
		{ 
			$x=$this->getX();
			$y=$this->getY();
			$w=array_sum($this->cellswidth);

			$this->setTextColor('gray',0.4);
			$this->setFont('goodtime', '', 26); 
			$lh=26.0*$this->mpp;
			$this->write($lh,'EN');
			$this->setY($y+$this->mpp/3);

			$lh=25.0*$this->mpp;
			$this->setFont('heln', '', 25);
			$this->write($lh,'GRAVE');

			$this->setXY($this->getX()+20,$y+$this->mpp*6);
			$this->setFontSize(6);
			$this->setTextColor('rgb',166/255,194/255,70/255);
			$this->write($lh,'ENGRAVED ');
			$this->setTextColor('rgb',207/255,92/255,117/255);
			$this->write($lh,'GRAPHIC DESIGN ');
			$this->setTextColor('rgb',134/255,155/255,202/255);
			$this->write($lh,'FASHION STYLE ');
			$this->setTextColor('rgb',186/255,185/255,190/255);
			$this->write($lh,'DIGITAL PRINT ');
			$this->setTextColor('rgb',70/255,180/255,226/255);
			$this->write($lh,'3D PRINT ');
			$this->setTextColor('rgb',217/255,191/255,54/255);
			$this->write($lh,'FURNISHING ACCESSORIES ');
			$this->setTextColor('rgb',123/255,73/255,101/255);
			$this->write($lh,'ADVERTISING');
			$this->newLine();
			$this->setY(20);

			$this->setFont('helvetica', '', 12);
//			$this->setFontSize(12);
			$this->setTextColor('gray',0.1);
			$titolo=sprintf("ENGRAVE snc - Gestione Cassa - Mese %02d/%d",$this->mese,$this->anno);
			$this->cell(0,8,$titolo,0,1,'C');

			$this->newLine();
			$this->setY(31);

			$this->setFontSize(9);

			if($this->getPageNo()==1)
			{
				$this->setTextColor('gray',0.1);
				$this->cell(0,8,"Riporto mese precedente   ".chr(128)." ".
					currency($this->saldo_prec),0,1,'R');
			}
			
			$this->setTextColor('gray',1);
			$this->setFillColor('gray',0.6);
			$this->cell($this->cellswidth[0],6.5,"Data",0,0,'L',1);
			$this->cell($this->cellswidth[1],6.5,"Descrizione",0,0,'C',1);
			$this->cell($this->cellswidth[2],6.5,"Entrata",0,0,'C',1);
			$this->cell($this->cellswidth[3],6.5,"Uscita",0,0,'C',1);
			$this->cell($this->cellswidth[4],6.5,"Saldo",0,1,'C',1);

		}
		function footer()
		{
			$this->setTextColor('gray',0.4);
			$this->setY(-20);
			$this->setFont('helvetica', 'B', 8.1);
			
			$this->write(3,"ENGRAVE  di Cristin Elisabetta, Quargnal L. & C. s.n.c. ");
			$this->setFont('heln', '', 8.1);
			$this->write(3,"Telefono: 0432 677991 ".chr(149)." e-mail: info@engravelab.it ".chr(149)." sito web: www.engravelab.it");
			$this->newLine();
			$this->write(3,"Sede legale: Via Andreuzzi,12 - 33100  Udine ".chr(149)." Sede operativa: Via San Daniele, 49 - 33035 Martignacco (UD)");
			$this->newLine();
			$this->write(3,"Registro Imprese UDINE ".chr(149)." Codice Fiscale e Partita IVA 02698910300 ".chr(149)." R.E.A. n.281242");
			         // Go to 1.5 cm from bottom

			$this->newLine();
			$this->setY(-12);
			$this->setFont('Arial', 'I', 8);
			$this->cell(0, 10, 'Pag ' . $this->getPageNo() . '/{nb}', 0,0, 'C');
		}
		function factory($in_mese,$in_anno,$in_saldo_prec)
		{
			$pdf=File_PDF::factory(array('orientation' => 'P','unit' => 'mm','format' => 'A4'),
					'My_File_PDF');
			$pdf->AddFont('goodtime','','GOODTIME.php');
			$pdf->AddFont('heln','','HELN.php');
			$pdf->AddFont('century','','centuryRoman.php');
			$pdf->AddFont('century','B','centuryBold.php');
			$pdf->setMargins(14,12,14);
			$pdf->mese=$in_mese;
			$pdf->anno=$in_anno;
			$pdf->saldo_prec=$in_saldo_prec;
			return $pdf;
		}
		function safeLine()
		{
			
		}
	}

	$pdf=My_File_PDF::factory($mese,$anno,$saldo_prec);
	$pdf->setDrawColor('gray',0.6);
	$pdf->setTextColor('gray',0.1);
	$pdf->setLineWidth(0.1);
	$pdf->addPage();
	$pdf->setFontSize(8);

	$pdf->setFontStyle('');

	$i=0;
	$totale=0;
	$wd=array_sum($pdf->cellswidth);
	$cellsheight=6.5;
	$saldo=$saldo_prec;
	foreach($rows as $row)
	{
		$i++;
		$saldo+=$row["importo"];
		if($row["importo"]>=0)
		{
			$entrata=currency($row["importo"]);
			$uscita="";
		}
		else
		{
			$uscita=currency(-$row["importo"]);
			$entrata="";
		}

		$pdf->setFillColor('gray',0.95+0.05*($i%2));
		$pdf->cell($pdf->cellswidth[0],$cellsheight,$row["data"],0,0,'L',1);
		$pdf->cell($pdf->cellswidth[1],$cellsheight,pdfstring($row["descrizione"]),0,0,'L',1);
		$pdf->cell($pdf->cellswidth[2],$cellsheight,$entrata,0,0,'R',1);
		$pdf->cell($pdf->cellswidth[3],$cellsheight,$uscita,0,0,'R',1);
		$pdf->cell($pdf->cellswidth[4],$cellsheight,currency($saldo),0,1,'R',1);
	}
	$pdf->setFontSize(9);
	$pdf->setTextColor('gray',0.1);
	$pdf->cell(0,8,"Saldo mese   ".chr(128)." ".
		currency($saldo),0,1,'R');


	$pdf->Output($nomefile.".pdf", false);
}

function stampa_fatture_mese_anno()
{
	require_once('File/PDF.php');
	require_once("pdf.php");
	require_once("mysql.php");

	$anno=(int)$_POST["anno"];
	$mese=(int)$_POST["mese"];
	
	if($anno>0)
	{
		$nomefile=sprintf("fatture_%d",$anno);
		if($mese>0)
		{
			$nomefile=sprintf("%s_%02d",$nomefile,$mese);
			$where="WHERE month(fatture.data) = '$mese' AND year(fatture.data) = '$anno'";
		}
		else
			$where="WHERE year(fatture.data) = '$anno'";
		
		$non_incassate=false;
	}
	else
	{
		$nomefile="fatture_non_incassate";
		$where="WHERE incassata=0";
		$non_incassate=true;
	}

	$conn=opendb();
	$query="SELECT fatture.*, 
				anag_clienti.denominazione, 
				SUM(prezzo) AS totale
			FROM fatture LEFT JOIN anag_clienti 
				ON fatture.id_cliente=anag_clienti.id_cliente
			LEFT JOIN fatture_items 
				ON fatture.id=fatture_items.id_fattura 
			$where 
			GROUP BY fatture.id
			ORDER BY YEAR(data),numero";
	$result=do_query($query,$conn);
	$rows=result_to_array($result,false);
	closedb($conn);


	class My_File_PDF extends File_PDF 
	{
		public $mese;
		public $anno;
		public $cellswidth=array(25,20,92,22,22);

		function header()
		{ 
			$x=$this->getX();
			$y=$this->getY();
			$this->setTextColor('gray',0.4);
			$this->setFont('goodtime', '', 26); 
			$lh=26.0*$this->mpp;
			$this->write($lh,'EN');
			$this->setY($y+$this->mpp/3);

			$lh=25.0*$this->mpp;
			$this->setFont('heln', '', 25);
			$this->write($lh,'GRAVE');

			$this->setXY($this->getX()+20,$y+$this->mpp*6);
			$this->setFontSize(6);
			$this->setTextColor('rgb',166/255,194/255,70/255);
			$this->write($lh,'ENGRAVED ');
			$this->setTextColor('rgb',207/255,92/255,117/255);
			$this->write($lh,'GRAPHIC DESIGN ');
			$this->setTextColor('rgb',134/255,155/255,202/255);
			$this->write($lh,'FASHION STYLE ');
			$this->setTextColor('rgb',186/255,185/255,190/255);
			$this->write($lh,'DIGITAL PRINT ');
			$this->setTextColor('rgb',70/255,180/255,226/255);
			$this->write($lh,'3D PRINT ');
			$this->setTextColor('rgb',217/255,191/255,54/255);
			$this->write($lh,'FURNISHING ACCESSORIES ');
			$this->setTextColor('rgb',123/255,73/255,101/255);
			$this->write($lh,'ADVERTISING');
			$this->newLine();
			$this->setY(20);

			$this->setFontSize(12);
			$this->setTextColor('gray',0.1);
			if($this->mese>0)
				$this->write($lh,"Fatture mese: ".$this->mese."/".$this->anno);
			elseif($this->anno>0)
				$this->write($lh,"Fatture anno: ".$this->anno);
			else
				$this->write($lh,"Fatture non incassate");

			$this->newLine();
			$this->setY(31);

			$this->setFontSize(9);
			$this->setTextColor('gray',1);
		
			$this->setFillColor('gray',0.6);
			$this->cell($this->cellswidth[0],6.5,"Numero Fattura",0,0,'L',1);
			$this->cell($this->cellswidth[1],6.5,"Data Fattura",0,0,'L',1);
			$this->cell($this->cellswidth[2],6.5,"Cliente",0,0,'L',1);
			$this->cell($this->cellswidth[3],6.5,"Totale",0,0,'R',1);
			$this->cell($this->cellswidth[4],6.5,"Totale+IVA",0,1,'R',1);

		}
		function footer()
		{
			$this->setTextColor('gray',0.4);
			$this->setY(-20);
			$this->setFont('helvetica', 'B', 8.1);
			
			$this->write(3,"ENGRAVE  di Cristin Elisabetta, Quargnal L. & C. s.n.c. ");
			$this->setFont('heln', '', 8.1);
			$this->write(3,"Telefono: 0432 677991 ".chr(149)." e-mail: info@engravelab.it ".chr(149)." sito web: www.engravelab.it");
			$this->newLine();
			$this->write(3,"Sede legale: Via Andreuzzi,12 - 33100  Udine ".chr(149)." Sede operativa: Via San Daniele, 49 - 33035 Martignacco (UD)");
			$this->newLine();
			$this->write(3,"Registro Imprese UDINE ".chr(149)." Codice Fiscale e Partita IVA 02698910300 ".chr(149)." R.E.A. n.281242");
			         // Go to 1.5 cm from bottom

			$this->newLine();
			$this->setY(-12);
			$this->setFont('Arial', 'I', 8);
			$this->cell(0, 10, 'Pag ' . $this->getPageNo() . '/{nb}', 0,0, 'C');
		}
		function factory($in_mese,$in_anno)
		{
			$pdf=File_PDF::factory(array('orientation' => 'P','unit' => 'mm','format' => 'A4'),
					'My_File_PDF');
			$pdf->AddFont('goodtime','','GOODTIME.php');
			$pdf->AddFont('heln','','HELN.php');
			$pdf->AddFont('century','','centuryRoman.php');
			$pdf->AddFont('century','B','centuryBold.php');
			$pdf->setMargins(14,12,14);
			$pdf->mese=$in_mese;
			$pdf->anno=$in_anno;
			return $pdf;
		}
		function safeLine()
		{
			
		}
	}

	$pdf=My_File_PDF::factory($mese,$anno);
	$pdf->setDrawColor('gray',0.6);
	$pdf->setTextColor('gray',0.1);
	$pdf->setLineWidth(0.1);
	$pdf->addPage();
	$pdf->setFontSize(8);

	$pdf->setFontStyle('');

	require_once("iva.php");
//	$iva=1.21;

	$i=0;
	$totale=0;
	$totale_ivato=0;
	$wd=array_sum($pdf->cellswidth);
	foreach($rows as $row)
	{
		$i++;
		$showNote=(($non_incassate)&&(strlen($row["note"])));
		$cellsheight=($showNote?4.5:6.5);

		$pdf->setFillColor('gray',0.95+0.05*($i%2));
		$iva_value=(float)getIva($row["data"]);
		$iva=1+$iva_value/100.0;
		$ivato=$iva*$row["totale"];
		$ivato_formatted=currency($ivato);
		$numero_fattura=sprintf("%d-%03d",substr($row["data"],0,4),$row["numero"]);

		$pdf->cell($pdf->cellswidth[0],$cellsheight,$numero_fattura,0,0,'L',1);
		$pdf->cell($pdf->cellswidth[1],$cellsheight,$row["data"],0,0,'L',1);
		$pdf->cell($pdf->cellswidth[2],$cellsheight,pdfstring($row["denominazione"]),0,0,'L',1);
		$pdf->cell($pdf->cellswidth[3],$cellsheight,currency($row["totale"]),0,0,'R',1);
		$pdf->cell($pdf->cellswidth[4],$cellsheight,$ivato_formatted,0,1,'R',1);

		if(($non_incassate)&&(strlen($row["note"])))
			$pdf->cell($wd,$cellsheight,"Note: ".$row["note"],0,1,'L',1);

		$totale+=$row["totale"];
		$totale_ivato+=$ivato;
	}


	if(!$non_incassate)
	{
		$pdf->setFontSize(9);
		$pdf->setTextColor('gray',1);
		$pdf->setFillColor('gray',0.6);

		$w=0;
		$c=count($pdf->cellswidth);
		for($i=0;$i<$c-2;$i++)
			$w+=$pdf->cellswidth[$i];
		$pdf->cell($w,6.5,"TOTALE",0,0,'R',1);
		$pdf->cell($pdf->cellswidth[$c-2],6.5,
			currency($totale),0,0,'R',1);
		$pdf->cell($pdf->cellswidth[$c-1],6.5,
			currency($totale_ivato),0,0,'R',1);
	}

	$pdf->Output($nomefile.".pdf", false);

}


function stampa_corrispettivi()
{
	require_once('File/PDF.php');
	require_once("pdf.php");
	require_once("mysql.php");

	$meseanno=$_POST["mese"];
	$anno=substr($meseanno,0,4);
	$mese=substr($meseanno,4);

	$nomefile=sprintf("corrispettivi_%d_%02d",$anno,$mese);
	$conn=opendb();


	$da=sprintf("%04d-%02d-01",$anno,$mese);
	$a=sprintf("%04d-%02d-01",$anno,$mese+1);
	$query="SELECT t.giorno,GROUP_CONCAT(ricevute.numero) AS numero, sum(ricevute.importo) as importo FROM
		(
			SELECT '".$da."' + INTERVAL a + b DAY giorno
			FROM
			 (SELECT 0 a UNION SELECT 1 a UNION SELECT 2 UNION SELECT 3
			    UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7
			    UNION SELECT 8 UNION SELECT 9 ) d,
			 (SELECT 0 b UNION SELECT 10 UNION SELECT 20 
			    UNION SELECT 30 UNION SELECT 40) m
			WHERE '".$da."' + INTERVAL a + b DAY  <  '".$a."'
			ORDER BY a + b
		) t
		LEFT JOIN ricevute ON t.giorno=ricevute.data
		GROUP BY t.giorno";

	$result=do_query($query,$conn);
	$rows=result_to_array($result,false);
	closedb($conn);


	class My_File_PDF extends File_PDF 
	{
		public $mese;
		public $anno;
		public $cellswidth=array(25,114,22,22);

		function header()
		{ 
			$x=$this->getX();
			$y=$this->getY();
			$w=array_sum($this->cellswidth);

			$this->setTextColor('gray',0.4);
			$this->setFont('goodtime', '', 26); 
			$lh=26.0*$this->mpp;
			$this->write($lh,'EN');
			$this->setY($y+$this->mpp/3);

			$lh=25.0*$this->mpp;
			$this->setFont('heln', '', 25);
			$this->write($lh,'GRAVE');

			$this->setXY($this->getX()+20,$y+$this->mpp*6);
			$this->setFontSize(6);
			$this->setTextColor('rgb',166/255,194/255,70/255);
			$this->write($lh,'ENGRAVED ');
			$this->setTextColor('rgb',207/255,92/255,117/255);
			$this->write($lh,'GRAPHIC DESIGN ');
			$this->setTextColor('rgb',134/255,155/255,202/255);
			$this->write($lh,'FASHION STYLE ');
			$this->setTextColor('rgb',186/255,185/255,190/255);
			$this->write($lh,'DIGITAL PRINT ');
			$this->setTextColor('rgb',70/255,180/255,226/255);
			$this->write($lh,'3D PRINT ');
			$this->setTextColor('rgb',217/255,191/255,54/255);
			$this->write($lh,'FURNISHING ACCESSORIES ');
			$this->setTextColor('rgb',123/255,73/255,101/255);
			$this->write($lh,'ADVERTISING');
			$this->newLine();
			$this->setY(20);

			$this->setFont('helvetica', '', 12);
//			$this->setFontSize(12);
			$this->setTextColor('gray',0.1);
			$titolo=sprintf("ENGRAVE snc - REGISTRO DEI CORRISPETTIVI - Mese %02d/%d",$this->mese,$this->anno);
			$this->cell(0,8,$titolo,0,1,'C');

			$this->newLine();
			$this->setY(31);

			$this->setFontSize(9);			
			$this->setTextColor('gray',1);
			$this->setFillColor('gray',0.6);
			$this->cell($this->cellswidth[0],6.5,"Data",0,0,'L',1);
			$this->cell($this->cellswidth[1],6.5,"totale corrispettivo giornalierocon emissione ricev. fiscale 22%",0,0,'C',1);
			$this->cell($this->cellswidth[2],6.5,"Ricevuta da",0,0,'C',1);
			$this->cell($this->cellswidth[3],6.5,"Ricevuta a",0,1,'C',1);

		}
		function footer()
		{
			$this->setTextColor('gray',0.4);
			$this->setY(-20);
			$this->setFont('helvetica', 'B', 8.1);
			
			$this->write(3,"ENGRAVE  di Cristin Elisabetta, Quargnal L. & C. s.n.c. ");
			$this->setFont('heln', '', 8.1);
			$this->write(3,"Telefono: 0432 677991 ".chr(149)." e-mail: info@engravelab.it ".chr(149)." sito web: www.engravelab.it");
			$this->newLine();
			$this->write(3,"Sede legale: Via Andreuzzi,12 - 33100  Udine ".chr(149)." Sede operativa: Via San Daniele, 49 - 33035 Martignacco (UD)");
			$this->newLine();
			$this->write(3,"Registro Imprese UDINE ".chr(149)." Codice Fiscale e Partita IVA 02698910300 ".chr(149)." R.E.A. n.281242");
			         // Go to 1.5 cm from bottom

			$this->newLine();
			$this->setY(-12);
			$this->setFont('Arial', 'I', 8);
			$this->cell(0, 10, 'Pag ' . $this->getPageNo() . '/{nb}', 0,0, 'C');
		}
		function factory($in_mese,$in_anno)
		{
			$pdf=File_PDF::factory(array('orientation' => 'P','unit' => 'mm','format' => 'A4'),
					'My_File_PDF');
			$pdf->AddFont('goodtime','','GOODTIME.php');
			$pdf->AddFont('heln','','HELN.php');
			$pdf->AddFont('century','','centuryRoman.php');
			$pdf->AddFont('century','B','centuryBold.php');
			$pdf->setMargins(14,12,14);
			$pdf->mese=$in_mese;
			$pdf->anno=$in_anno;
			return $pdf;
		}
		function safeLine()
		{
			
		}
	}

	$pdf=My_File_PDF::factory($mese,$anno);
	$pdf->setDrawColor('gray',0.6);
	$pdf->setTextColor('gray',0.1);
	$pdf->setLineWidth(0.1);
	$pdf->addPage();
	$pdf->setFontSize(8);

	$pdf->setFontStyle('');

	$i=0;
	$totale=0;
	$wd=array_sum($pdf->cellswidth);
	$cellsheight=6.5;
	$totale=0;
	$numero_min=0;
	$numero_max=0;
	foreach($rows as $row)
	{
		$i++;
		$totale+=$row["importo"];
		if($row["importo"]!=0)
			$importo=pdfstring("€ ".currency($row["importo"]));
		else
			$importo="";
		$numero=trim($row["numero"]);
		$numero_da="";
		$numero_a="";
		if(strlen($numero))
		{
			$numero_array=explode(",",$numero);
			sort($numero_array);
			$numero_da=$numero_array[0];
			$numero_a=$numero_array[count($numero_array)-1];
			if(($numero_min==0)||($numero_da<$numero_min))
				$numero_min=$numero_da;
			if($numero_a>$numero_max)
				$numero_max=$numero_a;
		}
		$giorno=date('d/m/Y',strtotime($row["giorno"]));

		$pdf->setFillColor('gray',0.95+0.05*($i%2));
		$pdf->cell($pdf->cellswidth[0],$cellsheight,$giorno,0,0,'L',1);
		$pdf->cell($pdf->cellswidth[1],$cellsheight,$importo,0,0,'C',1);
		$pdf->cell($pdf->cellswidth[2],$cellsheight,$numero_da,0,0,'C',1);
		$pdf->cell($pdf->cellswidth[3],$cellsheight,$numero_a,0,1,'C',1);
	}
	if($numero_max==0)
	{
		$numero_min="-";
		$numero_max="-";
	}
	$pdf->setFontSize(9);			
	$pdf->setTextColor('gray',1);
	$pdf->setFillColor('gray',0.6);
	$pdf->cell($pdf->cellswidth[0],6.5,"Totale mese",0,0,'L',1);
	$pdf->cell($pdf->cellswidth[1],6.5,pdfstring("€ ".currency($totale)),0,0,'C',1);
	$pdf->cell($pdf->cellswidth[2],6.5,$numero_min,0,0,'C',1);
	$pdf->cell($pdf->cellswidth[3],6.5,$numero_max,0,0,'C',1);


	$pdf->Output($nomefile.".pdf", false);


}
