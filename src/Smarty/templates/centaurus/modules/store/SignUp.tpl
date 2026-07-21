{strip}
{extends file="base/TopNavigation.tpl"}
{block name="css"}
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/box-frame.css" />
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/store.css" />
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/section/calculator.css" />
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/animate.css" />
{/block}
{block name="body-content"}
<div class="row">
	<form role="form" action="index.php?module=store&action=CreateInstance" method="post" onsubmit="return StoreUtils.createInstance ();">
		<div class="row setup-content" id="step-1">
			<div class="col-xs-12">
{include file="modules/store/SignUp_step1.tpl"}
			</div>
		</div>
		<div id="clock" style="background-color: rgba(0, 0, 0, 0.5); bottom: 0; display: none; left: -16px; position: fixed; right: -16px; top: 0; z-index: 5000;">
			<div style="left: 50%; position: absolute; top: 50%; transform: translate(-50%, -50%); z-index: 5001;">
{include file="modules/store/SignUp_step4.tpl"}
			</div>
		</div>
	</form>
</div>
{/block}
{block name="scripts"}
<script type="text/javascript" src="modules/store/store.js"></script>
{/block}
{/strip}