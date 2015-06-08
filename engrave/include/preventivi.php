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
			FROM preventivi LEFT JOIN anag_clienti 
				ON preventivi.id_cliente=anag_clienti.id_cliente 
			WHERE $where";
		$result=do_query($query,$conn);
		$rows=result_to_array($result,false);
		$total=$rows[0]["c"];
	
		$query="SELECT preventivi.id,
			CONCAT(YEAR(preventivi.data),'-',LPAD(preventivi.numero,3,'0')) AS numero,
			IFNULL(anag_clienti.denominazione,preventivi.denominazione) AS cliente,
			IFNULL(preventivi.data,'') AS data,
			preventivi.data_approvazione,
			preventivi.note
			FROM preventivi LEFT JOIN anag_clienti 
				ON preventivi.id_cliente=anag_clienti.id_cliente 
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
					$row['data'], 
					$row['numero'], 
					$row['cliente'], 
					$row['data_approvazione'], 
					$row['note']
				)
			); 
		}

		echo json_encode($data);
		die();
	case "print":
		$id_value=substr($_POST["id"],3);
		print_preventivo($id_value);
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
			"denominazione"=>array("value"=>"","label"=>"Denominazione"),
			"data"=>array("value"=>"","label"=>"Data"),
			"descrizione_titolo"=>array("value"=>"","label"=>"Descrizione Titolo"),
			"descrizione"=>array("value"=>"","label"=>"Descrizione"),
			"validita"=>array("value"=>"","label"=>"Validit&aacute;"),
			"consegna"=>array("value"=>"","label"=>"Consegna"),
			"trasporto"=>array("value"=>"","label"=>"Trasporto"),
			"pagamenti"=>array("value"=>"","label"=>"Pagamenti"),
			"condizione1"=>array("value"=>"","label"=>"Condizione1"),
			"condizione2"=>array("value"=>"","label"=>"Condizione2"),
			"condizione3"=>array("value"=>"","label"=>"Condizione3"),
			"data_approvazione"=>array("value"=>"","label"=>"Data Approvazione"),
			"note"=>array("value"=>"","label"=>"Note"),

		);
		require_once("forms.php");
		showForm($form_id,"preventivi",$fields);
		die();
	case "del":
		$conn=opendb();
		$id=$_POST["id"];
		$query="DELETE FROM preventivi WHERE id='$id'";
		do_query($query,$conn);
		closedb($conn);
		die();
	default:
		die();
}

function print_preventivo($id)
{
	require_once('File/PDF.php');
	require_once("pdf.php");
	require_once("mysql.php");


	$conn=opendb();
	$query="SELECT preventivi.*,preventivi.denominazione AS den_prev, 
				anag_clienti.*,
				prov_leg.provincia AS prov_leg
			FROM preventivi LEFT JOIN anag_clienti 
				ON preventivi.id_cliente=anag_clienti.id_cliente
			LEFT JOIN loc_province AS prov_leg 
				ON anag_clienti.provincia_legale=prov_leg.id_loc_province 
			WHERE preventivi.id='$id'";
	$result=do_query($query,$conn);
	$rows=result_to_array($result,false);
	$row=$rows[0];

	closedb($conn);

	class My_File_PDF extends File_PDF 
	{
		public $mpp=0.352777778;
		private $row;

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

			if($this->row["id_cliente"]>0)
			{
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
			}
			else
				$this->multiCell(80,4.5,pdfstring($this->row["den_prev"]));
	
			$this->setXY(14,91);
			$this->setFont('helvetica','',10);

			$this->multiCell(182,4.5,"Offerta n.".$this->row["numero"]." del ".date("d.m.Y",strtotime($this->row["data"])));
			$this->newLine(4);
			$this->multiCell(182,4.5,pdfstring("\t".$this->row["descrizione_titolo"]));
			$this->newLine(4);

			$this->multiCell(182,4.5,pdfstring($this->row["descrizione"]));
			$this->newLine(4);
			if(strlen($this->row["note"]))
			{
				$this->cell(182,4.5,pdfstring($this->row["note"]),0,1,"L");
				$this->newLine(4);
			}


			$this->SetFont('helvetica','BU');
			$this->cell(182,4.5,"Condizioni:",0,2);
			$this->newLine(4);
			$this->SetFont('helvetica','');
			if(strlen($this->row["validita"]))
				$this->cell(182,4.5,pdfstring("- Validità offerta: ".$this->row["validita"]),0,2,"L");
			if(strlen($this->row["consegna"]))
				$this->cell(182,4.5,pdfstring("- Consegna: ".$this->row["consegna"]),0,2,"L");
			if(strlen($this->row["trasporto"]))
				$this->cell(182,4.5,pdfstring("- Trasporto: ".$this->row["trasporto"]),0,2,"L");
			if(strlen($this->row["pagamenti"]))
				$this->cell(182,4.5,pdfstring("- Pagamenti: ".$this->row["pagamenti"]),0,2,"L");
			if(strlen($this->row["condizione1"]))
				$this->cell(182,4.5,pdfstring("- ".$this->row["condizione1"]),0,2,"L");
			if(strlen($this->row["condizione2"]))
				$this->cell(182,4.5,pdfstring("- ".$this->row["condizione2"]),0,2,"L");
			if(strlen($this->row["condizione3"]))
				$this->cell(182,4.5,pdfstring("- ".$this->row["condizione3"]),0,2,"L");
			if(strlen($this->row["condizione3"]))
				$this->cell(182,4.5,pdfstring("- ".$this->row["condizione3"]),0,2,"L");

			$this->newLine(8);
				$this->cell(182,4.5,pdfstring("In attesa di un gentile riscontro, cogliamo l'occasione per inviare cordiali saluti"),0,2,"L");
			$this->newLine(4);
			$this->setX(129);
			$this->cell(60,4.5,pdfstring("Elisabetta CRISTIN"),0,2,"C");
			$this->Image('../img/elisabetta.jpg',129,$this->getY(),60,0); 			
		
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

/*			$this->setFont('helvetica', 'B', 6);
			$this->Cell(182,3,'Page '.$this->getPageNo().'/{nb}', 0, 0, 'R');*/
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
		function initPDF($row=array())
		{
			$this->row=$row;		
			$this->aliasNbPages();
		}
		function safeLine()
		{
			
		}
	}


	$pdf=My_File_PDF::factory(array('orientation' => 'P','unit' => 'mm','format' => 'A4'),'My_File_PDF');
	$pdf->initPDF($row);
	$pdf->addPage();

	$numero_preventivo=sprintf("%d-%03d",date("Y",strtotime($row["data"]))
		,$row["numero"]);
	$pdf->output("preventivo_".$numero_preventivo.".pdf", false);
}


?>
