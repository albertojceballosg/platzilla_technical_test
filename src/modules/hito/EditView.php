<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

if (isset($_REQUEST['return_id']) && isset($_REQUEST['return_module']) && $_REQUEST['return_module'] == 'proyectos' )
	$proyectosid = $_REQUEST['return_id'];

if ( (isset($_REQUEST['proyectosid']) && $_REQUEST['proyectosid'] && !$_REQUEST['record'])  ) {

	$proyectosid = $_REQUEST['proyectosid'];
}

if ($proyectosid){
	$proyecto = CRMEntity::getInstance('proyectos');
	$proyecto->retrieve_entity_info($proyectosid, 'proyectos');
	$proyectoName = htmlspecialchars(html_entity_decode($proyecto->column_fields['name'], ENT_QUOTES, 'UTF-8'), ENT_NOQUOTES, 'UTF-8');
}


require_once 'modules/Vtiger/EditView.php';


if($focus->mode == 'edit') {
	$smarty->display('salesEditView.tpl');
} else {
	$smarty->display('CreateView.tpl');
}

?>




<script type="text/javascript">


	jQuery(function() {

		

		<?php if ($proyectosid){ ?>

		jQuery("#proyectosid_display").val("<?php echo $proyectoName; ?>");
		jQuery("#proyectosid").val("<?php echo $proyectosid; ?>");

		<?php  } ?>
		

		<?php  if($focus->mode != 'edit') {  ?>
		jQuery('textarea[name="description"]').val("");
		jQuery('input[name="name"]').val("");
		jQuery("#jscal_field_inidate").val("");
		jQuery("#jscal_field_enddate").val("");
		<?php  }  ?>
	});

</script>