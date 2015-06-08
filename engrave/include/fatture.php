<?
require_once('mysql.php');
require_once('const.php');
require_once('../config.php');

$op=$_REQUEST["op"];
$form_id="";


function printFatturaLine($pdf,$item)
{
	$pdf->newLine(4);
	$line="";

	if(strlen(trim($item["note"])))
	{
		$noteArray=explode("\n",$item["note"]);
		$pdf->setX(23);
		$pdf->multiCell(131,5,pdfstring(trim($item["note"])),0,'L',0);
	}
	$pdf->setX(18);
	$py=$pdf->getY();
	$line=pdfstring(trim($item["descrizione"]));
	$pdf->multiCell(136,5,$line,0,'L',0);
	$ch=$pdf->getY()-$py;
	$pdf->setXY(154,$py);
	$pdf->cell(42,$ch,chr(128)." ".str_replace(".",",",
		sprintf("%.02f",$item["prezzo"])),0,1,'R');
}

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
	
		$where="$qtype LIKE '%$q%'";
		
		$conn=opendb();
		$query="SELECT count(*) AS c 
			FROM fatture  LEFT JOIN anag_clienti 
				ON fatture.id_cliente=anag_clienti.id_cliente 
			WHERE $where";
		$result=do_query($query,$conn);
		$rows=result_to_array($result,false);
		$total=$rows[0]["c"];
	
		$query="SELECT fatture.id,
			CONCAT(YEAR(fatture.data),'-',LPAD(fatture.numero,3,'0')) AS numero,
			anag_clienti.denominazione AS cliente,
			fatture.data,
			fatture.rif_ordine,
			fatture.accompagnatoria,
			fatture.incassata,
			fatture.note
			FROM fatture LEFT JOIN anag_clienti 
				ON fatture.id_cliente=anag_clienti.id_cliente 
			WHERE $where 
			ORDER BY $sortname $sortorder LIMIT $limit";
	
		$result=do_query($query,$conn);
		$rows=result_to_array($result,true);
	
		closedb($conn);
		$data=array();
		$data['page'] = $page;
		$data['total'] = $total;
		foreach($rows as $id=>$row)
		{
			$accompagnatoria=($row['accompagnatoria']?"X":"");
			$incassata=($row['incassata']?"X":"");
			$data['rows'][] = array
			(
				'id' => $id,
				'cell' => array
				(
					$row['numero'], 
					$row['cliente'], 
					$row['data'], 
					$row['rif_ordine'], 
					$accompagnatoria, 
					$incassata, 
					$row['note']
				)
			); 
		}

		echo json_encode($data);
		die();
	case "print":
		$id_value=substr($_POST["id"],3);
		print_fattura($id_value);
		die();
	case "edit":
		$id_value=substr($_POST["id"],3);
		$id_field="id";
		$form_id="$id_field,$id_value";
	case "add":
		$fields=array
		(
			"id_cliente"=>array
			(
				"value"=>"",
				"label"=>"Cliente",
				"link"=>array
				(
					"table"=>"anag_clienti",
					"id"=>"id_cliente",
					"text"=>"denominazione"
				)
			),
			"data"=>array("value"=>"","label"=>"Data"),
			"rif_ordine"=>array("value"=>"","label"=>"Rif ordine"),
			"accompagnatoria"=>array("value"=>"","label"=>"Accompagnatoria"),
			"incassata"=>array("value"=>"","label"=>"Incassata"),
			"note"=>array("value"=>"","label"=>"Note"),
			"id"=>array
			(
				"value"=>"",
				"label"=>"Dettagli",
				"details"=>array
				(
					"table"=>"fatture_items",
					"id"=>"id_fattura",
					"fields"=>array
					(
						array(
							"label"=>"descrizione",
							"field"=>"descrizione",
							"length"=>"80"
							),
						array(
							"label"=>"importo",
							"field"=>"prezzo",
							"length"=>"6"
							),
						array(
							"label"=>"note",
							"field"=>"note_optional",
							"length"=>"50"
							)
					)
				)
			)
		);
		require_once("forms.php");
		showForm($form_id,"fatture",$fields);
		die();
	case "del":
		$conn=opendb();
		$id=$_POST["id"];
		$query="DELETE FROM fatture WHERE id='$id'";
		do_query($query,$conn);
		closedb($conn);
		die();
	default:
		die();
}

