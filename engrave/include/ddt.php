<?
require_once('mysql.php');
require_once('const.php');
require_once('../config.php');

$op=$_REQUEST["op"];
$form_id="";

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
			FROM ddt WHERE $where";
		$result=do_query($query,$conn);
		$rows=result_to_array($result,false);
		$total=$rows[0]["c"];
	
		$query="SELECT ddt.id,
			CONCAT(ddt.numero,'-',YEAR(ddt.data)) AS numero,
			IFNULL(anag_clienti.denominazione,anag_fornitori.denominazione) 
				AS cliente_fornitore,
			ddt.data,
			ddt.data_trasporto,
			ddt.num_colli,
			ddt.peso,
			ddt.merce_aspetto,
			ddt.corriere,
			ddt.causale
				FROM ddt 
					LEFT JOIN anag_clienti 
						ON ddt.id_cliente=anag_clienti.id_cliente 
					LEFT JOIN anag_fornitori 
						ON ddt.id_fornitore=anag_fornitori.id_fornitore 
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
			$data['rows'][] = array
			(
				'id' => $id,
				'cell' => array
				(
					$row['numero'], 
					$row['cliente_fornitore'], 
					$row['data'], 
					$row['data_trasporto'], 
					$row['num_colli'], 
					$row['peso'], 
					$row['merce_aspetto'], 
					$row['corriere'],
					$row['causale']
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
		$fields=array(
			"id_cliente"=>array("value"=>"","label"=>"Cliente"
				,"link"=>array("table"=>"anag_clienti"
				,"id"=>"id_cliente","text"=>"denominazione")),
			"id_fornitore"=>array("value"=>"","label"=>"Fornitore"
				,"link"=>array("table"=>"anag_fornitori"
				,"id"=>"id_fornitore","text"=>"denominazione")),
			"data"=>array("value"=>"","label"=>"Data DDT"),
			"data_trasporto"=>array("value"=>"","label"=>"Data trasporto"),
			"riferimento"=>array("value"=>"","label"=>"Riferimento"),
			"num_colli"=>array("value"=>"","label"=>"Num colli"),
			"peso"=>array("value"=>"","label"=>"Peso (kg)"),
			"merce_aspetto"=>array("value"=>"","label"=>"Aspetto"),
			"corriere"=>array("value"=>"","label"=>"Trasporto a cura"),
			"causale"=>array("value"=>"","label"=>"Causale"),
			"id"=>array
			(
				"value"=>"",
				"label"=>"Dettagli",
				"details"=>array
				(
					"table"=>"ddt_items",
					"id"=>"id_ddt",
					"fields"=>array
					(
						array(
							"label"=>"descrizione",
							"field"=>"descrizione",
							"length"=>"50"
							),
						array(
							"label"=>"qta",
							"field"=>"qta",
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
		showForm($form_id,"ddt",$fields);
		die();
	case "del":
		$conn=opendb();
		$id=$_POST["id"];
		$query="DELETE FROM ddt WHERE id='$id'";
		do_query($query,$conn);
		closedb($conn);
		die();
	case "print":
		$id_value=substr($_POST["id"],3);
		print_ddt($id_value);
		die();
	default:
		die();
}


function print_ddt($id)
{
	require_once('File/PDF.php');
	require_once("pdf.php");
	require_once("mysql.php");


	$conn=opendb();
	$query="SELECT ddt.*, 
				anag_clienti.denominazione,
				anag_clienti.indirizzo_legale,
				anag_clienti.citta_legale,
				anag_clienti.provincia_legale,
				anag_clienti.cap_legale,
				anag_clienti.indirizzo_destinazione,
				anag_clienti.citta_destinazione,
				anag_clienti.provincia_destinazione,
				anag_clienti.cap_destinazione,
				anag_clienti.piva AS piva_cliente,
				anag_clienti.cf AS cf_cliente,
				anag_fornitori.denominazione AS denominazione_fornitore,
				anag_fornitori.indirizzo AS indirizzo_fornitore,
				anag_fornitori.citta AS citta_fornitore,
				anag_fornitori.provincia AS provincia_fornitore,
				anag_fornitori.cap AS cap_fornitore,
				anag_fornitori.piva AS piva_fornitore,
				anag_fornitori.cf AS cf_fornitore,
				prov_leg.provincia AS prov_leg,
				prov_dest.provincia AS prov_dest,
				prov_fornitore.provincia AS prov_fornitore 
			FROM ddt LEFT JOIN anag_clienti 
				ON ddt.id_cliente=anag_clienti.id_cliente
			LEFT JOIN anag_fornitori 
				ON ddt.id_fornitore=anag_fornitori.id_fornitore
			LEFT JOIN loc_province AS prov_leg 
				ON anag_clienti.provincia_legale=prov_leg.id_loc_province 
			LEFT JOIN loc_province AS prov_dest 
				ON anag_clienti.provincia_destinazione=prov_dest.id_loc_province 
			LEFT JOIN loc_province AS prov_fornitore 
				ON anag_fornitori.provincia=prov_leg.id_loc_province 
			WHERE ddt.id='$id'";
	$result=do_query($query,$conn);
	$rows=result_to_array($result,false);
	$row=$rows[0];

	if(strlen($row["id_cliente"]))
	{
		$denominazione=$row["denominazione"];
		$indirizzo_legale=$row["indirizzo_legale"];
		$citta_legale=$row["citta_legale"];
		$provincia_legale=$row["prov_leg"];
		$cap_legale_field=$row["cap_legale"];
		$indirizzo_destinazione=(strlen($row["indirizzo_destinazione"])
			?$row["indirizzo_destinazione"]:$row["indirizzo_legale"]);
		$citta_destinazione=(strlen($row["citta_destinazione"])
			?$row["citta_destinazione"]:$row["citta_legale"]);
		$provincia_destinazione=(strlen($row["prov_dest"])
			?$row["prov_dest"]:$row["prov_leg"]);
		$cap_destinazione=(strlen($row["cap_destinazione"])
			?$row["cap_destinazione"]:$row["cap_legale"]);
		$piva=$row["piva_cliente"];
		$cf=$row["cf_cliente"];
	}
	else
	{
		$denominazione=$row["denominazione_fornitore"];
		$indirizzo_legale=$row["indirizzo_fornitore"];
		$citta_legale=$row["citta_fornitore"];
		$provincia_legale=$row["prov_fornitore"];
		$cap_legale_field=$row["cap_fornitore"];
		$indirizzo_destinazione=$row["indirizzo_fornitore"];
		$citta_destinazione=$row["citta_fornitore"];
		$provincia_destinazione=$row["provincia_fornitore"];
		$cap_destinazione=$row["cap_fornitore"];
		$piva=$row["piva_fornitore"];
		$cf=$row["cf_fornitore"];
	}

	$query="SELECT * FROM ddt_items WHERE id_ddt='$id'";
	$result=do_query($query,$conn);
	$items=result_to_array($result,false);

	closedb($conn);

	class My_File_PDF extends File_PDF 
	{
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
		}
		function factory()
		{
			$pdf=File_PDF::factory(array('orientation' => 'P','unit' => 'mm','format' => 'A4'),
					'My_File_PDF');
			$pdf->AddFont('goodtime','','GOODTIME.php');
			$pdf->AddFont('heln','','HELN.php');
			$pdf->AddFont('century','','centuryRoman.php');
			$pdf->AddFont('century','B','centuryBold.php');
			$pdf->setMargins(14,12,14);
			return $pdf;
		}
		function safeLine()
		{
			
		}
	}

	$pdf=My_File_PDF::factory();
	$pdf->setDrawColor('gray',0.6);
	$pdf->setLineWidth(0.1);
	$pdf->addPage();
	$pdf->setXY(114,46);
	$pdf->setFont('century','B',8);
	$pdf->cell(80,5,"Spett.le",0,2,"L");
	$pdf->setFontSize(11);
	$pdf->cell(80,4.5,$denominazione,0,2,"L");
	$pdf->cell(80,4.5,$indirizzo_legale,0,2,"L");
	$pdf->cell(80,4.5,$cap_legale." ".$citta_legale." (".$provincia_legale.")",0,2,"L");
	if(strlen($piva))
		$piva_cf=$piva;
	else
		$piva_cf=$cf;
	$pdf->cell(80,5,"PI/ CF ".$piva_cf,0,2,"L");

	$pdf->newLine(2);
	$pdf->setX(114);
	$pdf->setFontSize(8);
	$pdf->cell(80,3.5,"Destinazione:",0,2,"L");
	$pdf->setFontSize(9);

	$pdf->cell(80,3.5,pdfstring($indirizzo_destinazione),0,2,"L");

	$pdf->cell(80,3.5,$cap_destinazione." ".pdfstring($citta_destinazione).
		" (".$provincia_destinazione.")",0,2,"L");

	$pdf->setXY(14,91);
	$pdf->setFont('heln','',10);

	$rifWidth=5+$pdf->getStringWidth($row["riferimento"]);
	if($rifWidth<40)
		$rifWidth=40;
	$pdf->cell(68,8.5,"DOCUMENTO DI TRASPORTO (DDT) N.",1,0,'C');
	$pdf->cell(28,8.5,"DATA",1,0,'C');
	$pdf->cell($rifWidth,8.5,"VS. RIF.",1,1,'C');
	$pdf->cell(68,8.5,$row["numero"],1,0,'C');
	$pdf->cell(28,8.5,date("d.m.y",strtotime($row["data"])),1,0,'C');
	$pdf->cell($rifWidth,8.5,$row["riferimento"],1,1,'C');

	$pdf->setXY(14,115);
	$pdf->setFont('heln','',10);
	$pdf->cell(152,6,"DESCRIZIONE",1,0,'C');
	$pdf->cell(30,6,"QUANTITA",1,1,'C');
	$pdf->cell(152,107,"",1,0,'C');
	$pdf->cell(30,107,"",1,1,'C');

	$pdf->newLine(4);
	$x=$pdf->getX();
	$y=$pdf->getY();
	$pdf->setFont('heln','',10);
	$pdf->cell(55,18,"",1,0,'C');
	$pdf->cell(65,18,"",1,0,'C');
	$pdf->cell(31,18,"",1,0,'C');
	$pdf->cell(31,18,"",1,1,'C');

	$pdf->setY($y);
	$pdf->cell(55,6,"CAUSALE",0,0,'L');
	$pdf->cell(65,6,"ASPETTO DEI BENI",0,0,'L');
	$pdf->cell(31,6,"NUMERO COLLI",0,0,'L');
	$pdf->cell(31,6,"PESO",0,1,'L');
	$pdf->cell(55,12,pdfstring($row["causale"]),0,0,'L');
	$pdf->cell(65,12,pdfstring($row["merce_aspetto"]),0,0,'L');
	$pdf->cell(31,12,$row["num_colli"],0,0,'C');
	$pdf->cell(31,12,$row["peso"],0,1,'L');

	$pdf->newLine(4);
	$x=$pdf->getX();
	$y=$pdf->getY();
	$pdf->setFont('heln','',10);
	$pdf->cell(45.5,14,"",1,0,'C');
	$pdf->cell(45.5,14,"",1,0,'C');
	$pdf->cell(45.5,14,"",1,0,'C');
	$pdf->cell(45.5,14,"",1,1,'C');
	$pdf->setY($y);
	$pdf->cell(45.5,6,"TRASPORTO A CURA",0,0,'L');
	$pdf->cell(45.5,6,"DATA TRASPORTO",0,0,'L');
	$pdf->cell(45.5,6,"FIRMA CONDUCENTE",0,0,'L');
	$pdf->cell(45.5,6,"FIRMA DESTINATARIO",0,1,'L');
	$pdf->cell(45.5,8,$row["corriere"],0,0,'L');
	$pdf->cell(45.5,8,date("d.m.y",strtotime($row["data_trasporto"])),0,1,'L');

	$pdf->setY(121);
	$pdf->setFont('century','',12);
	foreach($items as $item)
	{
		$pdf->newLine(4);
		$pdf->setX(18);
		$py=$pdf->getY();
		$line="";
		if(strlen(trim($item["note"])))
			$line=" ".trim(pdfstring($item["note"]))."\n";
		$line.=trim(pdfstring($item["descrizione"]));
		$pdf->multiCell(148,5,$line,0,'L',0);
		$ch=$pdf->getY()-$py;
		$pdf->setXY(166,$py);
		$pdf->cell(30,$ch,$item["qta"],0,1,'C');
	}


	$numero_ddt=sprintf("%d-%03d",date("Y",strtotime($row["data"]))
		,$row["numero"]);
	$pdf->Output("ddt_".$numero_ddt.".pdf", false);

}

/*

DROP trigger IF EXISTS `ddt_insert`//

CREATE TRIGGER ddt_insert BEFORE INSERT ON ddt
FOR EACH ROW
BEGIN
	DECLARE new_numero INT default 0;
	DECLARE cur CURSOR FOR SELECT max(numero) FROM ddt WHERE YEAR(data)=YEAR(NEW.data);
	OPEN cur;
	FETCH cur INTO new_numero;
	CLOSE cur;

	IF new_numero IS NULL THEN
		set new_numero=0;
	END IF;
	set NEW.numero=new_numero+1;

	IF NEW.id_cliente=0 THEN
		set NEW.id_cliente=NULL;
	END IF;
	IF NEW.id_fornitore=0 THEN
		set NEW.id_fornitore=NULL;
	END IF;
END


DROP trigger IF EXISTS `ddt_update`//

CREATE TRIGGER ddt_update BEFORE UPDATE ON ddt
FOR EACH ROW
BEGIN
	IF NEW.id_cliente=0 THEN
		set NEW.id_cliente=NULL;
	END IF;
	IF NEW.id_fornitore=0 THEN
		set NEW.id_fornitore=NULL;
	END IF;
END
*/
?>

