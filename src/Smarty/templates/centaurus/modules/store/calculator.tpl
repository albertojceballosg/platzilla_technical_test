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
	<form role="form" action="index.php?module=store&action=CreateInstance" method="post" onsubmit="return StoreUtils.createInstance ('step-3');">
		<div class="row setup-content" id="step-1">
			<div class="col-xs-12">
{include file="modules/store/SignUp_step1.tpl"}
			</div>
		</div>
		<div class="row setup-content" id="step-2">
			<div class="col-md-12">
{include file="modules/store/SignUp_step2.tpl"}
			</div>
		</div>
		<div class="row setup-content" id="step-3">
			<div class="col-md-12">
{include file="modules/store/SignUp_step3.tpl"}
			</div>
		</div>
		<div id="clock" style="background-color: rgba(0, 0, 0, 0.5); bottom: 0; display: none; left: -16px; position: fixed; right: -16px; top: 0; z-index: 5000;">
			<div style="left: 50%; position: absolute; top: 50%; transform: translate(-50%, -50%); z-index: 5001;">
{include file="modules/store/SignUp_step4.tpl"}
			</div>
		</div>
		<div class="row">
			<div id="app-car">
				<div class="container">
					<div class="row">
						<div class="col-xs-12">
							<div class="row">
								<div id="text-status" class="col-sm-2">
									<h4>Has agregado:</h4>
								</div>
								<div id="apps-ready" class="col-sm-8">
									<ul id="list_items_ready" class="navbar-center">
									</ul>
									<h4 class="error" id="error_totalapps"></h4>
									<input type="hidden" class="form-control" id="added_apps" value="0">
								</div>
								<div style="margin-top: -10px;" class="col-xs-12 col-sm-2">
									<div class="col-xs-12" id="required_app" style="display:none">
										<h5 class="error" id="error_totalapps2" style="margin-top: -20px;font-size: .6em;margin-bottom: 20px;"></h5>
									</div>
									<button type="submit" class="btn btn-success" value="">Crear mi aplicación
										<span class="fa fa-arrow-right"></span>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
<div class="stepwizard col-xs-12" style="display:none">
	<div class="stepwizard-row setup-panel">
		<div class="stepwizard-step">
			<a id="step1_lnk" href="#step-1"></a>
		</div>
		<div class="stepwizard-step">
			<a href="#step-2"></a>
		</div>
		<div class="stepwizard-step">
			<a href="#step-3"></a>
		</div>
	</div>
</div>
{/block}
{block name="scripts"}
<script type="text/javascript" src="modules/store/store.js"></script>
{/block}
{/strip}