<?php
require_once 'modules/Vtiger/EditView.php';

global $focus, $smarty;

$upload_maxsize1 = 100000000;
$smarty->assign('UPLOAD_MAXSIZE',$upload_maxsize1);

if($focus->mode == 'edit') {
	$smarty->display('salesEditView.tpl');
} else {
	$smarty->display('CreateView.tpl');
}

?>
