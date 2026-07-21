{strip}
<style type="text/css">
.wizard-cards label {
	font-size: 13px;
}
</style>
<link type="text/css" rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css" />
<link type="text/css" rel="stylesheet" href="themes/centaurus/css/libs/bootstrap-timepicker.css" />
<link type="text/css" href="themes/centaurus/css/libs/bootstrap-wizard.css" rel="stylesheet" />
<link type="text/css" href="themes/centaurus/css/compiled/section/bootstrap-wizard_custom.css" rel="stylesheet">
<script type="text/html" id="calendar-task-wizard-template">
<div id="create-task-wizard" class="wizard">
	<h1 id="wizard-task-title">Crear Tarea</h1>
	<div class="wizard-card" data-cardname="basic">
		<h3 class="hide-element">Información básica</h3>
		<div class="wizard-input-section">
			<div class="form-group">
				<label for="activitytype">Tipo de Actividad (<span style="color: #FF0000;">*</span>)</label>
				<select id="activitytype" name="activitytype" class="form-control">
					<option value="Call">Llamada</option>
					<option value="Meeting">Reunión</option>
					<option value="Activity">Actividad</option>
				</select>
			</div>
			<div class="form-group">
				<label for="subject">Asunto (<span style="color: #FF0000;">*</span>)</label>
				<input type="text" id="subject" class="form-control" />
			</div>
		</div>
	</div>
	<div class="wizard-card" data-cardname="description">
		<h3 class="hide-element">Descripción</h3>
		<div class="wizard-input-section">
			<div class="form-group">
				<label for="description">Descripción</label>
				<textarea id="description" name="description" class="form-control"></textarea>
			</div>
			<div class="form-group">
				<label for="location">Lugar</label>
				<input type="text" id="location" name="location" class="form-control" />
			</div>
		</div>
	</div>
	<div class="wizard-card" data-cardname="priority">
		<h3 class="hide-element">Prioridad</h3>
		<div class="wizard-input-section">
			<div class="form-group">
				<label for="eventstatus">Estado (<span style="color: #FF0000;">*</span>)</label>
				<select id="eventstatus" name="eventstatus" class="form-control">
					<option value="Planned">Planeado</option>
					<option value="Not Held">Pendiente</option>
					<option value="Held">Realizada</option>
				</select>
			</div>
			<div class="form-group">
				<label for="taskImport">Importancia (<span style="color: #FF0000;">*</span>)</label>
				<select id="taskImport" name="taskImport" class="form-control">
					<option value="HIGH">Alto</option>
                    {*<option value="Medium">Medio</option>*}
					<option value="LOW">Bajo</option>
				</select>
			</div>
			<div class="form-group">
				<label for="taskpriority">Prioridad (<span style="color: #FF0000;">*</span>)</label>
				<select id="taskpriority" name="taskpriority" class="form-control">
					<option value="High">Alto</option>
					{*<option value="Medium">Medio</option>*}
					<option value="Low">Bajo</option>
				</select>
			</div>
			<div class="form-group">
				<label for="assigntype">Asignado a (<span style="color: #FF0000;">*</span>)&nbsp;</label>
				<input type="radio" name="assigntype" value="U" class="assigntype" checked="checked" placeholder="" />&nbsp;{$APP.LBL_USER}&nbsp;
{if (!empty ($AVAILABLE_GROUPS))}
				<input type="radio" name="assigntype" value="T" class="assigntype" placeholder="" />&nbsp;{$APP.LBL_GROUP}
{/if}
				<select id="assigned_user_id" name="assigned_user_id" class="form-control" title="">
{foreach $AVAILABLE_USERS as $userId => $userFullName}
					<option value="{$userId}"{if ($CURRENT_USER_ID == $userId)} selected="selected"{/if}>{$userFullName}</option>
{/foreach}
				</select>
{if (!empty ($AVAILABLE_GROUPS))}
				<select id="assigned_group_id" name="assigned_group_id" class="form-control" style="display: none;" title="">
	{foreach $AVAILABLE_GROUPS as $groupId => $groupName}
					<option value="{$groupId}">{$groupName}</option>
	{/foreach}
				</select>
{/if}
			</div>
		</div>
	</div>
	<div class="wizard-card" data-cardname="dates">
		<h3 class="hide-element">Fechas</h3>
		<div class="wizard-input-section">
			<div class="form-group">
				<label for="startdate">Fecha y hora de inicio (<span style="color: #FF0000;">*</span>)</label>
				<div class="row">
					<div class="col-xs-6">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							<input type="text" id="startdate" name="startdate" value="{date('Y-m-d')}" class="form-control date" readonly="readonly" />
						</div>
					</div>
					<div class="col-xs-6">
						<div class="input-group bootstrap-timepicker timepicker">
							<span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
							<input type="text" id="starttime" name="starttime" value="{date('H:i:s')}" class="form-control time" placeholder="" />
						</div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label for="enddate">Fecha y hora de vencimiento (<span style="color: #FF0000;">*</span>)</label>
				<div class="row">
					<div class="col-xs-6">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							<input type="text" id="enddate" name="enddate" value="{date('Y-m-d')}" class="form-control date" readonly="readonly" />
						</div>
					</div>
					<div class="col-xs-6">
						<div class="input-group bootstrap-timepicker timepicker">
							<span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
							<input type="text" id="endtime" name="endtime" value="{date('H:i:s')}" class="form-control time" placeholder="" />
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="wizard-card" data-cardname="share">
		<div class="form-group">
			<label for="categoryid">Grupo de tareas</label>
			<select id="categoryid" name="categoryid" class="form-control">
                {foreach $CATEGORIES as $id => $name}
					<option value="{$id}">{$name}</option>
                {/foreach}
			</select>
		</div>
		<h3 class="hide-element">Invitar</h3>
		<div class="wizard-input-section">
			<p>Invitar usuarios</p>
			<div class="row">
				<div class="form-group col-xs-6">
					<input type="button" value="Agregar >>" class="btn btn-primary btn-sm add-invitees" style="width: 100%" />
					<select id="availableusers" name="availableusers" class="form-control" multiple="multiple" title="" style="height: 8em;">
{foreach $AVAILABLE_USERS as $userId => $userFullName}
						<option value="{$userId}">{$userFullName}</option>
{/foreach}
					</select>
				</div>
				<div class="form-group col-xs-6">
					<input type="button" value="<< Eliminar " class="btn btn-danger btn-sm remove-invitees" style="width: 100%" />
					<select id="selectedusers" name="selectedusers" class="form-control" multiple="multiple" title="" style="height: 8em;"></select>
				</div>
			</div>
		</div>
	</div>
	<div class="wizard-card" data-cardname="related-entities">
		<h3 class="hide-element">Entidades relacionadas</h3>
		<div class="wizard-input-section">
			<p>Agregar entidades relacionadas</p>
			<div class="table-responsive">
				<table class="table" style="margin-bottom: 0;">
					<thead>
					<tr>
						<th style="width: 15em;">{$MOD.LBL_NAME_ENTITY}</th>
						<th>{$MOD.LBL_ENTITY}</th>
						<th style="width: 5em;">Acciones</th>
					</tr>
					</thead>
					<tbody></tbody>
					<tfoot>
					<tr>
						<td colspan="3" class="text-center">
							<button type="button" class="btn btn-link add-related-entity-button"><i class="fa fa-plus"></i></button>
						</td>
					</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>
	<div class="wizard-error">
		<div class="alert alert-error">
			<strong>Error!</strong> ¡Imposible guardar la actividad, por favor contactar el administrador!
		</div>
	</div>
	<div class="wizard-failure">
		<div class="alert alert-error">
			<strong>Error!</strong>¡Imposible guardar la actividad, por favor contactar el administrador!
		</div>
	</div>
	<div class="wizard-success">
		<div class="alert alert-success">
			<strong>Listo!</strong> La tarea ha sido creada
		</div>
		<a class="btn btn-default btn-sm close-wizard">Salir</a>
	</div>
