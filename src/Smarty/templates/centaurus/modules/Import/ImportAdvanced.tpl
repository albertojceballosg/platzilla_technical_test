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
<link rel="stylesheet" href="themes/{$THEME}/css/libs/datepicker.css" type="text/css" />
<link rel="stylesheet" href="themes/{$THEME}/css/libs/daterangepicker.css" type="text/css" />
<div class="row">
	<div class="col-lg-12">
		<h1>{'LBL_IMPORT'|@getTranslatedString:$MODULE} {$FOR_MODULE|@getTranslatedString:$FOR_MODULE}</h1>
	</div>
</div>

<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix" style="min-height: 820px;">
			<header class="main-box-header clearfix">
				<h2>&nbsp;</h2>
			</header>

			<div class="main-box-body clearfix">

				<div id="myWizard" class="wizard">

					<div class="wizard-inner">
						<ul class="steps">
							<li data-target="#step1"><span class="badge">1</span>{'LBL_IMPORT_STEP_1'|@getTranslatedString:$MODULE}<span class="chevron"></span></li>
							<li data-target="#step2"><span class="badge">2</span>{'LBL_IMPORT_STEP_2'|@getTranslatedString:$MODULE}<span class="chevron"></span></li>
							<li data-target="#step3" class="active"><span class="badge badge-primary">3</span>{'LBL_IMPORT_STEP_3'|@getTranslatedString:$MODULE}<span class="chevron"></span></li>
							<li data-target="#step4"><span class="badge">4</span>{'LBL_IMPORT_STEP_4'|@getTranslatedString:$MODULE}<span class="chevron"></span></li>
						</ul>

					</div>
					<div class="step-content">
						<div class="step-pane active" id="step3">
							<br/>
							<h4>{'LBL_IMPORT_STEP_3'|@getTranslatedString:$MODULE}</h4>
							<form onsubmit="VtigerJS_DialogBox.block();" action="index.php" enctype="multipart/form-data" method="POST" name="importAdvanced">
								<input type="hidden" name="module" value="{$FOR_MODULE}" />
								<input type="hidden" name="action" value="Import" />
								<input type="hidden" name="mode" value="import" />
								<input type="hidden" name="type" value="{$USER_INPUT->getString('type')}" />
								<input type="hidden" name="has_header" value='{$HAS_HEADER}' />
								<input type="hidden" name="file_encoding" value='{$USER_INPUT->getString('file_encoding')}' />
								<input type="hidden" name="delimiter" value='{$USER_INPUT->getString('delimiter')}' />
								<input type="hidden" name="merge_type" value='{$USER_INPUT->getString('merge_type')}' />
								<input type="hidden" name="merge_fields" value='{$USER_INPUT->getString('merge_fields')}' />

								<input type="hidden" id="mandatory_fields" name="mandatory_fields" value='{$ENCODED_MANDATORY_FIELDS}' />

								{include file='modules/Import/Import_Step4.tpl'}

							</form>

						</div>

					</div>
				</div>



			</div>

		</div>
	</div>
</div>

<!-- this page specific scripts -->
<script src="themes/{$THEME}/js/wizard.js"></script>
<script src="themes/{$THEME}/js/bootstrap-datepicker.js"></script>
<script src="themes/{$THEME}/js/bootstrap-datepicker.es.js"></script>
<script src="themes/{$THEME}/js/jquery.maskedinput.min.js"></script>
<script src="themes/{$THEME}/js/moment.min.js"></script>
<script src="themes/{$THEME}/js/daterangepicker.js"></script>
<script src="themes/{$THEME}/js/bootstrap-timepicker.min.js"></script>

<!-- this page specific inline scripts -->
<script>

jQuery(function () {ldelim}
	jQuery('#myWizard').wizard();

	{foreach key=_FIELD_NAME item=_FIELD_INFO from=$AVAILABLE_FIELDS}
		{assign var="_FIELD_TYPE" value=$_FIELD_INFO->getFieldDataType()}
		{if $_FIELD_TYPE eq 'date' || $_FIELD_TYPE eq 'datetime'}
			jQuery('#{$_FIELD_NAME}_defaultvalue').datepicker ({ format:'yyyy-mm-dd', language: 'es', weekStart: 1 });
		{/if}
	{/foreach}
{rdelim});
</script>
