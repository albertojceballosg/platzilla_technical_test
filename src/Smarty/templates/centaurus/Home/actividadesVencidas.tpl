{*
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/ *}

<div class="main-box clearfix">
	<div class="main-box-header clearfix">
		<h2>Tareas vencidas: {$ACTIVITIES_VENCIDAS.noofactivities}</h2>
	</div>
	<div class="main-box-body clearfix">
		{if $ACTIVITIES_VENCIDAS.noofactivities == 0}
			<div class="componentName">No se encontraron datos</div><br>
		{else}
			<ul class="pz-box-list">
				{foreach key=keyA item=activity from=$ACTIVITIES_VENCIDAS.Entries}
					<li>
						<a href="">{$activity.0}</a>
					</li>
				{/foreach}			
			</ul>
		{/if}
		<div class="clearfix">
			<a href="index.php?action=index&amp;module=Calendar&amp;parenttab=" class="btn btn-primary pull-right">Ver calendario</a>
		</div>
	</div>
</div>

