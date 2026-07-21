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
<script language="JavaScript" type="text/javascript" src="modules/Settings/Settings.js"></script>

<div id="email-box" class="clearfix">
	<div class="col-lg-12">	
			<table class="table" border="0" cellpadding="5" cellspacing="0" width="100%">
			<tr>
				<td rowspan="2" valign="top">
					<div class="infographic-box" style="width:30px;padding:0px;">
					<i class="fa  fa-file-code-o emerald-bg"></i>
					</div>
				</td>
				<td class="heading2" valign="bottom">
					<ol class="breadcrumb">				
						<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
						<li>{$MOD.CONFIG_XML} </li>				
					</ol>
				</td>
			</tr>
			<tr>
				<td class="small" colspan="3" valign="top">{$MOD.LBL_CONFIG_XML_DESCRIPTION}</td>
			</tr>
			</table>
	</div>
	<br/>
	<br/>
	<form class="form-horizontal" role="form" name='ExportForm'>
	<input type="hidden" value="" name="module" id="module">
	<input type="hidden" value="Export" name="action">
	<input type="hidden" value="" name="idstring">
	<input type="hidden" value="" name="id_cur_str">
	<input id="modexp" type="hidden" name="modexp" value="XML">
	<input type="hidden" value="withoutsearch" name="search_type" id="search_type">
	<input type="hidden" value="all" name="export_data" id="export_data">
		<div class="row">	
			<div class="col-lg-12">
				<div class="main-box clearfix">
					<div class="main-box-body clearfix">
						<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table-responsive">
		                	<tr>
		                    	<th><h2>{$MOD.CONFIG_XML_TITLE}</h2></th>
		                    		<td align="right">&nbsp;</td>
		                 	 </tr>
					  	</table>
					  	<br/>
					  	<div id="xmlcontents">
							<table class="table" border="0" cellpadding="5" cellspacing="0" width="100%">
								<tr>
									<td>{$MOD.LBL_CONFIG_XML_SELECT}&nbsp;&nbsp;&nbsp;
										<select name="selmodule" id="selmodule">
											{foreach name=configXML item=elements from=$MODULESFREE}
												<option value="{$elements.name}">{$elements.tablabel}</option>
											{/foreach}
										</select>

									</td>
								</tr>
							</table>
							
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">		
						<div class="main-box clearfix">
							<header class="main-box-header clearfix">
								<div class="pull-right">
									<input title="{$MOD.CONFIG_XML_TITLE}" accessKey="{$MOD.CONFIG_XML_TITLE}" class="btn btn-success btn-sm" onclick="return selectedRecordsXML('{$MODULE}','{$CATEGORY}', this.form)" href="javascript:void(0)" name="export_link" type="button" name="button" value="  {$MOD.CONFIG_XML_TITLE}  ">
								</div>
							</header>
						</div>
					</div>
				</div>	

				<br/>			
			</div>
		</div>
	</form>
</div>

<script type="text/javascript">
	{literal}	
		function selectedRecordsXML(moduleBase,category,form)
		{
			var module = jQuery('#selmodule').val();
			jQuery('#module').val(module);
			record_export(module,'',form,'');


		}

	{/literal}

</script>
