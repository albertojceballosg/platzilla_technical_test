{strip}
    {if (isset ($NOTIFICATION))}
        {assign var='notificationModuleFilter' value=$NOTIFICATION->getModuleFilter ()}
        {assign var='notificationAmbit' value=$NOTIFICATION->getAmbit ()}
        {assign var='notificationContents' value=$NOTIFICATION->getContents ()}
        {assign var='notificationDescription' value=$NOTIFICATION->getDescription ()}
        {assign var='notificationEvent' value=$NOTIFICATION->getEvent ()}
        {assign var='notificationEventParameter' value=$NOTIFICATION->getEventParameter ()}
        {assign var='notificationModuleNames' value=$NOTIFICATION->getModuleNames ()}
        {assign var='notificationName' value=$NOTIFICATION->getName ()}
        {assign var='notificationStatus' value=$NOTIFICATION->getStatus ()}
        {assign var='notificationType' value=$NOTIFICATION->getStyle ()}
        {assign var='notificationUsersFilter' value=$NOTIFICATION->getUsersFilter ()}
        {assign var='notificationView' value=$NOTIFICATION->getView ()}
        {assign var='notificationAction' value=$NOTIFICATION->getAction ()}
        {if preg_match("/alert-link/", $notificationContents)}
            {assign var='notificationstyle' value = 'ADL'}
        {else}
            {assign var='notificationstyle' value = 'SA'}
        {/if}
        {assign var='notificationArryFilter' value=$NOTIFICATION->getAdvancedFilter ()}
        {assign var='notificationColumnPeriod' value=$NOTIFICATION->getColumnPeriod ()}
        {assign var='notificationFilterPeriod' value=$NOTIFICATION->getFilterPeriod ()}
        {assign var='notificationStandardFilter' value=$NOTIFICATION->getStandardFilter ()|json_decode:true}

    {else}
        {assign var='notificationContents' value=null}
        {assign var='notificationDescription' value=null}
        {assign var='notificationEvent' value=null}
        {assign var='notificationEventParameter' value=null}
        {assign var='notificationModuleNames' value=array()}
        {assign var='notificationName' value=null}
        {assign var='notificationStatus' value=null}
        {assign var='notificationType' value=null}
    {/if}
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/bootstrap-wizard.css" />
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/section/bootstrap-wizard_custom.css" />
<link rel="stylesheet" type="text/css" href="modules/notifications/notifications.css" />
<script type="text/html" id="notifications-wizard-template">
<div id="notifications-wizard" class="wizard" data-title="Crear notificación">
	<h1>{$MOD['notifications']}</h1>
	<div id="start-section"   class="wizard-card" data-cardname="start">
		<input type="hidden" name="module" value="notifications" />
		<input type="hidden" name="action" value="Save" />
		<input type="hidden" name="record" value="" />
		<input type="hidden" name="datasource" value="wizard" />
		<input type="hidden" name="Ajax" value="true" />
		<h3 class="hide-element">{$MOD['NAV_START']}</h3>
		<h4 class="hidden-md hidden-lg">{$MOD['NAV_START']}</h4>
		<div class="row wizard-input-section data-section">
			<div id="new-notify-options" class="form-group col-xs-12 field-container wizard-actions" style="display: none;">
				<div class="radio-group">
					<label><input id="wizard-action-create" type="radio" name="wizardaction" value="EditView" checked="checked" disabled="disabled" onchange="NotificationUtils.setWizardAction (this);">{$MOD['WIZARD_CREATE']}</label>
				</div>
				<div class="radio-group">
					<label><input id="wizard-action-duplicate-from-pattern" type="radio" name="wizardaction" value="DuplicateNotification" disabled="disabled" onchange="NotificationUtils.setWizardAction (this);">{$MOD['WIZARD_DUPLICATE_FROM_PATTEM']}</label>
				</div>
			</div>
			<div id="existing-notify-options" class="form-group col-xs-12 field-container wizard-actions" style="display: none;">
				<div class="radio-group">
					<label><input id="wizard-action-edit" type="radio" name="wizardaction" value="EditView" checked="checked" disabled="disabled" onchange="NotificationUtils.setWizardAction (this);">{$MOD['WIZARD_EDIT']}</label>
				</div>
				<div class="radio-group">
					<label><input id="wizard-action-duplicate" type="radio" name="wizardaction" value="DuplicateNotification" disabled="disabled" onchange="NotificationUtils.setWizardAction (this);">{$MOD['WIZARD_DUPLICATE']}</label>
				</div>
			</div>
			<div id="notify-pattern" class="row" style="display: none;">
				<p class="col-xs-12">{$MOD['SELECT_PATTEM']}</p>
{if (!empty ($AVAILABLE_STYLE))}
				<div class="form-group col-xs-12 field-container" style="margin-bottom: 5px;">
					<select id="notify-type"  class="form-control" title="{$MOD['FILTER_TYPE']}" onchange="NotificationUtils.filterPatternByType (this);" disabled="disabled">
						<option value="">{$MOD['FILTER_TYPE']}</option>
{foreach $AVAILABLE_STYLE as $style}
							<option value="{$style}"{if ($notificationType eq $style)} selected="selected"{/if}>{$MOD[$style]}</option>
{/foreach}
					</select>
				</div>
{/if}
				<div id="dv-n-notify-pattern-id" class="form-group col-xs-12 field-container">
					<select id="notify-pattern-id" class="form-control" title="Selecciona el patrón"  onchange="NotificationUtils.getPattern (this);"  disabled="disabled">
						<option value="">{$MOD['FILTER_TYPE']}</option>
{if (!empty ($DATA_ALL))}
    {foreach $DATA_ALL.records as $notify}
				<option value="{{$notify->getId ()}}"   data-type="{$notify->getStyle ()}">{$notify->getName ()}</option>
    {/foreach}
{/if}
					</select>
					<span id="sp-n-notify-pattern-id"  class="help-block"></span>
				</div>
			</div>
			<div class="form-group col-xs-12 field-container">

			</div>
		</div>
	</div>
	<div id="setp-1-section" class="wizard-card" data-cardname="setp-1">
		<h3 class="hide-element">{$MOD['NAV_STEP1']}</h3>
		<h4 class="hidden-md hidden-lg">{$MOD['NAV_STEP1']}</h4>
		<div class="wizard-input-section data-section"></div>
	</div>
	<div id="setp-2-section" class="wizard-card" data-cardname="setp-2">
		<h3 class="hide-element">{$MOD['NAV_STEP2']}</h3>
		<h4 class="hidden-md hidden-lg">{$MOD['NAV_STEP2']}</h4>
		<div class="wizard-input-section data-section"></div>
	</div>
	<div id="setp-3-section" class="wizard-card" data-cardname="setp-3">
		<h3 class="hide-element">{$MOD['NAV_STEP3']}</h3>
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
	<script type="text/html" id="condition-template">
        {include file="modules/notifications/filterNotify.tpl"}
	</script>
	<script type="text/html" id="condition-group-template">
        {include file="modules/notifications/filterGroupNotify.tpl"}
	</script>
    {include file='modules/notifications/alertTemplates.tpl'}
{/strip}