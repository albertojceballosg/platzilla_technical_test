{strip}
{extends file="base/TopNavigation.tpl"}
{block name="css"}
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/box-frame.css" />
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/store.css" />
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/section/calculator.css" />
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/animate.css" />
	<style type="text/css">
		.panel-body {
			padding: 0px;
		}
		#login-box {
			max-width:       850px !important;
			min-width:       680px !important;
			margin:          60px auto 20px;
			overflow:        hidden;
			border-radius:   3px 3px 0 0;
			background-clip: padding-box;
			/* stops bg color from leaking outside the border: */
		}
		#footer-bar {
			position: relative !important;
			bottom: 0px!important;
		}
	</style>
{/block}
{block name="body-content"}
<div class="row" style="height: 1250px">
	{*$AD_QUEUES|var_dump*}
	<form role="form" id="bolletin-board-form"  {*action="index.php?module=store&action=CreateFormativeInstance" method="post" onsubmit="return BulletinBoardUtils.createFormativeInstance ();"*}>
		<input type="hidden" name="module" value="store"/>
		<input type="hidden" name="action" value="CreateFormativeInstance"/>
		<input type="hidden" name="Ajax" value="true"/>
		<div class="row setup-content">
			<div class="col-xs-12">
                {include file="modules/store/BulletinBoardForm.tpl"}
			</div>
		</div>
		<div id="clock" style="background-color: rgba(0, 0, 0, 0.5); bottom: 0; display: none; left: -16px; position: fixed; right: -16px; top: 0; z-index: 5000;">
			<div style="left: 50%; position: absolute; top: 50%; transform: translate(-50%, -50%); z-index: 5001;">
                {include file="modules/store/BulletinBoardMessages.tpl"}
			</div>
		</div>
	</form>
</div>
{/block}
{block name="scripts"}
<script type="text/javascript" src="modules/store/bulletin-board.js"></script>
{/block}
{/strip}