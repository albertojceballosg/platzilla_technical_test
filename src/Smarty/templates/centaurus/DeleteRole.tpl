{strip}
<script type="text/javascript" src="include/js/smoothscroll.js"></script>
<script type="text/javascript">
{literal}
	function openPopup (del_roleid) {
		window.open ("index.php?module=Users&action=UsersAjax&file=RolePopup&maskid=" + del_roleid + "&parenttab=Settings", "roles_popup_window", "height=425,width=640,toolbar=no,menubar=no,dependent=yes,resizable =no");
	}
{/literal}
</script>
<form name="newProfileForm" action="index.php" onsubmit="if (roleDeleteValidate ()) {ldelim} VtigerJS_DialogBox.block (); {rdelim} else {ldelim} return false; {rdelim}">
	<input type="hidden" name="module" value="Users" />
	<input type="hidden" name="action" value="DeleteRole" />
	<input type="hidden" name="delete_role_id" value="{$ROLEID}" />
	<div style="opacity: 1;" class="row">
		<div class="col-lg-12">
			<div class="row">
				<div class="col-lg-12">
					<ol class="breadcrumb">
						<li>
							<a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a>
						</li>
						<li class="active">
							<span><a href="index.php?module=Settings&action=listroles&parenttab=Settings">{$CMOD.LBL_ROLES}</a></span>
						</li>
					</ol>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<div class="main-box no-header clearfix">
						<div class="main-box-body clearfix">
							<div class="row">
								<div class="col-md-6">
									<b>{$MOD.LBL_DELETE} {$ROLE_NAME}</b>
								</div>
								<div class="col-md-6 text-right">
									<input type="submit" name="Delete" class="btn btn-primary crmbutton small save" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  " />
									<input type="button" class="btn btn-default" name="cancel" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" onClick="window.history.back ();" />
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<div class="main-box clearfix" style="">
						<header class="main-box-header clearfix">
							<h2>{$MOD.LBL_DELETE} {$ROLE_NAME}</h2>
						</header>
						<div class="main-box-body clearfix">
							<div class="form-group">
								<label for="role_name1">{$CMOD.LBL_ROLE_TO_BE_DELETED}</label>
								<input type="text" name="role_name1" id="role_name1" value="{$ROLENAME}" class="form-control" readonly="readonly" />
							</div>
							<label for="role_name">{$CMOD.LBL_TRANSFER_USER_ROLE}</label>
							<div class="input-group" style="width: 100%;">
								<input type="text" name="role_name" id="role_name" value="" class="form-control" readonly="readonly" />
								<div class="input-group-addon" onclick="openPopup ('{$ROLEID}');">
									<i class="fa fa-plus-circle"></i>
								</div>
								<div class="input-group-addon" onClick="document.forms.newProfileForm.role_name.value=''; document.forms.newProfileForm.user_role.value=''; return false;">
									<i class="fa fa-eraser"></i>
								</div>
								<input type="hidden" name="user_role" id="user_role" value="" />
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script>
{literal}
	function roleDeleteValidate () {
		if (document.getElementById ('role_name').value == '') {
{/literal}
			alert ('{$APP.SPECIFY_ROLE_INFO}');
			return false;
{literal}
		}
		return true;
	}
{/literal}
</script>
{/strip}