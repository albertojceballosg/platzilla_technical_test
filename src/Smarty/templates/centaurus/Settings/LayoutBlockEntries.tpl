{strip}

<style type="text/css">

</style>

{foreach $CFENTRIES as $entries}
	{include file='Settings/LayoutBlockListAddField.tpl'}
<div class="main-box clearfix">
	<header class="main-box-header clearfix">
		<div class="block-label pull-left">
			<h2 class="old-label" onclick="BlockUtils.showBlockLabelForm (this);">{$entries.blocklabel}</h2>
			<form action="index.php" method="post" class="block-label-form hidden" onsubmit="BlockUtils.saveBlockLabel (this); return false;">
				<input type="hidden" name="module" value="Settings" />
				<input type="hidden" name="action" value="SaveBlockLabel" />
				<input type="hidden" name="Ajax" value="true" />
				<input type="hidden" name="blockid" value="{$entries.blockid}" class="block-id" />
				<input type="text" name="label" value="{$entries.blocklabel}" class="form-control new-label" placeholder="Nombre del bloque" />
				<button type="submit" class="btn btn-primary"><i class="fa fa-check"></i></button>
				<button type="button" class="btn btn-default" onclick="BlockUtils.hideBlockLabelForm (this.form);"><i class="fa fa-times"></i></button>
			</form>
		</div>
		<div class="pull-right">
			<div class="btn-group pull-right">
				<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
					&nbsp;<span class="caret"></span>&nbsp;
				</button>
				<ul class="dropdown-menu" role="menu">
					<li>
						<a href="javascript:;" onclick="FieldUtils.openModal ('{$entries.blockid}')"><i class="fa fa-plus"></i>Agregar campo</a>
					</li>
					<li>
						<a href="javascript:;" onclick="BlockUtils.deleteBlock ('{$MODULE}', '{$entries.blockid}', {count($entries.field)});"><i class="fa fa-trash-o"></i>Eliminar bloque</a>
					</li>
{if $entries@first}
					<li>
						<a href="javascript:;" onclick="BlockUtils.moveBlock ('{$MODULE}', '{$entries.blockid}', {($entries.sequence + 1)});"><i class="fa fa-hand-o-down"></i>Bajar bloque</a>
					</li>
{elseif $entries@last}
					<li>
						<a href="javascript:;" onclick="BlockUtils.moveBlock ('{$MODULE}', '{$entries.blockid}', {($entries.sequence - 1)});"><i class="fa fa-hand-o-up"></i>Subir bloque</a>
					</li>
{else}
					<li>
						<a href="javascript:;" onclick="BlockUtils.moveBlock ('{$MODULE}', '{$entries.blockid}', {($entries.sequence + 1)});"><i class="fa fa-hand-o-down"></i>Bajar bloque</a>
					</li>
					<li>
						<a href="javascript:;" onclick="BlockUtils.moveBlock ('{$MODULE}', '{$entries.blockid}', {($entries.sequence - 1)});"><i class="fa fa-hand-o-up"></i>Subir bloque</a>
					</li>
{/if}
				</ul>
			</div>
		</div>
	</header>
	<div class="main-box-body clearfix">
		<div class="row cf nestable-lists">
			<div class="col-md-6 dd" id="nestable{$entries@iteration}" style="width: 100%">
				<ul class="dd-list">
{foreach $entries.field as $field}
					<li class="dd-item" data-id="{$field.fieldselect}">
						<div class="dd-handle" style="display: inline-block; float: none;">
							<div class="dd-nodrag input-group module-label">
								<label for="module-label-{$field@index}" class="module-label-value">{$field.label}</label>
								<div class="module-label-form hidden">
									<input type="text" id="module-label-{$field@index}" class="form-control module-label-field" />
									<input type="hidden" value="{$field.fieldlabel}" class="module-label-oldlabel" />
									<button type="button" class="btn btn-primary btn-accept"><i class="fa fa-check"></i></button>
									<button type="button" class="btn btn-default btn-cancel"><i class="fa fa-times"></i></button>
								</div>
	{if (in_array ($field.uitype, array (1, 2, 11, 17, 57)))}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">CAMPO DE TEXTO</span>
	{elseif $field.uitype eq "3" || $field.uitype eq "4"}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">CAMPO AUTO GENERADO</span>
	{elseif $field.uitype eq "5" || $field.uitype eq "23" || $field.uitype eq "70"}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">CAMPO DE FECHA</span>
	{elseif $field.uitype eq "6"}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">CAMPO DE FECHA Y HORA</span>
	{elseif ($field.uitype == 7)}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">CAMPO NUMÉRICO</span>
	{elseif $field.uitype eq "8"}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">MATRIZ JSON</span>
	{elseif ($field.uitype == 9)}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">PORCENTAJE</span>
	{elseif $field.uitype eq "10"}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">ENTIDAD RELACIONADA</span>
	{elseif $field.uitype eq "12" || $field.uitype eq "13" || $field.uitype eq "25"}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">CAMPO DE CORREO ELECTRÓNICO</span>
	{elseif $field.uitype eq "15" || $field.uitype eq "16" || $field.uitype eq "52" || $field.uitype eq "53"}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">COMBO DESPLEGABLE</span>
	{elseif $field.uitype eq "19" || $field.uitype eq "20" || $field.uitype eq "21" || $field.uitype eq "22" || $field.uitype eq "24" || $field.uitype eq "33"}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">ÁREA DE TEXTO</span>
	{elseif $field.uitype eq "26"}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">CARPETA DE DOCUMENTOS</span>
	{elseif $field.uitype eq "27"}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">TIPO DE ARCHIVO</span>
	{elseif $field.uitype eq "28"}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">NOMBRE DE ARCHIVO</span>
	{elseif $field.uitype eq "30"}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">MENÚS DESPLEGABLES</span>
	{elseif $field.uitype eq "51"}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">VENTANA EMERGENTE</span>
	{elseif $field.uitype eq "55"}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">PRIMER NOMBRE</span>
	{elseif $field.uitype eq "56"}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">CASILLA DE SELECCIÓN</span>
	{elseif $field.uitype eq "255"}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">PRIMER APELLIDO</span>
	{elseif $field.uitype eq "2202"}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">TABLA</span>
	{elseif $field.uitype eq "2206"}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">CAMPO CON CÁLCULO</span>
	{elseif $field.uitype eq "4096"}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">ANEXOS</span>
	{elseif $field.uitype eq "8192"}
									<span class="label info-span hidden-xs info-module-label-{$field@index}">PIPELINE</span>
	{/if}
							</div>
						</div>
						<div class="dd-links" style="display: inline-block; float: right;">
		{if (!in_array ($field.uitype, array (4, 53, 70)))}
							<button class="btn btn-primary mrg-b-lg" type="button" title="Editar" onclick="return {if ($field.uitype == 2202)}GridPropertiesUtils.editGridProperties ('{$MODULE}', '{$field.name}');{else}FieldPropertiesUtils.editFieldProperties ('{$MODULE}', '{$field.name}');{/if}">
								<i class="fa fa-pencil"></i>
							</button>
			{if (isset ($UNMODIFIABLE_FIELDS[$field.name]))}
							<button class="btn btn-warning" type="button" onclick="FieldPropertiesUtils.showUnmodifiableReasons ({htmlentities (json_encode ($UNMODIFIABLE_FIELDS[$field.name]))});"><i class="fa fa-lock"></i></button>
			{else}
							<button class="btn btn-danger" type="button" onclick="FieldUtils.deleteField ('{$MODULE}', '{$field.name}');">
								<i class="fa fa-trash-o"></i>
							</button>
			{/if}
		{/if}
						</div>
					</li>
{/foreach}
{if ($entries.hidden_count > 0)}
	{foreach $entries.hiddenfield as $field}
					<li class="dd-item" data-id="{$field.fieldselect}">
						<div class="dd-handle" style="display: inline-block; float: none; width: 90%;">
							<div class="dd-nodrag input-group module-label">
								<span style="padding-left: 13px;"><i class="fa fa-eye-slash"></i> </span>
								<label for="module-label-{$field@index}" class="module-label-value">{$field.label}</label>
								<div class="module-label-form hidden" style="display: inline-block;">
									<input type="text" id="module-label-{$field@index}" class="form-control module-label-field" />
									<input type="hidden" value="{$field.fieldlabel}" class="module-label-oldlabel" />
									<button type="button" class="btn btn-primary btn-accept"><i class="fa fa-check"></i></button>
									<button type="button" class="btn btn-default btn-cancel"><i class="fa fa-times"></i></button>
								</div>
							</div>
						</div>
						<div class="dd-links" style="display: inline-block; float: right; width: 10%;">
		{if (!in_array ($field.uitype, array (4, 53, 70)))}
							<button class="btn btn-primary mrg-b-lg" type="button" title="Editar" onclick="return {if ($field.uitype == 2202)}GridPropertiesUtils.editGridProperties ('{$MODULE}', '{$field.name}');{else}FieldPropertiesUtils.editFieldProperties ('{$MODULE}', '{$field.name}');{/if}">
								<i class="fa fa-pencil"></i>
							</button>
			{if (isset ($UNMODIFIABLE_FIELDS[$field.name]))}
							<button class="btn btn-warning" type="button" onclick="FieldPropertiesUtils.showUnmodifiableReasons ({htmlentities (json_encode ($UNMODIFIABLE_FIELDS[$field.name]))});"><i class="fa fa-lock"></i></button>
			{else}
							<button class="btn btn-danger" type="button" onclick="FieldUtils.deleteField ('{$MODULE}', '{$field.name}');">
								<i class="fa fa-trash-o"></i>
							</button>
			{/if}
		{/if}
						</div>
					</li>
	{/foreach}
{/if}
				</ul>
			</div>
		</div>
	</div>
