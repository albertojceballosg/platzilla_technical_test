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

<script type="text/javascript" src="include/js/general.js"></script>

<div class="row">
	<div class="col-lg-12">
		<h1>{$APP.LBL_EXPORT} {$MODULELABEL}</h1>
	</div>
</div>

<div class="row">
	<div class="col-lg-12">
		<div class="main-box">
			<header class="main-box-header clearfix">
				&nbsp;
			</header>
			
			<div class="main-box-body clearfix">
				<form class="form-horizontal" role="form">
					<input type="hidden" name="module" value="{$MODULE}">
					<input type="hidden" name="action" value="Export">
					<input type="hidden" name="idstring" value="{$IDSTRING}">
					<input type="hidden" name="id_cur_str" value="{$IDCURSTR}">
					<input id="modexp" type="hidden" value="{$MOD_EXPORT}" name="modexp">
					
					<div class="form-group">
						<div class="col-lg-offset-2 col-lg-10">
							{$APP.LBL_SEARCH_CRITERIA_RECORDS}:
						</div>
					</div>
					<div class="form-group">
						<label for="search_type" class="col-lg-6 control-label">{$APP.LBL_WITH_SEARCH}</label>
						<div class="col-lg-6">
							<div class="radio">
								<input type="radio" name="search_type" id="search_type" value="includesearch" { if $SESSION_WHERE neq ''}checked{/if}>
								<label for="search_type">&nbsp;</label>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="search_type2" class="col-lg-6 control-label">{$APP.LBL_WITHOUT_SEARCH}</label>
						<div class="col-lg-6">
							<div class="radio">
								<input type="radio" name="search_type" id="search_type2" value="withoutsearch" { if $SESSION_WHERE eq ''}checked{/if}>
								<label for="search_type2">&nbsp;</label>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-lg-offset-2 col-lg-10">
							{$APP.LBL_EXPORT_RECORDS}:
						</div>
					</div>
					<div class="form-group">
						<label for="export_data" class="col-lg-6 control-label">{$APP.LBL_ALL_DATA}</label>
						<div class="col-lg-6">
							<div class="radio">
								<input type="radio" name="export_data" id="export_data" value="all" { if $IDSTRING eq ''}checked{/if}>
								<label for="export_data">&nbsp;</label>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="export_data2" class="col-lg-6 control-label">{$APP.LBL_DATA_IN_CURRENT_PAGE}</label>
						<div class="col-lg-6">
							<div class="radio">
								<input type="radio" name="export_data" id="export_data2" value="currentpage">
								<label for="export_data2">&nbsp;</label>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="export_data3" class="col-lg-6 control-label">{$APP.LBL_ONLY_SELECTED_RECORDS}</label>
						<div class="col-lg-6">
							<div class="radio">
								<input type="radio" name="export_data" id="export_data3" value="selecteddata" { if $IDSTRING neq ''}checked{/if}>
								<label for="export_data3">&nbsp;</label>
							</div>
						</div>
					</div>
					<div class="form-group" id="not_search" style="position:absolute;display:none;width:400px;height:25px;"></div>
					<div class="form-group">
						<div class="col-lg-offset-4 col-lg-8">
							<input type="button" name="{$APP.LBL_EXPORT}" value="{$APP.LBL_EXPORT} {$MODULELABEL}" class="btn btn-success" onclick="record_export('{$MODULELABEL}','{$CATEGORY}',this.form,'{$smarty.request.idstring}')"/>&nbsp;&nbsp;
							<input type="button" name="{$APP.LBL_CANCEL_BUTTON_LABEL}" value=" {$APP.LBL_CANCEL_BUTTON_LABEL} " class="btn btn-warning" onclick="window.history.back()" />
						</div>
					</div>
				</form>
			</div>								
		</div>
	</div>	
