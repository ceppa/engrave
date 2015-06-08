<?
require_once('mysql.php');

$q = strtolower($_GET["q"]);
/*if(!$q)
	die();*/

$conn=opendb();
$query="SELECT loc_comuni.id_comune,loc_comuni.localita,
		cap,loc_comuni.id_loc_province AS id_loc_province
		,loc_province.provincia
	FROM loc_comuni LEFT JOIN loc_province 
		ON loc_comuni.id_loc_province=loc_province.id_loc_province
	WHERE loc_comuni.localita LIKE '%$q%' ORDER BY loc_comuni.localita";

$result=do_query($query,$conn);
$rows=result_to_array($result,false);

$resultString="";
foreach($rows as $row)
	$resultString .= sprintf("%s|%s|%d|%d|%05d\n",
			$row["localita"],
			$row["provincia"],
			$row["id_comune"],
			$row["id_loc_province"],
			$row["cap"]);
echo $resultString;
closedb($conn);
?>
