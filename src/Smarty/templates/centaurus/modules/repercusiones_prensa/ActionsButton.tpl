{strip}
<div class="dropdown{if (isset ($CLASS)) && (!empty ($CLASS))} {$CLASS}{/if}">
	<button type="button" class="btn dropdown-toggle" data-toggle="dropdown">Acciones <span class="caret"></span></button>
	<ul class="dropdown-menu dropdown-menu-right">
		<li><a href="#" onclick="MassCreateUtils.addRepercussion (this); return false;">Agregar justo debajo</a></li>
		<li class="divider"></li>
		<li><a href="#" onclick="MassCreateUtils.duplicateRepercussion (this, MassCreateUtils.DUPLICATE_COMPLETE); return false;">Duplicar por completo</a></li>
		<li><a href="#" onclick="MassCreateUtils.duplicateRepercussion (this, MassCreateUtils.DUPLICATE_TITLE_AND_IMAGES); return false;">Duplicar sólo título e imágenes</a></li>
		<li><a href="#" onclick="MassCreateUtils.duplicateRepercussion (this, MassCreateUtils.DUPLICATE_ALL_BUT_RELATED_TO); return false;">Duplicar todo excepto Relacionado con</a></li>
		<li><a href="#" onclick="MassCreateUtils.duplicateRepercussion (this, MassCreateUtils.DUPLICATE_ONLY_TITLE); return false;">Duplicar sólo título</a></li>
		<li class="divider"></li>
		<li><a href="#" onclick="MassCreateUtils.deleteRepercussion (this); return false;">Eliminar</a></li>
	</ul>
</div>
{/strip}