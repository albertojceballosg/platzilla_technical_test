<?php

if (strstr(getcwd(),"reportmanager")) chdir('../../');
require_once('include/utils/utils.php');
global $plat;

define('DEFAULT_MODULE_FOLDER', 'modules/reportmanager/');

if (isset($_REQUEST['page']) and ($_REQUEST['page'])) $PAGEACTUAL = (int)$_REQUEST['page']; else $PAGEACTUAL = 1;

if (isset($_REQUEST['idedit']) and ($_REQUEST['idedit'])) $idedit = (int)$_REQUEST['idedit']; else $idedit=0;

if (isset($_REQUEST['name']) and ($_REQUEST['name'])) $post_name = $_REQUEST['name']; else $post_name = "";
if (isset($_REQUEST['code']) and ($_REQUEST['code'])) $post_code = $_REQUEST['code']; else $post_code = "";
if (isset($_REQUEST['active1']) and ($_REQUEST['active1'])) $post_inventory = $_REQUEST['active1']; else $post_inventory = 0;


if (isset($_SESSION["authenticated_user_id"])) $userid = (int)$_SESSION['authenticated_user_id']; else exit();

if ($idedit > 0) {
		$sql = "UPDATE vtiger_report_template  
					SET has_inventory=?, code=?, name=?
					WHERE (id=?)";
		$result=$adb->pquery($sql, array($post_inventory, $post_code,$post_name,$idedit)); 
} else {
		$sql = "insert into vtiger_report_template(has_inventory,code,name) VALUES (?,?,?)";
		$adb->pquery($sql, array($post_inventory,$post_code,$post_name));
}

header("Location: index.php?action=listTemplate&module=reportmanager&parenttab=Settings&page=".$PAGEACTUAL);
?>

