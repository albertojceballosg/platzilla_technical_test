{strip}
<link rel="stylesheet" href="themes/centaurus/css/libs/timeline.css">
<link rel="stylesheet" href="themes/centaurus/css/compiled/platzilla-icons.css">
<link rel="stylesheet" href="themes/centaurus/css/compiled/custom.css">
<div class="container">
	<div class="row">
		<div class="col-lg-12">
{if (isset ($estatus))}
			<div class="alert alert-{if ($estatus == '1')}success{else}danger{/if}">
				<i class="fa fa-{if ($estatus == '1')}check{else}times{/if}-circle fa-fw fa-lg"></i>
				<strong>{if ($estatus == '1')}Cuenta verificada!{else}Código inválido{/if}</strong>
			</div>
{/if}
{if (isset ($MESSAGE))}
			<div class="alert alert-{if (!$IS_ERROR)}success{else}danger{/if}">
				<i class="fa fa-{if (!$IS_ERROR)}check{else}times{/if}-circle fa-fw fa-lg"></i>
				<strong>{if (!$IS_ERROR)}Listo{else}Error{/if}!</strong> {$MESSAGE}
			</div>
{/if}
			<h1>Actividad reciente</h1>
		</div>
{if (!empty ($NOTIFICATIONS))}
		<div class="col-lg-12">
	{foreach $NOTIFICATIONS as $notification}
			<div class="alert alert-dismissable notification{if ($notification@iteration > 1)} hidden{/if}" data-id="{$notification.notifyid}" style="background-color: #ffffff;">
				<button type="button" class="close notification-close" data-dismiss="alert" aria-label="close">&times;</button>
				<div>{$notification.design|unescape:"html"}</div>
			</div>
	{/foreach}
			<script type="text/javascript">
				(function (jQuery) {
					jQuery ('.notification').on ('closed.bs.alert', function () {
						var notificationId = jQuery (this).attr ('data-id'),
							arguments      = [
								'module=notifymanager',
								'action=Disable',
								'record=' + encodeURIComponent (notificationId)
							];
						jQuery.ajax ('index.php', {
							data:     arguments.join ('&'),
							dataType: 'text',
							method:   'post'
						}).done (function () {
							jQuery ('.notification.hidden:first').removeClass ('hidden');
						});
					});
				} (jQuery));
			</script>
		</div>
{/if}
		<div class="col-lg-12">
			<select class="form-control pull-right clearfix" name="selectActivity" id="selectActivity" onchange="getActivityRecent(this.value)" title="">
				<option value="lastWeek">Última semana</option>
				<option value="today">Hoy</option>
				<option value="yesterday">Ayer</option>
				<option value="lastMonth" selected="selected">Último mes</option>
			</select>
		</div>
		<div class="col-lg-12">
			<div id="div_timeline">
{include file="Home/timeline.tpl"}
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="themes/centaurus/js/timeline.js"></script>
<script type="text/javascript" src="modules/Home/Homestuff.js"></script>
{/strip}