{strip}
{if (isset ($MODULE))}
	{assign var='blocks' value=$MODULE->getBlocks ()}
	{assign var='menuLabel' value=$MODULE->getMenuLabel ()}
	{assign var='moduleEntityPrefix' value=$MODULE->getEntityPrefix ()}
	{assign var='moduleEntityInitialSequence' value=$MODULE->getEntityInitialSequence ()}
	{assign var='moduleEntityCurrentSequence' value=$MODULE->getEntityCurrentSequence ()}
	{assign var='moduleLabel' value=$MODULE->getLabel ()}
	{assign var='moduleName' value=$MODULE->getName ()}
	{assign var='showInAdminConsole' value=$MODULE->getShowInAdminConsole ()}
{else}
	{assign var='blocks' value=null}
	{assign var='moduleEntityPrefix' value=null}
	{assign var='moduleEntitySequence' value=null}
	{assign var='menuLabel' value=null}
	{assign var='moduleLabel' value=null}
	{assign var='moduleName' value=null}
	{assign var='showInAdminConsole' value=false}
{/if}
<link rel="stylesheet" type="text/css" href="/themes/centaurus/css/compiled/wizard.css" />
<link rel="stylesheet" type="text/css" href="/modules/Settings/layout-editor.css" />
<div id="email-box" class="clearfix">
	<table class="table" border="0" cellpadding="5" cellspacing="0" width="100%">
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box"><i class="fa fa-tasks yellow-bg"></i></div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
					<li><a href="index.php?module=Settings&action=ModuleManager&parenttab=Settings">{$MOD.VTLIB_LBL_MODULE_MANAGER|upper}</a></li>
					<li><a href="index.php?module=Settings&action=LayoutBlockList&parenttab=Settings&formodule={$moduleName}">{$moduleLabel|strtoupper}</a></li>
					<li class="active">{$MOD.LBL_LAYOUT_EDITOR}</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td colspan="3" valign="top">{$MOD.LBL_LAYOUT_EDITOR_DESCRIPTION}</td>
		</tr>
	</table>
	<div id="buttoms_field_list" class="action-bar">
		{if !$IS_INSTANCE}
		<button type="button" class="btn btn-warning" onclick="RelatedListsUtils.openModal ();">{$MOD.ARRANGE_RELATEDLIST}</button>
		<a href="index.php?module=Settings&action=CustomButtons&formodule={$moduleName}" class="btn btn-primary">{$MOD.LBL_CUSTOM_BUTTONS}</a>
		<button type="button" class="btn btn-info" onclick="EditableFieldsUtils.openModal ();">{$MOD.EDITABLE_FIELDS}</button>
		<button type="button" class="btn btn-default" onclick="GridUtils.openModal ('{$moduleName}');">{$MOD.LBL_CONFIGURAR_CAMPOS_GRID}</button>
		<button type="button" class="btn btn-success" onclick="BlockUtils.openModal ('{$moduleName}');">{$MOD.ADD_BLOCK}</button>
		<button type="button" class="btn btn-info" onclick="TableFieldUtils.openModalWizard ('{$moduleName}', 'table-fields-wizard-template', '');">{$MOD.LBL_CONFIGURAR_TABLE_FIELD}</button>
		<button type="button" class="btn btn-danger" onclick="PermissionUtils.openModal ();">Permisologías</button>
		{/if}
		<button type="button" class="btn btn-info" {if $IS_INSTANCE}style="margin-left: 20px"{/if}
				onclick="EntityNumberUtils.openModal ('{$moduleName}', '{$moduleEntityPrefix}', '{$moduleEntityInitialSequence}', '{$moduleEntityCurrentSequence}');">
			Número de Registro
		</button>
		{if $IS_INSTANCE && ($CUSTOM_BLOCK neq NULL)}
			<a   class="btn btn-success" href="javascript:;" onclick="FieldUtils.openModal ('{$CUSTOM_BLOCK->getId()}')"><i class="fa fa-plus"></i>&nbsp;Agregar campo</a>
		{/if}
	</div>
	<div id="layout-editor">
{if (!$IS_INSTANCE)}
		<div class="main-box clearfix" style="margin-top: 16px;">
			<div class="menu col-xs-6">
				<label for="menu-field" class="control-label col-xs-3 text-right">Menú:</label>
				<span class="old-value col-xs-9" onclick="ModuleUtils.showMenuForm (this);">{if (!empty ($menuLabel))}{$menuLabel}{else}Configuración{/if}</span>
				<form action="index.php" method="post" class="menu-form hidden" onsubmit="ModuleUtils.saveMenu (this); return false;">
					<input type="hidden" name="module" value="Settings" />
					<input type="hidden" name="action" value="SaveMenu" />
					<input type="hidden" name="modulename" value="{$moduleName}"
					<input type="hidden" name="Ajax" value="true" />
					<select id="menu-field" name="menulabel" class="form-control new-value">
						<option value=""{if (empty ($menuLabel))} selected="selected"{/if}>Configuración</option>
	{foreach $AVAILABLE_MENU_DATA as $menuData}
						<option value="{$menuData.parenttab_label}"{if ($menuData.parenttab_label == $menuLabel)} selected="selected"{/if}>{$menuData.parenttab_label}</option>
	{/foreach}
					</select>
					<button type="submit" class="btn btn-primary"><i class="fa fa-check"></i></button>
					<button type="button" class="btn btn-default" onclick="ModuleUtils.hideMenuForm (this.form);"><i class="fa fa-times"></i></button>
				</form>
			</div>
			<div class="admin-visibility col-xs-6">
				<label for="admin-visibility-field" class="control-label col-xs-9 text-right">Visible en la plataforma madre:</label>
				<span class="old-value col-xs-3" onclick="ModuleUtils.showAdminVisibilityForm (this);">{if ($showInAdminConsole)}Sí{else}No{/if}</span>
				<form action="index.php" method="post" class="admin-visibility-form hidden" onsubmit="ModuleUtils.saveAdminVisibility (this); return false;">
					<input type="hidden" name="module" value="Settings" />
					<input type="hidden" name="action" value="SaveAdminVisibility" />
					<input type="hidden" name="modulename" value="{$moduleName}"
					<input type="hidden" name="Ajax" value="true" />
					<select id="admin-visibility-field" name="visible" class="form-control new-value">
						<option value="1"{if ($showInAdminConsole)} selected="selected"{/if}>Sí</option>
						<option value="0"{if (!$showInAdminConsole)} selected="selected"{/if}>No</option>
					</select>
					<button type="submit" class="btn btn-primary"><i class="fa fa-check"></i></button>
					<button type="button" class="btn btn-default" onclick="ModuleUtils.hideAdminVisibilityForm (this.form);"><i class="fa fa-times"></i></button>
				</form>
			</div>
		</div>
{/if}
{foreach $blocks as $block}
	{if $block->isDeleted ()}
		{continue}
	{/if}
	{assign var='blockId' value=$block->getId ()}
	{assign var='blockLabel' value=$block->getLabel ()}
	{assign var='fields' value=$block->getFields ()}
	{if $blockLabel eq BlockInterface::CUSTOM_BLOCK}
        {assign var='blockLabel' value=$MOD[$block->getLabel ()]}
	{/if}
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">
				<div class="block-label pull-left">
					<h2 class="old-label {if $block->getVisibility ()}block-hide{/if}" onclick="BlockUtils.showBlockLabelForm (this);">{$blockLabel}</h2>
					<form action="index.php" method="post" class="block-label-form hidden" onsubmit="BlockUtils.saveBlockLabel (this); return false;">
						<input type="hidden" name="module" value="Settings" />
						<input type="hidden" name="action" value="SaveBlockLabel" />
						<input type="hidden" name="Ajax" value="true" />
						<input type="hidden" name="blockid" value="{$blockId}" class="block-id" />
						<input type="text" name="label" value="{$blockLabel}" class="form-control new-label" placeholder="Nombre del bloque" />
						<button type="submit" class="btn btn-primary"><i class="fa fa-check"></i></button>
						<button type="button" class="btn btn-default" onclick="BlockUtils.hideBlockLabelForm (this.form);"><i class="fa fa-times"></i></button>
					</form>
				</div>
				<div class="pull-right">
                    {if !$IS_INSTANCE }
					<div class="block-label pull-left">
						<a class="pull-left btn btn-success" style="margin-right: 4px; vertical-align:middle; padding-top:6px"
						   title="{if $block->getVisibility ()}Mostrar{else}Ocultar{/if} bloque"
						   rel="{$blockId}"
						   data-visibility="{$block->getVisibility ()}"
						   data-block-name="{$blockLabel}"
						   data-mandatory-fields='{if $MANDATORY_FIELDS[$blockId]} {$MANDATORY_FIELDS[$blockId]|@json_encode nofilter} {else}NO-MANDATORY{/if}'
						   href="#"
						   onclick="BlockUtils.updateVisibility (this, event); ">
							<span class="glyphicon {if $block->getVisibility ()}glyphicon-eye-close{else}glyphicon-eye-open{/if}"></span>
						</a>
					</div>
					<div class="btn-group pull-right">
						<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">&nbsp;<span class="caret"></span>&nbsp;</button>
						<ul class="dropdown-menu" role="menu">
							<li>
								<a href="javascript:;" onclick="FieldUtils.openModal ('{$blockId}')"><i class="fa fa-plus"></i>Agregar campo</a>
							</li>
							<li>
								<a href="javascript:;" onclick="BlockUtils.deleteBlock ('{$moduleName}', '{$blockId}', {count($block->getFields ())});"><i class="fa fa-trash-o"></i>Eliminar bloque</a>
							</li>
	{if $block@first}
							<li>
								<a href="javascript:;" onclick="BlockUtils.moveBlock ('{$moduleName}', '{$blockId}', {($block->getSequence () + 1)});"><i class="fa fa-hand-o-down"></i>Bajar bloque</a>
							</li>
	{elseif $block@last}
							<li>
								<a href="javascript:;" onclick="BlockUtils.moveBlock ('{$moduleName}', '{$blockId}', {($block->getSequence () - 1)});"><i class="fa fa-hand-o-up"></i>Subir bloque</a>
							</li>
	{else}
							<li>
								<a href="javascript:;" onclick="BlockUtils.moveBlock ('{$moduleName}', '{$blockId}', {($block->getSequence () + 1)});"><i class="fa fa-hand-o-down"></i>Bajar bloque</a>
							</li>
							<li>
								<a href="javascript:;" onclick="BlockUtils.moveBlock ('{$moduleName}', '{$blockId}', {($block->getSequence () - 1)});"><i class="fa fa-hand-o-up"></i>Subir bloque</a>
							</li>
	{/if}
						</ul>
					</div>
					{/if}
				</div>
			</header>
			<div class="main-box-body clearfix">
				<div class="row cf nestable-lists">
					<div id="block-fields-{$blockId}" class="col-md-6 dd block-fields" data-id="{$blockId}" style="width: 100%">
						<ul class="dd-list {if $IS_INSTANCE }row{/if}">
	{foreach $fields as $field}
        {assign var="isFieldList" value=false}
		{if (in_array ($field->getPresence (), array (Field::PRESENCE_ALWAYS_HIDDEN, Field::PRESENCE_HIDDEN)))}{continue}{/if}
		{assign var='fieldId' value=$field->getId ()}
		{assign var='fieldLabel' value=$field->getLabel ()}
		{assign var='fieldName' value=$field->getName ()}
		{assign var='fieldUiType' value=$field->getUiType ()}
        {assign var='fieldMandatory' value=$field->isMandatory ()}
		{if ($fieldUiType == Field::UI_TYPE_ATTACHMENTS)}
			{assign var='fieldTypeDescription' value='ANEXOS'}
		{elseif ($fieldUiType == Field::UI_TYPE_CALCULATED_LINK)}
			{assign var='fieldTypeDescription' value='CAMPO CON CÁLCULO'}
		{elseif ($fieldUiType == Field::UI_TYPE_CHECKBOX)}
			{assign var='fieldTypeDescription' value='CASILLA DE SELECCIÓN'}
		{elseif ($fieldUiType == Field::UI_TYPE_CODE)}
			{assign var='fieldTypeDescription' value='CAMPO AUTO GENERADO'}
		{elseif ($fieldUiType == Field::UI_TYPE_CREATED_TIME)}
			{assign var='fieldTypeDescription' value='CAMPO FECHA DE CREACIÓN'}
		{elseif ($fieldUiType == Field::UI_TYPE_CURRENCY)}
			{assign var='fieldTypeDescription' value='CAMPO DE MONEDA'}
		{elseif ($fieldUiType == Field::UI_TYPE_DATE)}
			{assign var='fieldTypeDescription' value='CAMPO DE FECHA'}
		{elseif ($fieldUiType == Field::UI_TYPE_DATETIME)}
			{assign var='fieldTypeDescription' value='CAMPO DE FECHA Y HORA'}
		{elseif ($fieldUiType == Field::UI_TYPE_EMAIL)}
			{assign var='fieldTypeDescription' value='CAMPO DE CORREO ELECTRÓNICO'}
		{elseif ($fieldUiType == Field::UI_TYPE_GLOBAL_PICKLIST)}
			{assign var='fieldTypeDescription' value='CAMPO DE LISTA ESPECIAL'}
		{elseif ($fieldUiType == Field::UI_TYPE_GRID)}
			{assign var='fieldTypeDescription' value='TABLA'}
		{elseif (in_array ($fieldUiType, array (Field::UI_TYPE_IMAGE_DISPLAY, Field::UI_TYPE_IMAGE_REFERENCE)))}
			{assign var='fieldTypeDescription' value='CAMPO DE IMAGEN'}
		{elseif ($fieldUiType == Field::UI_TYPE_MODIFIED_BY)}
			{assign var='fieldTypeDescription' value='CAMPO AUDITORÍA'}
		{elseif ($fieldUiType == Field::UI_TYPE_MODULE_REFERENCE)}
			{assign var='fieldTypeDescription' value='ENTIDAD RELACIONADA'}
		{elseif (in_array ($fieldUiType, array (Field::UI_TYPE_MULTI_SELECT, Field::UI_TYPE_PICKLIST)))}
			{assign var='fieldTypeDescription' value='CAMPO DE LISTA'}
            {assign var="isFieldList" value=true}
		{elseif ($fieldUiType == Field::UI_TYPE_NUMBER)}
			{assign var='fieldTypeDescription' value='CAMPO NUMÉRICO'}
		{elseif ($fieldUiType == Field::UI_TYPE_OWNER)}
			{assign var='fieldTypeDescription' value='CAMPO ASIGNADO A'}
		{elseif ($fieldUiType == Field::UI_TYPE_PERCENTAGE)}
			{assign var='fieldTypeDescription' value='CAMPO DE PORCENTAJE'}
		{elseif ($fieldUiType == Field::UI_TYPE_PHONE)}
			{assign var='fieldTypeDescription' value='CAMPO DE TELÉFONO'}
		{elseif ($fieldUiType == Field::UI_TYPE_PIPELINE)}
			{assign var='fieldTypeDescription' value='PIPELINE'}
        {elseif ($fieldUiType == Field::UI_TYPE_TABLE_FIELD)}
            {assign var='fieldTypeDescription' value='CAMPO TABLA'}
            {assign var="myTemplate" value="table-fields-"|cat:$fieldName|cat:"-template"}
		{elseif ($fieldUiType == Field::UI_TYPE_TEXT)}
			{assign var='fieldTypeDescription' value='CAMPO DE TEXTO'}
		{elseif ($fieldUiType == Field::UI_TYPE_TEXTAREA)}
			{assign var='fieldTypeDescription' value='ÁREA DE TEXTO'}
		{elseif ($fieldUiType == Field::UI_TYPE_TIME)}
			{assign var='fieldTypeDescription' value='CAMPO DE TIEMPO'}
		{elseif ($fieldUiType == Field::UI_TYPE_URL)}
			{assign var='fieldTypeDescription' value='CAMPO DE URL'}
        {elseif ($fieldUiType == Field::UI_TYPE_VIDEO)}
            {assign var='fieldTypeDescription' value='CAMPO DE VIDEO'}
		{else}
			{assign var='fieldTypeDescription' value=null}
		{/if}
        {if ($fieldUiType eq Field::UI_TYPE_TABLE_FIELD) && false}
		<div class="hide">
			{include file='Settings/GridManager/TableFieldWizardEdit.tpl' TF_TEMPLATE=$myTemplate TABLE_FIELDS= $TABLE_FIELDS BLOCK_NAME=$blockLabel FIELD=$field}
		</div>
        {/if}
							<li class="dd-item block-field {if $IS_INSTANCE }{if ($fieldUiType == Field::UI_TYPE_GRID)}col-xs-12 {else}col-xs-6{/if}{/if}" data-id="{$fieldId}">
                                <div class="dd-handle" style="display: inline-block; float: none;{if $IS_INSTANCE}width: 90%;{/if}{if $FIELDS_VISIBILITY[$fieldId]}background-color: #dddddd{/if}">
									{*<div class="pull-left" style="padding-left: 4px"><span class="glyphicon {if $FIELDS_VISIBILITY[$fieldId]}glyphicon-eye-close{else}glyphicon-eye-open{/if}"></span></div>*}
									<div class="dd-nodrag input-group field-label">
										<label class="old-label {if $FIELDS_VISIBILITY[$fieldId]}block-hide{/if}" onclick="FieldUtils.showFieldLabelForm (this);">{$fieldLabel}</label>
										<form action="index.php" method="post" class="field-label-form hidden" onsubmit="FieldUtils.saveFieldLabel (this); return false;">
											<input type="hidden" name="module" value="Settings" />
											<input type="hidden" name="action" value="SaveFieldLabel" />
											<input type="hidden" name="Ajax" value="true" />
											<input type="hidden" name="modulename" value="{$moduleName}" />
											<input type="hidden" name="fieldid" value="{$fieldId}" />
											<input type="text" name="label" value="{$fieldLabel}" class="form-control new-label" placeholder="Nombre del campo" />
											<button type="submit" class="btn btn-primary"><i class="fa fa-check"></i></button>
											<button type="button" class="btn btn-default" onclick="FieldUtils.hideFieldLabelForm (this.form);"><i class="fa fa-times"></i></button>
										</form>
		{if (!empty ($fieldTypeDescription))}
										<span class="label label-default {*hidden-xs*}">{$fieldTypeDescription}</span>
		{/if}
									</div>
								</div>
								{* INSTANCE MOTHER*}
                                {if !$IS_INSTANCE }
                                <div class="dd-links" style="display: inline-block; float: right; {if $FIELDS_VISIBILITY[$fieldId]}background-color: #dddddd{/if}">
		{if (!in_array ($fieldUiType, array (Field::UI_TYPE_CODE, Field::UI_TYPE_CREATED_TIME, Field::UI_TYPE_MODIFIED_BY, Field::UI_TYPE_OWNER)))}
			{if ($fieldUiType == Field::UI_TYPE_GRID)}
                {if !$IS_INSTANCE }
									<button class="btn btn-primary" type="button" title="Editar" onclick="return GridPropertiesUtils.editGridProperties ('{$moduleName}', '{$fieldName}');"><i class="fa fa-pencil"></i></button>
									<button class="btn recalculate-grid-btn" type="button" title="Actualizar cálculos de todos los registros del campo grid" data-fieldid="{$fieldId}" data-fieldname="{$fieldName}" data-fieldlabel="{$fieldLabel}" data-module="{$moduleName}" style="background-color: #ff6600; color: white; border-color: #ff6600;"><i class="fa fa-refresh"></i></button>
				{/if}
			{elseif ($fieldUiType eq Field::UI_TYPE_TABLE_FIELD)}
                {if !$IS_INSTANCE }
					<button class="btn btn-primary" type="button" title="Editar" onclick="return TableFieldUtils.openModalWizard ('{$moduleName}', '{$myTemplate}', '{$fieldName}');" ><i class="fa fa-pencil"></i></button>
                {/if}
			{else}
									<button class="btn btn-primary" type="button" title="Editar" onclick="return FieldPropertiesUtils.editFieldProperties ('{$moduleName}', '{$fieldName}');"><i class="fa fa-pencil"></i></button>
                                    <button class="btn btn-success" type="button" title="Visibilidad" data-mandatory-field="{$fieldMandatory}" onclick="return FieldPropertiesUtils.setFieldVisibility ('{$fieldMandatory}', '{$moduleName}', '{$fieldName}');"><span class="glyphicon {if $FIELDS_VISIBILITY[$fieldId]}glyphicon-eye-close{else}glyphicon-eye-open{/if}"></span></button>
			{/if}
			{if (isset ($UNMODIFIABLE_FIELDS[$fieldName]))}
									<button class="btn btn-warning" type="button" onclick="FieldPropertiesUtils.showUnmodifiableReasons ({htmlentities (json_encode ($UNMODIFIABLE_FIELDS[$fieldName]))});"><i class="fa fa-lock"></i></button>
			{elseif ($fieldUiType != Field::UI_TYPE_GRID)  && !($IS_INSTANCE)}
									<button class="btn btn-danger" type="button" onclick="FieldUtils.deleteField ('{$moduleName}', '{$fieldName}');"><i class="fa fa-trash-o"></i></button>
             {elseif ($fieldUiType == Field::UI_TYPE_GRID) && !($IS_INSTANCE)}
									<button class="btn btn-danger" type="button" onclick="FieldUtils.deleteField ('{$moduleName}', '{$fieldName}');"><i class="fa fa-trash-o"></i></button>
			{/if}
		{/if}
								</div>
                                {else}
                                {* INSTANCE DAUGHTERS *}
                                <div class="dd-links" style="display: inline-block; float: right; width: 10%;{if $FIELDS_VISIBILITY[$fieldId]}background-color: #dddddd{/if}">
									{if $isFieldList}
									<button class="btn btn-primary" type="button" title="Editar" onclick="return FieldPropertiesUtils.editFieldProperties ('{$moduleName}', '{$fieldName}');"><i class="fa fa-pencil"></i></button>
                                    {/if}
									<button class="btn btn-success" type="button" title="Visibilidad" data-mandatory-field="{$fieldMandatory}" onclick="return FieldPropertiesUtils.setFieldVisibility ('{$fieldMandatory}', '{$moduleName}', '{$fieldName}');"><span class="glyphicon {if $FIELDS_VISIBILITY[$fieldId]}glyphicon-eye-close{else}glyphicon-eye-open{/if}"></span></button>
                                </div>
                                {/if}
                            </li>
	{/foreach}
	{foreach $fields as $field}
		{if (!in_array ($field->getPresence (), array (Field::PRESENCE_ALWAYS_HIDDEN, Field::PRESENCE_HIDDEN)))}{continue}{/if}
		{assign var='fieldId' value=$field->getId ()}
		{assign var='fieldLabel' value=$field->getLabel ()}
		{assign var='fieldName' value=$field->getName ()}
		{assign var='fieldUiType' value=$field->getUiType ()}
							<li class="dd-item" data-id="{$fieldId}">
								<div class="dd-handle" style="display: inline-block; float: none; width: 90%;">
									<div class="dd-nodrag input-group module-label">
										<span style="padding-left: 13px;"><i class="fa fa-eye-slash"></i> </span>
										<label for="module-label-{$field@index}" class="module-label-value">{$fieldLabel}</label>
										<div class="module-label-form hidden" style="display: inline-block;">
											<input type="text" id="module-label-{$field@index}" class="form-control module-label-field" />
											<input type="hidden" value="{$fieldLabel}" class="module-label-oldlabel" />
											<button type="button" class="btn btn-primary btn-accept"><i class="fa fa-check"></i></button>
											<button type="button" class="btn btn-default btn-cancel"><i class="fa fa-times"></i></button>
										</div>
									</div>
								</div>
								<div class="dd-links" style="display: inline-block; float: right; width: 10%;">
		{if (!in_array ($fieldUiType, array (Field::UI_TYPE_CODE, Field::UI_TYPE_CREATED_TIME, Field::UI_TYPE_MODIFIED_BY, Field::UI_TYPE_OWNER)))}
			{if ($fieldUiType == Field::UI_TYPE_GRID)}
				{if !$IS_INSTANCE}
									<button class="btn btn-primary" type="button" title="Editar" onclick="return GridPropertiesUtils.editGridProperties ('{$moduleName}', '{$fieldName}');"><i class="fa fa-pencil"></i></button>
									<button class="btn recalculate-grid-btn" type="button" title="Actualizar cálculos de todos los registros del campo grid" data-fieldid="{$fieldId}" data-fieldname="{$fieldName}" data-fieldlabel="{$fieldLabel}" data-module="{$moduleName}" style="background-color: #ff6600; color: white; border-color: #ff6600;"><i class="fa fa-refresh"></i></button>
                {/if}
			{else}
									<button class="btn btn-primary" type="button" title="Editar" onclick="return FieldPropertiesUtils.editFieldProperties ('{$moduleName}', '{$fieldName}');"><i class="fa fa-pencil"></i></button>
			{/if}
			{if (isset ($UNMODIFIABLE_FIELDS[$fieldName]))}
									<button class="btn btn-warning" type="button" onclick="FieldPropertiesUtils.showUnmodifiableReasons ({htmlentities (json_encode ($UNMODIFIABLE_FIELDS[$fieldName]))});"><i class="fa fa-lock"></i></button>
			{elseif ($fieldUiType != Field::UI_TYPE_GRID)}
									<button class="btn btn-danger" type="button" onclick="FieldUtils.deleteField ('{$moduleName}', '{$fieldName}');"><i class="fa fa-trash-o"></i></button>
            {elseif ($fieldUiType == Field::UI_TYPE_GRID) && !($IS_INSTANCE) }
									<button class="btn btn-danger" type="button" onclick="FieldUtils.deleteField ('{$moduleName}', '{$fieldName}');"><i class="fa fa-trash-o"></i></button>
			{/if}
		{/if}
								</div>
							</li>
	{/foreach}
						</ul>
					</div>
				</div>
			</div>
		</div>
{/foreach}
	</div>
