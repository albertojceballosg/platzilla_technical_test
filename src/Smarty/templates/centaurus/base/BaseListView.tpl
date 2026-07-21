{strip}
<link type="text/css" rel="stylesheet" href="themes/centaurus/css/compiled/list-view.css?v=1.1"  />
<link type="text/css" rel="stylesheet" href="themes/centaurus/css/compiled/pipeline.css?v=1.0" />
<link type="text/css" rel="stylesheet" href="modules/instancesdatasharing/instancesdatasharing.css?v=1.0" />
{if (file_exists ("{$ROOT_FOLDER_PATH}/modules/{$MODULE}/{$MODULE}.css"))}
<link type="text/css" rel="stylesheet" href="modules/{$MODULE}/{$MODULE}.css?v=1.0" />
{/if}
{block name="css"}{/block}
{block name="js"}{/block}
{block name='buttons'}
{include file='Buttons_List.tpl'}
{/block}
{if (!$CAN_CREATE_RECORDS)}
<div class="row">
	<div class="alert alert-danger">
		<span><strong>Advertencia: </strong> El módulo está suscrito en modo de pruebas. Has llegado al límite de registros que puedes crear en este modo.</span>
	{if ($IS_ADMIN)}
		<span>Te invitamos a actualizar <a href="index.php?module=Home&action=ViewSubscriptionDetails&tab=subscription">tu suscripción</a></span>
	{/if}
	</div>
</div>
{/if}
{if (!empty ($MESSAGE))}
<div class="row">
	<div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
		<strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
	</div>
</div>
{/if}
    {if $TAB_GROUP eq 'ACTIVITY'}
        {assign var='idActivity' value=$TAB_HOME_ID}
	{else}
        {math equation= rand() assign= "idActivity"}
	{/if}

<div class="row" style="padding: 0 6px">
	<div id="list-view-container-{$idActivity}" class="col-xs-12">
		{if $TAB_GROUP eq 'ACTIVITY'}
		<div class="tab-content">
			<div id="VIEW-TASK-{$TAB_HOME_ID}"
				 class="tab-pane fade in active">
                {include file=$__LIST_VIEW_ENTRIES_TEMPLATE_PATH}
			</div>
			<div id="VIEW-KANBAN-{$TAB_HOME_ID}"
				 class="tab-pane fade in">
                {include file='utils/HTMLPageLoanding.tpl'}
			</div>
			<div id="VIEW-CALENDAR-{$TAB_HOME_ID}"
				 class="tab-pane fade in">
                {include file='utils/HTMLPageLoanding.tpl'}
			</div>
		</div>
		{else}
            {if (isset ($__LIST_VIEW_ENTRIES_TEMPLATE_PATH) )}
                {include file=$__LIST_VIEW_ENTRIES_TEMPLATE_PATH}
            {else}
                {include file='base/BaseListViewEntries.tpl'}
            {/if}
        {/if}
	</div>
</div>
<script type="text/html" id="mass-edit-modal-template">
	<div class="modal fade" id="mass-edit-modal" tabindex="-1" role="dialog" aria-hidden="false" style="top: 0;">
		<form action="index.php" method="post">
			<input type="hidden" name="module" value="{$MODULE}" />
			<input type="hidden" name="action" value="MassEditSave" />
			<div class="modal-dialog" style="width: 90vw;">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title"></h4>
					</div>
					<div class="modal-body" style="max-height: 70vh; min-height: 70vh; overflow-x: hidden; overflow-y: auto;"></div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-primary">Guardar</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</script>
