{strip}
<style type="text/css">
{literal}
	.hideTable {
		display: none;
	}
{/literal}
</style>
<form action="index.php" method="post" name="profileform" id="form" onsubmit="VtigerJS_DialogBox.block ();">
	<input type="hidden" name="module" value="Users" />
	<input type="hidden" name="parenttab" value="Settings" />
	<input type="hidden" name="action" value="{$ACTION}" />
	<input type="hidden" name="mode" value="{$MODE}" />
	<input type="hidden" name="profileid" value="{$PROFILEID}" />
	<input type="hidden" name="profile_name" value="{$PROFILE_NAME}" />
	<input type="hidden" name="profile_description" value="{$PROFILE_DESCRIPTION}" />
	<input type="hidden" name="parent_profile" value="{$PARENTPROFILEID}" />
	<input type="hidden" name="radio_button" value="{$RADIOBUTTON}" />
	<input type="hidden" name="return_action" value="{$RETURN_ACTION}" />
	<div style="opacity: 1;" class="row">
		<div class="col-lg-12">
			<div class="row">
				<div class="col-lg-12">
					<ol class="breadcrumb">
						<li>
							<a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a>
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
									<input type="submit" class="btn btn-primary" value=" Salvar " name="save2" title="Salvar">
								</div>
							</div>
							<div class="row">
								<div class="col-lg-12 table-responsive text-right" style="border: 0 solid #ff00c3">
									<div class="layerPopup" style="left:350px;width:100%;top:300px;display:none;" id="renameProfile">
										<table class="layerHeadingULine table" border="0" cellpadding="1" cellspacing="0" width="100%">
											<tr style="cursor:move;">
												<td class="layerPopupHeading" id="renameUI" align="right" width="60%">{$APP.LBL_RENAME_PROFILE}</td>
												<td align="right" width="40%">
													<a href="javascript:fnhide('renameProfile');"><img src="{'close.gif'|@vtiger_imageurl:$THEME}" align="middle" border="0"></a>
												</td>
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
									{$GLOBAL_PRIV.0}<b>{$CMOD.LBL_VIEW_ALL}</b>
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
{include file='ProfileEditViewModulePermissions.tpl'}
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<div class="main-box no-header clearfix">
						<div class="main-box-body clearfix">
							<div class="table-responsive">
								<div class="col-lg-12">
									<span style="color: blue">*</span>{$CMOD.LBL_MANDATORY_MSG}
									<br />
									<span style="color: blue">*</span>{$CMOD.LBL_DISABLE_FIELD_MSG}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript" src="include/js/smoothscroll.js"></script>
