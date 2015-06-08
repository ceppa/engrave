<?
require_once('mysql.php');
require_once('util.php');

$q = strtolower($_GET["q"]);
$excluded=(isset($_GET["exclude"])?rtrim($_GET["exclude"],","):"");
if(!strlen($excluded))
	$excluded="''";

$conn=opendb();
$query="SELECT parti.id, parti.codice, parti.descrizione, SUM( qta ) AS qta
		FROM  magazzino
		LEFT JOIN parti ON magazzino.id_parte = parti.id
		WHERE (parti.descrizione LIKE '%$q%' OR
			parti.codice LIKE '%$q%')
			AND parti.id NOT IN ($excluded) 
		GROUP BY parti.id
		HAVING qta>0
		ORDER BY parti.codice";

$result=do_query($query,$conn);
$rows=result_to_array($result,false);

$resultString="";
foreach($rows as $row)
{
	$qta=fixNumber($row["qta"]);
	$resultString.=sprintf("%s|%s|%d|%s\n",
			$row["codice"],
			$row["descrizione"],
			$row["id"],
			$qta);
}
echo $resultString;
closedb($conn);
?>
