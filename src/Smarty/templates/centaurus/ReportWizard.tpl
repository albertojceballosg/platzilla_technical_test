{strip}
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/bootstrap-wizard.css">
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/section/bootstrap-wizard_custom.css">
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/datepicker.css" />
	<link rel="stylesheet" href="themes/{$THEME}/css/libs/bootstrap-timepicker.css" type="text/css" />
	<style type="text/css">
		{literal}
			.label-input>label,
			.wizard-card label {
				font-size: 1.11em;
				font-weight: 300;
				line-height: 20px;
				margin: 0;
				padding: 6px 0;
			}

			.required {
				color: #F00;
			}

			select[multiple] {
				height: 7em;
			}

			select.reportcolumns {
				height: 15em;
			}

			#columns .form-group {
				margin-bottom: 0;
			}

			.columns-actions {
				height: 196px;
				margin-bottom: 0;
				margin-top: 32px;
			}

			.vertical-group {
				position: relative;
				transform: translate(0, -50%);
				top: 50%;
			}

			.btn.btn-icon {
				font-size: 14px;
				height: 27px;
				line-height: 27px;
				margin: 5px auto;
				padding: 0;
				text-align: center;
				width: 27px;
			}

			.wizard-steps {
				width: 150px !important;
			}

			.modal-backdrop.in {
				background-color: transparent;
				bottom: 0;
				z-index: 101;
			}

			.condition-group-body,
			.condition-group-footer {
				padding: 0;
			}

			.conditions,
			.condition-group {
				margin-bottom: 0;
			}

			.condition {
				border-left: 0;
				border-right: 0;
			}

			.condition:first-child {
				border-top: 0;
			}

			.condition:last-child {
				border-bottom: 0;
			}

		{/literal}
	</style>
	<div id="report-wizard" class="wizard" data-title="Crear reporte">
		<h1>{$MOD.LBL_CREATE_REPORT}</h1>
		<div class="wizard-card" data-cardname="general">
			{if ($IS_INSTANCE)}
				<input type="hidden" id="is-instance" value="" />
			{/if}
			<h3 class="hide-element">General</h3>
			<div class="row wizard-input-section">
				<div class="form-group col-xs-12 col-md-6">
					<label for="folderid">Carpeta <span class="required">*</span></label>
					<select id="folderid{$FILE_ID}" class="form-control">
						<option value=""></option>
						{foreach $AVAILABLE_FOLDERS as $folder}
							<option value="{$folder.folderid}">{$folder.foldername}</option>
						{/foreach}
					</select>
				</div>
				<div class="form-group col-xs-12 col-md-6">
					<label for="reporttype">Formato <span class="required">*</span></label>
					<select id="reporttype{$FILE_ID}" class="form-control"
						onchange="ReportWizardUtils.onChangeReportTypeHandler (this);">
						<option value="tabular" selected="selected">Tabulado</option>
						<option value="summary">Resumen</option>
					</select>
				</div>
				<div class="form-group col-xs-12 col-md-6">
					<label for="reportname">Nombre <span class="required">*</span></label>
					<input type="text" id="reportname{$FILE_ID}" class="form-control" maxlength="100" />
				</div>
				<div class="form-group col-xs-12 col-md-6">
					<label for="reportdescription">Descripción</label>
					<input type="text" id="reportdescription{$FILE_ID}" class="form-control" maxlength="250" />
				</div>
				<div class="form-group col-xs-12 col-md-6">
					<label for="modulename">Módulo principal <span class="required">*</span></label>
					<select id="modulename{$FILE_ID}" class="form-control"
						onchange="ReportWizardUtils.setRelatedModules (this); ReportWizardUtils.filterApplications (this);">
						<option value=""></option>
						{foreach $AVAILABLE_MODULES as $module}
							<option value="{$module.name}">{$module.tablabel}</option>
						{/foreach}
					</select>
				</div>
				<div id="relatedmodules" class="form-group col-xs-12 col-md-6" style="display: none;">
					<label for="relatedmodulenames">Módulos relacionados</label>
					<select id="relatedmodulenames{$FILE_ID}" class="form-control" multiple="multiple"></select>
				</div>
				<div class="form-group col-xs-12 col-md-6">
					<label for="applicationcodes">Disponible para la función{if ($IS_INSTANCE)} <span
							class="required">*</span>{/if}<span class="required">*</span></label>
					<select id="applicationcodes{$FILE_ID}" name="applicationcodes[]" class="form-control"
						multiple="multiple">
						{foreach $AVAILABLE_APPLICATIONS as $application}
							{assign var='applicationModules' value=array()}
							{foreach $application.modules as $applicationModule}
								{$applicationModules[] = $applicationModule.name}
							{/foreach}
							<option value="{$application.app_code}" class="hidden"
								data-modules="{join(', ', $applicationModules)}">{$application.app_name}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
		<div id="columns" class="wizard-card" data-cardname="columns">
			<h3 class="hide-element">Columnas</h3>
			<div class="row wizard-input-section">
				<div class="form-group col-xs-12 col-md-5">
					<label for="availablecolumns">Disponibles</label>
					<select id="availablecolumns{$FILE_ID}" class="form-control reportcolumns" multiple="multiple"></select>
				</div>
				<div class="form-group col-xs-12 col-md-1 columns-actions">
					<div class="vertical-group">
						<button type="button" class="btn btn-primary btn-icon center-block linea"
							onclick="ReportWizardUtils.addColumns ();"><i class="fa fa-angle-double-right"></i></button>
						<button type="button" class="btn btn-primary btn-icon center-block linea"
							onclick="ReportWizardUtils.addColumn ();"><i class="fa fa-angle-right"></i></button>
						<button type="button" class="btn btn-warning btn-icon center-block linea"
							onclick="ReportWizardUtils.deleteColumn ();"><i class="fa fa-angle-left"></i></button>
						<button type="button" class="btn btn-warning btn-icon center-block linea"
							onclick="ReportWizardUtils.deleteColumns ();"><i class="fa fa-angle-double-left"></i></button>
					</div>
				</div>
				<div class="form-group col-xs-12 col-md-5">
					<label for="selectedcolumns">Seleccionadas <span class="required">*</span></label>
					<select id="selectedcolumns{$FILE_ID}" class="form-control reportcolumns" multiple="multiple"></select>
				</div>
				<div class="form-group col-xs-12 col-md-1 columns-actions">
					<div class="vertical-group">
						<button type="button" class="btn btn-primary btn-icon center-block linea"
							onclick="ReportWizardUtils.moveColumnsUp ();"><i class="fa fa-angle-up"></i></button>
						<button type="button" class="btn btn-warning btn-icon center-block linea"
							onclick="ReportWizardUtils.moveColumnsDown ();"><i class="fa fa-angle-down"></i></button>
					</div>
				</div>
			</div>
			<div id="groupings-summary" class="row wizard-input-section" style="display: none;">
				<div class="table-responsive">
					<table class="table" id="grouping-fields-table">
						<thead>
							<tr>
								<th class="col-xs-12 col-md-8">Agrupar por</th>
								<th class="col-xs-12 col-md-3">Orden</th>
								<th class="col-xs-12 col-md-1"></th>
							</tr>
						</thead>
						<tbody id="grouping-fields-body">
							<tr data-grouping-index="1">
								<td class="col-xs-12 col-md-8">
									<select id="Group1{$FILE_ID}" name="Group1" class="form-control grouping-columns"
										title="Agrupar por"></select>
								</td>
								<td class="col-xs-12 col-md-3">
									<select id="Sort1{$FILE_ID}" name="Sort1" class="form-control" title="Orden">
										<option value="Ascending">Ascendente</option>
										<option value="Descending">Descendente</option>
									</select>
								</td>
								<td class="col-xs-12 col-md-1"></td>
							</tr>
							<tr data-grouping-index="2">
								<td class="col-xs-12 col-md-8">
									<select id="Group2{$FILE_ID}" name="Group2" class="form-control grouping-columns"
										title="Agrupar por"></select>
								</td>
								<td class="col-xs-12 col-md-3">
									<select id="Sort2{$FILE_ID}" name="Sort2" class="form-control" title="Orden">
										<option value="Ascending">Ascendente</option>
										<option value="Descending">Descendente</option>
									</select>
								</td>
								<td class="col-xs-12 col-md-1">
									<button type="button" class="btn btn-sm btn-danger remove-grouping-field"
										title="Eliminar campo">
										<i class="fa fa-trash-o"></i>
									</button>
								</td>
							</tr>
							<tr data-grouping-index="3">
								<td class="col-xs-12 col-md-8">
									<select id="Group3{$FILE_ID}" name="Group3" class="form-control grouping-columns"
										title="Agrupar por"></select>
								</td>
								<td class="col-xs-12 col-md-3">
									<select id="Sort3{$FILE_ID}" name="Sort3" class="form-control" title="Orden">
										<option value="Ascending">Ascendente</option>
										<option value="Descending">Descendente</option>
									</select>
								</td>
								<td class="col-xs-12 col-md-1">
									<button type="button" class="btn btn-sm btn-danger remove-grouping-field"
										title="Eliminar campo">
										<i class="fa fa-trash-o"></i>
									</button>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="text-right" style="margin-top: 10px;">
						<button type="button" id="add-grouping-field" class="btn btn-sm btn-primary"
							onclick="ReportWizardUtils.addGroupingField();">
							<i class="fa fa-plus"></i> Agregar campo de agrupación
						</button>
					</div>
				</div>
			</div>
		</div>
		<div id="totals" class="wizard-card" data-cardname="totals">
			<h3 class="hide-element">Totales</h3>
			<div class="row wizard-input-section">
				<div class="table-responsive">
					<table class="table">
						<thead>
							<tr>
								<td class="col-xs-12 col-md-8">Columnas</td>
								<td class="col-xs-12 col-md-1">Suma</td>
								<td class="col-xs-12 col-md-1">Promedio</td>
								<td class="col-xs-12 col-md-1">Máximo</td>
								<td class="col-xs-12 col-md-1">Mínimo</td>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
		</div>
		<div id="filters" class="wizard-card" data-cardname="filters">
			<h3 class="hide-element">Filtros</h3>
			<div id="standardfilter" class="row wizard-input-section">
				<h4>Estándar</h4>
				<div class="form-group col-xs-12 col-md-5">
					<label for="standardfiltercolumn">Columna</label>
					<select id="standardfiltercolumn{$FILE_ID}" class="form-control filter-columns"></select>
				</div>
				<div class="form-group col-xs-12 col-md-3">
					<label for="standardfilterperiod">Período</label>
					<select id="standardfilterperiod{$FILE_ID}" class="form-control"
						onchange="ReportWizardUtils.onChangeStandardFilterPeriodHandler (this);">
						<option value=""></option>
						{foreach $AVAILABLE_STANDARD_FILTER_PERIODS as $periodValue => $periodLabel}
							<option value="{$periodValue}">{$periodLabel}</option>
						{/foreach}
					</select>
				</div>
				<div class="form-group col-xs-12 col-md-2">
					<label for="standardfilterfrom">Desde</label>
					<input type="text" id="standardfilterfrom{$FILE_ID}" class="form-control date-field input-readonly"
						readonly="readonly" />
				</div>
				<div class="form-group col-xs-12 col-md-2">
					<label for="standardfilterto">Hasta</label>
					<input type="text" id="standardfilterto{$FILE_ID}" class="form-control date-field input-readonly"
						readonly="readonly" />
				</div>
			</div>
			<div id="advancedfilters" class="row wizard-input-section">
				<h4>Avanzados</h4>
				<div class="col-xs-12 col-md-12">
					<div class="condition-groups"></div>
					<div class="action-bar text-center">
						<button type="button" class="btn btn-link" onclick="ReportWizardUtils.addConditionGroup ();"
							title="Agregar grupo de condiciones"><i class="fa fa-plus"></i></button>
					</div>
				</div>
			</div>
		</div>
		<div id="sharing" class="wizard-card" data-cardname="sharing">
			<h3 class="hide-element">Visibilidad</h3>
			<div class="row wizard-input-section">
				<div class="form-group col-xs-12 col-md-12">
					<label for="visibility">Visibilidad</label>
					<select id="visibility{$FILE_ID}" class="form-control"
						onchange="ReportWizardUtils.onChangeVisibilityHandler (this);">
						<option value="">Público</option>
						<option value="Shared">Privado, compartido con otros usuarios</option>
						<option value="Private">Privado</option>
					</select>
				</div>
			</div>
			<div id="sharedwith" class="row wizard-input-section members" style="display: none;">
				<h3 class="hide-element">Compartir con</h3>
				<div class="col-xs-12 col-md-5">
					<label for="share-available-members">Disponibles</label>
					<select id="share-available-members{$FILE_ID}" class="form-control available-members"
						multiple="multiple" style="min-height: 15em;">
						{if (!empty ($AVAILABLE_GROUPS))}
							<optgroup label="Grupos" class="groups">
								{foreach $AVAILABLE_GROUPS as $groupId => $groupName}
									<option value="group::{$groupId}">{$groupName}</option>
								{/foreach}
							</optgroup>
						{/if}
						{if (!empty ($AVAILABLE_USERS))}
							<optgroup label="Usuarios" class="users">
								{foreach $AVAILABLE_USERS as $userId => $userName}
									<option value="user::{$userId}">{$userName}</option>
								{/foreach}
							</optgroup>
						{/if}
					</select>
				</div>
				<div class="form-group col-xs-12 col-md-1 columns-actions">
					<div class="vertical-group">
						<button type="button" class="btn btn-primary btn-icon center-block"
							onclick="ReportWizardUtils.addMembers (this);"><i class="fa fa-angle-right"></i></button>
						<button type="button" class="btn btn-warning btn-icon center-block"
							onclick="ReportWizardUtils.removeMembers (this);"><i class="fa fa-angle-left"></i></button>
					</div>
				</div>
				<div class="col-xs-12 col-md-6">
					<label for="share-selected-members">Miembros</label>
					<select id="share-selected-members{$FILE_ID}" class="form-control selected-members" multiple="multiple"
						style="min-height: 15em;"></select>
				</div>
			</div>
		</div>
		<div id="schedule" class="wizard-card" data-cardname="schedule">
			<h3 class="hide-element">Programar envío</h3>
			<div class="row wizard-input-section">
				<div class="col-xs-12 col-md-12">
					<label for="scheduled">Programar envío</label>
					<select id="scheduled{$FILE_ID}" class="form-control"
						onchange="ReportWizardUtils.onChangeScheduleHandler (this);">
						<option value="no">No</option>
						<option value="yes">Sí</option>
					</select>
				</div>
			</div>
			<div id="schedule-data" class="row wizard-input-section" style="display: none;">
				<div class="col-xs-12 col-md-3">
					<label for="schedule-frequency">Frecuencia</label>
					<select id="schedule-frequency{$FILE_ID}" class="form-control"
						onchange="ReportWizardUtils.onChangeScheduleFrequencyHandler (this);">
						<option value=""></option>
						<option value="2">Diario</option>
						<option value="3">Semanal</option>
						<option value="4">Quincenal</option>
						<option value="5">Mensual</option>
						<option value="6">Anual</option>
					</select>
				</div>
				<div id="schedule-weekday-container" class="col-xs-12 col-md-3" style="display: none;">
					<label for="schedule-weekday">Día semana</label>
					<select id="schedule-weekday{$FILE_ID}" class="form-control">
						<option value=""></option>
						<option value="0">Domingo</option>
						<option value="1">Lunes</option>
						<option value="2">Martes</option>
						<option value="3">Miércoles</option>
						<option value="4">Jueves</option>
						<option value="5">Viernes</option>
						<option value="6">Sábado</option>
					</select>
				</div>
				<div id="schedule-day-container" class="col-xs-12 col-md-3" style="display: none;">
					<label for="schedule-day">Día</label>
					<select id="schedule-day{$FILE_ID}" class="form-control">
						<option value=""></option>
						{for $i = 1; $i <= 31; $i++}
							<option value="{$i}">{$i}</option>
						{/for}
					</select>
				</div>
				<div id="schedule-month-container" class="col-xs-12 col-md-3" style="display: none;">
					<label for="schedule-month">Mes</label>
					<select id="schedule-month{$FILE_ID}" class="form-control">
						<option value=""></option>
						<option value="0">Enero</option>
						<option value="1">Febrero</option>
						<option value="2">Marzo</option>
						<option value="3">Abril</option>
						<option value="4">Mayo</option>
						<option value="5">Junio</option>
						<option value="6">Julio</option>
						<option value="7">Agosto</option>
						<option value="8">Septiembre</option>
						<option value="9">Octubre</option>
						<option value="10">Noviembre</option>
						<option value="11">Diciembre</option>
					</select>
				</div>
				<div id="schedule-time-container" class="col-xs-12 col-md-3" style="display: none;">
					<label for="schedule-time">Hora</label>
					<div class="input-group bootstrap-timepicker timepicker">
						<input type="text" id="schedule-time{$FILE_ID}" class="form-control time-field"
							style="text-align: left;">
					</div>
				</div>
			</div>
			<div id="schedule-format-container" class="row wizard-input-section" style="display: none;">
				<div class="col-xs-12 col-md-12">
					<label for="schedule-format">Formato</label>
					<select id="schedule-format{$FILE_ID}" class="form-control">
						<option value="pdf">PDF</option>
						<option value="excel">Microsoft Excel</option>
						<option value="both">Ambos</option>
					</select>
				</div>
			</div>
			<div id="schedule-sendto" class="row wizard-input-section members" style="display: none;">
				<h3 class="hide-element">Destinatarios</h3>
				<div class="col-xs-12 col-md-5">
					<label for="schedule-sendto-available-members">Disponibles</label>
					<select id="schedule-sendto-available-members{$FILE_ID}" class="form-control available-members"
						multiple="multiple" style="min-height: 15em;">
						{if (!empty ($AVAILABLE_GROUPS))}
							<optgroup label="Grupos" class="groups">
								{foreach $AVAILABLE_GROUPS as $groupId => $groupName}
									<option value="group::{$groupId}">{$groupName}</option>
								{/foreach}
							</optgroup>
						{/if}
						{if (!empty ($AVAILABLE_ROLES))}
							<optgroup label="Roles" class="roles">
								{foreach $AVAILABLE_ROLES as $roleId => $roleName}
									<option value="role::{$roleId}">{$roleName}</option>
								{/foreach}
							</optgroup>
						{/if}
						{if (!empty ($AVAILABLE_ROLES))}
							<optgroup label="Roles y subordinados" class="rs">
								{foreach $AVAILABLE_ROLES as $roleId => $roleName}
									<option value="rs::{$roleId}">{$roleName}</option>
								{/foreach}
							</optgroup>
						{/if}
						{if (!empty ($AVAILABLE_USERS))}
							<optgroup label="Usuarios" class="users">
								{foreach $AVAILABLE_USERS as $userId => $userName}
									<option value="user::{$userId}">{$userName}</option>
								{/foreach}
							</optgroup>
						{/if}
					</select>
				</div>
				<div class="form-group col-xs-12 col-md-1 columns-actions">
					<div class="vertical-group">
						<button type="button" class="btn btn-primary btn-icon center-block"
							onclick="ReportWizardUtils.addMembers (this);"><i class="fa fa-angle-right"></i></button>
						<button type="button" class="btn btn-warning btn-icon center-block"
							onclick="ReportWizardUtils.removeMembers (this);"><i class="fa fa-angle-left"></i></button>
					</div>
				</div>
				<div class="col-xs-12 col-md-6">
					<label for="schedule-sendto-selected-members">Miembros</label>
					<select id="schedule-sendto-selected-members{$FILE_ID}" class="form-control selected-members"
						multiple="multiple" style="min-height: 15em;"></select>
				</div>
			</div>
		</div>
		<div class="wizard-error">
			<div class="alert alert-error">
				<strong>Error!</strong>. Se ha presentado un error al generar el informe
			</div>
		</div>
		<div class="wizard-failure">
			<div class="alert alert-error">
				<strong>Error!</strong>. Se ha presentado un error al generar el informe
			</div>
		</div>
		<div class="wizard-success">
			<div class="alert alert-success">
				<strong>Listo!</strong>. Se ha creado el informe
			</div>
			{if !isset($VIEW)}
				<a href="index.php?module=Reports&action=index" class="btn btn-primary">Cerrar</a>
			{else}
				<button class="btn btn-primary wizard-cancel wizard-close  hidden-xs hidden-sm" type="button"
					style="display: inline-block;">Cerrar</button>
			{/if}


		</div>
	</div>
	<script type="text/html" id="condition-template">
		{include file="ReportCondition.tpl" GROUP_ID='__GROUP_ID__' CONDITION_ID='__CONDITION_ID__'}
	</script>
	<script type="text/html" id="condition-group-template">
		{include file="ReportConditionGroup.tpl" GROUP_ID='__GROUP_ID__'}
	</script>
	<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.js"></script>
	<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.es.js"></script>
	<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-timepicker.min.js"></script>
	<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-wizard.js"></script>
	<script id="report-wizard-tab" data-id-modal="{$FILE_ID}" type="text/javascript"
		src="modules/Reports/reports-wizard.js"></script>
{/strip}