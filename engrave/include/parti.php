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
			FROM parti 
				LEFT JOIN um ON parti.um_id=um.id 
					LEFT JOIN anag_fornitori 
						ON parti.id_fornitore=anag_fornitori.id_fornitore 
					LEFT JOIN tipo_materiale ON parti.id_tipo_materiale=tipo_materiale.id
			WHERE $where";
		$result=do_query($query,$conn);
		$rows=result_to_array($result,false);
		$total=$rows[0]["c"];
	
		$query="SELECT parti.id,
			parti.codice,
			parti.descrizione,
			um.um,
			anag_fornitori.denominazione AS fornitore,
			tipo_materiale.description AS tipo_materiale,
			parti.scorta_minima
				FROM parti
					LEFT JOIN um ON parti.um_id=um.id 
					LEFT JOIN anag_fornitori 
						ON parti.id_fornitore=anag_fornitori.id_fornitore 
					LEFT JOIN tipo_materiale ON parti.id_tipo_materiale=tipo_materiale.id
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
					$row['codice'], 
					$row['descrizione'], 
					$row['um'], 
					$row['fornitore'], 
					$row['tipo_materiale'], 
					$row['scorta_minima']
				)
			); 
		}
		echo json_encode($data);
		die();
	case "edit":
		$id_value=substr($_POST["id"],3);
		$id_field="id";
		$form_id="$id_field,$id_value";
		$fields=array(
			"codice"=>array("value"=>"","label"=>"Codice"),
			"descrizione"=>array("value"=>"","label"=>"Descrizione"),
			"um_id"=>array("value"=>"","label"=>"UM"
				,"link"=>array("table"=>"um"
				,"id"=>"id","text"=>"um")),
			"id_fornitore"=>array("value"=>"","label"=>"Fornitore"
				,"link"=>array("table"=>"anag_fornitori"
				,"id"=>"id_fornitore","text"=>"denominazione")),
			"id_tipo_materiale"=>array("value"=>"","label"=>"Tipo Materiale"
				,"link"=>array("table"=>"tipo_materiale"
				,"id"=>"id","text"=>"description")),
			"scorta_minima"=>array("value"=>"","label"=>"Scorta minima")
		);

	
		require_once("forms.php");
		showForm($form_id,"parti",$fields);
		die();
	default:
		die();
}

?>
