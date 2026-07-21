{strip}
    {math equation= rand() assign= "idGroupTheme"}
    <link rel="stylesheet" type="text/css" href="modules/questionnaire/question.css"/>
    <link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css">
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        <div class="row">
            <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
                <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
            </div>
        </div>
    {/if}
    {if isset($GROUP_THEME) && !empty($GROUP_THEME)}
        <div class="row-grid-view" style="max-height: 500px; overflow-y: auto; overflow-x: hidden">
            <div class="col-lg-12 col-md-12 col-xs-12 col-sm-12">
                <div class="row" id="question-{$idGroupTheme}">

                    <div class="col-md-6" style="margin-bottom: 10px">
                        <div class="col-md-4">
                            <div class="label-input">
                                <label for=asking-for">
                                    <span id="for=asking-for"></span>&nbsp;Filtrar preguntas</label>
                            </div>
                        </div>
                        <div class="form-group col-md-8 field-container" style="margin-bottom: 0!important;">
                            <select id="question-filter" class="border form-control for-filter"
                                    tabindex="" onchange="QuestionUtils.navFilter (this, 'ROW-GROUP-THEME');">
                                <option value="" disabled="disabled">Seleccionar pregunta</option>
                                <option value="" selected>Todas las preguntas</option>
                                {foreach $GROUP_THEME as $key => $groupTheme}
                                    <option value="ROW-GROUP-THEME-{$groupTheme->getQuestionId()}">{$key}
                                        &nbsp;-&nbsp;{$groupTheme->getQuestion()|substr:0:80|cat:'...'}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6" style="margin-bottom: 10px">
                        <div class="col-md-4">
                            <div class="label-input">
                                <label for="entiy-row">
                                    <span id="entiy-row"></span>&nbsp;</label>
                            </div>
                        </div>
                        <div class="form-group col-md-8" style="margin-bottom: 0!important;">&nbsp;</div>
                    </div>

                </div>
                <form id="form-{$idGroupTheme}" name="form-range-{$idGroupTheme}" method="post" action="index.php">
                    <input type="hidden" name="module" value="questionnaire"/>
                    <input type="hidden" name="action" value="AjaxQuestionUtils"/>
                    <input type="hidden" name="Ajax" value="true"/>
                    <input type="hidden" name="function" value="SAVE_GROUP_THEME"/>
                    <input type="hidden" name="record" value="{$QUESTIONNAIRE_ID}"/>
                    {foreach $GROUP_THEME as $keyGroup => $groupTheme}
                        {assign var="setQuestion" value="yes"}
                        <div id="ROW-GROUP-THEME-{$groupTheme->getQuestionId()}" class="row">
                            <div class="col-lg-12 col-md-12 col-xs-12" style="margin-bottom: 0">
                                <p class="text-left border" style="padding: 2px 4px; margin-bottom: 0">
                                    <span style="font-weight: bold;">Pregunta:&nbsp;</span>{$groupTheme->getQuestion()}
                                </p>
                            </div>
                            <div class="col-lg-12 col-md-12 col-xs-12" style="margin-top: 1px; padding-left: 0!important;padding-right: 0!important;">
                                <div class="col-lg-6 col-md-6 col-xs-6" style="margin-left: 0!important;">
                                    <p class="text-left border" style="padding: 2px 4px">
                                        <span style="font-weight: bold;">Grupo:&nbsp;</span>{$groupTheme->getGroupName()}
                                    </p>
                                </div>
                                <div class="col-lg-6 col-md-6 col-xs-6" style="font-weight: 0!important;">
                                    <p class="text-left border" style="padding: 2px 4px">
                                        <span style="font-weight: bold;">Tema:&nbsp;</span>{$groupTheme->getThemeName()}
                                    </p>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-xs-12">
                                <table class="table table-striped" style="margin-bottom: 0!important;">
                                    <thead>
                                    <tr>
                                        <th style="width: 2%"><small></small>ID</th>
                                        <th style="width: 10%"><small>Mínimo</small></th>
                                        <th style="width: 10%"><small>Máximo</small></th>
                                        <th style="width: 76%"><small>Feed back</small></th>
                                        <th style="width: 2%"><small></small>&nbsp;</th>
                                    </tr>
                                    </thead>
                                    <tbody id="THEME-RANGE-{$groupTheme->getQuestionId()}">
                                    {assign var="myTheme" value=$groupTheme->getThemeName()|cat:'-'|cat:$groupTheme->getQuestionId()}
                                    {foreach $groupTheme->getRanges() as $key => $range}
                                        {assign var="myTheme" value=$range->getThemeName()|cat:'-'|cat:$range->getQuestionId()}
                                    <tr>
                                        <td  style="vertical-align: top!important;">{$range->getId()}</td>
                                        <td  style="vertical-align: top!important;">
                                            <div  id="div-sn-minimum-{$key}" class="input-text">
                                                <!-- {$myTheme} -->
                                                <input type="text" title="Mínimo" class="col-lg-8 col-md-8 col-xs-8" name="ranges[{$myTheme}][minimum][{$key}]" value="{$range->getMinimum()}">
                                                <input type="hidden" name="ranges[{$myTheme}][ID][{$key}]" value="{$range->getId()}"> <!-- wa 08/06/22-->
                                            </div>
                                            <span id="sn-minimum-{$key}" class="help-block" style="color: red;font-size: small"></span>
                                        </td>
                                        <td  style="vertical-align: top!important;">
                                            <div  id="div-sn-maximum-{$key}" class="input-text">
                                                <input type="text" title="Máximo" class="col-lg-8 col-md-8 col-xs-8" name="ranges[{$myTheme}][maximum][{$key}]" value="{$range->getMaximum()}">
                                            </div>
                                            <span id="sn-maximum-{$key}" class="help-block" style="color: red;font-size: small"></span>
                                        </td>
                                        <td  style="vertical-align: top!important;">
                                            <div  id="div-sn-feedback-{$key}" class="input-text">
                                            <textarea id="question_feedback" title="Feed back" name="ranges[{$myTheme}][feedback][{$key}]" class="form-control border" placeholder="Feed back del rango"
                                                      rows="1">{$range->getFeedBack()}</textarea>
                                            </div>
                                            <span id="sn-feedback-{$key}" class="help-block" style="color: red;font-size: small"></span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-link"  title="Eliminar rango" onclick="QuestionUtils.delRange(this, '{$range->getId()}');">
                                                <i class="fa fa-trash-o"></i>
                                            </button>
                                        </td>
                                    </tr>
                                        {assign var='lastRangeId' value=$key}
                                        {assign var="lastTheme" value=$myTheme}
                                    {/foreach}
                                    <input type="hidden" name="ranges[{if $lastTheme eq NULL}{$myTheme}{else}{$lastTheme}{/if}][idquestion][0]" value="{$groupTheme->getQuestionId()}">
                                    </tbody>
                                </table>
                                <div class="action-bar text-center">
                                    <button type="button" class="btn btn-link"
                                            data-theme="{if $lastTheme eq NULL}{$myTheme}{else}{$lastTheme}{/if}"
                                            data-id="{if $lastRangeId eq NULL}0{else}{$lastRangeId + 1}{/if}"
                                            title="Agregar rango"
                                            onclick="QuestionUtils.addRange (this, '#THEME-RANGE-{$groupTheme->getQuestionId()}', '{$idGroupTheme}')"><i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        {assign var="lastId" value=$keyGroup}
                    {/foreach}
                    <input type="hidden" id="LAST-ID-{$idGroupTheme}" value="{$lastId + 1}"/>
                </form>
            </div>
        </div>
        <div class="row-grid-view justify-content-center">
            <div class="col-md-4" style="margin-top: 12px">
                <div class="btn-group center-block" style="text-align: center">
                    <button type="button" onclick="QuestionUtils.saveGroupTheme (this, '{$idGroupTheme}');" class="btn btn-success">Guardar</button>
                    <button type="button"data-dismiss="modal" aria-hidden="true" class="btn btn-warning">Cancelar</button>
                </div>
            </div>
        </div>
    {/if}
    <script type="text/html" id="RANGE-TEMPLATE-{$idGroupTheme}">
        <tr>
            <td>&nbsp;</td>
            <td  style="vertical-align: top!important;">
                <div id="div-sn-minimum-__ID_ROW__" class="input-text">
                    <input type="text"  title="Mínimo"  class="col-lg-8 col-md-8 col-xs-8" name="ranges[__THEME__][minimum][__ID_ROW__]" value="0">
                    <input type="hidden" name="ranges[__THEME__][ID][__ID_ROW__]" value="">
                </div>
                <span id="sn-minimum-__ID_ROW__" class="help-block" style="color: red;font-size: small"></span>
            </td>
            <td  style="vertical-align: top!important;">
                <div  id="div-sn-maximum-__ID_ROW__" class="input-text">
                    <input type="text"  title="Máximo"  class="col-lg-8 col-md-8 col-xs-8" name="ranges[__THEME__][maximum][__ID_ROW__]" value="1">
                </div>
                <span id="sn-maximum-__ID_ROW__" class="help-block" style="color: red;font-size: small"></span>
            </td>
            <td  style="vertical-align: top!important;">
                <div id="div-sn-feedback-__ID_ROW__" class="input-text">
                    <textarea id="question_feedback"  title="Feed back"  name="ranges[__THEME__][feedback][__ID_ROW__]" class="form-control border" placeholder="Feed back del rango" rows="1"></textarea>
                </div>
                <span id="sn-feedback-__ID_ROW__" class="help-block" style="color: red;font-size: small"></span>
            </td>
            <td>
                <button type="button" class="btn btn-link"  title="Eliminar rango" onclick="QuestionUtils.delRange(this, 'INSERT');">
                    <i class="fa fa-trash-o"></i>
                </button>
            </td>
        </tr>
    </script>
    <script type="text/javascript" src="/modules/questionnaire/question-utils.js"></script>
{/strip}