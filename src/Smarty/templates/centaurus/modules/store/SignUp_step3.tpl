{strip}
{extends file="base/BaseForm_flex.tpl"}
{block name="header-logo"}
	<h1>Crea tu plataforma</h1>
	<p class="title-description" style="padding-bottom: 0;">Selecciona las aplicaciones que más se adapten a las necesidades de tu empresa:</p>
	<p style="color: #8A9698; font-size: 0.5em !important;">Al crear tu cuenta en Platzilla manifiestas el acuerdo con la <a href="/politica-de-privacidad.html" target="_blank">política de privacidad</a> y las <a href="/terminos-de-servicio.html" target="_blank">condiciones del servicio</a></p>
{/block}
{block name="style-line"}
	style="width:100%"
{/block}
{block name="box-form"}
	<div class="row row-body">
		<div class="container store-wrapper">
			<div class="row">
				<div class="col-xs-12">
					<div class="apps-wrapper col-xs-12">
	{foreach $APPLICATIONS as $application}
						<div class="app col-xs-12 col-md-6 clearfix">
							<div class="col-xs-12 col-sm-3 col-md-4">
								<div class="app-icon addApp_{$application.config_applicationsid}" style="overflow: hidden;">
									<img src="{$APPSIMAGE_PATH}/{$application.app_code}.png" alt="" id="image_app_{$application.config_applicationsid}" style="border-radius: 50%;">
									<div class="app_estatus">
										<i class="fa fa-check"></i>
									</div>
								</div>
							</div>
							<div class="col-xs-12 col-sm-9 col-md-8" style="text-align: left" data-application-id="{$application.config_applicationsid}">
								<div class="app-title text-left" id="name_app_{$application.config_applicationsid}">{$application.app_name}</div>
								<div class="app-description">
									<p>{$application.app_descripcion}</p>
								</div>
								<button class="btn btn-add" data-application-id="{$application.config_applicationsid}" type="button" onclick="StoreUtils.addApplicationToCart (this, '{$APPSIMAGE_PATH}');">Agregar</button>
								<button class="btn btn-remove hidden" data-application-id="{$application.config_applicationsid}" type="button" onclick="StoreUtils.deleteApplicationFromCart (this, '{$APPSIMAGE_PATH}');">Quitar</button>
								<br />
							</div>
						</div>
	{/foreach}
					</div>
				</div>
			</div>
		</div>
	</div>
{/block}
{block name="box-content"}
{/block}
{block name="extra-content"}
{/block}
{/strip}