<script type="text/html" id="mass-mail-modal-template">
	<div class="modal fade" id="mass-mail-modal" tabindex="-1" role="dialog" aria-hidden="false" style="top: 0;">
		<form action="index.php" method="post" onsubmit="MassActionsUtils.sendEmail (this); return false;">
			<input type="hidden" name="module" value="{$MODULE}" />
			<input type="hidden" name="action" value="MassMailSend" />
			<input type="hidden" name="Ajax" value="true" />
			<div class="modal-dialog" style="width: 90vw;">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title">Enviar correo masivo</h4>
					</div>
					<div class="modal-body" style="max-height: 70vh; min-height: 70vh; overflow-x: hidden; overflow-y: auto;">
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input"><label for="mass-mail-language">Idioma: <span class="required">*</span></label></div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<select id="mass-mail-language" name="language" class="form-control parameter-value" onchange="MassActionsUtils.setTemplateOptions (this);"></select>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input"><label for="mass-mail-template-name">Plantilla: <span class="required">*</span></label></div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<select id="mass-mail-template-name" name="templatename" class="form-control parameter-value" onchange="MassActionsUtils.setVariableOptions (this);"></select>
								</div>
							</div>
						</div>
						<div class="col-md-12 parameter">
							<div class="col-md-2">
								<div class="label-input"><label for="mass-mail-recipients-type">Destinatarios: <span class="required">*</span></label></div>
							</div>
							<div class="col-md-3 form-group">
								<div class="input-group" style="width: 100%;">
									<select id="mass-mail-recipients-type" name="recipients[type]" class="form-control parameter-type" onchange="MassActionsUtils.setParameterValue (this);">
										<option value=""></option>
										<option value="SOURCE FIELD">Campo en los registros seleccionados</option>
										<option value="LITERAL">Valor</option>
										<option value="VARIABLE">Variable del sistema</option>
									</select>
								</div>
							</div>
							<div class="form-group col-md-7 field-container">
								<div class="input-group" style="width: 100%;">
									<input type="text" name="recipients[value]" value="" class="form-control parameter-value" placeholder="" data-type="LITERAL" disabled="disabled" style="display: none;" />
									<select id="mass-mail-recipients-source-fields" name="recipients[value]" class="form-control parameter-value" title="" data-type="SOURCE FIELD" disabled="disabled" style="display: none;">
										<option></option>
										<option value="record_id">(El registro que se está procesando)</option>
									</select>
									<div class="input-group variable" style="display: none;">
										<input type="text" name="recipients[value]" class="form-control parameter-value" placeholder="" data-type="VARIABLE" disabled="disabled" style="display: none;" />
										<div class="input-group-btn">
											<button class="btn btn-default" type="button" title="Campos en la fuente de datos" onclick="MassActionsUtils.openFieldsModal (this);"><i class="fa fa-code"></i></button>
											<button class="btn btn-default" type="button" title="Variables de sistema" onclick="MassActionsUtils.openVariablesModal (this);"><i class="fa fa-cogs"></i></button>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div id="mass-mail-variables-section" class="row" style="display: none;">
							<h4 class="col-md-12">Variables</h4>
							<div id="mass-mail-variables" class="col-md-12"></div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-primary">Enviar</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</script>
<script type="text/html" id="mass-mail-modal-template-variable">
	<div class="col-md-12 parameter">
		<div class="col-md-2">
			<div class="label-input">
				<input type="text" class="form-control variable-name" placeholder="" readonly="readonly" />
			</div>
		</div>
		<div class="col-md-3 form-group">
			<div class="input-group" style="width: 100%;">
				<select class="form-control parameter-type" title="" onchange="MassActionsUtils.setParameterValue (this);">
					<option value=""></option>
					<option value="SOURCE FIELD">Campo en los registros seleccionados</option>
					<option value="LITERAL">Valor</option>
					<option value="VARIABLE">Variable del sistema</option>
				</select>
			</div>
		</div>
		<div class="form-group col-md-7 field-container">
			<div class="input-group" style="width: 100%;">
				<input type="text" class="form-control parameter-value" placeholder="" data-type="LITERAL" />
				<select class="form-control parameter-value" title="" data-type="SOURCE FIELD">
					<option></option>
					<option value="record_id">(El registro que se está procesando)</option>
				</select>
				<div class="input-group variable">
					<input type="text" class="form-control parameter-value" placeholder="" data-type="VARIABLE" />
					<div class="input-group-btn">
						<button class="btn btn-default" type="button" title="Campos en la fuente de datos" onclick="MassActionsUtils.openFieldsModal (this);"><i class="fa fa-code"></i></button>
						<button class="btn btn-default" type="button" title="Variables del sistema" onclick="MassActionsUtils.openVariablesModal (this);"><i class="fa fa-cogs"></i></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="mass-mail-auxiliary-modal-template">
	<div class="modal fade" id="mass-mail-auxiliary-modal" tabindex="-1" role="dialog" aria-hidden="false">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title"></h4>
				</div>
				<div class="modal-body">
					<div class="table-responsive">
						<table class="table">
							<tbody></tbody>
						</table>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
				</div>
			</div>
		</div>
	</div>
</script>
{include file='modules/instancesdatasharing/SyncsModal.tpl'}
{include file='CreateTaskWizard.tpl'}
{block name='templates'}{/block}
<script type="text/javascript" src="themes/centaurus/js/modernizr.custom.js"></script>
<script type="text/javascript" src="themes/centaurus/js/snap.svg-min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/classie.js"></script>
<script type="text/javascript" src="themes/centaurus/js/notificationFx.js"></script>
<script type="text/javascript" src="include/jquery/jquery-ui-1.10.3.custom.min.js"></script>
<script type="text/javascript" src="include/js/list-view.js?v=1.1"></script>
<script type="text/javascript" src="include/js/mass-actions-utils.js?v=1.1"></script>
<script type="text/javascript" src="modules/instancesdatasharing/data-sharing.js?v=1.0"></script>
{if (file_exists ("{$ROOT_FOLDER_PATH}/modules/{$MODULE}/{$MODULE}.js"))}
	<script type="text/javascript" src="modules/{$MODULE}/{$MODULE}.js"></script>
{/if}
{include file='modules/notifications/NotificationsScripts.tpl'}
{block name="scripts"}{/block}
{/strip}