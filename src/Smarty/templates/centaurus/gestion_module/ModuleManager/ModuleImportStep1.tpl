{literal}
<style>
.fileUpload {
    position: relative;
    overflow: hidden;
    margin: 10px;
}
.fileUpload input.upload {
    position: absolute;
    top: 0;
    right: 0;
    margin: 0;
    padding: 0;
    font-size: 20px;
    cursor: pointer;
    opacity: 0;
    filter: alpha(opacity=0);
}
</style>
<script type="text/javascript">
function modulemanager_import_validate(form) {
	if(form.module_zipfile.value == '') {
		alert("Please select the zip file before proceeding.");
		return false;
	}
	return true;
}

jQuery(document).ready(function() {
	document.getElementById("uploadBtn").onchange = function () {
		document.getElementById("uploadFile").value = this.value;
	};
});
</script>
{/literal}

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
                	<form method="POST" action="index.php" enctype="multipart/form-data">
						<table class='tableHeading' cellpadding=5 cellspacing=0 border=0 width=100%>
						<tr>
							<td class='big' colspan=2><b>{$MOD.VTLIB_LBL_SELECT_PACKAGE_FILE}</b></td>
						</tr>
						</table>
						<table cellpadding=5 cellspacing=0 border=0 width=100%>
						<tr valign=top>
							<td class='cellLabel small'>
								<font color=red>*</font> <b>{$MOD.VTLIB_LBL_FILE_LOCATION}</b>
							</td>
							<td class='cellText small'>
								<input id="uploadFile" placeholder="Ningun archivo seleccionado" disabled="disabled" />
								<div class="fileUpload btn btn-info">
									<span>Cargar modulo</span>
									<input id="uploadBtn" type="file" class="upload" name="module_zipfile" size=50>
								</div>
								<p>
									{$MOD.VTLIB_LBL_PACKAGE_FILE_HELP}
								</p>
							</td>
						</tr>
						</table>
						<table class='tableHeading' cellpadding=5 cellspacing=0 border=0 width=100%>
						<tr valign=top>
							<td class='cellText small' colspan=2 align=right>
								<input type="hidden" name="module" value="Settings">
								<input type="hidden" name="action" value="ModuleManager">
								<input type="hidden" name="module_import" value="Step2">
								<input type="hidden" name="parenttab" value="Settings">
								
								<button class="btn btn-info" type="submit" onclick="return modulemanager_import_validate(this.form)">{$APP.LBL_IMPORT} {$APP.LBL_NEW}</button>&nbsp;
								<button class="btn btn-warning" type="submit" onclick="this.form.module_import.value='';">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
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
