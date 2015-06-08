<?
function showForm($id,$table,$fields)
{
	require_once("mysql.php");
?>
<form id="editform">
<table class="form">
	<tr>
		<td class="right">
			<input type="button" class="submit" name="submit" value="submit" id="submit" />
		</td>
		<td class="left">
			<input type="button" class="cancel" name="cancel" value="cancel" id="cancel" />
		</td>
		<td>
		</td>
	</tr>
<?
	$conn=opendb();
/*	foreach($fields as $name=>$details)
		$fields[$name]["value"]="";*/
	$idtext="";

	if(strlen($id))
	{
		$idtext=sprintf("<input type='hidden' name='id_edit' value='%s' />",$id);
		$field_string="";
		foreach($fields as $name=>$foo)
			$field_string.=($name.",");

		$field_string=rtrim($field_string,",");

		list($id_field,$id_value)=explode(",",$id);
		$query="SELECT $field_string FROM $table WHERE $id_field='$id_value'";
		$result=do_query($query,$conn);
		$rows=result_to_array($result,false);
		if(count($rows==1))
		{
			foreach($fields as $name=>$details)
				$fields[$name]["value"]=$rows[0][$name];
		}
	}

	$query="SHOW FIELDS FROM $table";
	$result=do_query($query,$conn);
	$rows=result_to_array($result,true);
	$field_types=array();
	foreach($rows as $row)
		$field_types[$row["Field"]]=$row["Type"];
	$fields_properties=array();
	unset($rows);
	$detailstext="";
	$hiddenfields="";

	foreach($fields as $field_name=>$field_details)
	{
		$innertext="";
		if(isset($field_details["type"]))
		{
			if($field_details["type"]=="hidden")
				$hiddenfields.=sprintf("<input type='hidden' 
					name='%s' value='%s'>",$field_name,$field_details["value"]);
			else
			{
				$field_label=$field_details["label"];
				$innertext=sprintf("<input type='%s' name='%s' 
							id='%s' value='%s' />",$filed_details["type"],
							$field_name,$field_name,$field_value);
			}
		}
		elseif(isset($field_types[$field_name]))
		{
			$comboitems=array();
			$type=strtolower($field_types[$field_name]);
			if(($pos=strpos($type,"("))!==false)
			{
				$type_def=substr($type,0,$pos);
				$endpos=strpos(substr($type,$pos+1),")");
				$type_size=substr($type,$pos+1,$endpos);
			}
			else
			{
				$type_def=$type;
				$type_size=0;
			}
			switch($type_def)
			{
				case "tinyint":
				case "smallint":
				case "mediumint":
				case "int":
				case "bigint":
					if(is_array($field_details["link"]))
					{
						$linked_table=$field_details["link"]["table"];
						$linked_id=$field_details["link"]["id"];
						$linked_text=$field_details["link"]["text"];
						if(strpos($linked_text,"CONCAT")===false)
							$linked_text="`$linked_text`";
						$query="SELECT `$linked_id` AS id,
							$linked_text AS caption FROM `$linked_table`";
						$result=do_query($query,$conn);
						$comboitems=result_to_array($result,1);
						$comboitems[0]='--';
						asort($comboitems);
						$type_def="combo";
					}
					elseif(is_array($field_details["details"]))
					{
						$details_table=$field_details["details"]["table"];
						$details_id=$field_details["details"]["id"];
						$details_fields=$field_details["details"]["fields"];
						$query_fields="";
						$field_labels=array();

						$details_fields_list="";
						foreach($details_fields as $details_field)
						{
							$df=$details_field["field"];
							if(substr($df,-9)=="_optional")
								$df=substr($df,0,strlen($df)-9);

							$query_fields.="`$df`,";
							$details_fields_list.=$details_field["field"]."|";
						}
						$details_fields_list=rtrim($details_fields_list,"|");

						$query_fields=rtrim($query_fields,",");
						$query="SELECT $query_fields FROM `$details_table` 
							WHERE `$details_id`='$id_value'";

						$result=do_query($query,$conn);
						$detailitems=result_to_array($result,0);
						$type_def="details";
					}
					elseif(($type_def=="tinyint")&&($type_size==1))
						$type_def="bool";
					else
						$type_def="integer";
					break;
				case "char":
				case "varchar":
					$type_def="text";
					break;
				case "tinytext":
				case "text":
				case "mediumtext":
				case "longtext":
					$type_def="textarea";
					break;
				case "date":
				case "datetime":
					$type_def="date";
					break;
				default:
					$type_def="text";
					$type_size=20;
					break;
			}
			$field_value=htmlspecialchars($field_details["value"],ENT_QUOTES);
			$field_label=$field_details["label"];
			$field_default=$row["Default"];
			$field_class=sprintf("%s_class",$type_def);
			$input_size=($type_size>50?50:$type_size);
			switch($type_def)
			{
				case "text":
				case "date":
				case "integer":
					$innertext=sprintf("<input type='text' name='%s' id='%s' 
						class='%s' value='%s' size='%s' maxlength='%s'/>",
						$field_name,$field_name,$field_class,
						$field_value,$input_size,$type_size);
					break;
				case "textarea":
					$innertext=sprintf("<textarea rows='4' cols='50' name='%s' 
						id='%s' class='%s'>%s</textarea>",
						$field_name,$field_name,$field_class,$field_value);
					break;
				case "bool":
					$innertext=sprintf("<input type='checkbox' name='%s' 
						id='%s' class='%s' %s />"
						,$field_name,$field_name,$field_class,
						($field_value>0?"checked='checked'":""));
					break;
				case "combo":
					$innertext=sprintf("<select name='%s' id='%s' class='%s'>"
						,$field_name,$field_name,$field_class);
					foreach($comboitems as $i=>$t)
					{
						$caption=(isset($t["caption"])?$t["caption"]:"");	
						$innertext.=sprintf("\n<option value='%s'%s>%s</option>"
							,$i,($i==$field_value?' selected':''),$caption);
					}
					$innertext.="\n</select>";
					break;
				case "details":
					$innertext="";
					$detailstext=sprintf("<input type='hidden' 
						name='det_table' value='%s'>",$details_table);
					$detailstext.=sprintf("<input type='hidden' 
						name='det_id' value='%s'>",$details_id);
					$detailstext.=sprintf("<input type='hidden' 
						name='det_fields_list' value='%s'>"
							,$details_fields_list);

//					print_r($details_fields);
//					echo "<br>".count($details_fields);
					$detailstext.="<table>
							<tr>
								<td colspan='2'>
									&nbsp;
								</td>
							</tr>
								";
					foreach($details_fields as $k=>$details_field)
					{
						if(isset($details_field["link"]))
						{
							$linked_table=$details_field["link"]["table"];
							$linked_id=$details_field["link"]["id"];
							$linked_text=$details_field["link"]["text"];
							if(strpos($linked_text,"CONCAT")===false)
								$linked_text="`$linked_text`";

							$query="SELECT `$linked_id` AS id,
								$linked_text AS caption FROM `$linked_table`";
							$result=do_query($query,$conn);
							$items=result_to_array($result,1);
							$details_fields[$k]["items"]=$items;
						}
						$detailstext.="
								<td>
									".$details_field["label"]."
								</td>";
					
					}
					$detailstext.="	  </tr>";


					$query="SHOW FIELDS FROM $details_table";
					$result=do_query($query,$conn);
					$rows=result_to_array($result,true);
					$detail_field_types=array();
					foreach($rows as $row)
						$detail_field_types[$row["Field"]]=$row["Type"];

					for($i=0;$i<25;$i++)
					{
						$detailstext.="<tr>";

						foreach($details_fields as $details_field)
						{
							$field_name=$details_field["field"];
							$field_name_cleared=$field_name;
							if(substr($field_name,-9)=="_optional")
								$field_name_cleared=substr($field_name,0,strlen($field_name)-9);


							$field_length=$details_field["length"];
							if(isset($details_field["value"]))
								$field_value=$details_field["value"];
							else
								$field_value="";
							if(is_array($detailitems[$i]))
								$field_value=$detailitems[$i][$field_name_cleared];

							$detailstext.="<td>\n";
							if(is_array($details_field["items"]))
							{
								$id_name=sprintf("det_%02d_hidden%s",$i,$field_name);
								$detailstext.="<input type='hidden' 
										name='$id_name' id='$id_name' 
										value='$field_value' />";
								$field_value=$details_field["items"][$field_value]["caption"];
							}

							if($detail_field_types[$field_name_cleared]!="text")
								$detailstext.="<input type='text' name='"
										.sprintf("det_%02d_%s",$i,$field_name)."' 
										id='"
										.sprintf("det_%02d_%s",$i,$field_name)."' 
										size='".$field_length."'  
										value='".str_replace("'","&#39;",$field_value)."' />";
							else
								$detailstext.="<textarea class='formdetails' name='"
										.sprintf("det_%02d_%s",$i,$field_name)."' 
										id='"
										.sprintf("det_%02d_%s",$i,$field_name)."'
										>".$field_value."</textarea>";



							$detailstext.="</td>\n";
						}
						$detailstext.="</tr>";
					}
					$detailstext.="</table>\n";
					break;
			}
		}
		if(strlen($innertext))
		{
?>
	<tr>
		<td class="right"><?=$field_details["label"]?></td>
		<td class="left"><?=$innertext?></td>
		<td>
			<span id="<?=sprintf("%s_exists",$field_name)?>" class="exists">
				esiste
			</span>
		</td>
	</tr>
<?
		}
	}
?>	
</table>
<?=$detailstext?>
<?=$idtext?>
<?=$hiddenfields?>
</form>
<?

	closedb($conn);
}

require_once('mysql.php');
require_once('const.php');

$op=$_REQUEST["op"];

if($op=='form_posted')
{
/*	$logfile = fopen("log.log", "a+"); 
	fwrite($logfile,print_r($_POST,true)."\r\n\r\n");
	fclose($logfile);
*/
	$conn=opendb();
	unset($_POST["op"]);
	$table=$_POST["form_table"];
	unset($_POST["form_table"]);
	$edit=(isset($_POST["id_edit"]));
	$det_table=(isset($_POST["det_table"])?$_POST["det_table"]:"");
	$det_id=(isset($_POST["det_id"])?$_POST["det_id"]:"");
	unset($_POST["det_table"]);
	unset($_POST["det_id"]);

	if($edit)
	{
		list($id_field,$id_value)=explode(",",$_POST["id_edit"]);
		unset($_POST["id_edit"]);

		if(strlen($det_table)&&strlen($det_id))
		{
			$query=sprintf("DELETE FROM `%s` WHERE `%s`='%s'",
				$det_table,$det_id,$id_value);

			do_query($query,$conn);
		}
		$field_string="";

		foreach($_POST as $k=>$v)
			if(substr($k,0,4)!="det_")
			{
				if($v=='true')
					$v=1;
				elseif($v=='false')
					$v=0;
				$field_string.=sprintf("%s='%s',",$k,str_replace("'","\'",$v));

			}
		$field_string=rtrim($field_string,",");
		$where=sprintf("WHERE %s='%s'",$id_field,$id_value);
		$query=sprintf("UPDATE %s SET %s %s",$table,$field_string,$where);
	}
	else
	{
		$field_string="";
		$field_value="";
		foreach($_POST as $k=>$v)
			if(substr($k,0,4)!="det_")
			{
				if($v=='true')
					$v=1;
				elseif($v=='false')
					$v=0;
				$v=str_replace("'","\'",$v);
				$field_string.="$k,";
				$field_value.="'$v',";
			}
		$query=sprintf("INSERT INTO %s(%s) VALUES(%s)",
			$table,rtrim($field_string,","),rtrim($field_value,","));
	}
//	$fp = fopen('data.txt', 'w');
//	fwrite($fp, $query);
//	fclose($fp);
	do_query($query,$conn);
	if(!$edit)
		$id_value=$conn->insert_id;

	if(isset($_POST["det_fields_list"]))
	{
		$details_fields_list=explode("|",$_POST["det_fields_list"]);

		$query="SHOW FIELDS FROM $det_table";
		$result=do_query($query,$conn);
		$rows=result_to_array($result,true);
		$det_field_types=array();
		foreach($rows as $row)
			$det_field_types[$row["Field"]]=$row["Type"];


		for($i=0;$i<25;$i++)
		{
			$field_present=1;
			$field_names="`$det_id`";
			$field_values="'$id_value'";
			foreach($details_fields_list as $details_field)
			{
				$field_name=sprintf("det_%02d_%s",$i,$details_field);
				$field_name_hidden=sprintf("det_%02d_hidden%s",$i,$details_field);

				$field_value=(isset($_POST[$field_name_hidden])?
					trim($_POST[$field_name_hidden]):trim($_POST[$field_name]));

				if(substr($details_field,-9)=="_optional")
					$details_field=substr($details_field,0,strlen($details_field)-9);
				else
				{
					if(strlen(trim($_POST[$field_name]))==0)
						$field_present=0;
				}
				if(stristr($det_field_types[$details_field],"decimal"))
					$field_value=str_replace(",",".",$field_value);
				$field_names.=",`$details_field`";
				$field_values.=",'".str_replace("'","\'",$field_value)."'";

			}



			if($field_present)
			{
				$query=sprintf("INSERT INTO %s(%s) values(%s)",
					$det_table,$field_names,$field_values);

				do_query($query,$conn);
			}
		}

	}
	closedb($conn);
}

?>
