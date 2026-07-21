{math equation= rand() assign= "idOption"}
{if isset($ANSWERS_OPTIONS) && !empty($ANSWERS_OPTIONS)}
    <div class="row-question-view justify-content-center" style="margin-bottom: 20px">
        <div class="col-md-8 rounded border" style="padding: 10px; background-color: #f9f8f7">
            <div class="table-responsive">
                <table class="table rules justify-content-center">
                    <thead>
                    <tr>
                        <th class="" style="width: 49%">Puntuación</th>
                        <th class="" style="width: 49%">Concepto</th>
                        <th class="" style="width: 12%">Acciones</th>
                    </tr>
                    </thead>
                    <tr>
                        <td><p class="text-right">Máximo valor:</p></td>
                        <td colspan="2"></td>
                    </tr>
                    <tbody id="TBODY-SORT_SIMPLE-{$idOption}" data-next-option="{$NUM_ROWS}">
                    <tr>
                        <td><p class="text-right">Cantidad de categorías:</p></td>
                        <td colspan="2">
                            <div class="col-md-2 col-xs-2" style="margin-left: 0; padding-left: 0">
                                <input type="number" tabindex="" min="0"
                                       name="question[{$ID}][response][0][data]"
                                       class="form-control col-lg-2 col-md-2 col-xs-2"
                                       id="sort-simple-max-{$idOption}" value="{$ANSWERS_OPTIONS[0]->getAdditionalData()}"
                                       data-tamplate="SORT_SIMPLE"
                                       onchange="QuestionUtils.setCategories(this, '{$idOption}')">
                            </div>
                        </td>
                    </tr>
                    {assign var="maxRow" value=$ANSWERS_OPTIONS[0]->getAdditionalData()}
                    {foreach $ANSWERS_OPTIONS as $answerOption}
                        <tr class="rule ">
                            <input type="hidden" name="question[{$ID}][response][{$answerOption->getSequence()}][answereid]" value="{$answerOption->getId()}">
                            <td class="" style="vertical-align: top!important;">
                                <div class="col-md-2 col-xs-2 pull-right">
                                    <select name="question[{$ID}][response][{$answerOption->getSequence()}][value]"
                                            class="col-md-2 col-xs-2 form-control for-filter"
                                            tabindex="">
                                        {section name=bar start=1 loop=($maxRow +1) step=1}
                                            <option value="{$smarty.section.bar.index}" {if $answerOption->getValue() eq $smarty.section.bar.index}selected="selected"{/if}>
                                                &nbsp;{$smarty.section.bar.index}&nbsp;</option>
                                        {/section}
                                    </select>
                                </div>
                            </td>
                            <td class="" style="vertical-align: top!important;">
                                <input type="text" tabindex="" name="question[{$ID}][response][{$answerOption->getSequence()}][label]"
                                       id="question_response"
                                       style="margin-bottom: 2px"
                                       value="{$answerOption->getMainLabel()}" class="form-control">
                                <textarea id="question_feedback" name="question[{$ID}][response][{$answerOption->getSequence()}][feedback]" class="form-control"
                                          tabindex="" placeholder="Feed back"  rows="2">{$answerOption->getFeedBack()}</textarea>
                            </td>
                            <td class="">
                                </button>&nbsp;<button type="button" class="btn btn-link" data-tamplate="SORT_SIMPLE"
                                                       title="Eliminar"
                                                       onclick="QuestionUtils.delResponseOption (this, '{$idOption}');">
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
            <div class="action-bar text-center">
                <div class="col-md-12 text-center">
                    <small>Seleccione la clasificación esperada.</small>
                </div>
                <button type="button" class="btn btn-link" title="Agregar opción" data-tamplate="SORT_SIMPLE"
                        onclick="QuestionUtils.addResponseOption (this, '{$idOption}');"><i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
    </div>
{else}
    <div class="row-question-view justify-content-center" style="margin-bottom: 20px">
        <div class="col-md-8 rounded border" style="padding: 10px; background-color: #f9f8f7">
            <div class="table-responsive">
                <table class="table rules justify-content-center">
                    <thead>
                    <tr>
                        <th class="" style="width: 49%">Puntuación</th>
                        <th class="" style="width: 49%">Concepto</th>
                        <th class="" style="width: 12%">Acciones</th>
                    </tr>

                    </thead>
                    <tbody id="TBODY-SORT_SIMPLE-{$idOption}" data-next-option="1">
                    <tr>
                        <td><p class="text-right">Cantidad de categorías:</p></td>
                        <td colspan="2">
                            <div class="col-md-2 col-xs-2" style="margin-left: 0; padding-left: 0">
                            <input type="number" tabindex="" min="0"
                                   name="question[{$ID}][response][0][data]"
                                   class="form-control col-lg-2 col-md-2 col-xs-2"
                                   data-tamplate="SORT_SIMPLE"
                                   id="sort-simple-max-{$idOption}" value="3"
                                   onchange="QuestionUtils.setCategories(this, '{$idOption}', event)">
                            </div>
                        </td>
                    </tr>
                    <tr class="rule ">
                        <td class="" style="vertical-align: top!important;">
                            <div class="col-md-2 col-xs-2 pull-right">
                                <select name="question[{$ID}][response][0][value]"
                                        class="col-md-2 col-xs-2 form-control for-filter"
                                        tabindex="">
                                    <option value="1">&nbsp;1&nbsp;</option>
                                    <option value="2">&nbsp;2&nbsp;</option>
                                    <option value="3">&nbsp;3&nbsp;</option>
                                </select>
                            </div>
                        </td>
                        <td class="" style="vertical-align: top!important;">
                            <input type="text" tabindex="" name="question[{$ID}][response][0][label]"
                                   id="question_response"
                                   style="margin-bottom: 2px"
                                   value="" class="form-control">
                            <textarea id="question_feedback" name="question[{$ID}][response][0][feedback]" class="form-control"
                                      tabindex="" placeholder="Feed back"  rows="2"></textarea>
                        </td>
                        <td class="">
                            </button>&nbsp;<button type="button" class="btn btn-link" data-tamplate="SORT_SIMPLE"
                                                   title="Eliminar"
                                                   onclick="QuestionUtils.delResponseOption (this, '{$idOption}');">
                                <i class="fa fa-trash-o"></i>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="action-bar text-center">
                <div class="col-md-12 text-center">
                    <small>Seleccione la clasificación esperada.</small>
                </div>
                <button type="button" class="btn btn-link" title="Agregar opción" data-tamplate="SORT_SIMPLE"
                        onclick="QuestionUtils.addResponseOption (this, '{$idOption}');"><i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
    </div>
{/if}
<script type="text/html" id="SORT_SIMPLE-TEMPLATE-{$idOption}">
    <tr class="rule ">
        <td class="" style="vertical-align: top!important;">
            <div class="col-md-2 col-xs-2 pull-right">
                <select name="question[{$ID}][response][__ID__][value]"
                        class="col-md-2 col-xs-2 form-control for-filter"
                        tabindex="">
                    <option value="1">&nbsp;1&nbsp;</option>
                </select>
            </div>
        </td>
        <td class="" style="vertical-align: top!important;">
            <input type="text" tabindex="" name="question[{$ID}][response][__ID__][label]" id="question_response"
                   style="margin-bottom: 2px"
                   value="" class="form-control">
            <textarea id="question_feedback" name="question[{$ID}][response][__ID__][feedback]" class="form-control"
                      tabindex="" placeholder="Feed back"  rows="2"></textarea>
        </td>
        <td class="">
            </button>&nbsp;<button type="button" class="btn btn-link" data-tamplate="SORT_SIMPLE" title="Eliminar"
                                   onclick="QuestionUtils.delResponseOption (this, '{$idOption}');">
                <i class="fa fa-trash-o"></i>
            </button>
        </td>
    </tr>
</script>