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
    {assign var='taskVideo' value=$TASK->getUrlVideo ()}
	{*assign var= 'LIST_MODULES' value=null*}
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
    {assign var='taskVideo' value=null}
    {*assign var= 'LIST_MODULES' value=null*}
{/if}
<link type="text/css" rel="stylesheet" href="themes/centaurus/css/libs/bootstrap-timepicker.css" />
<link type="text/css" rel="stylesheet" href="modules/backgroundtasks/backgroundtasks.css" />
<form method="post" action="index.php" onsubmit="return BackgroundTasksUtils.validateTask (this);">
	<input type="hidden" name="module" value="backgroundtasks" />
	<input type="hidden" name="action" value="SaveTask" />
{if (isset ($RECORD))}
	<input type="hidden" name="record" value="{$RECORD}" />
{/if}
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left">
				<a href="index.php?module=backgroundtasks&action=ListView&parenttab=Settings">Tarea automátizada</a>
			</h1>
			<div class="actions pull-right">
				<button type="submit" id="wizard-save-background-task" class="btn btn-info">Guardar</button>
				<a href="index.php?module=backgroundtasks&action=ListView&parenttab=Settings" class="btn btn-warning" role="button">Cancelar</a>
			</div>
		</div>
	</div>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
	<div class="row">
		<div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
			<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
		</div>
	</div>
{/if}
	<div id="basic-section" class="main-box">
		<header class="main-box-header clearfix">
			<h2 class="pull-left">Define la tarea</h2>
		</header>
		<div class="main-box-body data-section">
{include file='modules/backgroundtasks/EditViewSectionBasic.tpl'}
		</div>
	</div>
	<div id="event-section" class="main-box">
		<header class="main-box-header clearfix">
			<h2 class="pull-left">¿Dónde y cómo ocurre?</h2>
		</header>
		<div class="main-box-body data-section">
{include file='modules/backgroundtasks/EditViewSectionEvent.tpl'}
		</div>
	</div>
	<div id="filters-section" class="main-box"{if ($taskScope != BackgroundTask::SCOPE_SYSTEM) && (empty ($taskModuleName))} style="display: none;"{/if}>
		<header class="main-box-header clearfix">
			<h2 class="pull-left">¿Bajo qué condiciones?</h2>
			<div class="action-bar pull-right">
				<button type="button" class="btn btn-success" onclick="BackgroundTasksUtils.addFilterGroup (this);" title="Agregar grupo de filtros"><i class="fa fa-plus"></i></button>
			</div>
		</header>
		<div class="main-box-body data-section">
{include file='modules/backgroundtasks/EditViewSectionFilters.tpl'}
		</div>
	</div>
	<div id="actions-section" class="main-box"{if ($taskScope != BackgroundTask::SCOPE_SYSTEM) && (empty ($taskModuleName))} style="display: none;"{/if}>
		<header class="main-box-header clearfix">
			<h2 class="pull-left">¿Qué hace la tarea?</h2>
			<div class="action-bar pull-right">
				<button type="button" class="btn btn-success" onclick="BackgroundTasksUtils.addAction (this);" title="Agregar acción"><i class="fa fa-plus"></i></button>
			</div>
		</header>
		<div class="main-box-body data-section">
{include file='modules/backgroundtasks/EditViewSectionActions.tpl'}
		</div>
	</div>
</form>
<script type="text/javascript" src="themes/centaurus/js/moment.min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="modules/backgroundtasks/backgroundtasks.js?v=1.1"></script>
<script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
{/strip}