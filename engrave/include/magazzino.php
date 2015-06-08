<?
require_once('mysql.php');
require_once('const.php');
require_once('util.php');
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
		$where="$qtype LIKE '%$q%'";
		require_once("magazzino_common.php");

		$array2=computeWarehouse("9999-99-99",$where,"$sortname $sortorder,data_movimento");

		$total=count($array2);
		$array2=array_slice($array2,(int)($page-1)*$rp,(int)$rp);

		$data=array();
		$data['page'] = $page;
		$data['total'] = $total;
		foreach($array2 as $id=>$linee)
		{
			if(count($linee))
			{
				$qta_string="";
				$qta=0;
				foreach($linee as $linea)
				{
					$qta+=$linea['qta'];
					$qta_string.=fixNumber($linea['qta'])." - ".fixNumber($linea["prezzo"])."€<br>";
					$row=$linea;
				}
				$qta=fixNumber($qta);
				$qta_string=rtrim($qta_string,"<br>");
				$data['rows'][] = array
				(
					'id' => $id,
					'cell' => array
					(
						$row['tipo'], 
						$row['fornitore'], 
						$row['codice'], 
						$row['descrizione'], 
						$row['um'], 
						$qta_string,
						$qta,
						$row['scorta_minima']
					)
				);
			}
		}
//		file_put_contents("log.txt",print_r($data,true));
		echo json_encode($data);
		die();
	default:
		die();
}
/*

DROP TRIGGER IF EXISTS `magazzino_insert`//

CREATE TRIGGER magazzino_insert BEFORE INSERT ON magazzino
FOR EACH ROW 
BEGIN
	DECLARE max_qty DECIMAL(10,3) default 0;
	DECLARE cur CURSOR FOR SELECT SUM( qta ) AS qta FROM  magazzino
		WHERE id_parte=NEW.id_parte;

	OPEN cur;
	FETCH cur INTO max_qty;
	CLOSE cur;

	IF max_qty IS NULL THEN
		set max_qty=0;
	END IF;

	IF NEW.qta<0 AND ABS(NEW.qta)>max_qty THEN
		SET NEW.qta=-max_qty;
    END IF;
END;
*/

?>



