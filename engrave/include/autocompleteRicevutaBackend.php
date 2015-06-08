<?
require_once('mysql.php');

$q=strtolower($_GET["q"]);

$conn=opendb();
$query="SELECT ricevute.id, 
		CONCAT(numero,'-',YEAR(data)) as numero_ricevuta,
		cliente
		FROM  ricevute
		WHERE CONCAT(numero,' - ',YEAR(data)) LIKE '%$q%'
		ORDER BY YEAR(data) DESC,numero DESC";


$result=do_query($query,$conn);
$rows=result_to_array($result,false);

$resultString="";
foreach($rows as $row)
	$resultString .= sprintf("%s|%s|%d\n",
			$row["numero_ricevuta"],
			$row["cliente"],
			$row["id"]);
echo $resultString;
closedb($conn);
?>
