{strip}
<div align="center">
	<table width="100%" cellspacing="1" cellpadding="3" border="0" class="lvt small">
		<tbody>
		<tr>
			<td class="lvtCol">
				<a class="listFormHeaderLinks">Módulos</a>
			</td>
			<td class="lvtCol">Acción</td>
		</tr>
{if (isset ($MODULES_DATA)) && (is_array ($MODULES_DATA)) && (count ($MODULES_DATA) > 0)}
	{foreach $MODULES_DATA as $moduleData}
		<tr bgcolor="white" onmouseout="this.className='lvtColData';" onmouseover="this.className='lvtColDataHover';" class="lvtColData">
			<td {* onmouseout="vtlib_listview.trigger ('cell.onmouseout', $(this))" onmouseover="vtlib_listview.trigger ('cell.onmouseover', $(this))" *}>{$moduleData.label}</td>
			<td {* onmouseout="vtlib_listview.trigger ('cell.onmouseout', $(this))" onmouseover="vtlib_listview.trigger ('cell.onmouseover', $(this))" *}>
				<input type="checkbox" name="tabid[]" value="{$moduleData.name}" placeholder="" />
			</td>
		</tr>
	{/foreach}
{/if}
		</tbody>
	</table>
</div>
{/strip}