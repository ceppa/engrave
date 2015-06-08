<?
require_once("const.php");
require_once("util.php");
require_once("../config.php");

$op=$_REQUEST["op"];

if($op=='list')
{
?>
	<div id="flexi"></div>
<?
	die();
}
elseif($op=="show")
{
	die();
}
else
{
	die();
}
?>

<?
        while ($row = mysql_fetch_assoc($results)) {
         $data['rows'][] = array(
                                  'id' => $row['pf_id'],
                                  'cell' => array(
                                               $row['cat_code'], 
                                               $row['cat_title'], 
                                               $row['cat_link'] = "<a href=\"catagory_edit.php?cat_id=".$row['cat_id']."\">Edit</a> | <a href=\"catagory_to_family_association.php?cat_id=".$row['cat_id']."\">Associate Familys</a> | <a href=\"category_child_order.php?cat_id=".$row['cat_id']."\">Order Children</a>")); }
echo json_encode($data);
?>
