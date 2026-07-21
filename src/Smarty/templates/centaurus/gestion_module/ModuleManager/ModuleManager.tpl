<script language="JavaScript" type="text/javascript" src="modules/gestion_module/gestion_module.js"></script>
<script type='text/javascript'>
<!-- Add Smarty vars to module Object -->
moduleobj.modstrings={$LOAD_JS_MOD_STRINGS};
moduleobj.appstrings={$LOAD_JS_APP_STRINGS};
{literal}
function vtlib_toggleModule(module, action, type) {
	if(typeof(type) == 'undefined') type = '';

	var data = "module=gestion_module&action=gestion_moduleAjax&file=ModuleManager&module_name=" + encodeURIComponent(module) + "&" + action + "=true" + "&module_type=" + type;

	$('status').show();
	new Ajax.Request(
		'index.php',
        {queue: {position: 'end', scope: 'command'},
        	method: 'post',
            postBody: data,
            onComplete: function(response) {
				$('status').hide();
				// Reload the page to apply the effect of module setting
				window.location.href = 'index.php?module=gestion_module&action=ModuleManager&parenttab=gestion_module';
			}
		}
	);
}
{/literal}
</script>
{$DLG_ERROR}
<div id="vtlib_modulemanager" style="display:block;position:absolute;width:100%;"></div>
	<div id="email-box" class="clearfix">
		{*
		<div class="col-left-nano-content" style="float:left;width:30%;">
		 {include file='SetMenu.tpl'} 
		</div>
		*}
		<div class="col-lg-12" style="">
			<!--table class="settingsSelUITopLine" border="0" cellpadding="5" cellspacing="0" width="100%"-->
			<table class="table" border="0" cellpadding="5" cellspacing="0" width="100%">
			<tr>
				<td rowspan="2" valign="top">
				<div class="infographic-box" style="width:30px;padding:0px;">
				<i class="fa fa-list-alt purple-bg"></i>
				</div>
				</td>
				<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li><a href="index.php?module=gestion_module&action=ModuleManager&parenttab=gestion_module">{$MOD.LBL_MY_MODULES}</a></li>
					<li class="active">{$MOD.VTLIB_LBL_MODULE_MANAGER_CUSTOMMOD}</li>
				</ol>
				</td>
			</tr>

			<tr>
				<td class="small" valign="top">{$MOD.VTLIB_LBL_MODULE_MANAGER_DESCRIPTION}</td>
			</tr>
			</table>		
			<table border="0" cellpadding="10" cellspacing="0" width="100%">
			<tr>
				<td>
					<div id="vtlib_modulemanager_list">
						{include file="gestion_module/ModuleManager/ModuleManagerAjax.tpl"}
					</div>	
				
					<table border="0" cellpadding="5" cellspacing="0" width="100%">
					<tr>
						<td class="small" align="right" nowrap="nowrap"><a href="#top">{$MOD.LBL_SCROLL}</a></td>
					</tr>
					</table>
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
   </div>

<br>
