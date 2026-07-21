<?php
session_start();
if (strstr(getcwd(),"reportmanager")) chdir('../../');
require_once('include/utils/utils.php');
require_once('modules/reportmanager/reportmanager.php');

global $plat;

define('DEFAULT_MODULE_FOLDER', 'modules/reportmanager/');

if (isset($_REQUEST['page']) and ($_REQUEST['page'])) $PAGEACTUAL = (int)$_REQUEST['page']; else $PAGEACTUAL = 1;


if (isset($_REQUEST['idedit']) and ($_REQUEST['idedit'])) $idedit = (int)$_REQUEST['idedit']; else $idedit = 0;
if (isset($_REQUEST['idduplicate']) and ($_REQUEST['idduplicate'])) $idduplicate = (int)$_REQUEST['idduplicate']; else $idduplicate = 0;


$template = array();
$template = getTemplate($idedit, $idduplicate);


if ($idduplicate) $template['subject']=$template['name'].' (copy)';

$TITULO = getTranslatedString('LBL_PLAT_REPORTMANAGER_TEMPLATE_TITLE_CREATE');
if ($idduplicate) $TITULO = getTranslatedString('LBL_PLAT_REPORTMANAGER_TEMPLATE_TITLE_DUPLICATE');
if ($idedit) $TITULO = getTranslatedString('LBL_PLAT_REPORTMANAGER_TEMPLATE_TITLE_EDIT');

?>

<script type="text/javascript">


jQuery( document ).ready(function () {
	jQuery('#code').keyup(function(){
		validField('code');
	});
});

function validField(id) {
	jQuery('#'+id).val(jQuery('#'+id).val().toLowerCase());
	jQuery('#'+id).val(jQuery('#'+id).val().replace(' ', '_'));
	str = jQuery('#'+id).val();
	
	// remove accents, swap ñ for n, etc
	var from = "àáäâèéëêìíïîòóöôùúüûñç·/-,:;";
	var to   = "aaaaeeeeiiiioooouuuunc______";
	for (var i=0, l=from.length ; i<l ; i++) {
		str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
	}

	str = str.replace(/[^a-z0-9 _]/g, '') // remove invalid chars
	.replace(/\s+/g, '_') // collapse whitespace and replace by -
	.replace(/-+/g, '_'); // collapse dashes
	
	jQuery('#'+id).val(str);
}

function ValidaForm(){
	if (jQuery('#name').val()==''){
		alert(alert_arr.VALID_FIELD_DEPEND+' '+jQuery('#namelabel b').html());
		jQuery('#name').focus();
		return false;
	}

	if (jQuery('#code').val()==''){
		alert(alert_arr.VALID_FIELD_DEPEND+' '+jQuery('#codelabel b').html());
		jQuery('#code').focus();
		return false;
	}

	if(jQuery('#inventoy').is(':checked')){
		jQuery('#active1').val('1');
	}else{
		jQuery('#active1').val('0');
	}

	
	return true;
}
</script>


<div class="row">
	<div class="col-lg-12">
		<h1><?php echo getTranslatedString('ModuleName');?>: <?php echo $TITULO;?></h1>
	</div>
</div>

<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<form name="reportmanagerEditView" action="index.php" method="POST" onsubmit='return ValidaForm(this);'>
			<input type="hidden" name="module" value="reportmanager" />
			<input type="hidden" name="action" value="SaveTemplate" />
			<input type="hidden" name="parenttab" value="Settings" />
			<input type="hidden" name="page" value="<?php echo $PAGEACTUAL;?>" />
			<input type="hidden" name="idedit" value="<?php echo $idedit;?>" />
			<input type="hidden" name="active1" id="active1" value="<?php echo $template['has_inventory'];?>" />

			<header class="main-box-header clearfix">
				<div class="filter-block pull-right">
					<div class="filter-block pull-right">
						<input title="Save [Alt+S]" accessKey="S" class="btn btn-info"  type="submit" name="submit" value="<?php echo getTranslatedString('LBL_BUTTON_SUBMIT');?>" >
						<input title="Cancel [Alt+X]" accessKey="X" class="btn btn-warning" onclick="window.history.back()" type="button" name="button" value="<?php echo getTranslatedString('LBL_BUTTON_CANCEL');?>">
					</div>
				</div>
			</header>
			
			<div class="main-box-body clearfix">
				<div class="table-responsive">
					<table class="table">
						<tr>
							<td align="right" class="lvtCol" width="35%" id="namelabel"><b><?php echo getTranslatedString('LBL_PLAT_REPORTMANAGER_TEMPLATE_NAME');?></b>:</td>
							<td align="left" bgcolor="white" width="65%">
								<input type="text" name="name"  id="name" value="<?php echo $template['name'] ;?>" class="form-control" /></td>
							</td>
						</tr>
						<tr>
							<td align="right" class="lvtCol" width="35%" id="codelabel"><b><?php echo getTranslatedString('LBL_PLAT_REPORTMANAGER_TEMPLATE_CODE');?></b>:</td>
							<td align="left" bgcolor="white" width="65%">
								<input type="text" name="code"  id="code"  value="<?php echo $template['code'] ;?>" class="form-control" /></td>
							</td>
						</tr>						
						<tr>
							<td align="right" class="lvtCol" width="35%"><b><?php echo getTranslatedString('LBL_PLAT_REPORTMANAGER_INVENTORY_ITEMS');?></b>:</td>   
							<td align="left" bgcolor="white" width="65%">
								<div class="checkbox-nice">
									<input id="inventoy" name="inventoy" <?php if((isset($template['has_inventory']) && $template['has_inventory'] == '1') ||
											$idedit == 0){ echo 'checked="checked"'; } ?> type="checkbox">
									<label for="inventoy"></label>
								</div>
							</td>
						</tr>
					</table>
				</div>
			</div>
			</form>
		</div>
	</div>
</div>


