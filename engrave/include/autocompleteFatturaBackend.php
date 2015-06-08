<?
require_once('mysql.php');

$q=strtolower($_GET["q"]);

$conn=opendb();
$query="SELECT fatture.id, CONCAT(numero,'-',YEAR(data)) as numero_fattura,
		anag_clienti.denominazione AS cliente 
		FROM  fatture
			LEFT JOIN anag_clienti ON fatture.id_cliente=anag_clienti.id_cliente
		WHERE CONCAT(numero,' - ',YEAR(data)) LIKE '%$q%'
			OR anag_clienti.denominazione LIKE '%$q%' 
		ORDER BY YEAR(data) DESC,numero DESC";


$result=do_query($query,$conn);
$rows=result_to_array($result,false);

$resultString="";
foreach($rows as $row)
	$resultString .= sprintf("%s|%s|%d\n",
			$row["numero_fattura"],
			$row["cliente"],
			$row["id"]);
echo $resultString;
closedb($conn);
?>
