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
<table class="table table-striped table-hover">
	<thead>
		<tr>
			<th class="text-center">#</th>
			<th class="text-left">{$MOD.LBL_CONFIG_APPS_CODE}</th>
			<th class="text-left">{$MOD.LBL_CONFIG_APPS_NAME}</th>
			<th class="text-left">{$MOD.LBL_CONFIG_APPS_DESCRIPTION_LIST}</th>
			<th class="text-left">{$MOD.LBL_CONFIG_APPS_STATUS}</th>
			<th class="text-center">{$MOD.CONFIG_APPS}</th>
			<th>{$MOD.LBL_CONFIG_APPS_ACTION}</th>
		</tr>
	</thead>
	<tbody>
		{foreach name=configApp item=elements from=$CONFIGCATAPPLICATION}
		<tr>
			<td class="text-center">{$smarty.foreach.configApp.iteration}</td>
			<td>{$elements.code}</td>
			<td>{$elements.name}</td>
			<td>{$elements.description}</td>
			<td>{$elements.status}</td>
			<td>

				{foreach name=apps item=app from=$elements.appsAsociadas}
					{$app.app_name} <br>
				{/foreach}

			</td>
			<td>
				<a class="md-trigger table-link" title="Editar" alt="Editar" href="index.php?module=gestion_app&action=EditCatApps&record={$elements.catappid}&parenttab=gestion_app">
					<span class="fa-stack">
						<i class="fa fa-square fa-stack-2x"></i>
						<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
					</span>
				</a>

				<a class="table-link danger" alt="Eliminar" title="Borrar" align="absmiddle" href="javascript:confirmdelete('index.php?module=gestion_app&action=CatAppDelete&record={$elements.catappid}&parenttab=gestion_app')">
					<span class="fa-stack">
						<i class="fa fa-square fa-stack-2x"></i>
						<i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>
					</span>
				</a>
			</td>
		</tr>
		{/foreach}
	</tbody>
</table>