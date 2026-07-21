{strip}
{if (isset ($ACTION))}
	{assign var='actionHandlerClass' value=$ACTION->getHandlerClass ()}
	{assign var='actionName' value=$ACTION->getName ()}
	{assign var='actionParameters' value=$ACTION->getParameters ()}
	{assign var='actionScope' value=$ACTION->getScope ()}
	{assign var='actionSequence' value=$ACTION->getSequence ()}
	{assign var='actionType' value=$ACTION->getType ()}
	{assign var='actionLabel' value=$MOD[$actionType]}
	{assign var='numAction' value="$NUM_ACTION"}
{else}
	{assign var='actionHandlerClass' value=null}
	{assign var='actionName' value=null}
	{assign var='actionParameters' value=null}
	{assign var='actionSequence' value='__ACTION-ID__'}
	{assign var='actionScope' value=BackgroundTask::SCOPE_USER}
	{assign var='actionType' value=null}
	{assign var='actionLabel' value='Sin tipo'}
    {assign var='numAction' value=-1}
{/if}
<div class="panel">
	<div class="panel-heading" style="position: relative;">
		<a data-toggle="collapse" data-parent="#actions" href="#action-{$actionSequence}">
			<h4 class="panel-title">
				<span class="actionname">{if (!empty ($actionName))}{$actionName}{else}Nueva acción{/if}</span> (<span class="actiontype">{$actionLabel}</span>)
			</h4>
		</a>
		<button type="button" class="btn btn-default" onclick="BackgroundTasksUtils.deleteAction (this);">
			<i class="fa fa-trash-o"></i>
		</button>
	</div>
	<div id="action-{$actionSequence}" class="panel-collapse collapse in action" data-id="{$actionSequence}">
		<div class="row">
			<div class="form-group col-xs-12 col-md-6">
				<label for="actionname-{$actionSequence}">Nombre: <span class="required">*</span></label>
				<input type="text" id="actionname-{$actionSequence}" name="actions[{$actionSequence}][actionname]" class="form-control actionname" value="{if (!empty ($actionName))}{$actionName}{/if}" onchange="BackgroundTasksUtils.updatePanelTitle (this);" />
			</div>
			<div class="form-group col-xs-12 col-md-6">
				<label for="actiontype-{$actionSequence}">Tipo: <span class="required">*</span></label>
				<select id="actiontype-{$actionSequence}" name="actions[{$actionSequence}][actiontype]" class="form-control actiontype" onchange="BackgroundTasksUtils.setParameters (this); BackgroundTasksUtils.updatePanelTitle (this);">
					<option value=""></option>
						{foreach $AVAILABLE_ACTIONS as $availableAction}
							{assign var='availableActionScope' value = $availableAction->getScope ()}
							{assign var='availableActionType' value = $availableAction->getType ()}
											<option value="{$availableActionType}" {if ($actionType == $availableActionType)} selected="selected"{/if} data-scope="{$availableActionScope}"{if ($actionScope != $availableActionScope)} style="display: none;"{/if}>{$MOD[$availableActionType]}</option>
						{/foreach}
				</select>
			</div>
		</div>
		<div class="row parameters">
			{if (isset ($ACTION))}
				{if (file_exists ("{$smarty.current_dir}/actions/{$actionHandlerClass}/Parameters.tpl"))}
					{include file="modules/backgroundtasks/actions/{$actionHandlerClass}/Parameters.tpl" TASK_SCOPE=$taskScope ACTION_HANDLER_CLASS=$actionHandlerClass ACTION_SEQUENCE=$actionSequence PARAMETERS=$actionParameters}
				{else}
					{include file='modules/backgroundtasks/Parameters.tpl' TASK_SCOPE=$taskScope ACTION_HANDLER_CLASS=$actionHandlerClass ACTION_SEQUENCE=$actionSequence PARAMETERS=$actionParameters}
				{/if}
			{/if}
		</div>
	</div>
</div>
{/strip}