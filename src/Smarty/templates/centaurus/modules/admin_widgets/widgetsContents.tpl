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
			<th class="text-center">{$MOD.LBL_WIDGETLIST_TITLE}</th>
			<th class="text-center">{$MOD.LBL_WIDGETLIST_MODULE}</th>
			<th class="text-center">{$MOD.LBL_STATUS}</th>
			<th class="text-center">{$MOD.LBL_CONFIG_APPS_ACTION}</th>
		</tr>
	</thead>
	<tbody>
		{foreach name=widgetsApp item=elements from=$WIDGETSLIST}
		<tr>
			<td class="text-center">{$elements.widgetid}</td>
			<td>{$elements.texto}</td>			
			<td>{$elements.modulelabel}</td>			
			<td class="text-center"> <span class="label label-{if $elements.estatus eq '1'}success{else}danger{/if}">{if $elements.estatus eq '1'}Activo{else}Inactivo{/if}</span> </td>
			<td>
				<a class="md-trigger table-link" title="Ver" alt="Ver" href="index.php?module={$MODULE}&action=DetailWidgets&record={$elements.widgetid}">
					<span class="fa-stack">
						<i class="fa fa-square fa-stack-2x"></i>
						<i class="fa fa-search fa-stack-1x fa-inverse"></i>
					</span>
				</a>

				<a class="md-trigger table-link" title="Editar" alt="Editar" href="index.php?module={$MODULE}&action=EditWidgets&record={$elements.widgetid}">
					<span class="fa-stack">
						<i class="fa fa-square fa-stack-2x"></i>
						<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
					</span>
				</a>

				<a class="table-link danger" alt="Eliminar" title="Borrar" align="absmiddle" href="javascript:confirmdelete('index.php?module={$MODULE}&action=WidgetsDelete&record={$elements.widgetid}')">
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


