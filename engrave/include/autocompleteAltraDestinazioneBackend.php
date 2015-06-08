<?
require_once('mysql.php');

$q = strtolower($_GET["q"]);
/*if(!$q)
	die();*/

$conn=opendb();
$query="SELECT magazzino_scarico_lost.id
		,magazzino_scarico_lost.description 
	FROM magazzino_scarico_lost 
	WHERE description LIKE '%$q%' ORDER BY description";

$result=do_query($query,$conn);
$rows=result_to_array($result,false);

$resultString="";
foreach($rows as $row)
	$resultString .= sprintf("%s|%d\n",
			$row["description"],
			$row["id"]);
echo $resultString;
closedb($conn);
?>
