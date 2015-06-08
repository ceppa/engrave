<?
require_once('mysql.php');

$q = strtolower($_GET["q"]);
$id_fornitore = $_GET["id_fornitore"];
$id_row=$_GET["id_row"];

$conn=opendb();
$query="SELECT parti.id, parti.codice, parti.descrizione, 1 AS qta
		FROM  parti
		WHERE (parti.descrizione LIKE '%$q%' OR
			parti.codice LIKE '%$q%') 
			AND parti.id_fornitore='$id_fornitore' 
		ORDER BY parti.codice";

$result=do_query($query,$conn);
$rows=result_to_array($result,false);

$resultString="";
foreach($rows as $row)
	$resultString .= sprintf("%s|%s|%d|%d\n",
			$row["codice"],
			$row["descrizione"],
			$row["id"],
			$id_row);
echo $resultString;
closedb($conn);
?>
