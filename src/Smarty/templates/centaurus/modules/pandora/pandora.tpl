<iframe id="pandoraframe" src="http://noc.timemanagement.es/pandora_console/index.php?loginhash=auto&loginhash_user=apadema&loginhash_data=116da79c87d2f22ba2ab77fb01d93220&sec=estado&sec2=operation/agentes/estado_agente&refr=60" frameborder="0" style="overflow:hidden; display:block;" width="100%">
Your browser does not support iframes.
</iframe>
{literal}
<script>
jQuery( window ).resize(function() {
	windowResize();
});
jQuery(document).ready(function() {
	windowResize();
});

function windowResize() {
jQuery('#pandoraframe').css('min-height', '200px');
jQuery('.settingsSelectedUI').parent().children('br').remove();
jQuery('.settingsSelectedUI').css({'padding-top':0, 'padding-bottom':0});
jQuery('#pandoraframe').css('min-height', (jQuery(document).height()-130)+'px');
}
{/literal}
</script>

