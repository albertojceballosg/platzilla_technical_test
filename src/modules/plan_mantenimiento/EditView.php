<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

require_once 'modules/Vtiger/EditView.php';
global $currentModule;

$hitosCerrados = $focus->estanHitosCerrados($currentModule);

$related_array = getRelatedLists($currentModule, $focus);
	$smarty->assign('RELATEDLISTS', $related_array);
	$smarty->assign('MOSTRAR_DMAIC', 1);
		
	require_once('include/ListView/RelatedListViewSession.php');
	if(!empty($_REQUEST['selected_header']) && !empty($_REQUEST['relation_id'])) {
		$relationId = vtlib_purify($_REQUEST['relation_id']);
		RelatedListViewSession::addRelatedModuleToSession($relationId,
				vtlib_purify($_REQUEST['selected_header']));
	}
	$open_related_modules = RelatedListViewSession::getRelatedModulesFromSession();
	$smarty->assign("SELECTEDHEADERS", $open_related_modules);
	
	if (isset($_REQUEST['platdb']) && !empty($_REQUEST['platdb']))
		$smarty->assign("PLATDB", vtlib_purify($_REQUEST['platdb']));
	

if($focus->mode == 'edit') {
	$smarty->display('salesEditView.tpl');
} else {
	$smarty->display('CreateView.tpl');
}




?>

<?php  if($focus->mode == 'edit') {  ?>

<script language="javascript">

	var hitosCerrados = "<?php echo $hitosCerrados; ?>";
	jQuery('#finalizado').click(function() {
	    if (jQuery(this).is(':checked') && hitosCerrados != '1') {
	    	alert("<?php echo $mod_strings['LBL_NO_PUEDE_CERRAR']; ?>");
	        return false;
	    }
	});

</script>

<?php  }  ?>
