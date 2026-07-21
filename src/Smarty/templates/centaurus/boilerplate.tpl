{if !isset($IS_MODAL) || !$IS_MODAL}
<!DOCTYPE html>
<html lang="es">

<head>

	{if $WP eq 'true'}
		{include file="header.wp.tpl"}
	{/if}
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	{block name="title"}
		<title>{$MODULE_NAME|@getTranslatedString:$MODULE_NAME} - {$USER} - {$APP.LBL_BROWSER_TITLE}</title>
	{/block}
	<!-- CSS globales esenciales -->
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/bootstrap/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/theme_styles.min.css?v=1.2" />
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/theme_custom.min.css?v=1.1" />
	<link rel="stylesheet" href="themes/centaurus/css/roboto_regular_macroman/stylesheet.css" />
	{include file="base/include/favicon.tpl"}
	<link rel="stylesheet" href="themes/centaurus/css/bootstrap/bootstrap-icons.css">
	<link rel="stylesheet" type="text/css"
		href="//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400" />
	<link rel="stylesheet" type="text/css" href="themes/centaurus/css/custom-platzilla.css" />
	{if file_exists ("`$smarty.current_dir`/../../../modules/`$MODULE_NAME`/`$MODULE_NAME`.css")}
		<link rel="stylesheet" type="text/css" href="modules/{$MODULE_NAME}/{$MODULE_NAME}.css" />
	{/if}
	{block name="css"}{/block}
	{block name="action_css"}{/block}
	<!-- Añadidos CSS y JS globales para que estén disponibles para todas las vistas -->
	<link rel="stylesheet" href="themes/centaurus/css/compiled/fullcalendar.min.css">
	<link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css" />
	<!-- JS globales esenciales -->
	<script src="themes/centaurus/js/compiled/jquery.min.js"></script>
	<script src="themes/{$THEME}/js/bootstrap-original.js"></script>
	<script src="themes/centaurus/js/compiled/bootstrap-timepicker.min.js"></script>
	<script src="themes/centaurus/js/compiled/jquery.nanoscroller.min.js"></script>
	<script src="themes/centaurus/js/compiled/scripts.min.js"></script>
	<script src="themes/centaurus/js/moment.min.js"></script>
	<script src="themes/centaurus/js/compiled/fullcalendar.min.js"></script>
	<script src="modules/Home/CalendarManager.js?v={$smarty.now}"></script>
	<script src="themes/centaurus/js/jquery-ui.custom.min.js"></script>
	<script src="include/js/ekko-lightbox.min.js"></script>
	<script src="themes/centaurus/js/daterangepicker.js"></script>
	<script src="themes/centaurus/js/morris.js"></script>
	<script src="modules/Calendar/TaskViewModal.js"></script>
	<script src="modules/preloaded_tasks/precreated-task-utils.js"></script>
	<script type="text/javascript"
		src="include/js/{php} echo $_SESSION['authenticated_user_language'];{/php}.lang.js?v={$VERSION}"></script>
	<!--2025-08-25 GGC -->
	<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
	<script type="text/javascript" src="include/js/related-module-modal.js?v=1.1"></script>
	<!-- GGC -->
	<!-- js del módulo -->
	{if isset($INCLUDE_MODULE_JS) && $INCLUDE_MODULE_JS neq ''}
		<script src="modules/{$INCLUDE_MODULE_JS}/{$INCLUDE_MODULE_JS}.js"></script>
	{/if}
	<!-- fin js del módulo -->


	{block name="js"}{/block}
	{block name="action_js"}{/block}

	<!-- Global user settings -->
	<script type="text/javascript">
		var gUserDateFormat = '{$USER_DATE_FORMAT|default:'yyyy-mm-dd'}';
		var gUserNumberFormat = '{$NUMBERING_FORMAT|default:'AMERICAN_FORMAT'}';
		/*console.log('[boilerplate] Global JS vars set', {
			gUserDateFormat: gUserDateFormat,
			gUserNumberFormat: gUserNumberFormat,
smartyNumberingFormat: '{$NUMBERING_FORMAT|default:'(not set)'}'
		});*/
	</script>

</head>

<body class="pace-done">
{/if}
	{block name="body"}
		<div id="loading-overlay"
			style="background-color: rgba(255, 255, 255, 0.4); bottom: 0; display: none; left: 0; position: fixed; right: 0; top: 0; z-index: 5000;">
			<div style="left: 50%; position: absolute; top: 50%; transform: translate(-50%, -50%); z-index: 5001;">
				<div class="main-box">
					<header class="main-box-header text-center">
						<figure>
							<img src="themes/centaurus/img/logo-platzilla-vert.png" class="img-responsive" alt="Platzilla"
								style="display: inline-block; max-width: 150px;" />
						</figure>
						<hr class="linea" />
					</header>
					<div class="main-box-body text-center">
						<h1 style="display: inline-block;">Por favor espera</h1>
						<p class="">Estamos procesando tu solicitud</p>
						<img src="themes/images/loading.gif" alt="Loading" class="img-responsive" />
					</div>
				</div>
			</div>
		</div>
	{/block}
{if !isset($IS_MODAL) || !$IS_MODAL}
	{include file="modal/modal-help.tpl"}
	{include file="RelatedModuleModal.tpl"}
	{* start crm content *}
	{block name="action_js"}{/block}
	<script type="text/javascript">
		// Configuración global para evitar "null" en footer de ekkoLightbox
		jQuery(document).ready(function() {
			if (typeof jQuery.fn.ekkoLightbox !== 'undefined') {
				// Establecer defaults globales para ekkoLightbox
				jQuery.fn.ekkoLightbox.defaults = jQuery.extend(jQuery.fn.ekkoLightbox.defaults, {
					footer: ""
				});
			}
		});

		// Suprimir advertencia de Components deprecated en Firefox
		if (typeof window !== 'undefined' && window.console) {
			// Filtrar console.warn
			if (window.console.warn) {
				var originalWarn = console.warn;
				console.warn = function() {
					for (var i = 0; i < arguments.length; i++) {
						if (typeof arguments[i] === 'string' &&
							(arguments[i].includes('Components') || arguments[i].includes('components')) &&
							(arguments[i].includes('desaprobado') || arguments[i].includes('deprecated'))) {
							return; // No mostrar esta advertencia específica
						}
					}
					return originalWarn.apply(console, arguments);
				};
			}

			// Filtrar console.error
			if (window.console.error) {
				var originalError = console.error;
				console.error = function() {
					for (var i = 0; i < arguments.length; i++) {
						if (typeof arguments[i] === 'string' &&
							(arguments[i].includes('Components') || arguments[i].includes('components')) &&
							(arguments[i].includes('desaprobado') || arguments[i].includes('deprecated'))) {
							return; // No mostrar esta advertencia específica
						}
					}
					return originalError.apply(console, arguments);
				};
			}
		}
	</script>
	{if !$SKIP_HEADERS}
		{$MESSAGE_ERROR}
	{/if}
<!--
</body>
</html>
-->
{/if}