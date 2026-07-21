{extends file="platzilla_layout.tpl"}

{block name="action_css"}
	<link rel="stylesheet" href="themes/centaurus/css/compiled/ns-default.min.css">
	<link rel="stylesheet" href="themes/centaurus/css/compiled/ns-style-growl.min.css">
	<link rel="stylesheet" href="themes/centaurus/css/compiled/ns-style-bar.min.css">
	<link rel="stylesheet" href="themes/centaurus/css/compiled/ns-style-attached.min.css">
	<link rel="stylesheet" href="themes/centaurus/css/compiled/ns-style-other.min.css">
	<link rel="stylesheet" href="themes/centaurus/css/compiled/ns-style-theme.min.css">
	<link rel="stylesheet" href="themes/centaurus/css/compiled/pipeline.min.css">
	<link rel="stylesheet" href="themes/centaurus/css/compiled/datepicker.min.css">
	<link rel="stylesheet" href="themes/centaurus/css/compiled/nifty-component.min.css">
	<link rel="stylesheet" type="text/css" href="themes/centaurus/css/listview-platzilla.css">
	{* Otros CSS específicos de la vista pueden agregarse aquí *}
{/block}

{block name="action_js"}
	<script src="themes/centaurus/js/compiled/modernizr.custom.min.js"></script>
	<script src="themes/centaurus/js/compiled/snap.svg-min.min.js"></script>
	<script src="themes/centaurus/js/compiled/classie.min.js"></script>
	<script src="themes/centaurus/js/compiled/notificationFx.min.js"></script>
	<script src="themes/centaurus/js/compiled/bootstrap-datepicker.min.js"></script>
	<script src="themes/centaurus/js/compiled/bootstrap-datepicker.es.min.js"></script>
	<script src="themes/centaurus/js/compiled/moment.min.js"></script>
	<script src="themes/centaurus/js/listview-platzilla.js"></script>
	<script type="text/javascript" src="include/js/vtlib.js?v=5.4.0"></script>
	{* Otros JS específicos de la vista pueden agregarse aquí *}

	<!-- Inicialización de Tooltip para campos de área de texto truncados -->
	<script type="text/javascript">
		(function($) {
			'use strict';

			var hideTimeout = null;
			var isOverTooltip = false;

			function initTextAreaPopovers() {
				var elements = $('.protip[data-pt-title]');

				elements.each(function() {
					var $element = $(this);

					if ($element.data('protip-initialized')) {
						return;
					}
					$element.data('protip-initialized', true);

					var content = $element.attr('data-pt-title');
					var width = $element.attr('data-pt-width') || 500;

					if (content && content.length > 0) {
						$element.on('mouseenter.protip', function(e) {
							if (hideTimeout) {
								clearTimeout(hideTimeout);
								hideTimeout = null;
							}

							var $tooltip = $('#protip-tooltip');
							if ($tooltip.length === 0) {
								$tooltip = $('<div id="protip-tooltip"></div>').appendTo('body');

								$tooltip.on('mouseenter', function() {
									isOverTooltip = true;
									if (hideTimeout) {
										clearTimeout(hideTimeout);
										hideTimeout = null;
									}
								}).on('mouseleave', function() {
									isOverTooltip = false;
									hideTimeout = setTimeout(function() {
										$('#protip-tooltip').hide();
									}, 100);
								});
							}

							// Calcular altura máxima disponible (80% de la ventana visible)
							var windowHeight = $(window).height();
							var maxTooltipHeight = Math.min(500, Math.floor(windowHeight * 0.8));

							$tooltip.html(content.replace(/\n/g, '<br>'))
								.css({
									'position': 'absolute',
									'max-width': width + 'px',
									'max-height': maxTooltipHeight + 'px',
									'overflow-y': 'auto',
									'background': '#333',
									'color': '#fff',
									'padding': '12px 16px',
									'border-radius': '6px',
									'font-size': '13px',
									'line-height': '1.5',
									'z-index': '99999',
									'box-shadow': '0 4px 12px rgba(0,0,0,0.3)',
									'white-space': 'pre-wrap',
									'word-wrap': 'break-word',
									'scrollbar-width': 'thin',
									'scrollbar-color': '#666 #333'
								})
								.show();

							var offset = $(this).offset();
							var tooltipHeight = $tooltip.outerHeight();
							var tooltipWidth = $tooltip.outerWidth();
							var scrollTop = $(window).scrollTop();
							var top = offset.top - tooltipHeight - 5;
							var left = offset.left;

							// Si no cabe arriba, mostrar abajo
							if (top < scrollTop) {
								top = offset.top + $(this).outerHeight() + 5;
								// Si tampoco cabe abajo, centrar verticalmente en la ventana
								if (top + tooltipHeight > scrollTop + windowHeight) {
									top = scrollTop + Math.floor((windowHeight - tooltipHeight) / 2);
									if (top < scrollTop + 10) top = scrollTop + 10;
								}
							}
							if (left + tooltipWidth > $(window).width()) {
								left = $(window).width() - tooltipWidth - 10;
							}

							$tooltip.css({ top: top, left: left });

						}).on('mouseleave.protip', function() {
							hideTimeout = setTimeout(function() {
								if (!isOverTooltip) {
									$('#protip-tooltip').hide();
								}
							}, 150);
						});
					}
				});
			}

			$(document).ready(function() {
				setTimeout(initTextAreaPopovers, 500);
			});

			$(document).ajaxComplete(function() {
				setTimeout(initTextAreaPopovers, 300);
			});

		})(jQuery);
	</script>
{/block}

