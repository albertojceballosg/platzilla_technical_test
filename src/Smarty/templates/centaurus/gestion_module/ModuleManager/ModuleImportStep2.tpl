<div id="vtlib_modulemanager" style="display:block;position:absolute;width:100%;"></div>
	<div id="email-box" class="clearfix">
		{*
		<div class="col-left-nano-content" style="float:left;width:30%;">
		 {include file='SetMenu.tpl'} 
		</div>
		*}
		<div class="col-lg-12" style="">
		
		<!--<table class="settingsSelUITopLine" border="0" cellpadding="5" cellspacing="0" width="100%">-->
		
		<table class="table" border="0" cellpadding="5" cellspacing="0" width="100%">
			<tr>
				<td rowspan="2" valign="top">
				<div class="infographic-box" style="width:30px;padding:0px;">
				<i class="fa fa-list-alt purple-bg"></i>
				</div>
				</td>
				<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
					<li class="active">{$MOD.VTLIB_LBL_MODULE_MANAGER}</li>
					<li class="active">{$APP.LBL_IMPORT}</li>
				</ol>
				</td>
			</tr>

			<tr>
				<td class="small" valign="top">{$MOD.VTLIB_LBL_MODULE_MANAGER_DESCRIPTION}</td>
			</tr>
		</table>		
			
				
		<br>
		<table border="0" cellpadding="10" cellspacing="0" width="100%">
		<tr>
			<td>
				<div id="vtlib_modulemanager_import_div">
                	<form method="POST" action="index.php">
						{if $MODULEIMPORT_FAILED neq ''}
							<table class='tableHeading' cellpadding=5 cellspacing=0 border=0 width=100%>
							<tr>
								<td class='big' colspan=2><b>{$MOD.VTlIB_LBL_IMPORT_FAILURE}</b></td>
							</tr>
							</table>
							<table cellpadding=5 cellspacing=0 border=0 width=80%>
							<tr valign=top>
								<td class='cellText small'>
									{if $MODULEIMPORT_FILE_INVALID eq "true"}
										<font color=red><b>{$MOD.VTLIB_LBL_INVALID_FILE}</b></font> {$MOD.VTLIB_LBL_INVALID_IMPORT_TRY_AGAIN}
									{else}
										<font color=red>{$MOD.VTLIB_LBL_UNABLE_TO_UPLOAD}</font> {$MOD.VTLIB_LBL_UNABLE_TO_UPLOAD2}
									{/if}
								</td>
							</tr>
							</table>
							<table class='tableHeading' cellpadding=5 cellspacing=0 border=0 width=100%>
							<tr valign=top>
								<td class='cellText small' colspan=2 align=right>
									<input type="hidden" name="module" value="Settings">
									<input type="hidden" name="action" value="ModuleManager">
									<input type="hidden" name="parenttab" value="Settings">						
									<button class="btn btn-danger" type="submit">{$APP.LBL_FINISH}</button>
								</td>
							</tr>
							</table>
						{else}
							<table class='tableHeading' cellpadding=5 cellspacing=0 border=0 width=100%>
							<tr>
								<td class='big' colspan=2><b>{$MOD.VTLIB_LBL_VERIFY_IMPORT_DETAILS}</b></td>
							</tr>
							</table>
							<table cellpadding=5 cellspacing=0 border=0 width=100%>
							<tr valign=top>
								<td class='cellLabel small' width=20%>
									<b>{$MOD.VTLIB_LBL_MODULE_NAME}</b>
								</td>
								<td class='cellText small'>
									{$MODULEIMPORT_NAME}
									{if $MODULEIMPORT_EXISTS eq 'true'} <font color=red><b>{$MOD.VTLIB_LBL_EXISTS}</b></font> {/if}
								</td>
							</tr>
							{if $MODULEIMPORT_DIR}
							<tr valign=top>
								<td class='cellLabel small' width=20%>
									<b>{$MOD.VTLIB_LBL_MODULE_DIR}</b>
								</td>
								<td class='cellText small'>
									{$MODULEIMPORT_DIR}
									{if $MODULEIMPORT_DIR_EXISTS eq 'true'} <font color=red><b>{$MOD.VTLIB_LBL_EXISTS}</b></font> 
										{* -- Avoiding File Overwrite
										 <br> Overwrite existing files? <input type="checkbox" name="module_dir_overwrite" value="true"> 
										-- *}
									{/if}
								</td>
							</tr>
							{/if}
							<tr valign=top>
								<td class='cellLabel small' width=20%>
									<b>{$MOD.VTLIB_LBL_REQ_VTIGER_VERSION}</b>
								</td>
								<td class='cellText small'>
									{$MODULEIMPORT_DEP_VTVERSION}
								</td>
							</tr>

							{assign var="need_license_agreement" value="false"}

							{if $MODULEIMPORT_LICENSE}
							{assign var="need_license_agreement" value="true"}
							<tr valign=top>
								<td class='cellLabel small' width=20%>
									<b>{$MOD.VTLIB_LBL_LICENSE}</b>
								</td>
								<td class='cellText small'>
									<textarea readonly class='small' style="background-color: #F5F5F5; border: 0; height: 150px; font: 10px 'Lucida Console', 'Courier New', Arial, sans-serif;">{$MODULEIMPORT_LICENSE}</textarea><br>
									{literal}
									<input type="checkbox" onclick="if(this.form.yesbutton){if(this.checked){this.form.yesbutton.disabled=false;}else{this.form.yesbutton.disabled=true;}}"> {/literal} {$MOD.VTLIB_LBL_LICENSE_ACCEPT_AGREEMENT}
								</td>
							</tr>
							{/if}
							</table>
							<table class='tableHeading' cellpadding=5 cellspacing=0 border=0 width=100%>
							<tr valign=top>
								<td class='cellText small' colspan=2 align=right>
									<input type="hidden" name="module" value="Settings">
									<input type="hidden" name="action" value="ModuleManager">
									<input type="hidden" name="parenttab" value="Settings">
									<input type="hidden" name="module_import_file" value="{$MODULEIMPORT_FILE}">
									<input type="hidden" name="module_import_type" value="{$MODULEIMPORT_TYPE}">
									<input type="hidden" name="module_import" value="Step3">
									<input type="hidden" name="module_import_cancel" value="false">
									
									{if $MODULEIMPORT_EXISTS eq 'true' || $MODULEIMPORT_DIR_EXISTS eq 'true'}										
										<button class="btn btn-warning" type="submit" onclick="this.form.module_import.value=''; this.form.module_import_cancel.value='true';">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
									{else}
										
										<input type="checkbox" id="modulopropio" name="modulopropio" checked="checked" value="1">
										<label for="modulopropio">
											¿Importar como módulo separado de la instancia?
										</label>
										<br/>
										{$MOD.VTLIB_LBL_PROCEED_WITH_IMPORT}
										<button class="btn btn-info" name="yesbutton" type="submit" onclick="return modulemanager_import_validate(this.form)" {if $need_license_agreement eq 'true'} disabled=true {/if}>{$MOD.LBL_YES}</button>&nbsp;
										<button class="btn btn-warning" type="submit" onclick="this.form.module_import.value=''; this.form.module_import_cancel.value='true';">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
									{/if}
								</td>
							</tr>
							</table>
						{/if}
					</form>
                </div>
			</td>
		</tr>
		</table>
		<!-- End of Display -->
		
		</td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
   </div>

        </td>
        <td valign="top"><img src="{'showPanelTopRight.gif'|@vtiger_imageurl:$THEME}"></td>
	</tr>
</table>
		</div>
   </div>

<br>
