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

<script language="JAVASCRIPT" type="text/javascript" src="include/js/smoothscroll.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/menu.js"></script>
<script language="JAVASCRIPT" type="text/javascript" src="include/scriptaculous/scriptaculous.js"></script>
<script language="JAVASCRIPT" type="text/javascript" src="include/js/json.js"></script>
<script language="JAVASCRIPT" type="text/javascript" src="modules/PickList/DependencyPicklist.js"></script>
<br>

<div class="col-lg-12">				
	<div class="row">
		<div class="col-lg-12">
			<ol class="breadcrumb">
			<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
			<li>{$MOD_PICKLIST.LBL_PICKLIST_DEPENDENCY_SETUP}</li>
		</ol>
			
		</div>
	</div>
</div>


<div class="col-lg-12">
							
	<div class="row">
		<div class="main-box no-header clearfix" style="">
			<table align="center" border="0" cellpadding="0" cellspacing="0" width="98%" class="table">
				<table border=0 cellspacing=0 cellpadding=10 width=100% class="table">
		<tr>
			<td valign=top>			
				<div id="picklist_datas">
					{if $SUBMODE eq 'editdependency'}
						{include file='modules/PickList/PickListDependencyContents.tpl'}
					{else}
						{include file='modules/PickList/PickListDependencyList.tpl'}
					{/if}
				</div>

				
			</td>
		</tr>
		</table>
			</table>
		</div>
	</div>
</div>

