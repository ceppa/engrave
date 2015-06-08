<?
	$dbname="engravel_engrave";
	$myhost="localhost";
	$myuser="engravel_envy";
	$mypass="minair";

	function my_die($message)
	{
		$fp = fopen('error.txt', 'w');
		fwrite($fp, "$message\n");
		fclose($fp);
		die();
	}

	function opendb()
	{
		global $dbname,$myhost,$myuser,$mypass;
		$mysqli=new mysqli($myhost,$myuser,$mypass,$dbname);
		if (mysqli_connect_errno())
			die("Connect failed: ". mysqli_connect_error());
		return $mysqli;
	}

	function closedb($mysqli)
	{
		$mysqli->close();
	}

	function do_query($query,$mysqli)
	{
		if(($result=$mysqli->query($query))===false)
			my_die("$query<br>".$mysqli->error);
		return $result;
	}

	function result_to_array($result,$useid=true)
	{
		$out=array();
		while($row=$result->fetch_assoc())
		{
			if(isset($row["id"])&&$useid)
			{
				$id=$row["id"];
				unset($row["id"]);
				$out[$id]=$row;
			}
			else
				$out[]=$row;
		}
		$result->free_result();
		return $out;
	}


?>
