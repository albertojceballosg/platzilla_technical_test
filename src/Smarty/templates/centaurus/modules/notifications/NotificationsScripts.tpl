{if (!empty ($NOTIFICATIONS))}
	{foreach $NOTIFICATIONS as $index => $notification}
		{if $index >= 1}
			{$notification->getContents ()|regex_replace:"/__ID__/":$notification->getId ()|regex_replace:"/__COLLAPSE_IN__/":'collapse'|regex_replace:"/__HIDDEN__/":'hidden'|unescape:"html"}
		{else}
			{$notification->getContents ()|regex_replace:"/__ID__/":$notification->getId ()|regex_replace:"/__COLLAPSE_IN__/":'collapse'|regex_replace:"/__HIDDEN__/":''|unescape:"html"}
		{/if}
	{/foreach}
<script type="text/javascript">
	(function (jQuery) {
		jQuery ('.notification').on ('closed.bs.alert', function () {
			var notificationId = jQuery (this).attr ('data-id'),
				arguments      = [
					'module=notifications',
					'action=Disable',
					'record=' + encodeURIComponent (notificationId),
					'Ajax=true'
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
{/if}