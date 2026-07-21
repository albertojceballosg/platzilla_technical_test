{strip}
{if (isset ($TASK))}
	{assign var='taskActions' value=$TASK->getActions ()}
	{assign var='taskCategory' value=$TASK->getCategory ()}
	{assign var='taskDescription' value=$TASK->getDescription ()}
	{assign var='taskEvent' value=$TASK->getEvent ()}
	{assign var='taskEventInstant' value=$TASK->getEventInstant ()}
	{assign var='taskFilterGroups' value=$TASK->getFilterGroups ()}
	{assign var='taskFrequency' value=$TASK->getFrequency ()}
	{assign var='taskModuleName' value=$TASK->getModuleName ()}
	{assign var='taskName' value=$TASK->getName ()}
	{assign var='taskScope' value=$TASK->getScope ()}
	{assign var='taskStatus' value=$TASK->getStatus ()}
	{assign var='taskTrigger' value=$TASK->getTrigger ()}
{else}
	{assign var='taskActions' value=null}
	{assign var='taskCategory' value=null}
	{assign var='taskDescription' value=null}
	{assign var='taskEvent' value=null}
	{assign var='taskEventInstant' value=null}
	{assign var='taskFilterGroups' value=null}
	{assign var='taskFrequency' value=null}
	{assign var='taskModuleName' value=null}
	{assign var='taskName' value=null}
	{assign var='taskScope' value=BackgroundTask::SCOPE_USER}
	{assign var='taskStatus' value=null}
	{assign var='taskTrigger' value=null}
{/if}
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/bootstrap-wizard.css" />
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/section/bootstrap-wizard_custom.css" />
<link rel="stylesheet" type="text/css" href="modules/backgroundtasks/backgroundtasks.css" />
<script type="text/html" id="background-task-wizard-template">
<div id="background-task-wizard" class="wizard" data-title="Crear tarea">
	<h1>Tarea automátizada</h1>
	<div class="wizard-card" data-cardname="start">
		<input type="hidden" name="module" value="backgroundtasks" />
		<input type="hidden" name="action" value="SaveTask" />
		<input type="hidden" name="record" value="" />
		<input type="hidden" name="datasource" value="wizard" />
		<input type="hidden" name="Ajax" value="true" />
		<h3 class="hide-element">¿Qué quieres?</h3>
		<h4 class="hidden-md hidden-lg">¿Qué quieres?</h4>
		<div class="row wizard-input-section data-section">
			<div id="new-task-options" class="form-group col-xs-12 field-container wizard-actions" style="display: none;">
				<div class="radio-group">
					<label><input id="wizard-action-create-task" type="radio" name="wizardaction" value="EditView" checked="checked" disabled="disabled" onchange="BackgroundTasksUtils.setWizardAction (this);">Crear una tarea vacía</label>
				</div>
				<div class="radio-group">
					<label><input id="wizard-action-duplicate-task-from-pattern" type="radio" name="wizardaction" value="DuplicateTask" disabled="disabled" onchange="BackgroundTasksUtils.setWizardAction (this);">Crear una tarea basada en un patrón</label>
				</div>
			</div>
			<div id="existing-task-options" class="form-group col-xs-12 field-container wizard-actions" style="display: none;">
				<div class="radio-group">
					<label><input id="wizard-action-edit-task" type="radio" name="wizardaction" value="EditView" checked="checked" disabled="disabled" onchange="BackgroundTasksUtils.setWizardAction (this);">Modificar la tarea seleccionada</label>
				</div>
				<div class="radio-group">
					<label><input id="wizard-action-duplicate-task" type="radio" name="wizardaction" value="DuplicateTask" disabled="disabled" onchange="BackgroundTasksUtils.setWizardAction (this);">Duplicar la tarea seleccionada</label>
				</div>
			</div>
			<div id="task-pattern" class="row" style="display: none;">
				<p class="col-xs-12">¿Cuál es el patrón?</p>
{if (!empty ($AVAILABLE_CATEGORIES))}
				<div class="form-group col-xs-12 field-container" style="margin-bottom: 5px;">
					<select id="category" class="form-control" title="Filtrar por categoría..." onchange="BackgroundTasksUtils.filterPatternByCategory (this);" disabled="disabled">
						<option value="">Filtrar por categoría...</option>
	{foreach $AVAILABLE_CATEGORIES as $category}
						<option value="{$category.categoryname}">{$category.categoryname}</option>
	{/foreach}
					</select>
				</div>
{/if}
				<div class="form-group col-xs-12 field-container">
					<select id="task-pattern-id" class="form-control" title="Selecciona el patrón" disabled="disabled">
{if (!empty ($TASKS))}
	{assign var='tasksScopes' value=array_keys($TASKS)}
	{foreach $tasksScopes as $scope}
						<optgroup label="{if ($scope == BackgroundTask::SCOPE_SYSTEM)}Sistema{else}Usuario{/if}">
		{foreach $TASKS[$scope] as $task}
							<option value="{$task->getId ()}">{$task->getName ()}</option>
		{/foreach}
						</optgroup>
	{/foreach}
{/if}
					</select>
				</div>
			</div>
			<div class="form-group col-xs-12 field-container">
				<div class="checkbox">
					<label><input type="checkbox" name="wizardlocation" value="1" onchange="BackgroundTasksUtils.setLocation (this);">Cambiar asistente por formulario</label>
					<button type="button" class="btn btn-primary" onclick="BackgroundTasksUtils.openTaskInNewWindow (this);" style="display: none; margin-left: 1em;">Abrir</button>
				</div>
			</div>
		</div>
	</div>
	<div id="basic-section" class="wizard-card" data-cardname="basic">
		<h3 class="hide-element">Define la tarea</h3>
		<h4 class="hidden-md hidden-lg">Define la tarea</h4>
		<div class="wizard-input-section data-section"></div>
	</div>
	<div id="event-section" class="wizard-card" data-cardname="event">
		<h3 class="hide-element">¿Dónde y cómo ocurre?</h3>
		<h4 class="hidden-md hidden-lg">¿Dónde y cómo ocurre?</h4>
		<div class="wizard-input-section data-section"></div>
	</div>
	<div id="filters-section" class="wizard-card" data-cardname="filters">
		<h3 class="hide-element">¿Bajo qué condiciones?</h3>
		<div class="row">
			<h4 class="pull-left">Condiciones</h4>
			<div class="action-bar pull-right">
				<button type="button" class="btn btn-success" onclick="BackgroundTasksUtils.addFilterGroup (this);" title="Agregar grupo de filtros"><i class="fa fa-plus"></i></button>
			</div>
		</div>
		<div class="wizard-input-section data-section"></div>
	</div>
	<div id="actions-section" class="wizard-card" data-cardname="actions">
		<h3 class="hide-element">¿Qué hace la tarea?</h3>
		<h4 class="hidden-md hidden-lg">¿Qué hace la tarea?</h4>
		<div class="row">
			<h4 class="pull-left">Acciones</h4>
			<div class="action-bar pull-right">
				<button type="button" class="btn btn-success" onclick="BackgroundTasksUtils.addAction (this);" title="Agregar acción"><i class="fa fa-plus"></i></button>
			</div>
		</div>
		<div class="wizard-input-section data-section"></div>
	</div>
	<div class="wizard-failure text-center">
		<h4><strong style="color: #880000;">Error!</strong>: Se ha presentado un error al guardar la tarea</h4>
		<p class="message"></p>
	</div>
	<div class="wizard-loading text-center">
		<h4><strong>Por favor espera</strong></h4>
		<p>Estamos guardando la tarea. Por favor espera unos instantes y por favor no cierres esta ventana</p>
		<img src="themes/images/loading.gif" class="img-responsive" style="display: inline-block;" />
	</div>
	<div class="wizard-success text-center">
		<h4><strong style="color: #008800;">Listo!</strong>: Se ha guardado la tarea</h4>
		<button type="button" class="btn btn-default" style="margin-left: 5px;" onclick="BackgroundTasksUtils.closeTaskWizard ();">Terminar</button>
	</div>
</div>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-wizard.js"></script>
{/strip}