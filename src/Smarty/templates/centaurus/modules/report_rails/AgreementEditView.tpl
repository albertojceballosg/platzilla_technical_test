{extends file='modules/report_rails/Base/SummaryReportFormLayout.tpl'}
{if $AGREEMENT neq NULL}
    {assign var='tabname' value=$AGREEMENT->getTabName  ()}
    {assign var='content' value=$AGREEMENT->getDescription ()}
    {assign var='entity' value=$AGREEMENT->getExecution ()}
    {assign var='usersIds' value=$AGREEMENT->getUsersInvolved ()}
    {assign var='agreement' value=$AGREEMENT->getAgreement ()}
    {assign var='record' value=$AGREEMENT->getAgreementId()}
    {assign var='agreementName' value=$AGREEMENT->getAgreementName()}
    {assign var='status' value=$AGREEMENT->getAgreementStatus ()}
    {assign var='sequence' value=$AGREEMENT->getSequence ()}
    {assign var='relatedAgreement' value=$AGREEMENT->getRelatedAgreement ()}
{else}
    {assign var='tabname' value=null}
    {assign var='content' value=null}
    {assign var='entity' value=null}
    {assign var='usersIds' value=null}
    {assign var='agreement' value=null}
    {assign var='agreementName' value=null}
    {assign var='record' value=null}
    {assign var='status' value=null}
    {assign var='sequence' value=1}
    {assign var='relatedAgreement' value=null}
{/if}
{block name="css"}
    <link rel="stylesheet" href="include/colorpicker/css/colorpicker.css" type="text/css"/>
    <link rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css" type="text/css"/>
    <link rel="stylesheet" href="themes/centaurus/css/libs/bootstrap-timepicker.css" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="modules/report_rails/report_rails-utils.css"/>
{/block}
{block name="form_name"}agreements-{$idSummaryReport}{/block}
{block name="actionFile"}SaveAgreement{/block}
{block name="isAgreement"}
    <input type="hidden" name="agreement_name" value="{$agreementName}"/>
    <input type="hidden" id="update_istance-{$idSummaryReport}"  name="update_istance" value="no"/>
{/block}
{block name="isAjax"}<input type="hidden" name="Ajax" value="true"/>{/block}
{block name="tabName"}Acuerdos{/block}
{block name="selectedTab"}&tab=AGREEMENTS{/block}
{block name="saveJsAction"}
    onclick="ReportRailesUtils.saveAgreement(this, '{$idSummaryReport}')"
{/block}
{block name="update_button"}
    {if $agreementName neq NULL && $REPORT_PUBLISHED}
        <button type="button"
                data-form-id="{$agreementName}"
                onclick="ReportRailesUtils.updateAgreement(this, '{$idSummaryReport}')"
                class="btn btn-success">Actualizar acuerdo
        </button>&nbsp;
    {/if}
{/block}
{block name="mainBoxHeader"}
    <header class="main-box-header clearfix">
        <h2 class="pull-left">Información general del acuerdo</h2>
    </header>
{/block}
{block name="mainBoxBody"}
    {* Linea uno *}
    <div class="row">
        {* Acuerdo *}
        <div class="col-md-6">
            <div class="label-input" style="text-align: left;">
                <label for="performance-from">Enunciado del acuerdo&nbsp;<span class="required">*</span></label>
            </div>
            <div id="a-div-agreement_title" class="form-group field-container">
                <input type="text" id="agreement_title_{$idSummaryReport}" name="agreement_title"
                       value="{$agreement}"
                       title="indice de rendimiento"
                       maxlength="255" class="form-control"/>
                <span id="a-agreement_title" class="help-block"></span>
            </div>

        </div>
        {* Estado *}
        <div class="col-md-6">
            <div class="label-input" style="text-align: left;">
                <label for="news-from">Estado&nbsp;<span class="required">*</span></label>
            </div>
            <div id="a-div-agreement_status" class="form-group field-container">
                <select class="form-control" name="agreement_status" title="Estado del rendimiento"
                        id="agreement_status_{$idSummaryReport}">
                    {foreach $AGREEMENTS_STATUS as $statusValue => $statusName}
                        <option value="{$statusValue}"
                                {if $statusValue eq $status}selected{/if} >{$statusName}</option>
                    {/foreach}
                </select>
                <span id="a-agreement_status" class="help-block"></span>
            </div>
        </div>
    </div>
    {* Linea dos *}
    <div class="row">
        {*$AVAILABLE_MODULES|var_dump}
        {* Módulos de la instancia *}
        <div class="col-md-6">
            <div class="label-input" style="text-align: left;">
                <label for="performance-from">Módulo del acuerdo&nbsp;<span class="required">*</span></label>
            </div>
            <div id="a-div-$agreement_module" class="form-group field-container">
                <select class="form-control" name="agreement_module" title="Modulo del acuerdo"
                        onchange="ReportRailesUtils.selectTabAgreement(this,'{$idSummaryReport}')"
                        id="agreement_module_{$idSummaryReport}">
                    <option value="">Seleccionar modulo</option>
                    {foreach $AVAILABLE_MODULES as $tab}
                        <option value="{$tab->getName()}"
                                {if $tab->getName() eq $tabname}selected{/if} >{$tab->getLabel()}</option>
                    {/foreach}
                </select>
                <span id="a-agreement_module" class="help-block"></span>
            </div>
        </div>
        {* entities *}
        <div class="col-md-6">
            <div class="label-input" style="text-align: left;">
                <label for="news-from">Ejecución&nbsp;<span class="required">*</span></label>
            </div>
            <div class="form-group col-md-12 field-container" id="div_entity-{$idSummaryReport}" style="">
                <input type="hidden" id="entity_type" name="entity_type" value="" class="small">
                <div class="input-group" style="width: 100%;">
                    <input type="hidden" id="entity" name="entity" value="{$entity}"
                           class="for-filter module-reference">
                    <input type="text" id="edit_entity_display" name="entity_display" value="{$relatedAgreement}"
                           class="form-control input-readonly b-right"
                           readonly="readonly" placeholder="{if $tabname eq NULL}No hay modulo seleccionado{/if}">
                    <div id="related_tab-{$idSummaryReport}" class="input-group-addon"
                         data-current-module="report_rails@{$INSTANCE_CODE}" data-display-field-id="edit_entity_display"
                         data-field-id="entity"
                         data-referenced-module="{if $tabname eq NULL}report_rails{else}{$tabname}{/if}"
                         data-title="Seleccione "
                         onclick="RelatedModuleModalUtils.openModal (this);">
                        <i class="fa fa-plus-circle"></i>
                    </div>
                    <div class="input-group-addon"
                         onclick="var fieldContainer = jQuery (this).closest ('.field-container'); fieldContainer.find ('#edit_entity_display').val (''); fieldContainer.find ('#entity').val (''); return false;">
                        <i class="fa fa-eraser"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {* Linea tres *}
    <div class="row">
        {* Usuarios *}
        <div class="col-md-6">
            <div class="label-input" style="text-align: left;">
                <label for="news-from">Usuarios&nbsp;<span class="required">*</span></label>
            </div>
            <div id="a-div-agreement_users" class="form-group field-container">
                <select class="form-control" name="agreement_users[]" title="Usuarios de la instancia" multiple
                        id="agreement_users_{$idSummaryReport}">
                    {foreach $AVAILABLE_USERS as $user}
                        <option value="{$user->getId()}"
                                {if in_array($user->getId(),$usersIds)}selected{/if} >{$user->getFirstName()} {$user->getLastName()}</option>
                    {/foreach}
                </select>
                <span id="a-agreement_users" class="help-block"></span>
            </div>
        </div>
        <div class="col-md-6">&nbsp;
            <input type="hidden" name="agreement_sequence" value="{$sequence}" class="small">
        </div>
    </div>
    {* Linea cuatro *}
    <div class="row">
        {* Comentarios del estado de rendimiento *}
        <div class="col-xs-12">
            <div class="label-input" style="text-align: left;">
                <label for="performance-content">Descripción del acuerdo <span class="required">*</span></label>
            </div>
            <div class="form-group field-container">
                <textarea id="agreement-content" name="agreement_content" class="form-control">{$content}</textarea>
            </div>
        </div>
    </div>
{/block}
{block name="js"}
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-timepicker.min.js"></script>
    <script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="include/colorpicker/js/colorpicker.js"></script>
    <script type="text/javascript" src="modules/report_rails/report_rails-utils.js"></script>
    <script type="text/javascript">
        ReportRailesUtils.initAgreement();
    </script>
{/block}