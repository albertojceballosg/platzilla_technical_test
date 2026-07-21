<div class="text-center">
	<h4>El módulo ha sido creado satisfactoriamente</h4>
{if (isset ($SHOW_PANEL_CONFIGURATION_BUTTON)) && ($SHOW_PANEL_CONFIGURATION_BUTTON)}
	<a href="index.php?module=Settings&action=LayoutPanelList&parenttab=Settings&formodule={$MODULE_NAME}" class="btn btn-info">Configurar paneles del módulo creado</a>
	&nbsp;
{/if}
	<a href="index.php?module={$MODULE_NAME}&action=index" class="btn btn-success">Ir al módulo</a>
</div>