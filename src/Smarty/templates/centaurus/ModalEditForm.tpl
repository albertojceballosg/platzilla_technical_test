<script type="text/javascript" src="include/jquery/jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="include/jquery/jquery-ui-1.10.3.custom.min.js"></script>
	<script type="text/javascript" src="include/jquery/jquery-ui-timepicker-addon.js"></script>
	<script type="text/javascript" src="include/jquery/jquery.autocomplete.min.js"></script>
	
	<script type="text/javascript" src="include/jquery/jquery.dimScreen.js"></script>
	<script type="text/javascript" src="include/jquery/jquery.form.min.js"></script>
	
	<link rel="stylesheet" media="all" type="text/css" href="include/jquery/jquery-ui.css">
	
   	<script type="text/javascript">
		J=jQuery.noConflict();
	</script>
<div id="modalEditUI" class="calAddEvent layerPopup" style="position:fixed; top:5%;left: 7%; z-index:1000; width: 85%; max-height: 600px; max-width:1200px;background-color:#FFFFFF;border:2px solid;padding:5px;border-color:#00000;">
	<table border="0" cellspacing="0" cellpadding="5" width="100%" class="layerHeadingULine">
		<tbody>
		<tr>
			<td class="layerPopupHeading" align="left">{"Edición Rápida"|getTranslatedString}</td>
				<td align="right">
					<a href="javascript:void(0);" onclick="unloadModalUI('modalEditUI')"><img src="themes/images/close.gif" border="0" align="absmiddle"></a>
				</td>
		</tr>
		</tbody>
	</table>

	{include file='ModalEditFormContent.tpl'}
</div>
<script language="JavaScript" type="text/javascript" src="modules/{$MODULE}/{$MODULE}.js"></script>