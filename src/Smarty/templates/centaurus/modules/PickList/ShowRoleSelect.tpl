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
<b>{$MOD.LBL_SELECT_ROLES}</b><br>
<!-- [ TT11270 ] Resolución de fallas Setting Editor de Listas Desplegables - Jesus A.- 10/08/2016 - Se igualan los estilos centaurus -->
<select multiple id="roleselect" align="center" name="roleselect" class="form-control" style="overflow:auto;">
	{foreach item=rolename key=roleid from=$ROLES}
		<option value="{$roleid}">{$rolename}</option>
	{/foreach}
</select>
