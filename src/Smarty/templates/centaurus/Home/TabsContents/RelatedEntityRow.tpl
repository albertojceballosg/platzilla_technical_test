{strip}
{if (isset ($RELATED_ENTITY_DATA))}
	{assign var='relatedEntityId' value=$RELATED_ENTITY_DATA.id}
	{assign var='relatedEntityValue' value=$RELATED_ENTITY_DATA.value}
	{assign var='relatedModuleName' value=$RELATED_ENTITY_DATA.modulename}
{else}
	{assign var='relatedEntityId' value=null}
	{assign var='relatedEntityValue' value=null}
	{assign var='relatedModuleName' value=null}
{/if}
<tr class="related-entity-row">
	<td class="col-module-name">
		<select class="form-control module-name" title="Módulo">
			<option value=""></option>
{foreach $AVAILABLE_MODULES as $availableModule}
			<option value="{$availableModule->getName ()}"{if ($availableModule->getName () == $relatedModuleName)} selected="selected"{/if}>{$availableModule->getLabel ()}</option>
{/foreach}
		</select>
	</td>
	<td class="col-related-entity">
		<div class="input-group">
			<input type="hidden" id="relatedentityid-{$MESSAGE_ID}-0" name="relatedentityids[]" value="{$relatedEntityId}" class="data-field" />
			<input type="text" id="relatedentityid-{$MESSAGE_ID}-0-display" value="{$relatedEntityValue}" class="form-control display-field" readonly="readonly" placeholder="" />
			<div class="input-group-addon" onclick="return WebmailUtils.openRelatedEntityModal (this);"><i class="fa fa-plus-circle"></i></div>
			<div class="input-group-addon" onclick="WebmailUtils.clearRelatedEntityFields (this);"><i class="fa fa-eraser"></i></div>
		</div>
	</td>
	<td class="col-actions">
		<button type="button" class="btn btn-link" onclick="WebmailUtils.deleteRelatedEntity (this);"><i class="fa fa-trash-o"></i></button>
	</td>
</tr>
{/strip}