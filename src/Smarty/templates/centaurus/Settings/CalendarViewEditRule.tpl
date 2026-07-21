{strip}
    {math equation= rand() assign= "idARule"}
<tr id="{$idARule}" class="rule {if $cicle}rule-group{/if}">
	<td class="col-color">
		<input type="hidden" name="ruleids[{$INDEX_KEY}][]" value="{if (isset ($RULE))}{$RULE.ruleid}{else}0{/if}" />
		<input type="text" name="rulebackgroundcolors[{$INDEX_KEY}][]" value="{if (isset ($RULE))}{$RULE.backgroundcolor}{else}#FFFFFF{/if}" class="color  {if !$cicle}hide{/if}" readonly="readonly" maxlength="6" size="6" style="background-color: {if (isset ($RULE))}{$RULE.backgroundcolor}{else}#FFFFFF{/if}; color: {if (isset ($RULE))}{$RULE.backgroundcolor}{else}#FFFFFF{/if};" placeholder="" />
	</td>
	<td class="col-constraints field-container">
		<input type="hidden" name="rulemodulenames[{$INDEX_KEY}][]" value="{if (isset ($RULE))}{$RULE.modulename}{/if}" class="fieldmodulename" />
		<select name="rulefieldnames[{$INDEX_KEY}][]" class="form-control fieldname" title="" onchange="CalendarUtils.setModuleNameField (this, '{$idARule}');">
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
		<select name="ruleoperators[{$INDEX_KEY}][]" class="form-control operator" title="">
			<option value=""></option>
			<option value="="{if (isset ($RULE)) && ($RULE.operator == '=')} selected="selected"{/if}>igual a</option>
			<option value="&lt;"{if (isset ($RULE)) && ($RULE.operator == '<')} selected="selected"{/if}>menor a</option>
			<option value="&lt;="{if (isset ($RULE)) && ($RULE.operator == '<=')} selected="selected"{/if}>menor o igual a</option>
			<option value="&gt;"{if (isset ($RULE)) && ($RULE.operator == '>')} selected="selected"{/if}>mayor a</option>
			<option value="=&gt;"{if (isset ($RULE)) && ($RULE.operator == '=>')} selected="selected"{/if}>mayor o igual a</option>
			<option value="!="{if (isset ($RULE)) && ($RULE.operator == '!=')} selected="selected"{/if}>diferente a</option>
		</select>
		<input type="text" name="rulevalues[{$INDEX_KEY}][]" value="{if !in_array($RULE.uitype, array('15', '53', '56'))}{$RULE.value}{else}''{/if}"
			   id="rule-input-value-{$idARule}"
			   {if in_array($RULE.uitype, array('15', '53', '56'))} disabled="disabled"{/if}
			   class="form-control value {if in_array($RULE.uitype, array('15', '53', '56'))}hide{/if}" placeholder="Valor" />
		{if in_array($RULE.uitype, array('5', '6', '70'))}
			<script type="text/javascript">jQuery ('#rule-input-value-{$idARule}').datepicker ({ format: "yyyy-mm-dd", language: 'es', weekStart: 1 });</script>
		{/if}
		<select class="form-control {if !in_array($RULE.uitype, array('15', '53', '56'))}hide{/if}" name="rulevalues[{$INDEX_KEY}][]"  id="rule-select-value-{$idARule}"
                {if !in_array($RULE.uitype, array('15', '53', '56'))} disabled="disabled"{/if}>
            {if in_array($RULE.uitype, array('15', '53', '56'))}{$RULE.value}{else}''{/if}
		</select>
		<!--  {$RULE.joinrule}     -->
		<select name="ruleglues[{$INDEX_KEY}][]" class="form-control glue"{if $RULE.joinrule eq NULL} disabled="disabled"{/if} title="">
			<option value="AND"{if (isset ($RULE)) && ($RULE.joinrule == 'AND')} selected="selected"{/if}>y</option>
			<option value="OR"{if (isset ($RULE)) && ($RULE.joinrule == 'OR')} selected="selected"{/if}>o</option>
		</select>
	</td>
	<td class="col-actions">
		<button type="button" class="btn btn-link {if $KEY neq $totalRules}hide{/if}" title="agregar condición a la regla" onclick="CalendarUtils.addGroupRule (this, '{$INDEX_KEY}');">
			<i class="fa fa-plus"></i>
		</button>&nbsp;<button type="button" class="btn btn-link" title="Eliminar" onclick="CalendarUtils.deleteRule (this, '{$idARule}');">
			<i class="fa fa-trash-o"></i>
		</button>
	</td>
</tr>
	<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
	<script type="text/javascript" src="modules/Settings/calendar-view.js"></script>
{/strip}