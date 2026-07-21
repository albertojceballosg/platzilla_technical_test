{strip}
<style type="text/css">
{literal}
	.hideTable {
		display: none;
	}
{/literal}
</style>
<script type="text/javascript" src="include/js/smoothscroll.js"></script>
<div style="opacity: 1;" class="row">
	<div class="col-lg-12">
		<div class="row">
			<div class="col-lg-12">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
					<li class="active">
						<a href="index.php?module=panelusuarios&action=index&parenttab=Settings">{$CMOD.LBL_USERS}</a>
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
								<b> {$CMOD.LBL_DEFINE_PRIV_FOR} &lt;{$PROFILE_NAME}&gt; </b>
								<br />
								{$CMOD.LBL_USE_OPTION_TO_SET_PRIV}
							</div>
							<div class="col-md-6 text-right">
								<form method="post" name="new" id="form" onsubmit="VtigerJS_DialogBox.block();">
									<input type="submit" value="{$APP.LBL_EDIT_BUTTON_LABEL}" title="{$APP.LBL_EDIT_BUTTON_LABEL}" class="btn btn-primary" name="edit" />
									<input type="hidden" name="module" value="Settings" />
									<input type="hidden" name="action" value="profilePrivilegesInstances" />
									<input type="hidden" name="parenttab" value="Settings" />
									<input type="hidden" name="return_action" value="profilePrivilegesInstances" />
									<input type="hidden" name="mode" value="edit" />
									<input type="hidden" name="profileid" value="{$PROFILEID}" />
								</form>
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
{include file='ProfileDetailViewModulePermissions.tpl'}
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
{/literal}
</script>
{/strip}