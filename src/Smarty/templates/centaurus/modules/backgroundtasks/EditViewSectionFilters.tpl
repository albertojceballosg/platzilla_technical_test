{strip}
<div class="row filter-groups">
{if (!empty ($taskFilterGroups))}
	{foreach $taskFilterGroups as $group}
		{include file='modules/backgroundtasks/FilterGroup.tpl' GROUP=$group}
	{/foreach}
{/if}
</div>
{/strip}