function print_fattura($id)
{
	require_once('File/PDF.php');
	require_once("pdf.php");
	require_once("mysql.php");


	$conn=opendb();
	$query="SELECT fatture.*, 
				anag_clienti.*, 
				mod_pagamento.description AS mod_pagamento,
				prov_leg.provincia AS prov_leg,
				prov_dest.provincia AS prov_dest
			FROM fatture LEFT JOIN anag_clienti 
				ON fatture.id_cliente=anag_clienti.id_cliente
			LEFT JOIN mod_pagamento
				ON mod_pagamento.id=anag_clienti.id_mod_pagamento
			LEFT JOIN loc_province AS prov_leg 
				ON anag_clienti.provincia_legale=prov_leg.id_loc_province 
			LEFT JOIN loc_province AS prov_dest 
				ON anag_clienti.provincia_legale=prov_dest.id_loc_province 
			WHERE fatture.id='$id'";
	$result=do_query($query,$conn);
	$rows=result_to_array($result,false);
	$row=$rows[0];

	$query="SELECT * FROM fatture_items WHERE id_fattura='$id'";
	$result=do_query($query,$conn);
	$items=result_to_array($result,false);

	$query="SELECT * FROM coordinate_banca";
	$result=do_query($query,$conn);
	$banche=result_to_array($result,false);
	$banca=$banche[0];

	closedb($conn);

	class My_File_PDF extends File_PDF 
	{
		public $mpp=0.352777778;
		private $row;
		private $banca;

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


		
			$this->setTextColor('gray',0);
			$this->setXY(114,46);
			$this->setFont('century','B',8);
			$this->cell(80,5,"Spett.le",0,2,"L");
			$this->setFontSize(11);
			$this->cell(80,4.5,pdfstring($this->row["denominazione"]),0,2,"L");
			$this->cell(80,4.5,pdfstring($this->row["indirizzo_legale"]),0,2,"L");
			if(strlen(trim($this->row["indirizzo_legale_cont"])))
				$this->cell(80,4.5,pdfstring(trim($this->row["indirizzo_legale_cont"])),0,2,"L");
			$this->cell(80,4.5,$row["cap_legale"]." ".pdfstring($this->row["citta_legale"])." (".$this->row["prov_leg"].")",0,2,"L");
		
			$cf=trim($this->row["cf"]);
			$piva=trim($this->row["piva"]);
		
			if(strlen($cf)||strlen($piva))
			{
				if((strlen($piva))&&(($piva==$cf)||(!strlen($cf))))
					$this->cell(80,5,"PI/ CF ".$piva,0,2,"L");
				elseif((strlen($cf))&&(!strlen($piva)))
					$this->cell(80,5,"PI/ CF ".$cf,0,2,"L");
				else
				{
					$this->cell(80,5,"PI ".$piva,0,2,"L");
					$this->cell(80,5,"CF ".$cf,0,2,"L");
				}
			}
		/*	$pdf->newLine(2);
			$pdf->setX(114);
			$pdf->setFontSize(8);
			$pdf->cell(80,3.5,"Invio a:",0,2,"L");
			$pdf->setFontSize(9);
			if(!strlen($row["indirizzo_destinazione"]))
				$ind_dest=$row["indirizzo_legale"];
			else
				$ind_dest=$row["indirizzo_destinazione"];
		
			if(!strlen($row["cap_destinazione"]))
				$cap_dest=$row["cap_legale"];
			else
				$cap_dest=$row["cap_destinazione"];
		
			if(!strlen($row["citta_destinazione"]))
				$citta_dest=$row["citta_legale"];
			else
				$citta_dest=$row["citta_destinazione"];
		
			$pdf->cell(80,3.5,$row["denominazione"],0,2,"L");
			$pdf->cell(80,3.5,pdfstring($ind_dest),0,2,"L");
		
			$prov_dest=strlen($row["prov_dest"]?$row["prov_dest"]:$row["prov_leg"]);
			$pdf->cell(80,3.5,$cap_dest." ".pdfstring($citta_dest)." (".$row["prov_dest"].")",0,2,"L");
		*/
			$this->setXY(14,91);
			$this->setFont('heln','',10);
		
			if($this->row["accompagnatoria"])
			{
				$fattura_text="FATTURA ACCOMPAGNATORIA N.";
				$fattura_width=60;
			}
			else
			{
				$fattura_text="FATTURA N.";
				$fattura_width=32;
			}
			$rifWidth=5+$this->getStringWidth($this->row["rif_ordine"]);
			if($rifWidth<40)
				$rifWidth=40;
			$this->cell($fattura_width,8.5,$fattura_text,1,0,'C');
			$this->cell(28,8.5,"DATA",1,0,'C');
			$this->cell($rifWidth,8.5,"RIF. VS ORDINE",1,1,'C');
			$this->cell($fattura_width,8.5,$this->row["numero"],1,0,'C');
			$this->cell(28,8.5,date("d.m.y",strtotime($this->row["data"])),1,0,'C');
			$this->cell($rifWidth,8.5,$this->row["rif_ordine"],1,1,'C');
		
			$this->setXY(14,112.5);
			$this->setFont('century','',9);
			$this->cell(0,4,"Condizioni pagamento: ".$row["mod_pagamento"],0,1,'L');
			$this->cell(12,4,"Banca: ",0,0,'L');
			$this->cell(12,4,$this->banca["banca"],0,2,'L');
			$this->cell(12,4,"IBAN: ".$this->banca["iban"],0,2,'L');
		
			$this->setXY(14,129);
			$this->setFont('heln','',10);
			$this->cell(140,6,"DESCRIZIONE",1,0,'C');
			$this->cell(42,6,"IMPORTO",1,1,'C');
			$this->cell(140,107,"",1,0,'C');
			$this->cell(42,107,"",1,1,'C');
			$this->setY(135);
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
			$this->newLine(4);

			$this->setFont('helvetica', 'B', 6);
			$this->Cell(182,3,'Page '.$this->getPageNo().'/{nb}', 0, 0, 'R');
		}
		function &factory($params = array(), $class = 'File_PDF')
		{
			$pdf=File_PDF::factory($params,$class);
			$pdf->AddFont('goodtime','','GOODTIME.php');
			$pdf->AddFont('heln','','HELN.php');
			$pdf->AddFont('century','','centuryRoman.php');
			$pdf->AddFont('century','B','centuryBold.php');
			$pdf->setMargins(14,12,14);
			$pdf->setDrawColor('gray',0.6);
			$pdf->setLineWidth(0.1);

			return $pdf;
		}
		function initPDF($row=array(),$banca="")
		{
			$this->row=$row;
			$this->banca=$banca;			
			$this->aliasNbPages();

		}
		function safeLine()
		{
			
		}
	}


	$pdf=My_File_PDF::factory(array('orientation' => 'P','unit' => 'mm','format' => 'A4'),'My_File_PDF');
	$pdf->initPDF($row,$banca);
	$pdf->addPage();

	$pdf->cell(140,6.5,$x." ".$y,0,0,'R');

	$pdf->setFont('century','',12);
	$temppdf=File_PDF::factory();
	$temppdf->AddFont('century','','centuryRoman.php');
	$temppdf->setFont('century','',12);
	$noiva=0;

	foreach($items as $item)
	{
		$temppdf->setXY(0,0);
		printFatturaLine($temppdf,$item);
		$h=$temppdf->getY();
		if($pdf->getY()-135+$h > 107)
			$pdf->addPage();
		printFatturaLine($pdf,$item);
		$noiva+=$item["prezzo"];
	}

	require_once("iva.php");
	$iva_value=(float)getIva($row["data"]);

	$iva=$noiva*$iva_value/100.0;
	$totale=$noiva+$iva;

	$pdf->setXY(14,242);
	$pdf->newLine(3);
	$string="Imponibile ".chr(128)." ";
	$pdf->cell(140,6.5,$string,0,0,'R');
	$pdf->cell(42,6.5,str_replace(".",",",
			sprintf("%.02f",$noiva)),1,1,'R');
	$pdf->cell(140,6.5,"IVA ".$iva_value."% ",0,0,'R');
	$pdf->cell(42,6.5,str_replace(".",",",
			sprintf("%.02f",$iva)),1,1,'R');
	$pdf->cell(140,6.5,"TOTALE DA PAGARE ",0,0,'R');
	$pdf->cell(42,6.5,str_replace(".",",",
			sprintf("%.02f",$totale)),1,1,'R');

	$numero_fattura=sprintf("%d-%03d",date("Y",strtotime($row["data"]))
		,$row["numero"]);
	$pdf->output("fattura_".$numero_fattura.".pdf", false);
}


/*
DROP trigger IF EXISTS `fatture_insert`//

CREATE TRIGGER fatture_insert BEFORE INSERT ON fatture
FOR EACH ROW
BEGIN
	DECLARE new_numero INT default 0;
	DECLARE cur CURSOR FOR SELECT max(numero) FROM fatture WHERE YEAR(data)=YEAR(NEW.data);
	OPEN cur;
	FETCH cur INTO new_numero;
	CLOSE cur;

	IF new_numero IS NULL THEN
		set new_numero=0;
	END IF;
	set NEW.numero=new_numero+1;
END
*/
?>