{block name="action_content"}
	{include file='utils/CSSPlatzillaTabs.tpl'}

	{* Marcador de migración de scripts eliminado para evitar que se muestre en la vista *}
	{if (!empty ($NOTIFICATIONS))}
		{foreach $NOTIFICATIONS as $index => $notification}
			{if $index >= 1}
				{$notification->getContents ()|regex_replace:"/__ID__/":$notification->getId ()|regex_replace:"/__COLLAPSE_IN__/":'collapse'|regex_replace:"/__HIDDEN__/":'hidden'|unescape:"html"}
			{else}
				{$notification->getContents ()|regex_replace:"/__ID__/":$notification->getId ()|regex_replace:"/__COLLAPSE_IN__/":'collapse'|regex_replace:"/__HIDDEN__/":''|unescape:"html"}
			{/if}
		{/foreach}
		<script type="text/javascript">
			(function(jQuery) {
				jQuery('.notification').on('closed.bs.alert', function() {
					var notificationId = jQuery(this).attr('data-id'),
						arguments = [
							'module=notifications',
							'action=Disable',
							'record=' + encodeURIComponent(notificationId),
							'Ajax=true'
						];
					jQuery.ajax('index.php', {
						data: arguments.join('&'),
						dataType: 'text',
						method: 'post'
					}).done(function() {
						jQuery('.notification.hidden:first').removeClass('hidden');
					});
				});
			}(jQuery));
		</script>

	{/if}
	{math equation= rand() assign= "idListView"}
	{include file='Buttons_List.tpl'}
	{if (!empty ($MESSAGE))}
		<div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
			<strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
		</div>
	{/if}
	{assign var='selectedModule' value=','|explode:"Documents,Calendar,formacion_cursos,encuesta_online"}
	{if (in_array ($MODULE, $selectedModule))}
		<div id="ListViewContents">
			{if ($MODULE == 'Documents')}
				{include file="DocumentsListViewEntries.tpl"}
			{elseif ($MODULE == 'Calendar')}
				{include file="ActivityListViewEntries.tpl"}
			{elseif $MODULE eq "formacion_cursos"}
				{include file="modules/formacion_cursos/ListViewCursos.tpl"}
			{elseif $MODULE eq "encuesta_online"}
				{include file="modules/encuesta_online/ListViewEncuestas.tpl"}
			{elseif isset($KANBAN_VIEW) }
				{include file="modules/kanban_views/DetailViewKanban.tpl"}
			{/if}
		</div>
	{else}
		<div class="tab-content">
			<div id="ListViewContents" data-current-module="{$MODULE}"
				data-buttons='{json_encode ($STATUS_BUTTONS)|escape: 'htmlall'}'
				data-kanba='{json_encode($KANBAN_LIST)|escape: 'htmlall'}' data-tab="{$LIST_VIEW_TAB}"
				data-main-tab="{$idListView}" class="tab-pane fade in active">
				{include file="ListViewEntries.tpl"}
			</div>
			{* LIST-VIEW-KANBAN-VIEW *}
			{if $STATUS_BUTTONS['kanban']}
				<div id="LIST-VIEW-KANBAN-VIEW" data-current-module="{$MODULE}" class="tab-pane fade in">
					{include file='utils/HTMLPageLoanding.tpl'}
				</div>
			{/if}
			{* LIST-VIEW-CALENDAR *}
			{if $STATUS_BUTTONS['calendar']}
				<div id="LIST-VIEW-CALENDAR" data-current-module="{$MODULE}" class="tab-pane fade in">
					{include file='utils/HTMLPageLoanding.tpl'}
				</div>
			{/if}
			{* LIST-VIEW-BOX-SCORE *}
			{if $STATUS_BUTTONS['boxscore']}
				<div id="LIST-VIEW-BOX-SCORE" data-current-module="{$MODULE}" class="tab-pane fade in">
					{include file='utils/HTMLPageLoanding.tpl'}
				</div>
			{/if}
			{* LIST-VIEW-GRAPHIC *}
			{if $STATUS_BUTTONS['graphic']}
				<div id="LIST-VIEW-GRAPHIC" data-current-module="{$MODULE}" class="tab-pane fade in ">
					{include file='utils/HTMLPageLoanding.tpl'}
				</div>
			{/if}
			{* report *}
			{if $STATUS_BUTTONS['report']}
				<div id="LIST-VIEW-REPORT" data-current-module="{$MODULE}" class="tab-pane fade in">
					{include file='utils/HTMLPageLoanding.tpl'}
				</div>
			{/if}
			{* kanban-task *}
			{if $STATUS_BUTTONS['task']}
				<div id="LIST-VIEW-KANBAN-TASK-VIEW" data-current-module="{$MODULE}" class="tab-pane fade in">
					{include file='utils/HTMLPageLoanding.tpl'}
				</div>
			{/if}
			{* LIST-VIEW-GANTT-MODULE *}
			{if $STATUS_BUTTONS['gantt']}
				<div id="LIST-VIEW-GANTT-MODULE" data-current-module="{$MODULE}" class="tab-pane fade in">
					<div id="gantt-module-content"></div>
				</div>
			{/if}
		</div>
	{/if}
	<div id="massedit" class="layerPopup" style="display: none; width: 80%;">
		<table width="100%" border="0" cellpadding="3" cellspacing="0" class="layerHeadingULine">
			<tr>
				<td class="layerPopupHeading" align="left" width="60%">{$APP.LBL_MASSEDIT_FORM_HEADER}</td>
				<td>&nbsp;</td>
				<td align="right" width="40%"><img onClick="fninvsh('massedit');" title="{$APP.LBL_CLOSE}"
						alt="{$APP.LBL_CLOSE}" style="cursor:pointer;" src="{'close.gif'|@vtiger_imageurl:$THEME}"
						align="absmiddle" border="0"></td>
			</tr>
		</table>
		<div id="massedit_form_div"></div>
	</div>

	{if $MENSAJE neq ''}
		<script type="text/javascript">
			(function() {
				new NotificationFx({
					message : '<span class="icon fa fa-exclamation-circle fa-2x"></span><p>{$MENSAJE}</p>',
					layout: 'bar',
					effect: 'slidetop',
					type : {if $TIPO_MENSAJE EQ 'fail'} 'error' {else} 'success' {/if} , // notice, warning or error
					onClose: function() {}
				}).show();
			})();
		</script>
	{/if}
	<script type="text/javascript">
		{$BUILD_SEARCH}
	</script>
	{if ($IS_FIRST_CONNECTION)}
		{include file='modal/FirstConnectionModal.tpl'}
	{/if}
	{math equation= rand() assign= "idModalDetalView"}
	{* Marcador de migración de CSS eliminado para evitar que se muestre en la vista *}
	<div class="md-modal md-effect-7-2" id="modal-detail-row" name="modal-detail-row-listview">
		<div class="md-content">
			<div class="modal-header">
				<button class="md-close close">&times;</button>
				<h4 class="modal-title" style="display:block;">Modal title</h4>
			</div>
			<div id="modal-detail-body-{$idModalDetalView}" data-status="0" class="modal-body">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary md-close" data-status="0">Cerrar</button>
			</div>
		</div>
	</div>
	<div class="md-overlay"></div>
	<script type="text/html" id="mass-edit-modal-template">
		<div class="modal fade" id="mass-edit-modal" tabindex="-1" role="dialog" aria-hidden="false" style="top: 0;">
			<form action="index.php" method="post">
				<input type="hidden" name="module" value="{$MODULE}" />
				<input type="hidden" name="action" value="MassEditSave" />
				<div class="modal-dialog" style="width: 90vw;">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							<h4 class="modal-title"></h4>
						</div>
						<div class="modal-body"
							style="max-height: 70vh; min-height: 70vh; overflow-x: hidden; overflow-y: auto;"></div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-primary">Guardar</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</script>
	<script type="text/javascript" src="include/js/mass-actions-utils.js?v=1.1"></script>
	{include file='modules/instancesdatasharing/SyncsModal.tpl'}
	<script type="text/javascript" src="modules/instancesdatasharing/data-sharing.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/modalDetailOverListView.js"></script>
	<script id="detail-over-listview" data-id-modal="{$idModalDetalView}" type="text/javascript"
		src="themes/centaurus/js/modal-detail-view.js"></script>
{/block}