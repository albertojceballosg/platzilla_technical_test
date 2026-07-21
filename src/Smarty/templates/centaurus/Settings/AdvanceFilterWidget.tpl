{if !$ROWAJAX}
<script language="JavaScript" type="text/javascript" src="include/js/advancefilter.js"></script>
<script>

</script>
<table class="table" id="conditiongrouptable_{$COL_INDEX}" >
	<tr id="groupfooter_{$COL_INDEX}">
		<td align="center" colspan="5">
			<input type="button" onclick="addConditionRowNew({$COL_INDEX})" class="btn btn-success btn-sm" value="Nueva Condición" class="">
		</td>
	</tr>
{/if}
{if $ROWAJAX}
	<tr id="conditioncolumn_{$COL_INDEX}_{$ROW_INDEX}" name="conditionColumn">
		<td>
			<select class="form-control" onchange="updatefOptions(this, 'fop{$val}');addRequiredElements('{$val}');" id="fcol{$val}" name="fcol{$val}" style="max-width: 140px;">
			<option value="">{'LBL_NONE'|@getTranslatedString:$MODULE}</option>
			{$COLUMNS_BLOCK}
			</select>
			<input type="text" name= "ffie{$val}" id= "ffie{$val}" value="" class="form-control" style="max-width: 140px;"/>
		</td>
		<td>
			<select onchange="addRequiredElements('{$val}');" style="width:100px;" class="form-control" id="fop{$val}" name="fop{$val}">
			{$CONDITIONS}
			</select>
		</td>
		<td >
			<div class="input-group">
				<input type="text" value="" id="fval{$val}" name="fval{$val}" class="form-control">
				<div class="input-group-addon" onclick="document.getElementById('fval{$val}').value='';return false;" title="Borrar" alt="Borrar"><i class="fa fa-eraser"></i></div>
			</div>
		</td>
		
		<td width="60px" id="columnconditionglue_{$val}">
			<select class="form-control" id="fcon{$val}" name="fcon{$val}" style="display:none;">
				<option value="and">y</option><option value="or">o</option>
			</select>
		</td>
		
		<td width="30px">
			<a href="javascript:;" onclick="deleteColumnRow('{$COL_INDEX}','{$ROW_INDEX}');">
			<img border="0" align="absmiddle" title="Borrar..." src="themes/images/delete.gif"></a>
		</td>
	</tr>
{/if}
{if !$ROWAJAX}
</table>
{/if}