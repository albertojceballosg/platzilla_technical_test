{strip}
<form action="index.php" method="post" name="profileform" id="form" onsubmit="VtigerJS_DialogBox.block();">
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
						<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
						<li class="active">
							<a href="index.php?module=Settings&action=ListProfiles&parenttab=Settings">{$CMOD.LBL_USERS}</a>
						</li>
						<li>
							<b>{$CMOD.LBL_VIEWING} "{$PROFILE_NAME}"</b>
						</li>
					</ol>
					<h1>Usuario</h1>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<div class="main-box no-header clearfix">
						<div class="main-box-body clearfix">
							<div class="row">
								<div class="col-md-6">
									<b> {$CMOD.LBL_DEFINE_PRIV_FOR} &lt;{$PROFILE_NAME}&gt; </b><br>
									{$CMOD.LBL_USE_OPTION_TO_SET_PRIV}
								</div>
								<div class="col-md-6 text-right">
									<a href="index.php?action=profilePrivilegesInstances&module=Settings&mode=view&parenttab=Settings&profileid={$PROFILEID}" class="btn btn-warning">Cancelar</a>
									<input type="submit" class="btn btn-primary" value=" Salvar " name="save2" title="Salvar" />
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
{include file='ProfileEditViewModulePermissions.tpl'}
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<div class="main-box no-header clearfix">
						<div class="main-box-body clearfix">
							<div class="table-responsive">
								<!-- etiquetas campos mandatorios -->
								<div class="col-lg-12">
									<span style="color: blue;">*</span>{$CMOD.LBL_MANDATORY_MSG}
									<br />
									<span style="color: blue;">*</span>{$CMOD.LBL_DISABLE_FIELD_MSG}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
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
	function fnToggleVIew (obj) {
		if ($ (obj).hasClassName ('hideTable')) {
			$ (obj).removeClassName ('hideTable');
		} else {
			$ (obj).addClassName ('hideTable');
		}
	}
{/literal}
</script>
{/strip}