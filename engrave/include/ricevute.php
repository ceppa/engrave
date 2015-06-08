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
	
		$where="YEAR(data)>=2015 AND $qtype LIKE '%$q%'";
		
		$conn=opendb();
		$query="SELECT ricevute.id,
			CONCAT(YEAR(ricevute.data),'-',LPAD(ricevute.numero,3,'0')) AS numero,
			ricevute.cliente,
			ricevute.data,
			REPLACE(ricevute.importo,'.',',') AS importo,
			ricevute.data_incasso,
			IF(IFNULL(ricevute.contanti,0)=0,'','Y') AS contanti,
			IF(IFNULL(ricevute.bonifico,0)=0,'','Y') AS bonifico
			FROM ricevute
			WHERE $where";

		$result=do_query($query,$conn);
		$total=$result->num_rows;
	
		$query="SELECT ricevute.id,
			CONCAT(YEAR(ricevute.data),'-',LPAD(ricevute.numero,3,'0')) AS numero,
			ricevute.cliente,
			ricevute.data,
			REPLACE(ricevute.importo,'.',',') AS importo,
			ricevute.data_incasso,
			IF(IFNULL(ricevute.contanti,0)=0,'','Y') AS contanti,
			IF(IFNULL(ricevute.bonifico,0)=0,'','Y') AS bonifico
			FROM ricevute
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
					$row['data'], 
					$row['cliente'], 
					$row['importo'],
					$row['data_incasso'],
					$row['contanti'],
					$row['bonifico']
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
		$fields=array
		(
			"data"=>array("value"=>"","label"=>"Data"),
			"cliente"=>array("value"=>"","label"=>"Cliente"),
			"importo"=>array("value"=>"","label"=>"Importo"),
			"data_incasso"=>array("value"=>"","label"=>"Data incasso"),
			"contanti"=>array("value"=>"","label"=>"Contanti"),
			"bonifico"=>array("value"=>"","label"=>"Bonifico")
		);
		require_once("forms.php");
		showForm($form_id,"ricevute",$fields);
		die();
	case "del":
		$conn=opendb();
		$id=$_POST["id"];
		$query="DELETE FROM ricevute WHERE id='$id'";
		do_query($query,$conn);
		closedb($conn);
		die();
	default:
		die();
}

/*
DROP trigger IF EXISTS `ricevute_insert`//

CREATE TRIGGER ricevute_insert BEFORE INSERT ON ricevute
FOR EACH ROW
BEGIN
	DECLARE new_numero INT default 0;
	DECLARE cur CURSOR FOR SELECT max(numero) FROM ricevute WHERE YEAR(data)=YEAR(NEW.data);
	OPEN cur;
	FETCH cur INTO new_numero;
	CLOSE cur;

	IF new_numero IS NULL THEN
		set new_numero=0;
	END IF;
	set NEW.numero=new_numero+1;

	IF NEW.data_incasso="" THEN
		set NEW.data_incasso=NULL;
	END IF;
END
*/
?>
