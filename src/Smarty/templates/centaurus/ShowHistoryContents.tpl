{*<!--

/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/

-->*}


<table class="table">
	<tbody>
		<tr>
			<td>
				{if $LIST_ENTRIES neq ''}
					{$RECORD_COUNTS}
				{/if}
			</td>
			<td class="text-right">
				{$NAVIGATION}
			</td>
		</tr>
	</tbody>
</table>





<table class="table table-striped table-hover">

	<thead>
		<tr>
			{foreach item=header from=$LIST_HEADER}
				<th>{$header}</th>
			{/foreach}
		</tr>
	</thead>


	<tbody>
		
		{foreach item=entity key=entity_id from=$LIST_ENTRIES}
			<tr>
			{foreach item=data from=$entity}	
				{if $data neq "0000-00-00 00:00:00"}
					<td>{$data}</td>
				{else}
					<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;---</td>
				{/if} 
			{/foreach}

		{foreachelse}
			<tr>
				<td colspan="5" height="100px" align="center"><b><font size="6px">{$MOD.LBL_NO_DATA}</font></b>
				</td>
			</tr>
		{/foreach}

	</tbody>
</table>


