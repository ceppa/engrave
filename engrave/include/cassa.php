<?
require_once('mysql.php');
require_once('const.php');
require_once('util.php');
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
		FROM cassa WHERE $where";
	$result=do_query($query,$conn);
	$rows=result_to_array($result,false);
	$total=$rows[0]["c"];

	$query="SELECT cassa.id AS id,
		cassa.data,
		cassa.descrizione,
		cassa.importo 
			FROM cassa  
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
		$entrata=($row["importo"]>0?$row["importo"]:"");
		$uscita=($row["importo"]<0?-$row["importo"]:"");
		if(strlen($entrata))
			$entrata=currency($entrata);
		elseif(strlen($uscita))
			$uscita=currency($uscita);

		$data['rows'][] = array
		(
			'id' => $id,
			'cell' => array
			(
				$row['data'], 
				$row['descrizione'], 
				$entrata, 
				$uscita
			)
		); 
	}
	echo json_encode($data);
	die();
}
elseif($op=='add')
{
	$fields=array("data"=>array("value"=>"","label"=>"Data"),
		"descrizione"=>array("value"=>"","label"=>"Descrizione"),
		"entrata"=>array("value"=>"","label"=>"Entrata"),
		"uscita"=>array("value"=>"","label"=>"Uscita"));

	require_once("forms.php");
	showForm("","cassa_view",$fields);
}
elseif($op=='del')
{
	$conn=opendb();
	$id=$_POST["id"];
	$query="DELETE FROM cassa WHERE id='$id'";
	do_query($query,$conn);
	closedb($conn);
}
elseif($op=='edit')
{
	$fields=array("data"=>array("value"=>"","label"=>"Data"),
		"descrizione"=>array("value"=>"","label"=>"Descrizione"),
		"entrata"=>array("value"=>"","label"=>"Entrata"),
		"uscita"=>array("value"=>"","label"=>"Uscita"));

	$id_value=substr($_POST["id"],3);
	$id_field="id";

	require_once("forms.php");
	showForm("$id_field,$id_value","cassa_view",$fields);
}
else
{
	die();
}

?>