<script type="text/javascript">
{literal}
	var Imagid_array = ['img_2', 'img_4', 'img_6', 'img_7', 'img_8', 'img_9', 'img_10', 'img_13', 'img_14', 'img_18', 'img_19', 'img_20', 'img_21', 'img_22', 'img_23', 'img_26'];
	function fnToggleVIew (obj) {
		if ($ (obj).hasClassName ('hideTable')) {
			$ (obj).removeClassName ('hideTable');
		} else {
			$ (obj).addClassName ('hideTable');
		}
	}
	function invokeview_all () {
		if ($ ('view_all_chk').checked == true) {
			for (var i = 0; i < document.profileform.elements.length; i++) {
				if (document.profileform.elements[ i ].type == 'checkbox') {
					if (document.profileform.elements[ i ].id.indexOf ('tab_chk_com_') != -1 || document.profileform.elements[ i ].id.indexOf ('tab_chk_4') != -1 || document.profileform.elements[ i ].id.indexOf ('_field_') != -1) {
						document.profileform.elements[ i ].checked = true;
					}
				}
			}
			showAllImages ();
		}
	}
	function showAllImages () {
		for (var j = 0; j < Imagid_array.length; j++) {

			if (typeof($ (Imagid_array[ j ])) != 'undefined') {
				$ (Imagid_array[ j ]).style.display = 'block';
			}
		}
	}
	function invokeedit_all () {
		if ($ ('edit_all_chk').checked == true) {
			$ ('view_all_chk').checked = true;
			for (var i = 0; i < document.profileform.elements.length; i++) {
				if (document.profileform.elements[ i ].type == 'checkbox') {
					if (document.profileform.elements[ i ].id.indexOf ('tab_chk_com_') != -1 || document.profileform.elements[ i ].id.indexOf ('tab_chk_4') != -1 || document.profileform.elements[ i ].id.indexOf ('tab_chk_1') != -1 || document.profileform.elements[ i ].id.indexOf ('_field_') != -1) {
						document.profileform.elements[ i ].checked = true;
					}
				}
			}
			showAllImages ();
		}

	}
	function unselect_edit_all () {
		$ ('edit_all_chk').checked = false;
	}
	function unselect_view_all () {
		$ ('view_all_chk').checked = false;
	}
	function unSelectView (id) {
		var createid = 'tab_chk_1_' + id;
		var deleteid = 'tab_chk_2_' + id;
		var tab_id = 'tab_chk_com_' + id;
		if ($ ('tab_chk_4_' + id).checked == false) {
			unselect_view_all ();
			unselect_edit_all ();
			$ (createid).checked = false;
			$ (deleteid).checked = false;
			$ (tab_id).checked = false;
		} else {
			var imageid = 'img_' + id;
			if (typeof($ (imageid)) != 'undefined') {
				$ (imageid).style.display = 'block';
			}
			$ ('tab_chk_com_' + id).checked = true;
		}
	}
	function unSelectCreate (id) {
		var viewid = 'tab_chk_4_' + id;
		if ($ ('tab_chk_1_' + id).checked == false) {
			unselect_edit_all ();
		} else {
			var imageid = 'img_' + id;
			viewid = 'tab_chk_4_' + id;
			if (typeof($ (imageid)) != 'undefined') {
				$ (imageid).style.display = 'block';
			}
			$ ('tab_chk_com_' + id).checked = true;
			$ (viewid).checked = true;
		}
	}
	function unSelectDelete (id) {
		if ($ ('tab_chk_2_' + id).checked == false) {
		} else {
			var imageid = 'img_' + id;
			var viewid = 'tab_chk_4_' + id;
			if (typeof($ (imageid)) != 'undefined') {
				$ (imageid).style.display = 'block';
			}
			$ ('tab_chk_com_' + id).checked = true;
			$ (viewid).checked = true;
		}

	}
	function hideTab (id) {
		var createid = 'tab_chk_1_' + id;
		var viewid = 'tab_chk_4_' + id;
		var deleteid = 'tab_chk_2_' + id;
		var imageid = 'img_' + id;
		var contid = id + '_view';
		if ($ ('tab_chk_com_' + id).checked == false) {
			unselect_view_all ();
			unselect_edit_all ();
			if (typeof($ (imageid)) != 'undefined') {
				$ (imageid).style.display = 'none';
			}
			if (typeof($ (contid)) != 'undefined') {
				$ (contid).className = 'hideTable';
			}
			if (typeof($ (createid)) != 'undefined') {
				$ (createid).checked = false;
			}
			if (typeof($ (deleteid)) != 'undefined') {
				$ (deleteid).checked = false;
			}
			if (typeof($ (viewid)) != 'undefined') {
				$ (viewid).checked = false;
			}
		} else {
			if (typeof($ (imageid)) != 'undefined') {
				$ (imageid).style.display = 'block';
			}
			if (typeof($ (createid)) != 'undefined') {
				$ (createid).checked = true;
			}
			if (typeof($ (deleteid)) != 'undefined') {
				$ (deleteid).checked = true;
			}
			if (typeof($ (viewid)) != 'undefined') {
				$ (viewid).checked = true;
			}
			var fieldid = id + '_field_';

			if (typeof($ (contid)) != 'undefined') {
				for (var i = 0; i < document.profileform.elements.length; i++) {
					if (document.profileform.elements[ i ].type == 'checkbox' && document.profileform.elements[ i ].id.indexOf (fieldid) != -1) {
						document.profileform.elements[ i ].checked = true;
					}
				}
			}
		}
	}
	function selectUnselect (oCheckbox) {
		if (oCheckbox.checked == false) {
			unselect_view_all ();
			unselect_edit_all ();
		}
	}
	function initialiseprofile () {
		var module_array = [1, 2, 4, 6, 7, 8, 9, 10, 13, 14, 15, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27];
		for (var i = 0; i < module_array.length; i++) {
			hideTab (module_array[ i ]);
		}
	}
{/literal}
</script>
{/strip}