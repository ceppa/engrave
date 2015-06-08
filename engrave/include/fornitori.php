<?
require_once('mysql.php');
require_once('const.php');
require_once('../config.php');

$op=$_REQUEST["op"];

if($op=="list")
{
	$page=$_POST["page"];
	
	$rp=$_POST["rp"];
	$limit=sprintf("%d,%d",(int)($page-1)*$rp,(int)$rp);

	$sortname=$_POST["sortname"];
	$sortorder=$_POST["sortorder"];
	$q=$_POST["query"];
	$qtype=$_POST["qtype"];
	$where="$qtype LIKE '%$q%'";

	$conn=opendb();
	$query="SELECT count(*) AS c 
		FROM anag_fornitori WHERE $where";
	$result=do_query($query,$conn);
	$rows=result_to_array($result,false);
	$total=$rows[0]["c"];

	$query="SELECT anag_fornitori.id_fornitore AS id,
		anag_fornitori.denominazione,
		anag_fornitori.indirizzo,
		anag_fornitori.citta,
		loc_province.provincia,
		anag_fornitori.cap,
		anag_fornitori.piva,
		anag_fornitori.cf,
		anag_fornitori.note
			FROM anag_fornitori LEFT JOIN loc_province 
				ON anag_fornitori.provincia=loc_province.id_loc_province 
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
				$row['indirizzo'], 
				$row['citta'], 
				$row['provincia'], 
				$row['cap'], 
				$row['piva'], 
				$row['cf'],
				$row['note']
			)
		); 
	}
	echo json_encode($data);
		
	die();
}
elseif($op=='add')
{
	$fields=array("denominazione"=>array("value"=>"","label"=>"Denominazione"),
		"indirizzo"=>array("value"=>"","label"=>"Indirizzo"),
		"citta"=>array("value"=>"","label"=>"Città"),
		"provincia"=>array("value"=>"","label"=>"Provincia"
			,"link"=>array("table"=>"loc_province"
			,"id"=>"id_loc_province","text"=>"provincia")),
		"cap"=>array("value"=>"","label"=>"CAP"),
		"piva"=>array("value"=>"","label"=>"Partita IVA"),
		"cf"=>array("value"=>"","label"=>"Codice Fiscale"),
		"note"=>array("value"=>"","label"=>"Note"));

	require_once("forms.php");
	showForm("","anag_fornitori",$fields);
}
elseif($op=='del')
{
	$conn=opendb();
	$id=$_POST["id"];
	$query="DELETE FROM anag_fornitori WHERE id_fornitore='$id'";
	do_query($query,$conn);
	closedb($conn);
}
elseif($op=='edit')
{
	$fields=array("denominazione"=>array("value"=>"","label"=>"Denominazione"),
		"indirizzo"=>array("value"=>"","label"=>"Indirizzo"),
		"citta"=>array("value"=>"","label"=>"Città"),
		"provincia"=>array("value"=>"","label"=>"Provincia"
			,"link"=>array("table"=>"loc_province"
			,"id"=>"id_loc_province","text"=>"provincia")),
		"cap"=>array("value"=>"","label"=>"CAP"),
		"piva"=>array("value"=>"","label"=>"Partita IVA"),
		"cf"=>array("value"=>"","label"=>"Codice Fiscale"),
		"note"=>array("value"=>"","label"=>"Note"));

	$id_value=substr($_POST["id"],3);
	$id_field="id_fornitore";

	require_once("forms.php");
	showForm("$id_field,$id_value","anag_fornitori",$fields);
}
else
{
	die();
}

?>
