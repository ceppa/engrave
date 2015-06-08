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
		FROM mailing_list WHERE $where";
	$result=do_query($query,$conn);
	$rows=result_to_array($result,false);
	$total=$rows[0]["c"];

	$query="SELECT mailing_list.id,
		mailing_list.denominazione,
		mailing_list.indirizzo,
		mailing_list.citta,
		loc_province.provincia AS provincia,
		mailing_list.email,
		mailing_list.check_mailing_list,
		mailing_list.note
			FROM mailing_list 
				LEFT JOIN loc_province 
					ON mailing_list.provincia=loc_province.id_loc_province 
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
				$row['email'], 
				$row['check_mailing_list'], 
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
	$query="DELETE FROM mailing_list WHERE id='$id'";
	do_query($query,$conn);
	closedb($conn);
}
elseif(($op=='edit')||($op=='add'))
{
	$fields=array("denominazione"=>array("value"=>"","label"=>"Denominazione"),
		"indirizzo"=>array("value"=>"","label"=>"Indirizzo"),
		"indirizzo_more"=>array("value"=>"","label"=>"Indirizzo"),
		"citta"=>array("value"=>"","label"=>"Città"),
		"provincia"=>array("value"=>"","label"=>"Provincia"
			,"link"=>array("table"=>"loc_province"
			,"id"=>"id_loc_province","text"=>"provincia")),
		"email"=>array("value"=>"","label"=>"email"),
		"check_mailing_list"=>array("value"=>"","label"=>"Check Mailing List"),
		"note"=>array("value"=>"","label"=>"Note"));

	$id='';
	if($op=='edit')
	{
		$id_value=substr($_POST["id"],3);
		$id_field="id";
		$id="$id_field,$id_value";
	}

	require_once("forms.php");
	showForm($id,"mailing_list",$fields);
}
else
{
	die();
}


/*

DROP TRIGGER IF EXISTS `mailing_list_insert`//

CREATE TRIGGER mailing_list_insert BEFORE INSERT ON mailing_list
FOR EACH ROW 
BEGIN
    IF NEW.provincia = '' THEN
        SET NEW.provincia = NULL;
    END IF;
END;

DROP TRIGGER IF EXISTS `mailing_list_update`//

CREATE TRIGGER mailing_list_update BEFORE UPDATE ON mailing_list
FOR EACH ROW 
BEGIN
    IF NEW.provincia = '' THEN
        SET NEW.provincia = NULL;
    END IF;
END;
*/
?>
