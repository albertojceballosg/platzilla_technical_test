{strip}
<div id="actions" class="panel-group actions">
{if (!empty ($taskActions))}
	{foreach $taskActions as $key => $action}
		{include file='modules/backgroundtasks/Action.tpl' TASK_SCOPE=$taskScope ACTION=$action TASK_ACTIONS=$taskActions NUM_ACTION=$key}
	{/foreach}
{/if}
</div>
{/strip}