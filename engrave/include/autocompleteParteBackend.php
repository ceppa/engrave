<?
require_once('mysql.php');

$q = strtolower($_GET["q"]);

$conn=opendb();
$query="SELECT parti.id, parti.codice, parti.descrizione, SUM( qta ) AS qta
		FROM  magazzino
		LEFT JOIN parti ON magazzino.id_parte = parti.id
		WHERE parti.descrizione LIKE '%$q%' OR
			parti.codice LIKE '%$q%' 
		GROUP BY parti.id
		ORDER BY parti.codice";

$result=do_query($query,$conn);
$rows=result_to_array($result,false);

$resultString="";
foreach($rows as $row)
	$resultString .= sprintf("%s|%s|%d|%d\n",
			$row["codice"],
			$row["descrizione"],
			$row["id"],
			$row["qta"]);
echo $resultString;
closedb($conn);
?>