</div>
{*
<table align="center" border="0" cellpadding="0" cellspacing="0" width="98%">
	<tr>
   		<td valign="top"><img src="{'showPanelTopLeft.gif'|@vtiger_imageurl:$THEME}" /></td>
   		<td class="showPanelBg" valign="top" width="100%">
   			<table  cellpadding="0" cellspacing="0" width="100%" border=0>
    				<tr>
 					<td width="50%" valign=top>
						<form  name="Export_Records"  method="POST" onsubmit="VtigerJS_DialogBox.block();">
							<input type="hidden" name="module" value="{$MODULE}">
							<input type="hidden" name="action" value="Export">
							<input type="hidden" name="idstring" value="{$IDSTRING}">
							<input type="hidden" name="id_cur_str" value="{$IDCURSTR}">
							<table align="center" cellpadding="15" cellspacing="0" width="85%" class="mailClient importLeadUI small" border="0">
								<tr>
									<td colspan="2" valign="middle" align="left" class="mailClientBg  genHeaderSmall">{$MODULELABEL} >> {$APP.LBL_EXPORT} </td>
									<br>
								</tr>
								<tr>
  									<td border="0" cellpadding="5" cellspacing="0" width="50%">
	 									<table>
			   								<tr>
			       									<td colspan="2" align="left" valign="top" style="padding-left:40px;">
		    	       										<span class="genHeaderSmall">{$APP.LBL_SEARCH_CRITERIA_RECORDS}:</span>
												</td>
			   								</tr>
		  	   								<tr>
												{ if $SESSION_WHERE neq ''}
												<td align="right" valign="top" width="50%" class=small>{$APP.LBL_WITH_SEARCH}</td>
												<td  align="left" valign="top" width="5%" class=small>
													<input type="radio" name="search_type" checked value="includesearch">
												</td>
												{else}
												<td align="right" valign="top" width="50%" class=small>{$APP.LBL_WITH_SEARCH}</td>
												<td  align="left" valign="top" width="5%" class=small>
													<input type="radio" name="search_type"  value="includesearch">
												</td>
												{/if}
			   								</tr>
											<tr>
												{if $SESSION_WHERE eq ''}
												<td align="right" valign="top" width="50%" class=small>{$APP.LBL_WITHOUT_SEARCH}</td>	
												<td align="left" valign="top" width="5%" class=small>
	                 										<input type="radio" name="search_type" checked value="withoutsearch">
												</td>
												{else}
												<td align="right" valign="top" width="50%" class=small>{$APP.LBL_WITHOUT_SEARCH}</td>	
												<td align="left" valign="top" width="5%" class=small>
	                 										<input type="radio" name="search_type" value="withoutsearch">
												</td>
												{/if}
			   								</tr>
			   								<tr>
												<td colspan="2" align="left" valign="top" style="padding-left:40px;">
													<span class="genHeaderSmall">{$APP.LBL_EXPORT_RECORDS}:</span>
												</td>
			   								</tr>
			   								<tr>
												{if   $IDSTRING eq ''}
												<td align="right" valign="top" width="50%" class=small>{$APP.LBL_ALL_DATA}</td>
												<td align="left" valign="top" width="5%" class=small>
													<input type="radio" name="export_data" checked value="all">
												</td>
												{else}
												<td align="right" valign="top" width="50%" class=small>{$APP.LBL_ALL_DATA}</td>
												<td align="left" valign="top" width="5%" class=small>
													<input type="radio" name="export_data"  value="all">
												</td>
												{/if}
			   								</tr>
			   								<tr>
			        								<td align="right" valign="top" width="50%" class=small >{$APP.LBL_DATA_IN_CURRENT_PAGE}</td>
												<td align="left" valign="top" width="5%" class=small>
													<input type="radio" name="export_data" value="currentpage">
												</td>
			   								</tr>
			   								<tr>
												{if  $IDSTRING neq ''}
		   	       									<td align="right" valign="top" width="50%" class=small >{$APP.LBL_ONLY_SELECTED_RECORDS}</td>
		   										<td align="left" valign="top" width="5%" class=small>
		   											<input type="radio" name="export_data" checked value="selecteddata">
												</td>
												{else}
												<td align="right" valign="top" width="50%" class=small >{$APP.LBL_ONLY_SELECTED_RECORDS}</td>
		   										<td align="left" valign="top" width="5%" class=small>
		   											<input type="radio" name="export_data"  value="selecteddata">
												</td>
												{/if}
		   									</tr>
										</table>
									</td>
									<td border="0" cellpadding="5" cellspacing="0" width="50%">
										<table >
											<tr>		
												<td><div id="not_search" style="position:absolute;display:none;width:400px;height:25px;"></div></td>
											</tr>
										</table>
									</td>	
								</tr>
								<tr>
									<td align="center" colspan="2" border=0 cellspacing=0 cellpadding=5 width=98% class="layerPopupTransport">	
										<input type="button" name="{$APP.LBL_EXPORT}" value="{$APP.LBL_EXPORT} {$MODULELABEL} " class="crmbutton small create" onclick="record_export('{$MODULELABEL}','{$CATEGORY}',this.form,'{$smarty.request.idstring}')"/>&nbsp;&nbsp;
                								<input type="button" name="{$APP.LBL_CANCEL_BUTTON_LABEL}" value=" {$APP.LBL_CANCEL_BUTTON_LABEL} " class="crmbutton small cancel" onclick="window.history.back()" />
									</td>
								</tr>
							</table>
						</form>
					</td>
				</tr>
			</table>
		</td>
		<td valign="top"><img src="{'showPanelTopRight.gif'|@vtiger_imageurl:$THEME}" /></td>
	</tr>
</table>

*}