</div>
{/foreach}

<script type="text/javascript">
{literal}
	jQuery (document).ready (function () {
		var container;
		var updateOutput = function (e) {
			if (jQuery (e.target).hasClass ('module-label-field')) {
				return;
			}
			var list   = e.length ? e : jQuery (e.target),
				output = list.data ('output');
			var fields = [];
			var elementos = list[ 0 ][ 'children' ][ 0 ][ 'children' ];
			for (var i = 0; i < elementos.length; i++) {
				var obj = {};
				obj[ 'id' ] = elementos[ i ][ 'dataset' ][ 'id' ];
				fields.push (obj);
			}

			var param = 'jsonFields=' + window.JSON.stringify (fields);
			new Ajax.Request (
				'index.php',
				{
					queue:      {
						position: 'end',
						scope: 'command'
					},
					method:     'post',
					postBody:   'action=SettingsAjax&module=Settings&file=SaveSequenceFields&' + param
				}
			);
		};
{/literal}
{foreach $CFENTRIES as $entries}
{literal}
		jQuery ({/literal}'#nestable{$entries@iteration}'{literal}).nestable ({
			group: {/literal}{$entries@iteration}{literal}
		}).on ('change', updateOutput);
{/literal}
{/foreach}
{literal}

		container = jQuery ('.module-label');
		container.find ('.btn-accept').click (saveLabel);
		container.find ('.btn-cancel').click (hideLabelField);
		jQuery ('.module-label-value').click (showLabelField);

		container = jQuery ('.module-menu-form');
		container.find ('.btn-cancel').click (hideMenuField);
		container.find ('.btn-accept').click (saveMenu);
		jQuery ('.module-menu-value').click (showMenuField);

		container = jQuery ('.admin-visibility-form');
		container.find ('.btn-cancel').click (hideAdminVisibilityField);
		container.find ('.btn-accept').click (saveAdminVisibility);
		jQuery ('.admin-visibility-value').click (showAdminVisibilityField);
});
{/literal}
</script>
{/strip}