{strip}
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/bootstrap-wizard.css" />
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/section/bootstrap-wizard_custom.css" />
<link rel="stylesheet" type="text/css" href="modules/okrs/okrs.css" />
<script type="text/html" id="okrs-wizard-template">
<div id="okrs-wizard" class="wizard" data-title="{$MOD['DATA_TITLE']}">
	<h1>{$MOD['okrs']}</h1>
	{* On boarding card *}
	<div id="start-section"   class="wizard-card" data-cardname="start">
		<input type="hidden" name="module" value="okrs" />
		<input type="hidden" name="action" value="Save" />
		<input type="hidden" name="record" value="" />
		<input type="hidden" name="datasource" value="wizard" />
		<input type="hidden" name="Ajax" value="true" />
		<h3 class="hide-element">{$MOD['NAV_START']}</h3>
		<h4 class="hidden-md hidden-lg">{$MOD['NAV_START']}</h4>
		<div class="row wizard-input-section data-section">
			<h1>Hoja de inicio  (on boarding)</h1>
			<div id="new-notify-options" class="form-group col-xs-12 field-container wizard-actions" style="display: none;">

			</div>
			<div class="form-group col-xs-12 field-container">

			</div>
		</div>
	</div>
    {* Select companyType and Phase *}
	<div id="setp-1-section" class="wizard-card" data-cardname="setp-1">
		<h3 class="hide-element">{$MOD['NAV_STEP1']}</h3>
		<h4 class="hidden-md hidden-lg">{$MOD['NAV_STEP1']}</h4>
		<div class="wizard-input-section data-section">
            {include file="modules/Okrs/Wizard/WizardCompany.tpl"}
		</div>
	</div>
    {* Objective *}
	<div id="setp-2-section" class="wizard-card" data-cardname="setp-2">
		<h3 class="hide-element">{$MOD['NAV_STEP2']}</h3>
		<h4 class="hidden-md hidden-lg">{$MOD['NAV_STEP2']}</h4>
		<div class="wizard-input-section data-section">

		</div>
	</div>
	{* Key Results *}
	<div id="setp-3-section" class="wizard-card" data-cardname="setp-3">
		<h3 class="hide-element">{$MOD['NAV_STEP3']}</h3>
		<div class="wizard-input-section data-section"></div>
	</div>
	{* Next setp *}
	<div id="setp-4-section" class="wizard-card" data-cardname="setp-4">
		<h3 class="hide-element">{$MOD['NAV_STEP4']}</h3>
		<div class="wizard-input-section data-section"></div>
	</div>
    {* End setp *}
	<div id="setp-4-section" class="wizard-card" data-cardname="setp-4">
		<h3 class="hide-element">{$MOD['NAV_STEP5']}</h3>
		<div class="wizard-input-section data-section"></div>
	</div>
	<div class="wizard-failure text-center">
		<h4><strong style="color: #880000;">Error!</strong>: Se ha presentado un error al guardar la notificación</h4>
		<p class="message"></p>
	</div>
	<div class="wizard-loading text-center">
		<h4><strong>Por favor espera</strong></h4>
		<p>Estamos guardando la notificación. Por favor espera unos instantes y por favor no cierres esta ventana</p>
		<img src="themes/images/loading.gif" class="img-responsive" style="display: inline-block;" />
	</div>
	<div class="wizard-success text-center">
		<h4><strong style="color: #008800;">Listo!</strong>: Se ha guardado la notificación</h4>
		<button type="button" class="btn btn-default" style="margin-left: 5px;" onclick="NotificationUtils.closeNotifyWizard ();">Terminar</button>
	</div>
</div>
</script>
	<script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-wizard.js"></script>
{/strip}