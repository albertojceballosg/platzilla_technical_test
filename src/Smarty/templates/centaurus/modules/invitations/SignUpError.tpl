{extends file="base/TopNavigation.tpl"}
{block name="css"}
	<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/box-frame.css" />
	<link rel="stylesheet" href="themes/centaurus/css/compiled/store.css">
	<link rel="stylesheet" href="themes/centaurus/css/compiled/section/calculator.css">
	<style type="text/css">
		.nextBtn {
			width: 100%;
		}
	</style>
{/block}
{block name="body-content"}
	<div class="row">
		<div class="col-xs-12">
			<div id="login-box">
				<header class="main-box-header clearfix" id="login-header">
					<div id="login-logo">
						<a href="http://www.platzilla.com"><img alt="" src="themes/centaurus/img/logo-platzilla-vert.png"></a>
					</div>
					<div style="background: white;">
						<hr class="linea">
					</div>
				</header>
				<div id="login-box-inner">
					<div class="alert alert-danger">
						<strong>Error:</strong> {$ERRORMESSAGE|urldecode}
					</div>
				</div>
			</div>
		</div>
	</div>
{/block}