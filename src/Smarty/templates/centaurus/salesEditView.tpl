{extends file="base/EditView.tpl"}

{block name="scripts" append}

{if $POPUPCREATE eq 'create'}
<script type="text/javascript">
	{literal}
		jQuery(document).ready(function() {
			jQuery('#header-navbar').hide();
			jQuery('#nav-col').hide();
			jQuery('#config-tool-bar').hide();
		});
	{/literal}
</script>
{/if}

{/block}
