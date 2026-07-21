<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

if (isset($_REQUEST['return_id']) && isset($_REQUEST['return_module']) && $_REQUEST['return_module'] != ''){
	$proyectosid = $_REQUEST['return_id'];
	$module = $_REQUEST['return_module'];
}

if ($proyectosid){
	$proyecto = CRMEntity::getInstance($module);
	$proyecto->retrieve_entity_info($proyectosid, $module);
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
		jQuery("#<?php echo $module; ?>id_display").val("<?php echo $proyectoName; ?>");
		jQuery("#<?php echo $module; ?>id").val("<?php echo $proyectosid; ?>");

		var nameid = '';
		
		jQuery("#tblDatosbásicos .row div").each(function (index){ 
			nameid = jQuery(this).attr('id');

        	if(typeof nameid !== 'undefined' && nameid.indexOf('td_plan_')!= -1 && nameid !== 'td_<?php echo $module; ?>id'){
        		jQuery('#'+nameid).hide();
        	}
		 });

		
		<?php  } ?>
		

		<?php  if($focus->mode != 'edit') {  ?>
		jQuery('textarea[name="description"]').val("");
		jQuery('input[name="name"]').val("");
		jQuery("#jscal_field_inidate").val("");
		jQuery("#jscal_field_enddate").val("");
		<?php  }  ?>

		
	});

</script>