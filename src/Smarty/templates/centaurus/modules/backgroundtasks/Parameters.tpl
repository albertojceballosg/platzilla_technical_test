{strip}
{$SELECTED_PARAMETER_VALUES = array()}
{foreach $PARAMETERS as $parameter}
	{assign var='availableTypes' value=$parameter->getAvailableTypes ()}
	{if (!empty ($availableTypes))}
		{php}
			usort ($_smarty_tpl->tpl_vars['availableTypes']->value, function ($typeA, $typeB) { return strcmp (getTranslatedString ($typeA, 'backgroundtasks'), getTranslatedString ($typeB, 'backgroundtasks')); });
		{/php}
	{/if}

	{assign var='parameterName' value=$parameter->getName ()}
	{assign var='showExpanded' value=$parameter->showExpanded ()}
	{if (file_exists ("{$smarty.current_dir}/actions/{$ACTION_HANDLER_CLASS}/parameters/{$parameterName}.tpl"))}
		{include file="modules/backgroundtasks/actions/{$ACTION_HANDLER_CLASS}/parameters/{$parameterName}.tpl" PARAMETER=$parameter}
	{elseif ($showExpanded)}
		{include file='modules/backgroundtasks/ParameterExpanded.tpl' PARAMETER=$parameter}
	{elseif (!empty ($availableTypes))}
		{include file='modules/backgroundtasks/ParameterWithAvailableTypes.tpl' PARAMETER=$parameter}
	{else}
		{include file='modules/backgroundtasks/ParameterWithoutAvailableTypes.tpl' PARAMETER=$parameter LIST_MODULES=$LIST_MODULES}
	{/if}
	{$SELECTED_PARAMETER_VALUES[$parameterName] = $parameter->getValueFormula ()}
{/foreach}
{/strip}