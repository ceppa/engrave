<?
require_once('mysql.php');
require_once('const.php');
require_once('../config.php');

function showList()
{
	global $livelli;
	$conn=opendb();

	if($_SESSION["livello"]==3)
		$query="SELECT * FROM utenti WHERE eliminato=0 ORDER BY cognome";
	else
		$query="SELECT * FROM utenti
			WHERE eliminato=0
			AND attivo=1
			AND livello<3
		ORDER BY cognome";
	$result=do_query($query,$conn);
	$rows=result_to_array($result,true);
	closedb($conn);
	?>

	<table class="plot">
		<tr class="footer">
			<td colspan="11" style="cursor:pointer" onclick="users_add()">
					<img src="img/b_add.png" alt="Nuovo" 
						style="vertical-align:middle" title="Nuovo" />
					&nbsp;Nuovo utente
			</td>
		</tr>
		<tr class="header" >
			<td colspan="2">&nbsp;</td>
			<td>login</td>
			<td>nome</td>
			<td>cognome</td>
			<td>livello</td>
			<td>expired</td>
			<td>attivo</td>
		</tr>
	<?
	foreach($rows as $id=>$row)
	{
		$reset_message=str_replace("'","\'","Resetto la password di ".$row["nome"]." ".$row["cognome"]."?");
		$del_message=str_replace("'","\'","Elimino utente ".$row["login"]."?");
		$reset_link="user_reset($id,'$reset_message')";
		$del_link="user_del($id,'$del_message')";
		$edit_link="user_edit('$id')";

		$row_class=(($row["attivo"]==1)?"row_attivo":"row_inattivo");
		?>
		<tr class="<?=$row_class?>" onmouseover="$(this).removeClass('<?=$row_class?>').addClass('high')"
				onmouseout="$(this).removeClass('high').addClass('<?=$row_class?>')">
			<td>
				<img src="img/b_drop.png" alt="Elimina" title="Elimina"
					onclick="<?=$del_link?>" />
			</td>
			<td>
				<img src="img/b_reset.png" alt="Resetta password"
					title="Resetta password" onclick="<?=$reset_link?>" />
			</td>
			<td onclick="<?=$edit_link?>"><?=$row["login"]?></td>
			<td onclick="<?=$edit_link?>"><?=$row["nome"]?></td>
			<td onclick="<?=$edit_link?>"><?=$row["cognome"]?></td>
			<td onclick="<?=$edit_link?>">
				<?=$livelli[$row["livello"]]?>
			</td>
			<td onclick="<?=$edit_link?>">
				<?=($row["expired"]==1?"si":"no")?>
			</td>
			<td onclick="<?=$edit_link?>">
				<?=($row["attivo"]==1?"si":"no")?>
			</td>
		</tr>
		<?
	}?>
		<tr class="footer">
			<td colspan="11" style="cursor:pointer" onclick="users_add()">
					<img src="img/b_add.png" alt="Nuovo" 
						style="vertical-align:middle" title="Nuovo" />
					&nbsp;Nuovo utente
			</td>
		</tr>
	</table>

<?
}

