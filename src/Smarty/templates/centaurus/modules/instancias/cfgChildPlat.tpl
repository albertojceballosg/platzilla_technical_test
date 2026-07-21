{strip}
<script type="text/javascript">
{literal}
	function doEdit () {
		jQuery.ajax ({
			url: {/literal}'{$URL}'{literal}
		}).done (
			function (html) {
				jQuery ({/literal}'#texto{$DIALOG_ID}'{literal}).html (html);
			}
		);
	}
{/literal}
</script>
<div style="width: 100%; margin-left: auto; margin-right: auto; text-align: center;">
	<input type="button" name="Edit" value="{$LABEL}" class="crmbutton small edit" onclick="doEdit ();" />
</div>
{/strip}