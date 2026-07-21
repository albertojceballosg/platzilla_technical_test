{strip}
<style type="text/css">
{literal}
	.hideTable {
		display: none;
	}
{/literal}
</style>
<script type="text/javascript" src="include/js/smoothscroll.js"></script>
<script type="text/javascript">
{literal}
	function UpdateProfile () {
		var prof_name,
			prof_desc;
		if (default_charset.toLowerCase () == 'utf-8') {
			prof_name = $ ('profile_name').value;
			prof_desc = $ ('description').value;
		} else {
			prof_name = escapeAll ($ ('profile_name').value);
			prof_desc = escapeAll ($ ('description').value);
		}

		if (prof_name == '') {
			$ ('profile_name').focus ();
			alert ({/literal}"{$APP.PROFILENAME_CANNOT_BE_EMPTY}"{literal});
		} else {
			var urlstring = "module=Users&action=UsersAjax&file=RenameProfile&profileid="+ {/literal}{$PROFILEID}{literal} + "&profilename=" + prof_name + "&description=" + prof_desc;
			new Ajax.Request (
				'index.php',
				{
					queue:      { position: 'end', scope: 'command' },
					method:     'post',
					postBody:   urlstring,
					onComplete: function () {
						$ ('renameProfile').style.display = "none";
						window.location.reload ();
						alert ({/literal}"{$APP.PROFILE_DETAILS_UPDATED}"{literal});
					}
				}
			);
		}
	}
</script>
{/literal}
<div style="opacity: 1;" class="row">
	<div class="col-lg-12">
		<div class="row">
			<div class="col-lg-12">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS} </a>
					</li>
					<li class="active">
						<a href="index.php?module=Settings&action=ListProfiles&parenttab=Settings">{$CMOD.LBL_PROFILE_PRIVILEGES}</a>
					</li>
					<li>
						<b>{$CMOD.LBL_VIEWING} "{$PROFILE_NAME}"</b>
					</li>
				</ol>
				<h1>Perfil</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<div class="main-box no-header clearfix">
					<div class="main-box-body clearfix">
						<div class="row">
							<div class="col-md-6">
								<b> {$CMOD.LBL_DEFINE_PRIV_FOR} &lt;{$PROFILE_NAME}&gt; </b>
								<br />
								{$CMOD.LBL_USE_OPTION_TO_SET_PRIV}
							</div>
							<div class="col-md-6 text-right">
								<form method="post" name="new" id="form" onsubmit="VtigerJS_DialogBox.block();">
									<input type="button" value="{$APP.LBL_RENAMEPROFILE_BUTTON_LABEL}" title="{$APP.LBL_RENAMEPROFILE_BUTTON_LABEL}" class="btn btn-primary" name="rename_profile" onClick="show('renameProfile');" />
									&nbsp;
									<input type="submit" value="{$APP.LBL_EDIT_BUTTON_LABEL}" title="{$APP.LBL_EDIT_BUTTON_LABEL}" class="btn btn-primary" name="edit" />
									<input type="hidden" name="module" value="Settings" />
									<input type="hidden" name="action" value="profilePrivileges" />
									<input type="hidden" name="parenttab" value="Settings" />
									<input type="hidden" name="return_action" value="profilePrivileges" />
									<input type="hidden" name="mode" value="edit" />
									<input type="hidden" name="profileid" value="{$PROFILEID}" />
								</form>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-12 table-responsive text-right" style="border:0 solid #ff00c3">
								<div class="layerPopup" style="left: 350px; width: 100%; top: 300px; display: none;" id="renameProfile">
									<table class="layerHeadingULine table" border="0" cellpadding="1" cellspacing="0" width="100%">
										<tr style="cursor:move;">
											<td class="layerPopupHeading" id="renameUI" align="right" width="60%">{$APP.LBL_RENAME_PROFILE}</td>
											<td align="right" width="40%"><a href="javascript:fnhide('renameProfile');"><img src="{'close.gif'|@vtiger_imageurl:$THEME}" align="middle" border="0"></a></td>
										</tr>
									</table>
									<table class=" table table-bordered" border="0" cellpadding="5" cellspacing="0" width="95%">
										<tr>
											<td class="mini-products">
												<table cellspacing="0" align="center" bgcolor="white" border="0" cellpadding="5" width="100%">
													<tr>
														<td align="right" width="25%" style="padding-right:10px;" nowrap>
															<b>{$APP.LBL_PROFILE_NAME} :</b>
														</td>
														<td align="left" width="75%" style="padding-right:10px;">
															<input id="profile_name" name="profile_name" class="txtBox" value="{$PROFILE_NAME}" type="text" placeholder="" />
														</td>
													</tr>
													<tr>
														<td class="mini-products" align="right" width="25%" style="padding-right:10px;" nowrap>
															<b>{$APP.LBL_DESCRIPTION} :</b>
														</td>
														<td class="mini-products" align="left" width="75%" style="padding-right:10px;">
															<textarea name="description" id="description" class="txtBox" placeholder="">{$PROFILE_DESCRIPTION}</textarea>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
									<table class="layerPopupTransport" border="0" cellpadding="5" cellspacing="0" width="100%">
										<tr>
											<td align="center">
												<input name="save" value="{$APP.LBL_UPDATE}" class="crmbutton small save" onclick="UpdateProfile();" type="button" title="{$APP.LBL_UPDATE}" />
												&nbsp;
												<input name="cancel" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" class="crmbutton small save" onclick="fnhide('renameProfile');" type="button" title="{$APP.LBL_CANCEL_BUTTON_LABEL}" />
												&nbsp;
											</td>
										</tr>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<div class="main-box no-header clearfix">
					<div class="main-box-body clearfix">
						<div class="col-lg-12">
							<b>{$CMOD.LBL_SUPER_USER_PRIV}</b>
						</div>
						<div class="row">
							<div class="col-md-6">
								{$GLOBAL_PRIV.0} <b>{$CMOD.LBL_VIEW_ALL}</b>
								<br />
								{$CMOD.LBL_ALLOW} "{$PROFILE_NAME}" {$CMOD.LBL_MESG_VIEW}
							</div>
							<div class="col-md-6 ">
								{$GLOBAL_PRIV.1}<b>{$CMOD.LBL_EDIT_ALL}</b>
								<br />
								{$CMOD.LBL_ALLOW} "{$PROFILE_NAME}" {$CMOD.LBL_MESG_EDIT}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">
				<h2>{$CMOD.LBL_SET_PRIV_FOR_EACH_MODULE}</h2>
			</header>
			<div class="main-box-body clearfix">
				<div class="panel-group accordion" id="accordion">
					{include file="ProfileDetailViewModulePermissions.tpl"}
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
{literal}
	function fnToggleVIew (obj) {
		if ($ (obj).hasClassName ('hideTable')) {
			$ (obj).removeClassName ('hideTable');
		} else {
			$ (obj).addClassName ('hideTable');
		}
	}
	//for move RenameProfile
	var Handle = document.getElementById ("renameUI");
	var Root = document.getElementById ("renameProfile");
	Drag.init (Handle, Root);
{/literal}
</script>
{/strip}