{strip}
<script type="text/javascript">
{literal}
	function getCourses () {
		jQuery.ajax ({
			url: {/literal}'{$URL}'{literal}
		}).done (function (html) {
			jQuery ({/literal}'#texto{$DIALOG_ID}'{literal}).html (html);
		});
	}
{/literal}
</script>
<div style="margin-left: auto; margin-right: auto; text-align:center; width: 100%;">
	<a class="webMnu" href="#" onclick="getCourses ();"><img hspace="5" align="absmiddle" border="0" src="themes/images/down_layout.gif" /></a>
	<a class="webMnu" href="#" onclick="getCourses ();">Obtener Cursos y Formaciones</a>
</div>
{/strip}