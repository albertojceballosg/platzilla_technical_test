{strip}
{if (isset ($RELATED_LIST))}
	{assign var='relatedListActions' value=$relatedList->getActions ()}
	{assign var='relatedListLabel' value=$relatedList->getLabel ()}
	{assign var='relatedListRelatedModuleName' value=$relatedList->getRelatedModuleName ()}
	{assign var='relatedListSequence' value=$relatedList->getSequence ()}
{else}
	{assign var='relatedListActions' value=[]}
	{assign var='relatedListLabel' value=null}
	{assign var='relatedListRelatedModuleName' value=null}
	{assign var='relatedListSequence' value=null}
{/if}
<tr class="related-list" data-index="{$INDEX}">
	<td class="col-label">
		<input type="hidden" name="relatedlists[{$INDEX}][sequence]" value="{$relatedListSequence}" class="related-list-sequence" />
		<input type="text" name="relatedlists[{$INDEX}][label]" value="{$relatedListLabel}" class="form-control related-list-label" placeholder="Etiqueta" />
	</td>
	<td class="col-module-name">
{if ($relatedListRelatedModuleName|in_array:$ENTITY_MODULES_NAME) || ($relatedListRelatedModuleName eq NULL)}
		<select name="relatedlists[{$INDEX}][relatedmodulename]"
				class="form-control related-list-module-name"
				onchange="RelatedListsUtils.selectedModule(this, {$idRelatedList})"
				title="Módulo">
			<option value=""></option>
{foreach $AVAILABLE_ENTITY_MODULES as $module}
			<option value="{$module.name}"{if ($module.name == $relatedListRelatedModuleName)} selected="selected"{/if}>{$module.label}</option>
{/foreach}
		</select>
{else}
	<select name="relatedlists[{$INDEX}][relatedmodulename]" class="form-control related-list-module-name" title="Módulo">
		<option value="{$relatedListRelatedModuleName}" selected="selected">{$relatedListRelatedModuleName}</option>
	</select>
{/if}
	</td>
	<td class="col-available-for">
		<label>
			<input type="checkbox" name="relatedlists[{$INDEX}][actions][]" value="ADD" class="related-list-action related-list-action-add"{if (in_array ('ADD', $relatedListActions))} checked="checked"{/if} />
			<span>Insertar</span>
		</label>
		<label>
			<input type="checkbox" name="relatedlists[{$INDEX}][actions][]" value="SELECT" class="related-list-action related-list-action-select"{if (in_array ('SELECT', $relatedListActions))} checked="checked"{/if} />
			<span>Seleccionar</span>
		</label>
	</td>
	<td class="col-actions text-right">
		<span class="btn-icon btn-up-dummy hidden"></span>
		<button type="button" class="btn btn-primary btn-icon btn-up hidden" onclick="RelatedListsUtils.moveListUp (this);" title="Subir">
			<i class="fa fa-arrow-up"></i>
		</button>
		<span class="btn-icon btn-down-dummy hidden"></span>
		<button type="button" class="btn btn-primary btn-icon btn-down hidden" onclick="RelatedListsUtils.moveListDown (this);" title="Bajar">
			<i class="fa fa-arrow-down"></i>
		</button>
		<button type="button" class="btn btn-danger btn-icon" onclick="RelatedListsUtils.deleteList (this, {$idRelatedList})" title="Eliminar">
			<i class="fa fa-trash-o"></i>
		</button>
	</td>
</tr>
{/strip}