</div>
</script>
<script type="text/html" id="calendar-task-related-entity-template">
<tr class="related-record">
	<td>
		<select class="form-control modulename" title="">
			<option value=""></option>
{if (!empty ($RELATED_MODULES))}
	{foreach $RELATED_MODULES as $relatedModule}
			<option value="{$relatedModule.name}">{$relatedModule.tablabel}</option>
	{/foreach}
{/if}
		</select>
	</td>
	<td>
		<div class="form-group field-container" style="margin-bottom: 0;">
			<div class="input-group" style="width: 100%;">
				<input id="relatedcrmid-__ID__" name="relatedcrmids[]" type="hidden" value="" class="for-filter data-field">
				<input id="relatedcrmid-__ID___display" readonly="readonly" type="text" class="form-control placeholderStyle input-readonly b-right display-field" value="" placeholder="">
				<div class="input-group-addon select-related-entity-button"><i class="fa fa-plus-circle"></i></div>
				<div class="input-group-addon clear-related-entity-button"><i class="fa fa-eraser"></i></div>
			</div>
		</div>
	</td>
	<td>
		<button type="button" class="btn btn-link delete-related-entity-button"><i class="fa fa-trash-o"></i></button>
	</td>
</tr>
</script>
<script type="text/javascript" src="themes/centaurus/js/moment.min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-wizard.js"></script>
	{if $MODULE eq 'Calendar'}
		<script type="text/javascript" src="modules/Calendar/calendar-wizard_copy.js?v=1.2"></script>
	{else}
		<script type="text/javascript" src="modules/Calendar/calendar-wizard.js?v=1.2"></script>
	{/if}
{/strip}