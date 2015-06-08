<?
require_once('mysql.php');

function getIva($data)
{
	$conn=opendb();
	$query="SELECT iva
		FROM iva WHERE '$data'>=data_inizio
		ORDER BY data_inizio DESC";

	$result=do_query($query,$conn);
	$rows=result_to_array($result,false);
	if(count($rows))
		$iva=$rows[0]["iva"];
	else
		$iva=21;
	closedb($conn);
	return $iva;
}

?>