</div>
    {if !$IS_INSTANCE}
    	{if (isset ($MODULE))}
        {include file='Settings/GridManager/TableFieldWizard.tpl' TF_TEMPLATE = 'table-fields-wizard-template'}
        {foreach $MODULE->getFields () as $field}
            {if ($field->getUiType() eq Field::UI_TYPE_TABLE_FIELD)}
                {foreach $MODULE->getBlocks () as $block}
                    {if $block->getId() eq $field->getBlockId()}
						{foreach $block->getFields() as $blckField}
                            {if $blckField->getId() eq $field->getId()}
                                {assign var="seqBlock" value=$block->getSequence()}
								{assign var="blockLabel" value=$block->getLabel()}
                                {break}
                            {/if}
						{/foreach}
						{break}
                    {/if}
                {/foreach}
                {assign var="myTemplate" value="table-fields-"|cat:$field->getName()|cat:"-template"}
                {include file='Settings/GridManager/TableFieldWizardEdit.tpl' TF_TEMPLATE=$myTemplate BLOCK_NAME=$blockLabel FIELD=$field SEQUENCE=$seqBlock}
            {/if}
        {/foreach}
		{/if}
    {/if}
{include file='Settings/LayoutEditor/FieldPropertiesModal.tpl'}
{include file='Settings/LayoutEditor/GridPropertiesModal.tpl'}
{include file='Settings/LayoutEditor/EntityNumberModal.tpl'}
{include file='Settings/LayoutEditor/BlockModal.tpl'}
{include file='Settings/LayoutEditor/FieldModal.tpl'}
{include file='Settings/LayoutEditor/GridModal.tpl'}
{include file='Settings/LayoutEditor/ModuleEditPermissionsModal.tpl'}
{include file='Settings/LayoutEditor/RelatedListsModal.tpl'}
{include file='Settings/LayoutEditor/EditableFieldsModal.tpl'}
<script type="text/javascript" src="include/colorpicker/js/colorpicker.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/modernizr.custom.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/jquery.nestable.js"></script>
<script type="text/javascript" src="modules/Settings/block-utils.js?v=1.0"></script>
<script type="text/javascript" src="modules/Settings/entity-number-utils.js?v=1.0"></script>
<script type="text/javascript" src="modules/Settings/field-properties-utils.js?v=1.0"></script>
<script type="text/javascript" src="modules/Settings/field-utils.js?v=1.0"></script>
<script type="text/javascript" src="modules/Settings/grid-properties-utils.js?v=1.0"></script>
<script type="text/javascript" src="modules/Settings/grid-utils.js?v=1.0"></script>
<script type="text/javascript" src="modules/Settings/permissions-utils.js?v=1.0"></script>
<script type="text/javascript" src="modules/Settings/related-lists-utils.js?v=1.0"></script>
<script type="text/javascript" src="modules/Settings/wizard-utils.js?v=1.0"></script>
<script type="text/javascript" src="modules/Settings/module-utils.js?v=1.0"></script>
<script type="text/javascript" src="modules/Settings/editable-fields-utils.js?v=1.0"></script>
<script type="text/javascript" src="modules/Settings/tablefields-wizard.js?v=1.0"></script>
<script type="text/javascript">
jQuery(document).ready(function() {
	// Manejar clic en botón de recálculo de grid
	jQuery('.recalculate-grid-btn').on('click', function(e) {
		e.preventDefault();
		
		var $btn = jQuery(this);
		var fieldId = $btn.data('fieldid');
		var fieldName = $btn.data('fieldname');
		var fieldLabel = $btn.data('fieldlabel');
		var moduleName = $btn.data('module');
		
		// Confirmar acción
		var confirmMsg = '¿Está seguro de recalcular todos los valores del campo "' + fieldLabel + '"?\n\n' +
		                'Esta acción:\n' +
		                '• Recalculará todos los campos calculados (uitype 2204)\n' +
		                '• Actualizará las filas summary (uitype 2203)\n' +
		                '• Puede tomar varios minutos si hay muchos registros\n\n' +
		                '¿Desea continuar?';
		
		if (!confirm(confirmMsg)) {
			return;
		}
		
		// Deshabilitar botón y mostrar spinner
		$btn.prop('disabled', true);
		var originalHtml = $btn.html();
		$btn.html('<i class="fa fa-spinner fa-spin"></i>');
		
		// Ejecutar recálculo vía AJAX
		jQuery.ajax({
			url: 'index.php?module=Settings&action=SettingsAjax&file=RecalculateGridField',
			method: 'POST',
			data: {
				fieldid: fieldId,
				batchsize: 100
			},
			dataType: 'json',
			contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
			timeout: 300000, // 5 minutos de timeout
			beforeSend: function(xhr) {
				xhr.overrideMimeType('application/json; charset=UTF-8');
			},
			success: function(response) {
				if (response.success) {
					// Construir mensaje de éxito
					var message = 'Recalculo completado exitosamente\n\n';
					message += 'Campo: ' + (response.field_info.fieldlabel || fieldLabel) + '\n';
					message += 'Modulo: ' + (response.field_info.module || moduleName) + '\n\n';
					message += 'Resultados:\n';
					message += '- Registros procesados: ' + response.processed + '\n';
					message += '- Registros omitidos: ' + response.skipped + '\n';
					
					if (response.errors > 0) {
						message += '- Errores: ' + response.errors + '\n';
					}
					
					if (response.total > 0) {
						message += '- Total de registros: ' + response.total + '\n';
					}
					
					alert(message);
					
					// Mostrar detalles en consola
					console.log('=== RECALCULO GRID COMPLETADO ===');
					console.log('Campo:', fieldLabel);
					console.log('Resultado:', response);
					console.log('Mensajes:', response.messages);
					
				} else {
					alert('Error al recalcular:\n\n' + (response.message || 'Error desconocido'));
					console.error('Error en recalculo:', response);
				}
			},
			error: function(xhr, status, error) {
				var errorMsg = '❌ Error al ejecutar el recálculo';
				
				try {
					var response = JSON.parse(xhr.responseText);
					errorMsg += ':\n\n' + (response.message || error);
				} catch(e) {
					errorMsg += ':\n\n' + error;
				}
				
				alert(errorMsg);
				console.error('Error AJAX:', xhr, status, error);
			},
			complete: function() {
				// Rehabilitar botón
				$btn.prop('disabled', false);
				$btn.html(originalHtml);
			}
		});
	});
	
	// Inicializar tooltips para los botones de recálculo
	jQuery('.recalculate-grid-btn').tooltip({
		placement: 'top',
		container: 'body',
		delay: { show: 500, hide: 100 }
	});
});
</script>
{/strip}
