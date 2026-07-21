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
						<table class='tableHeading' cellpadding=5 cellspacing=0 border=0 width=100%>
						<tr>
							<td class='big' colspan=2><b>{$MOD.VTLIB_LBL_IMPORTING_MODULE_START}</b></td>
						</tr>
						</table>
						
						<table class='tableHeading' cellpadding=5 cellspacing=0 border=0 width=100%>
						<tr valign=top>
							<td class='cellText small'>
								{* Invoking API inside template to capture the logging details. *}
								{php}
									$__moduleimport_package = $this->_tpl_vars['MODULEIMPORT_PACKAGE'];
									$__moduleimport_package_file = $this->_tpl_vars['MODULEIMPORT_PACKAGE_FILE'];
									$__moduleimport_dir_overwrite = $this->_tpl_vars['MODULEIMPORT_DIR_OVERWRITE'];

									$__moduleimport_package->import($__moduleimport_package_file, $__moduleimport_dir_overwrite);
									unlink($__moduleimport_package_file);
								{/php}
							</td>
						</tr>
						</table>

						<table class='tableHeading' cellpadding=5 cellspacing=0 border=0 width=100%>
						<tr valign=top>
							<td class='cellText small' align=right>
								<input type="hidden" name="module" value="Settings">
								<input type="hidden" name="action" value="ModuleManager">
								<input type="hidden" name="parenttab" value="Settings">
								
								<button class="btn btn-info" name="yesbutton" type="submit" onclick="return modulemanager_import_validate(this.form)">{$APP.LBL_FINISH}</button>&nbsp;
							</td>
						</tr>
						</table>
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
