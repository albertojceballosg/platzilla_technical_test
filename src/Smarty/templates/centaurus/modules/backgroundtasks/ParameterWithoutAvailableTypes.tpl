{strip}
	{*$PARAMETER|var_dump*}
{assign var='defaultOptions' value=$PARAMETER->getDefaultOptions ()}
{assign var='isMandatory' value=$PARAMETER->isMandatory ()}
{assign var='refreshOnChanges' value=$PARAMETER->refreshOnChanges ()}
{assign var='valueFormula' value=$PARAMETER->getValueFormula ()}
<div class="col-xs-12 col-md-6 parameter{if ($isMandatory)} mandatory{/if}">
	<div class="row">
		<label for="{$parameterName}-{$ACTION_SEQUENCE}" class="col-xs-12">{$MOD[$parameterName]}:{if ($isMandatory)} <span class="required">*</span>{/if}</label>
		<div class="col-xs-12 form-group">
{if (!empty ($defaultOptions))}
			<select id="{$parameterName}-{$ACTION_SEQUENCE}" name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}]" class="form-control parametervalue"{if ($refreshOnChanges)} onchange="BackgroundTasksUtils.refreshFields (this);"{/if}>
				<option value=""></option>
	{foreach $defaultOptions as $optionValue => $optionData}
		{if (!empty ($optionData['attributes']))}
			{assign var='optionAttributes' value=array()}
			{foreach $optionData['attributes'] as $attributeName => $attributeValue}
				{array_push ($optionAttributes, "data-{$attributeName}=\"{$attributeValue|escape: 'html'}\"")}
			{/foreach}
		{else}
			{assign var='optionAttributes' value=null}
		{/if}
				<option value="{$optionValue|escape: 'html' }"
                        {if (!empty ($optionAttributes))} {join(' ', $optionAttributes)}{/if}
                        {if ($optionValue == $valueFormula)} selected="selected"
                            {$LIST_MODULES[$valueFormula] = {$optionData['label']|escape: 'html'}}
                        {/if}
                >{$optionData['label']|escape: 'html'}</option>
	{/foreach}
			</select>
{else}
			<input type="text" name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}]" class="form-control parametervalue" placeholder="" value="{$valueFormula}"{if ($refreshOnChanges)} onchange="BackgroundTasksUtils.refreshFields (this);"{/if} />
{/if}
		</div>
	</div>
</div>
{/strip}