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
			<th class="text-center">{$MOD.LBL_CUSTOM_BUTTONS_TITLE}</th>
			<th class="text-center">{$MOD.LBL_CUSTOM_BUTTONS_TYPEBUTTON}</th>
			<th class="text-center">{$MOD.LBL_CUSTOM_BUTTONS_MODULE}</th>
			<th class="text-center">{$MOD.LBL_CUSTOM_BUTTONS_VIEW}</th>
			<th class="text-center">{$MOD.LBL_CUSTOM_BUTTONS_DESCRIPCION}</th>
			<th class="text-center">{$MOD.LBL_CONFIG_APPS_STATUS}</th>
			<th class="text-center">{$MOD.LBL_CONFIG_APPS_ACTION}</th>
		</tr>
	</thead>
	<tbody>
		{foreach name=configApp item=elements from=$CUSTOMBUTTONS}
		<tr>
			<td class="text-center">{$elements.custombuttonid}</td>
			<td>{$elements.label}</td>
			<td class="text-center">{$elements.typelabel}</td>
			<td>{$elements.modulelabel}</td>
			<td>{$elements.viewlabel}</td>
			<td>{$elements.description}</td>
			<td class="text-center"> <span class="label label-{if $elements.active eq 'Activa'}success{else}danger{/if}">{$elements.active}</span> </td>
			<td>
				<a class="md-trigger table-link" title="Ver" alt="Ver" href="index.php?module=Settings&action=DetailCustomButtons&record={$elements.custombuttonid}&parenttab=Settings">
					<span class="fa-stack">
						<i class="fa fa-square fa-stack-2x"></i>
						<i class="fa fa-search fa-stack-1x fa-inverse"></i>
					</span>
				</a>

				<a class="md-trigger table-link" title="Editar" alt="Editar" href="index.php?module=Settings&action=EditCustomButtons&record={$elements.custombuttonid}&parenttab=Settings">
					<span class="fa-stack">
						<i class="fa fa-square fa-stack-2x"></i>
						<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
					</span>
				</a>

				<a class="table-link danger" alt="Eliminar" title="Borrar" align="absmiddle" href="javascript:confirmdelete('index.php?module=Settings&action=CustomButtonsDelete&record={$elements.custombuttonid}&parenttab=Settings&Ajax=true')">
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


