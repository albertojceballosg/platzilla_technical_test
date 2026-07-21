{strip}
    {if (isset ($HELP_FIELD))}
        {assign var='fieldId' value=$HELP_FIELD->getId()}
        {assign var='fieldDescription' value=$HELP_FIELD->getDescription()}
        {assign var='fieldModuleName' value=$HELP_FIELD->getModuleName()}
        {assign var='fieldFieldId' value=$HELP_FIELD->getFieldId()}
        {assign var='fieldFieldName' value=$HELP_FIELD->getFieldName()}
        {assign var='fieldFieldLabel' value=$HELP_FIELD->getFieldLabel()}
        {assign var='fieldUiType' value=$HELP_FIELD->getUiType()}
        {assign var='fieldTitle' value=$HELP_FIELD->getTitle()}
        {assign var='fieldIsEditable' value=$HELP_FIELD->isEditable()}
        {assign var='fieldStatus' value=$HELP_FIELD->getStatus()}
        {assign var='fieldImage' value=$HELP_FIELD->getImage()}
        {assign var="fieldImageType" value=null}
        {assign var='fieldVideo' value=$HELP_FIELD->getUrlVideo()}
        {assign var='fieldTypeVideo' value=$HELP_FIELD->getVideoType ()}
    {else}
        {assign var='fieldId' value=null}
        {assign var='fieldDescription' value=null}
        {assign var='fieldModuleName' value=null}
        {assign var='fieldFieldLabel' value=null}
        {assign var='fieldUiType' value=null}
        {assign var='fieldFieldName' value=null}
        {assign var='fieldTitle' value=null}
        {assign var='fieldIsEditable' value=null}
        {assign var='fieldStatus' value=null}
        {assign var='fieldImage' value=null}
        {assign var="fieldImageType" value=null}
        {assign var='fieldVideo' value=null}
        {assign var='fieldTypeVideo' value=null}
    {/if}
    {math equation= rand() assign= "idHelp"}
    {math equation= rand() assign= "idImage"}
    {if $FIELD_OBJECT neq NULL}
        {assign var='fieldMandatory' value=$FIELD_OBJECT->isMandatory ()}
    {/if}
    <style type="text/css">
        .row-panel-course {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px
        }

        .justify-content-center {
            -webkit-box-pack: center !important;
            -ms-flex-pack: center !important;
            justify-content: center !important
        }

        .no-gutters > .col,
        .no-gutters > [class*=col-] {
            padding-right: 1px;
            padding-left: 1px;
        }

        fieldset.scheduler-border {
            border: solid 1px #DDD !important;
            padding: 0 10px 10px 10px;
            border-bottom: none;
        }

        legend.scheduler-border {
            width: auto !important;
            border: none;
            font-size: 14px;
        }
        thead, tbody { display: block; }
        tbody {
            height: 210px;
            overflow-y: auto;
            overflow-x: hidden;
        }

    </style>
    <link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/wizard.css"/>
    <link rel="stylesheet" type="text/css" href="modules/Settings/layout-editor.css"/>
    {*<link type="text/css" href="modules/Courses/Courses.css"/>*}
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        <div class="row">
            <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
                <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
            </div>
        </div>
    {/if}
    <div class="row">
        <div class="col-xs-12 col-md-12">
            {if (!empty ($fieldVideo))}
                {if $fieldTypeVideo eq 'VIMEO'}
                    <div id="video-{$fieldId}"
                         class="embed-responsive embed-responsive-16by9 video"{if (null !== $fieldVideo)} data-vimeo-url="{$fieldVideo}"{/if}></div>
                {else}
                    <div style="text-align: center">
                        <iframe id="video-{$fieldId}" class="youtube-video" width="512" height="288"
                                src="{if (null !== $fieldVideo)}{$fieldVideo}{/if}" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen="allowfullscreen">
                        </iframe>
                    </div>
                {/if}
            {elseif false}
                {if !empty ($fieldImage)}
                    <img id="course-photo-{$fieldId}"
                         src="data:{$fieldImageType}; base64, {$courseImage}"
                         class="img-responsive center-block">
                {else}
                    <img class="img-responsive center-block"
                         src='data:image/png;base64,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'/>
                {/if}
            {/if}
            <div class="">{$fieldDescription}</div>
        </div>
        <div id="block-fields-{$BLOCKS_ID}" class="col-md-12 dd block-fields" data-id="{$BLOCKS_ID}" style="width: 100%; margin-top: 12px">
        </div>
        <div class="col-xs-12">
            <form id="form-help-label-{$idHelp}" method="post" class="form-inline" role="form">
                <fieldset>
                    <legend style="font-size: small">
                        <strong style="margin-left: 10px">EDITAR CAMPO:</strong>
                    </legend>
                    <input type="hidden" id="module-{$idHelp}" name="module" value="{$fieldModuleName}"/>
                    <input type="hidden" name="action" value="AjaxEditViewUtils"/>
                    <input type="hidden" name="Ajax" value="true"/>
                    <input type="hidden" name="modulename" value="{$fieldModuleName}"/>
                    <input type="hidden" name="fieldid" value="{$fieldFieldId}"/>
                    <input type="hidden" name="function" value="SAVE_FIELD_LABEL"/>
                    <input type="text" id="label-{$idHelp}" name="label" value="{$fieldFieldLabel}"
                           class="form-control new-label" placeholder="Nombre del campo"/>&nbsp;
                    <button type="button"
                            class="btn btn-primary"
                            onclick="HelpUtils.changeFieldLabel(this, '{$idHelp}')"><i class="fa fa-check"></i>&nbsp;Cambiar&nbsp;
                        nombre
                    </button>&nbsp;
                    {if $fieldIsEditable eq 'YES'}
                        <button title="Ocultar campo" type="button"
                                onclick="HelpUtils.setFieldVisibility (this, '{$fieldMandatory}', '{$fieldModuleName}', '{$fieldFieldId}')"
                                class="btn btn-info"><i class="fa fa-eye" aria-hidden="true"></i></button>
                        &nbsp;
                        <button title="{if empty ($fieldMandatory)}Colocar obligatoriedad{else}Quitar obligatoriedad{/if}"
                                type="button"
                                onclick="HelpUtils.setMandatory (this,'{$fieldMandatory}', '{$fieldFieldName}', '{$fieldModuleName}')"
                                class="btn {if empty ($fieldMandatory)}btn-success{else}btn-danger{/if}"><i
                                    class="fa fa-gavel" aria-hidden="true"></i></button>
                        &nbsp;
                    {/if}
                    <span id="help-label-{$idHelp}" style="color: red" class="help-block"></span>
                </fieldset>
            </form>
        </div
        {if ($fieldIsEditable eq 'YES') && ($PICK_LIST_DATA neq NULL) }
            {assign var='pickLists' value=$PICK_LIST_DATA->getValues()}
            <div class="col-xs-12 col-md-12 text-center">
                <div class="table-responsive">
                    <table class="table" id="help-pick-list-{$idHelp}" style="margin-bottom: 0;">
                        <thead>
                        <tr>
                            <th class="col-xs-3">Valores de la lista</th>
                            <th class="col-xs-2"></th>
                        </tr>
                        </thead>
                        <tbody id="picklist-values">
                        {foreach from=$pickLists item=$pickList name=list}
                            <tr class="picklist-value" data-picklist-value-id="{$pickList->getId()}">
                                <td class="col-xs-3">
                                    <input type="hidden" class="picklist-value-id" value="{$pickList->getId()}">
                                    <input type="hidden" class="picklist-seq" value="{$smarty.foreach.list.index}">
                                    <input type="text" class="form-control picklist-label" placeholder=""
                                           value="{$pickList->getValue()}"">
                                </td>
                                <td class="col-xs-3">
                                    <div class="center-block" style="display: inline">
                                        {if $PICK_LIST_RELATED eq NULL}
                                        <button type="button" class="btn btn-xs btn-danger delete-value-button"
                                                onclick="HelpUtils.deletePicklistValue (this);"><i
                                                    class="fa fa-trash-o"></i></button>&nbsp;
                                        {/if}
                                        <button type="button" class="btn btn-primary btn-xs"
                                                onclick="HelpUtils.movePickListRowUp (this)"><i
                                                    class="fa fa-arrow-up" aria-hidden="true"></i></button>&nbsp;
                                        <button type="button" class="btn btn-danger btn-xs"
                                                onclick="HelpUtils.movePickListRowDown (this)"><i
                                                    class="fa fa-arrow-down" aria-hidden="true"></i></button>
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                        <tr id="picklist-value-hidden"  class="hide" data-picklist-value-id="">
                            <td class="col-xs-3">
                                <input type="hidden" class="picklist-value-id" value="">
                                <input type="hidden" class="picklist-seq" value="">
                                <input type="text" class="form-control picklist-label" placeholder=""
                                       value="">
                            </td>
                            <td class="col-xs-3">
                                <div class="center-block" style="display: inline">
                                    {if $PICK_LIST_RELATED eq NULL}
                                        <button type="button" class="btn btn-xs btn-danger delete-value-button"
                                                onclick="HelpUtils.deletePicklistValue (this);"><i
                                                    class="fa fa-trash-o"></i></button>&nbsp;
                                    {/if}
                                    <button type="button" class="btn btn-primary btn-xs"
                                            onclick="HelpUtils.movePickListRowUp (this)"><i
                                                class="fa fa-arrow-up" aria-hidden="true"></i></button>&nbsp;
                                    <button type="button" class="btn btn-danger btn-xs"
                                            onclick="HelpUtils.movePickListRowDown (this)"><i
                                                class="fa fa-arrow-down" aria-hidden="true"></i></button>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr class="action-bar">
                            <td colspan="5" style="padding: 0;text-align: center">
                                <span id="help-pick-list-msn-{$idHelp}" style="color: red" class="help-block"></span>
                                <button type="button" class="btn btn-success btn-icon center-block add-value-button"
                                        style="margin-top: 10px"
                                        onclick="HelpUtils.addPicklistValue (this, '{$idHelp}');">
                                    <i class="fa fa-plus"></i></button>&nbsp;
                                <button type="button" class="btn btn-primary"
                                        style="margin-top: 10px"
                                        onclick="HelpUtils.savePicklistValue (this, '{$idHelp}', '{$fieldFieldName}', '{$fieldModuleName}');">
                                    <i class="fa fa-check"></i>&nbsp;Actualizar lista</button>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        {/if}
        <div class="col-xs-12" style="min-height: {if (empty ($fieldVideo))}80px {else}20px{/if}">&nbsp;</div>
    </div>
    {include file='Settings/LayoutEditor/FieldPropertiesModal.tpl'}
    {include file='Settings/LayoutEditor/FieldModal.tpl'}
    <script type="text/javascript" src="modules/Settings/field-properties-utils.js?v=1.0"></script>
    <script type="text/javascript" src="modules/Settings/field-utils.js?v=1.0"></script>
    <script type="text/javascript" src="https://player.vimeo.com/api/player.js"></script>
{/strip}