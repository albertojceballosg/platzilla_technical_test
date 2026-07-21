{extends file="../boilerplate_out_footer.tpl"}
{block name="title"}
<title>Platzilla Management</title>
{/block}
{block name="css"}
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/box-frame.css" />
{/block}
{block name="body"}
<div class="page-wrap container">
	<div class="row">
		<div class="col-xs-12">
			<div class="row">
				<div id="login-box">
					<div class="row">
						<div class="col-xs-12">
							<div id="login-box-holder">
								<div class="row">
									<div class="col-xs-12">
										<header class="main-box-header clearfix" id="login-header">
											<div id="login-logo">{block name="header-logo"}{/block}</div>
											<div style="background: white;"><hr class="linea"><div></div></div>
										</header>
										<div id="login-box-inner">
											<div class="row">
												<div class="col-xs-12">{block name="box-title"}{/block}</div>
											</div>
											<div class="row">
												<div class="col-xs-12">{block name="box-form"}{/block}
{if (isset ($LOGIN_ERROR))}
													<div class="row">
														<div class="col-xs-12">
															<div class="login-error">
																<p class="social-text" style="text-align:justify">{$LOGIN_ERROR}</p>
															</div>
														</div>
													</div>
{/if}
													{block name="box-content"}{/block}
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
{/block}
{block name="scripts"}
<script src="themes/centaurus/js/jquery.js"></script>
<script src="themes/centaurus/js/bootstrap.js"></script>
<script src="themes/centaurus/js/jquery.nanoscroller.min.js"></script>
<script src="themes/centaurus/js/scripts.js"></script>
{block name="scripts"}{/block}
{/block}

