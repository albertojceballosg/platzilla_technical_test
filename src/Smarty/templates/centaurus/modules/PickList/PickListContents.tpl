<div id="pickListContents" class="row">
	<div class="col-lg-12">
		<table class="table table-stripped">
			<thead>
			<tr>
				<td><span>{$MOD.LBL_SELECT_PICKLIST}</span></td>
				<td style="text-align: right;">
					<select name="avail_picklists" id="allpick" class="form-control" style="font-weight: normal;width:100%;" title="">
{foreach key=fld_nam item=fld_lbl from=$ALL_LISTS}
						<option value="{$fld_nam}">{$fld_lbl|getTranslatedString:$MODULE}</option>
{/foreach}
					</select>
					<br />
					<input type="button" name="add" class="btn btn-primary btn-sm" value="{'LBL_ADD_BUTTON'|@getTranslatedString}" onclick="showAddDiv();">
					<input type="button" name="del" class="btn btn-info btn-sm" value="{'LBL_EDIT_BUTTON'|@getTranslatedString}" onclick="showEditDiv();">
					<input type="button" name="del" class="btn btn-danger btn-sm" value="{'LBL_DELETE_BUTTON'|@getTranslatedString}" onclick="showDeleteDiv();">
				</td>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td colspan="2">
					<strong>{$MOD.LBL_PICKLIST_AVAIL} {$MODULE|@getTranslatedString:$MODULE} {$MOD.LBL_FOR} &nbsp;</strong>
					<select name="pickrole" id="pickid" class="form-control" onChange="showPicklistEntries('{$MODULE}');" style="width: 30%; display: inline;" title="">
{foreach key=roleid item=role from=$ROLE_LISTS}
	{if $SEL_ROLEID eq $roleid}
						<option value="{$roleid}" selected>{$role}</option>
	{else}
						<option value="{$roleid}">{$role}</option>
	{/if}
{/foreach}
					</select>
					<br>
					<span style="color: #FF0000;">* {$MOD_PICKLIST.LBL_DISPLAYED_VALUES}</span>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<table width="100%" class="listTable" cellpadding="5" cellspacing="0">
{foreach item=picklists from=$PICKLIST_VALUES}
						<tr>
	{foreach item=picklistfields from=$picklists}
		{if $picklistfields neq ''}
							<td>
			{if $TEMP_MOD[$picklistfields.fieldlabel] neq ''}
								<b>{$TEMP_MOD[$picklistfields.fieldlabel]}</b>
			{else}
								<b>{$picklistfields.fieldlabel}</b>
			{/if}
								<input type="button" value="{$MOD_PICKLIST.LBL_ASSIGN_BUTTON}" class="btn btn-primary btn-sm" onclick="assignPicklistValues('{$MODULE}','{$picklistfields.fieldname}','{$picklistfields.fieldlabel}');">
							</td>
		{/if}
	{/foreach}
						</tr>
						<tr>
	{foreach item=picklistelements from=$picklists}
		{if $picklistelements neq ''}
							<td style="vertical-align: top;">
								<table class="table table-hover">
									<tbody>
			{foreach item=elements from=$picklistelements.value}
									<tr>
										<td class="mini-products" style="vertical-align: top;">
				{if $TEMP_MOD[$elements] neq ''}
					{$TEMP_MOD[$elements]}
				{elseif $MOD_PICKLIST[$elements] neq ''}
					{$MOD_PICKLIST[$elements]}
				{else}
					{$elements}
				{/if}
										</td>
									</tr>
			{/foreach}
									</tbody>
								</table>
							</td>
		{else}
							<td>&nbsp;</td>
		{/if}
	{/foreach}
						</tr>
{/foreach}
					</table>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
</div>