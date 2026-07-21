<?php
session_start();
if (strstr(getcwd(),"reportmanager")) chdir('../../');
require_once('include/utils/utils.php');
require_once('modules/reportmanager/reportmanager.php');

global $plat,$adb, $current_user;

define('DEFAULT_MODULE_FOLDER', 'modules/reportmanager/');

if (isset($_REQUEST['page']) and ($_REQUEST['page'])) $PAGEACTUAL = (int)$_REQUEST['page']; else $PAGEACTUAL = 1;


if (isset($_REQUEST['idedit']) and ($_REQUEST['idedit'])) $idedit = (int)$_REQUEST['idedit']; else $idedit = 0;
if (isset($_REQUEST['idduplicate']) and ($_REQUEST['idduplicate'])) $idduplicate = (int)$_REQUEST['idduplicate']; else $idduplicate = 0;


$template=array();
$template=getReport($idedit, $idduplicate);

$eventos = array();
$eventos = getTemplateAll();

$tabs = array();
$tabs = getUserTabsPermissions($current_user->id);


if ($idduplicate) $template['subject']=$template['name'].' (copy)';

$TITULO = getTranslatedString('LBL_PLAT_REPORTMANAGER_TITLE_CREATE');
if ($idduplicate) $TITULO = getTranslatedString('LBL_PLAT_REPORTMANAGER_TITLE_DUPLICATE');
if ($idedit) $TITULO = getTranslatedString('LBL_PLAT_REPORTMANAGER_TITLE_EDIT');

?>

<script type="text/javascript">

	function ValidaForm(oform){

		if (jQuery('#code').val()==0){
			alert(alert_arr.VALID_FIELD_DEPEND+' '+jQuery('#codelabel b').html());
			jQuery('#code').focus();
			return false;
		}

		if (jQuery('#module1').val()==0){
			alert(alert_arr.VALID_FIELD_DEPEND+' '+jQuery('#modulelabel b').html());
			jQuery('#module1').focus();
			return false;
		}

		if(jQuery('#active').is(':checked')){
			jQuery('#active1').val('1');
		}else{
			jQuery('#active1').val('0');
		}

		if(jQuery('#active').is(':checked') && jQuery('#module1').val()!=0){

			var module = jQuery('#module1').val();
			var label = jQuery('#module1 option:selected').text();

			var urlstring ="module=reportmanager&action=reportmanagerAjax&file=reportValidate&tabid="+module;
			new Ajax.Request(
			'index.php',
			{queue: {position: 'end', scope: 'command'},
				method: 'post',
				postBody:urlstring,
				onComplete: function(response) {
					
					if(response.responseText == 'report_active'){
						alert(label+' '+alert_arr.VALID_REPORT_MODULE_ACTIVE);
						return false;
					}else{

						saveReport();					
					}				
				}
		       }
	        );
		}else if(jQuery('#module1').val()!=0){
			saveReport();
		}
	}

		function saveReport(){

		var name = jQuery('#name').val();
		var code = jQuery('#code').val();
		var module = jQuery('#module1').val();
		var active = jQuery('#active1').val();

		var page = jQuery('#page').val();
		var idedit = jQuery('#idedit').val();

		var urlstring ="module=reportmanager&action=reportmanagerAjax&file=SaveReport&code="+code;
		urlstring +="&module1="+module+"&active1="+active+"&page="+page+"&idedit="+idedit;

		new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody:urlstring,
			onComplete: function(response) {				
				if(response.responseText == 'success'){
					window.location.href = 'index.php?action=index&module=reportmanager&parenttab=Settings&page='+page;

				}
	
			}
	       }
        );


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
			<form name="reportmanagerEditView" id='reportmanagerEditView' action="index.php" method="POST" >
			<input type="hidden" name="module" value="reportmanager" />
			<input type="hidden" name="action" value="SaveReport" />
			<input type="hidden" name="parenttab" value="Settings" />
			<input type="hidden" name="page" id="page" value="<?php echo $PAGEACTUAL;?>" />
			<input type="hidden" name="idedit" id="idedit" value="<?php echo $idedit;?>" />
			<input type="hidden" name="active1" id="active1" value="<?php echo $template['active'];?>" />

			<header class="main-box-header clearfix">
				<div class="filter-block pull-right">
					<div class="filter-block pull-right">
						<input title="Save [Alt+S]" accessKey="S" class="btn btn-info"  type="button" onclick="ValidaForm()" name="submit" id="submit1" value="<?php echo getTranslatedString('LBL_BUTTON_SUBMIT');?>" >
						<input title="Cancel [Alt+X]" accessKey="X" class="btn btn-warning" onclick="window.history.back()" type="button" name="button" value="<?php echo getTranslatedString('LBL_BUTTON_CANCEL');?>">
					</div>
				</div>
			</header>
			
			<div class="main-box-body clearfix">
				<div class="table-responsive">
					<table class="table">
						<tr>
							<td align="right" class="lvtCol" width="35%" id="codelabel"><b><?php echo getTranslatedString('LBL_PLAT_REPORTMANAGER_TEMPLATE_CODE');?></b>:</td>
							<td align="left" bgcolor="white" width="65%">
							<select name="code" id="code" class="form-control">
								<option value="0"><?php echo getTranslatedString('LBL_PLAT_SELECT');?></option>
								<?php foreach ($eventos as $evento):?>
										<option value="<?php echo $evento['code']; ?>"  <?php echo (($evento['code']==$template['code_template'])?'selected':''); ?>><?php echo $evento['code'].'  ('.$evento['name'].')'; ?></option>
								<?php endforeach ?>
							</select>
							</td>
						</tr>
						<tr>
							<td align="right" class="lvtCol" width="35%" id="modulelabel"><b><?php echo getTranslatedString('LBL_PLAT_REPORTMANAGER_MODULE');?></b>:</td>
							<td align="left" bgcolor="white" width="65%">
							<select name="module1" id="module1" class="form-control">
								<option value="0"><?php echo getTranslatedString('LBL_PLAT_SELECT');?></option>
								<?php foreach ($tabs as $tab):?>
										<option value="<?php echo $tab['tabid']; ?>"  <?php echo (($tab['tabid']==$template['tabid'])?'selected':''); ?>><?php echo getTranslatedString($tab['tablabel']);?></option>
								<?php endforeach ?>
							</select>
							</td>
						</tr>															
						<tr>
							<td align="right" class="lvtCol" width="35%"><b><?php echo getTranslatedString('LBL_PLAT_REPORTMANAGER_ACTIVE');?></b>:</td>   
							<td align="left" bgcolor="white" width="65%">
								<div class="checkbox-nice">
									<input id="active" name="active" <?php if((isset($template['active']) && $template['active'] == '1') ||
											$idedit == 0){ echo 'checked="checked"'; } ?> type="checkbox">
									<label for="active"></label>
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

