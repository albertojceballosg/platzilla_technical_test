{strip}
<header class="main-box-header clearfix">
	<div class="row">
		<div class="col-xs-12 col-md-10">
{if (!empty ($AVAILABLE_CATEGORIES))}
			<select class="form-control" title="Filtrar por categoría..." style="display: inline-block;" onchange="BackgroundTasksUtils.filterByCategory (this);">
				<option value=""{if (empty ($SELECTED_CATEGORY))} selected="selected"{/if}>Todas</option>
	{foreach $AVAILABLE_CATEGORIES as $category}
				<option value="{$category.categoryname}"{if ($SELECTED_CATEGORY == $category.categoryname)} selected="selected"{/if}>Categoría: {$category.categoryname}</option>
	{/foreach}
			</select>
{/if}
		</div>
		<div class="col-xs-12 col-md-2 text-right">
			<button class="btn btn-primary" onclick="BackgroundTasksUtils.openTaskWizard ();"><i class="fa fa-plus-circle"></i> Crear tarea</button>
		</div>
	</div>
</header>
<div class="main-box-body clearfix" id="ListViewContents">
	<div class="table-responsive">
		<table class="table table-striped table-hover">
			<thead>
			<tr>
				<th class="col-name"><b>Nombre</b></th>
				<th class="col-trigger"><b>Ejecutada por</b></th>
				<th class="col-module"><b>Módulo</b></th>
				<th class="col-status"><b>Status</b></th>
				<th class="col-actions">Acciones</th>
			</tr>
			</thead>
			<tbody>
{if (!empty ($DATA)) }
	{foreach $DATA as $task}
		{assign var='taskCategory' value=$task->getCategory ()}
		{assign var='taskDescription' value=$task->getDescription ()}
		{assign var='taskEvent' value=$task->getEvent ()}
		{assign var='taskEventInstant' value=$task->getEventInstant ()}
		{assign var='taskFrequency' value=$task->getFrequency ()}
		{assign var='taskId' value=$task->getId ()}
		{assign var='taskIsProtected' value=$task->isProtected ()}
		{assign var='taskModuleLabel' value=$task->getModuleName ()|getTranslatedString: $task->getModuleName () }
		{assign var='taskName' value=$task->getName ()}
		{assign var='taskStatus' value=$task->getStatus ()}
		{assign var='taskTrigger' value=$task->getTrigger ()}
			<tr class="task-row" data-category="{$taskCategory}" data-id="{$taskId}" data-name="{$taskName}" data-description="{$taskDescription}">
				<td class="col-name">
					<p style="margin: 0;">{$taskName}</p>
					<p style="font-size: 0.9em; font-style: italic; margin: 0;">{$taskDescription}</p>
				</td>
				<td class="col-trigger">
					<p style="margin: 0;">{$MOD[$taskTrigger]}</p>
		{if ($taskTrigger == BackgroundTask::TRIGGER_EVENT)}
					<p style="font-size: 0.9em; font-style: italic; margin: 0;">{$MOD[$taskEventInstant]} de {$MOD[$taskEvent]|lower}</p>
		{elseif ($taskTrigger == BackgroundTask::TRIGGER_DAILY_SCHEDULE)}
					<p style="font-size: 0.9em; font-style: italic; margin: 0;">Diariamente a las {gmdate('H:i:s', $taskFrequency)}</p>
		{elseif ($taskTrigger == BackgroundTask::TRIGGER_TIMED_SCHEDULE)}
					<p style="font-size: 0.9em; font-style: italic; margin: 0;">Cada {if (!empty ($taskFrequency))}{$taskFrequency} segundos{else}vez que se ejecute el despachador de tareas{/if}</p>
		{/if}
				</td>
				<td class="col-module">{$taskModuleLabel}</td>
				<td class="col-status">{$MOD[$taskStatus]}</td>
				<td class="col-actions">
					<ul class="actions">
		{if ($taskStatus == BackgroundTask::STATUS_DISABLED) }
						<li class="action">
							<form method="post" action="index.php">
								<input type="hidden" name="module" value="backgroundtasks" />
								<input type="hidden" name="action" value="EnableTask" />
								<input type="hidden" name="record" value="{$taskId}" />
								<input type="hidden" name="Ajax" value="true" />
								<button type="submit" class="btn btn-success" title="Habilitar"><i class="fa fa-check"></i></button>
							</form>
						</li>
		{else}
						<li class="action">
							<form method="post" action="index.php"{if ($IS_INSTANCE) && ($taskIsProtected)} onsubmit="return confirm ('Esta tarea forma parte de la funcionalidad de una aplicación instalada. ¿Estás seguro que quieres desactivarla?');"{/if}>
								<input type="hidden" name="module" value="backgroundtasks" />
								<input type="hidden" name="action" value="DisableTask" />
								<input type="hidden" name="record" value="{$taskId}" />
								<input type="hidden" name="Ajax" value="true" />
								<button type="submit" class="btn btn-warning" title="Deshabilitar"><i class="fa fa-ban"></i></button>
							</form>
						</li>
		{/if}
						<li class="action">
							<a href="index.php?module=backgroundtasks&action=LogView&record={$taskId}&parenttab=Settings" class="btn btn-default" title="Registro de eventos"><i class="fa fa-search"></i></a>
						</li>
						<li class="action">
							<button class="btn btn-primary" title="Editar" onclick="BackgroundTasksUtils.openTaskWizard ('{$taskId}');"><i class="fa fa-pencil"></i></button>
						</li>
						<li class="action">
		{if (!$IS_INSTANCE) || (!$taskIsProtected)}
							<form method="post" action="index.php" onsubmit="return BackgroundTasksUtils.deleteTask ('{$taskId}', '{$taskName}');">
								<input type="hidden" name="module" value="backgroundtasks" />
								<input type="hidden" name="action" value="DeleteTask" />
								<input type="hidden" name="record" value="{$taskId}" />
								<input type="hidden" name="Ajax" value="true" />
								<button class="btn btn-danger" type="submit" title="Eliminar"><i class="fa fa-trash-o"></i></button>
							</form>
		{/if}
						</li>
					</ul>
				</td>
			</tr>
	{/foreach}
{else}
			<tr>
				<td colspan="5" class="text-center">No hay tareas registradas</td>
			</tr>
{/if}
			</tbody>
		</table>
	</div>
</div>
{/strip}