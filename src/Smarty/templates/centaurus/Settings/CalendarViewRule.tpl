{strip}
<tr id="__ID_ROW__" class="rule __RULE_GROUP__">
	<td class="col-color">
		<input type="hidden" name="ruleids[__ID__][]" value="{if (isset ($RULE))}{$RULE.ruleid}{else}0{/if}" />
		<input type="text" name="rulebackgroundcolors[__ID__][]" value="{if (isset ($RULE))}{$RULE.backgroundcolor}{else}#FFFFFF{/if}" class="color" readonly="readonly" maxlength="6" size="6" style="background-color: {if (isset ($RULE))}{$RULE.backgroundcolor}{else}#FFFFFF{/if}; color: {if (isset ($RULE))}{$RULE.backgroundcolor}{else}#FFFFFF{/if};" placeholder="" />
	</td>
	<td class="col-constraints field-container">
		<input type="hidden" name="rulemodulenames[__ID__][]" value="{if (isset ($RULE))}{$RULE.modulename}{/if}" class="fieldmodulename" />
		<select name="rulefieldnames[__ID__][]" class="form-control fieldname" title="" onchange="CalendarUtils.setModuleNameField (this,'__ID_ROW__');">
			<option value=""></option>
{foreach $AVAILABLE_FIELDS as $moduleLabel => $fields}
			<optgroup label="{$moduleLabel}">
	{foreach $fields as $field}
				<option value="{$field.fieldname}"
						data-modulename="{$field.modulename}"
						data-uity="{$field.uitype}"
						{if (isset ($RULE)) && ($RULE.fieldname == $field.fieldname)} selected="selected"{/if}>{$field.fieldlabel}</option>
	{/foreach}
			</optgroup>
{/foreach}
		</select>
		<select name="ruleoperators[__ID__][]" class="form-control operator" title="">
			<option value=""></option>
			<option value="="{if (isset ($RULE)) && ($RULE.operator == '=')} selected="selected"{/if}>igual a</option>
			<option value="&lt;"{if (isset ($RULE)) && ($RULE.operator == '<')} selected="selected"{/if}>menor a</option>
			<option value="&lt;="{if (isset ($RULE)) && ($RULE.operator == '<=')} selected="selected"{/if}>menor o igual a</option>
			<option value="&gt;"{if (isset ($RULE)) && ($RULE.operator == '>')} selected="selected"{/if}>mayor a</option>
			<option value="=&gt;"{if (isset ($RULE)) && ($RULE.operator == '=>')} selected="selected"{/if}>mayor o igual a</option>
			<option value="!="{if (isset ($RULE)) && ($RULE.operator == '!=')} selected="selected"{/if}>diferente a</option>
		</select>
		<input type="text" name="rulevalues[__ID__][]" id="rule-input-value-__ID_ROW__"
			   value="{if (isset ($RULE))}{$RULE.value}{/if}"
			   class="form-control value" placeholder="Valor" />
		<select class="form-control hide" name="rulevalues[__ID__][]" 	id="rule-select-value-__ID_ROW__"
				disabled="disabled">
		</select>
		<select name="ruleglues[__ID__][]" class="form-control glue"{if (isset ($RULE)) || (empty ($RULE.joinrule))} disabled="disabled"{/if} title="">
			<option value="AND"{if (isset ($RULE)) && ($RULE.joinrule == 'AND')} selected="selected"{/if}>y</option>
			<option value="OR"{if (isset ($RULE)) && ($RULE.joinrule == 'OR')} selected="selected"{/if}>o</option>
		</select>
	</td>
	<td class="col-actions">
		<button type="button" class="btn btn-link" title="agregar condición a la regla" onclick="CalendarUtils.addGroupRule (this, '__ID__');">
			<i class="fa fa-plus"></i>
		</button>&nbsp;<button type="button" class="btn btn-link" title="Eliminar" onclick="CalendarUtils.deleteRule (this, '__ID_ROW__');">
			<i class="fa fa-trash-o"></i>
		</button>
	</td>
</tr>
{/strip}