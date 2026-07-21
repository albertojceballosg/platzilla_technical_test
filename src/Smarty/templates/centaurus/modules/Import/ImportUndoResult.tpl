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
		<h1>{'LBL_IMPORT'|@getTranslatedString:$MODULE} {$FOR_MODULE|@getTranslatedString:$FOR_MODULE} - sss{'LBL_RESULT'|@getTranslatedString:$MODULE}</h1>
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
							<li data-target="#step3"><span class="badge">3</span>{'LBL_IMPORT_STEP_3'|@getTranslatedString:$MODULE}<span class="chevron"></span></li>
							<li data-target="#step4" class="active"><span class="badge badge-primary">4</span>{'LBL_IMPORT_STEP_4'|@getTranslatedString:$MODULE}<span class="chevron"></span></li>
						</ul>
						
					</div>
					<div class="step-content">
						<div class="step-pane active" id="step4">
							<br/>
							<h4>{'LBL_IMPORT'|@getTranslatedString:$MODULE} {$FOR_MODULE|@getTranslatedString:$FOR_MODULE} - {'LBL_UNDO_RESULT'|@getTranslatedString:$MODULE}</h4>
							<div class="col-lg-12">
								<div class="table-responsive">
									<table class="table">
										<tr>
											<td>{'LBL_TOTAL_RECORDS'|@getTranslatedString:$MODULE}</td>
											<td width="10%">:</td>
											<td width="10%">{$TOTAL_RECORDS}</td>
										</tr>
										<tr>
											<td>{'LBL_NUMBER_OF_RECORDS_DELETED'|@getTranslatedString:$MODULE}</td>
											<td width="10%">:</td>
											<td width="10%">{$DELETED_RECORDS_COUNT}</td>
										</tr>
									</table>

								</div>
							</div>
							
							{include file='modules/Import/Import_Done_Buttons.tpl'}
							
						</div>
						
					</div>
				</div>
				
				
				
			</div>
			
		</div>
	</div>
</div>
