<?
require_once('mysql.php');
require_once('const.php');
require_once('../config.php');

$op=$_REQUEST["op"];

if($op=="list")
{
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
		FROM anag_clienti WHERE $where";
	$result=do_query($query,$conn);
	$rows=result_to_array($result,false);
	$total=$rows[0]["c"];

	$query="SELECT anag_clienti.id_cliente AS id,
		anag_clienti.denominazione,
		anag_clienti.indirizzo_legale,
		anag_clienti.citta_legale,
		loc_province.provincia AS provincia_legale,
		anag_clienti.cap_legale,
		anag_clienti.indirizzo_destinazione,
		anag_clienti.citta_destinazione,
		loc_province.provincia AS provincia_destinazione,
		anag_clienti.cap_destinazione,
		anag_clienti.piva,
		anag_clienti.cf,
		anag_clienti.email,
		anag_clienti.mailing_list,
		anag_clienti.note 
			FROM anag_clienti 
				LEFT JOIN loc_province 
					ON anag_clienti.provincia_legale=loc_province.id_loc_province 
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
				$row['denominazione'], 
				$row['indirizzo_legale'], 
				$row['citta_legale'], 
				$row['provincia_legale'], 
				$row['cap_legale'], 
				$row['piva'], 
				$row['cf'], 
				$row['email'], 
				$row['mailing_list'], 
				$row['note']
			)
		); 
	}
	echo json_encode($data);
	die();
}
elseif($op=='del')
{
	$conn=opendb();
	$id=$_POST["id"];
	$query="DELETE FROM anag_clienti WHERE id_cliente='$id'";
	do_query($query,$conn);
	closedb($conn);
}
elseif(($op=='edit')||($op=='add'))
{
	$fields=array("denominazione"=>array("value"=>"","label"=>"Denominazione"),
		"indirizzo_legale"=>array("value"=>"","label"=>"Indirizzo"),
		"indirizzo_legale_cont"=>array("value"=>"","label"=>"Indirizzo"),
		"citta_legale"=>array("value"=>"","label"=>"Città"),
		"provincia_legale"=>array("value"=>"","label"=>"Provincia"
			,"link"=>array("table"=>"loc_province"
			,"id"=>"id_loc_province","text"=>"provincia")),
		"cap_legale"=>array("value"=>"","label"=>"CAP"),
		"indirizzo_destinazione"=>array("value"=>"","label"=>"Indirizzo dest"),
		"indirizzo_destinazione_cont"=>array("value"=>"","label"=>"Indirizzo dest"),
		"citta_destinazione"=>array("value"=>"","label"=>"Città dest"),
		"provincia_destinazione"=>array("value"=>"","label"=>"Provincia dest"
			,"link"=>array("table"=>"loc_province"
			,"id"=>"id_loc_province","text"=>"provincia")),
		"cap_destinazione"=>array("value"=>"","label"=>"CAP dest"),
		"piva"=>array("value"=>"","label"=>"Partita IVA"),
		"cf"=>array("value"=>"","label"=>"Codice Fiscale"),
		"id_mod_pagamento"=>array("value"=>"","label"=>"Modalità di pagamento"
			,"link"=>array("table"=>"mod_pagamento"
			,"id"=>"id","text"=>"description")),
		"banca_appoggio"=>array("value"=>"","label"=>"Banca di appoggio"),
		"iban"=>array("value"=>"","label"=>"IBAN"),
		"email"=>array("value"=>"","label"=>"email"),
		"mailing_list"=>array("value"=>"","label"=>"Mailing_list"),
		"note"=>array("value"=>"","label"=>"Note"));

	$id='';
	if($op=='edit')
	{
		$id_value=substr($_POST["id"],3);
		$id_field="id_cliente";
		$id="$id_field,$id_value";
	}

	require_once("forms.php");
	showForm($id,"anag_clienti",$fields);
}
else
{
	die();
}


/*

DROP TRIGGER IF EXISTS `clienti_insert`//

CREATE TRIGGER clienti_insert BEFORE INSERT ON anag_clienti
FOR EACH ROW 
BEGIN
    IF NEW.provincia_destinazione = '' THEN
        SET NEW.provincia_destinazione = NULL;
    END IF;
    IF NEW.id_mod_pagamento = '0' THEN
        SET NEW.id_mod_pagamento = NULL;
    END IF;
END;

DROP TRIGGER IF EXISTS `clienti_update`//

CREATE TRIGGER clienti_update BEFORE UPDATE ON anag_clienti
FOR EACH ROW 
BEGIN
    IF NEW.provincia_destinazione = '' THEN
        SET NEW.provincia_destinazione = NULL;
    END IF;
    IF NEW.id_mod_pagamento = '0' THEN
        SET NEW.id_mod_pagamento = NULL;
    END IF;
END;
*/
?>
