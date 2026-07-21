{strip}
<link rel="stylesheet" type="text/css" href="{$THEME_PATH}/css/bootstrap/bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="{$THEME_PATH}/css/libs/font-awesome.css" />
<link rel="stylesheet" type="text/css" href="{$THEME_PATH}/css/libs/nanoscroller.css" />
<link rel="stylesheet" type="text/css" href="{$THEME_PATH}/css/libs/nifty-component.css" />
<link rel="stylesheet" type="text/css" href="{$THEME_PATH}/css/compiled/theme_styles.css" />
<script type="text/javascript" src="include/scriptaculous/prototype.js"></script>
<form action="index.php" method="post" id="form" onsubmit="VtigerJS_DialogBox.block ();">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" id="userid" name="userid" value="{$USERID}">
	<div class="theme-blue pace-done">
		<div class="row">
			<div class="col-lg-12">
				<div class="main-box clearfix">
					<header class="main-box-header">
						<h1>{$MOD.LBL_AUDIT_TRAIL}</h1>
					</header>
					<div class="main-box-body clearfix">
						<div id="AuditTrailContents" class="table-responsive">
{include file="ShowAuditTrailContents.tpl"}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript">
{literal}
	function getListViewEntries_js (module, url) {
		var userid = document.getElementById ('userid').value;
		new Ajax.Request (
			'index.php',
			{
				queue:      {
					position: 'end',
					scope: 'command'
				},
				method:     'post',
				postBody:   'module=Settings&action=SettingsAjax&file=ShowAuditTrail&ajax=true&' + url + '&userid=' + userid,
				onComplete: function (response) {
					$ ("#AuditTrailContents").innerHTML = response.responseText;
				}
			}
		);
	}
{/literal}
</script>
{/strip}