$op = $_REQUEST['op'];
if(($op=="checkduplicate")&&(isset($_REQUEST["user"])))
{
	$user=$_REQUEST["user"];
	$id=$_REQUEST["id"];
	$conn=opendb();
	$query="SELECT id FROM utenti
			WHERE id!='$id' AND login='$user'";
	$result=do_query($query,$conn);
	$rows=result_to_array($result,true);
	closedb($conn);
	
	echo count($rows);
	die();
}
elseif(($op=="edit")||($op=="add"))
{
	if($op=="edit")
	{
		$conn=opendb();
		$user_to_edit=$_REQUEST["user_to_edit"];
		$query="SELECT utenti.* FROM utenti WHERE utenti.id=".$user_to_edit;
		$result=do_query($query,$conn);
		$rows=result_to_array($result,true);
		$valori=$rows[$user_to_edit];
		$valori["id"]=$user_to_edit;
		closedb($conn);
	}
	if(!isset($valori["livello"]))
		$valori["livello"]=0;

	$conn=opendb();

	$utenti="";
	$query="SELECT login FROM utenti
			WHERE utenti.id<>'$user_to_edit'";
	$result=do_query($query,$conn);
	while($row=$result->fetch_assoc())
		$utenti.='"'.$row["login"].'":"1",';
	$utenti=rtrim($utenti,",");
	$result->free();

	closedb($conn);

	?>
	<form id="edit_form"
			onsubmit="return check_post()">

	<div class="centra">
			<?
			if($op=="edit")
			{?>
		<input type="hidden" value="<?=$valori["id"]?>" name="user_to_edit" />
		<input type="hidden" value="edit_post" name="op" />
			<?}
			else
			{?>
		<input type="hidden" value="add_post" name="op" />
			<?}?>
		<table class="plot">
			<tr>
				<td class="right">login</td>
				<td class="left">
					<input type="text" id="to_focus" name="utente" size="15" value="<?=$valori["login"]?>" />
				</td>
			</tr>
			<tr>
				<td class="right">nome</td>
				<td class="left">
					<input type="text" name="nome" size="15" value="<?=$valori["nome"]?>" />
				</td>
			</tr>
			<tr>
				<td class="right">cognome</td>
				<td class="left">
					<input type="text" name="cognome" size="15" value="<?=$valori["cognome"]?>" />
				</td>
			</tr>
			<tr>
				<td class="right">email</td>
				<td class="left">
					<input type="text" id="email" name="email" size="30" value="<?=$valori["email"]?>" />
				</td>
			</tr>
			<tr>
				<td class="right">livello</td>
				<td class="left">
					<select class="input" name="livello">
					<?
					foreach($livelli as $liv_id=>$liv_text)
					{
						if($liv_id<=$_SESSION["livello"])
						{?>
						<option value="<?=$liv_id?>"<?=($liv_id==$valori["livello"]?" selected='selected'":"")?>>
							<?=$liv_text?>
						</option>
						<?}
					}?>
					</select>
				</td>
			</tr>
			<?
				if($op=="edit")
				{?>
			<tr>
				<td class="right">expired</td>
				<td class="left">
					<input type="checkbox" class="check" name="expired"<?=(($valori["expired"]==1)?" checked='checked'":"")?> />
				</td>
			</tr>
			<tr>
				<td class="right">attivo</td>
				<td class="left">
					<input type="checkbox" class="check" name="attivo"<?=(($valori["attivo"]==1)?" checked='checked'":"")?> />
				</td>
			</tr>
				<?}?>
			<tr class="row_attivo">
				<td colspan="2" style="text-align:center">
					<input type="button" class="button" 
						onclick="users_post('#edit_form')"
						value="accetta" />&nbsp;
					<input type="button" class="button" onclick="users_list()" value="annulla" />
				</td>
			</tr>
		</table>
	</div>
	</form>
	<?
}
elseif($op=="list")
{
	showlist();
}
elseif(($op=="del")&&($_SESSION["livello"]>0))
{
	$user_to_del=$_REQUEST["user_to_del"];
	$conn=opendb();
	$query="UPDATE utenti SET eliminato=1,attivo=0 WHERE id=\"".$user_to_del."\"";
	$result=do_query($query,$conn);
	closedb($conn);
	echo "0Utente eliminato|";
	showList();
}
elseif(($op=="reset")&&($_SESSION["livello"]>0))
{
	$siteAddress="http://".$_SERVER["HTTP_HOST"];
	$user_to_reset=$_REQUEST["user_to_reset"];
	require_once("pwgenerator.php");
	$pass=randomPass();
	$conn=opendb();
	$query="UPDATE utenti SET pass=md5('$pass'),expired=1 WHERE id='$user_to_reset'";
	$result=do_query($query,$conn);

	$query="SELECT * FROM utenti WHERE id='$user_to_reset'";
	$result=do_query($query,$conn);
	$login=result_to_array($result,false);
	$row=$login[0];
	closedb($conn);

	require_once("mail.php");
	$from = "System Administrator <noreply@hightecservice.biz>";
	$to = $row["nome"]." ".$row["cognome"]." <".$row["email"].">";
	$subject = "nuova password";

	$mailtext=file_get_contents("../template/mailTemplateNewPass.html");
	$mailtext=str_replace("{username}",$row["login"],$mailtext);
	$mailtext=str_replace("{password}",$pass,$mailtext);
	$mailtext=str_replace("{name}",$row["nome"],$mailtext);
	$mailtext=str_replace("{surname}",$row["cognome"],$mailtext);
	$mailtext=str_replace("{siteAddress}",$siteAddress,$mailtext);
	emailHtml($from, $subject, $mailtext, $to);

	echo "0Password resettata|";
	showList();
}
elseif(($op=="edit_post")&&($_SESSION["livello"]>0))
{
	$expired=(isset($_POST["expired"])?1:0);
	$attivo=(isset($_POST["attivo"])?1:0);
	$conn=opendb();
	$query="UPDATE utenti SET login=\"".$_POST["utente"]."\",
				nome=\"".$_POST["nome"]."\", 
				cognome=\"".$_POST["cognome"]."\",
				email=\"".$_POST["email"]."\", 
				livello=".$_POST["livello"].", 
				expired=$expired,
				attivo=$attivo 
			WHERE id=\"".$_POST["user_to_edit"]."\"";
	do_query($query,$conn);
	closedb($conn);
	echo "0Modifica effettuata|";
	showList();
}
elseif(($op=="add_post")&&($_SESSION["livello"]>0))
{
	require_once("pwgenerator.php");
	$pass=randomPass();
	$conn=opendb();
	$query="INSERT INTO utenti(login,pass,nome,cognome,email,
				livello,expired)
			VALUES(\"".$_POST["utente"]."\", md5(\"$pass\"),
				\"".$_POST["nome"]."\", 
				\"".$_POST["cognome"]."\",
				\"".$_POST["email"]."\",
				".$_POST["livello"].", 1)";
	do_query($query,$conn);
	closedb($conn);

	require_once("mail.php");

	$from = "System Administrator <noreply@hightecservice.biz>";
	$to = $_POST["nome"]." ".$_POST["cognome"]." <".$_POST["email"].">";
	$subject = "registratione utente";

	$mailtext=file_get_contents("../template/mailTemplateNewUser.html");
	$mailtext=str_replace("{username}",$_POST["utente"],$mailtext);
	$mailtext=str_replace("{password}",$pass,$mailtext);
	$mailtext=str_replace("{name}",$_POST["nome"],$mailtext);
	$mailtext=str_replace("{surname}",$_POST["cognome"],$mailtext);
	$mailtext=str_replace("{siteAddress}",$siteAddress,$mailtext);
	emailHtml($from, $subject, $mailtext, $to);

	echo "0Utente inserito|";
	showList();
}

?>

