{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 ********************************************************************************/
-->*}
<script language="JavaScript" type="text/javascript" src="modules/Import/resources/Import.js"></script>
<!-- this page specific styles -->
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/wizard.css">
<div class="row">
	<div class="col-lg-12">
		<h1>{'LBL_IMPORT'|@getTranslatedString:$MODULE} {$FOR_MODULE|@getTranslatedString:$FOR_MODULE}</h1>
	</div>
</div>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
	<div class="row">
		<div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
			<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
		</div>
	</div>
{/if}
<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix" style="min-height: 820px;">
			<header class="main-box-header clearfix">
				<h2>&nbsp;</h2>
			</header>
			
			<div class="main-box-body clearfix">
			
				<div id="myWizard" class="wizard">
					<form onsubmit="VtigerJS_DialogBox.block();" action="index.php" enctype="multipart/form-data" method="POST" name="importBasic">
						<input type="hidden" name="module" value="{$FOR_MODULE}" />
						<input type="hidden" name="action" value="Import" />
						<input type="hidden" name="mode" value="upload_and_parse" />
						<div class="wizard-inner">
							<ul class="steps">
								<li data-target="#step1" class="active"><span class="badge badge-primary">1</span>{'LBL_IMPORT_STEP_1'|@getTranslatedString:$MODULE}<span class="chevron"></span></li>
								<li data-target="#step2"><span class="badge">2</span>{'LBL_IMPORT_STEP_2'|@getTranslatedString:$MODULE}<span class="chevron"></span></li>
								<li data-target="#step3" onclick="javascript:void(0)"><span class="badge">3</span>{'LBL_IMPORT_STEP_3'|@getTranslatedString:$MODULE}<span class="chevron"></span></li>
								<li data-target="#step4" onclick="javascript:void(0)"><span class="badge">4</span>{'LBL_IMPORT_STEP_4'|@getTranslatedString:$MODULE}<span class="chevron"></span></li>
							</ul>
							
						</div>
						<div class="step-content">
							<div class="step-pane active" id="step1">
								<br/>
								<h4>{'LBL_IMPORT_STEP_1'|@getTranslatedString:$MODULE}</h4>
								{include file='modules/Import/Import_Step1.tpl'}
							</div>
							
							<div class="step-pane" id="step2">
								<br/>
								<h4>{'LBL_IMPORT_STEP_2'|@getTranslatedString:$MODULE}</h4>
								{include file='modules/Import/Import_Step2.tpl'}
							</div>
							
						</div>
					</form>
				</div>
			</div>
			
		</div>
	</div>
</div>

<!-- this page specific scripts -->
<script src="themes/{$THEME}/js/wizard.js"></script>
<script src="themes/{$THEME}/js/jquery.maskedinput.min.js"></script>

<!-- this page specific inline scripts -->
<script>
{literal}
jQuery(function () {
	jQuery('#myWizard').wizard();

});

{/literal}
</script>
