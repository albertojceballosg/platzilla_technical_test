{math equation= rand() assign= "idOption"}
{if isset($ANSWERS_OPTIONS) && !empty($ANSWERS_OPTIONS)}
    <div class="row-question-view justify-content-center" style="margin-bottom: 20px">
        <div class="col-md-8 rounded border" style="padding: 10px; background-color: #f9f8f7">
            <div class="table-responsive">
                <table class="table rules justify-content-center">
                    <thead>
                    <tr>
                        <th class="" style="width: 75%">Opción</th>
                        <th class="" style="width: 25%">Valor</th>
                        <th class="" style="width: 5%">Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="TBODY-MORE_THAT_ONE_OPTION-{$idOption}" data-next-option="{$NUM_ROWS}">
                    {foreach $ANSWERS_OPTIONS as $answerOption}
                        <tr class="rule ">
                            <input type="hidden" name="question[{$ID}][response][{$answerOption->getSequence()}][answereid]" value="{$answerOption->getId()}">
                            <td class="" style="vertical-align: top!important;width: 75%">
                                <div class="checkbox pull-left"  style="margin-top: 0;width: 100%">
                                    <label class="col-md-12" style="margin-bottom: 1px">
                                        <input type="checkbox" name="question[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                               value="{$answerOption->getSequence()}" {if $answerOption->getSelected() neq NULL}checked {/if}>
                                        <input type="text" tabindex="" placeholder="Etiqueta"
                                               class="form-control"
                                               name="question[{$ID}][response][{$answerOption->getSequence()}][label]" value="{$answerOption->getMainLabel()}" class="form-control">
                                    </label>
                                    <textarea id="question_feedback" name="question[{$ID}][response][{$answerOption->getSequence()}][feedback]" class="form-control"
                                              tabindex="" placeholder="Feed back"  rows="2">{$answerOption->getFeedBack()}</textarea>
                                </div>
                            </td>
                            <td class="" style="vertical-align: top!important;width: 25%">
                                <input type="text" tabindex="" placeholder="Valor de la opción"  style="margin-top: 0"
                                       name="question[{$ID}][response][{$answerOption->getSequence()}][value]" value="{$answerOption->getValue()}" class="form-control">

                            </td>
                            <td class="" style="vertical-align: top!important;width: 5%">
                                </button>&nbsp;<button type="button" class="btn btn-link" data-tamplate="MORE_THAT_ONE_OPTION"
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
                    <small>Seleccione la respuesta esperada.</small>
                </div>
                <button type="button" class="btn btn-link" title="Agregar opción" data-tamplate="MORE_THAT_ONE_OPTION"
                        onclick="QuestionUtils.addResponseOption (this, '{$idOption}');"><i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
    </div>
{else}
<div class="row-question-view justify-content-center"  style="margin-bottom: 20px">
    <div class="col-md-8 rounded border" style="padding: 10px; background-color: #f9f8f7">
        <div class="table-responsive">
            <table class="table rules justify-content-center">
                <thead>
                <tr>
                    <th class="" style="width: 75%">Opción</th>
                    <th class="" style="width: 20%">Valor</th>
                    <th class="" style="width: 5%">Acciones</th>
                </tr>
                </thead>
                <tbody id="TBODY-MORE_THAT_ONE_OPTION-{$idOption}" data-next-option="1"  >
                <tr class="rule ">
                    <td class="" style="vertical-align: top!important;width: 75%">
                        <div class="checkbox pull-left" style="margin-top: 0;width: 100%">
                            <label class="col-md-12"  style="margin-bottom: 1px">
                                <input type="checkbox" name="question[{$ID}][response][0][selected]"  value="0">
                                <input type="text" tabindex="" placeholder="Etiqueta" name="question[{$ID}][response][0][label]" value="" class="form-control">
                            </label>
                            <textarea id="question_feedback" name="question[{$ID}][response][0][feedback]" class="form-control"
                                      tabindex="" placeholder="Feed back"  rows="2"></textarea>
                        </div>
                    </td>
                    <td class="" style="vertical-align: top!important;width: 20%">
                        <input type="text" tabindex=""  name="question[{$ID}][response][0][value]" id="question_response" value="" class="form-control"  style="margin-bottom: 2px">
                    </td>
                    <td class="" style="vertical-align: top!important;width: 5%">
                        </button>&nbsp;<button type="button" class="btn btn-link" data-tamplate="MORE_THAT_ONE_OPTION" title="Eliminar" onclick="QuestionUtils.delResponseOption (this, '{$idOption}');">
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="action-bar text-center">
            <div class="col-md-12 text-center"><small>Seleccione las respuestas esperadas.</small></div>
            <button type="button" class="btn btn-link" title="Agregar opción" data-tamplate="MORE_THAT_ONE_OPTION" onclick="QuestionUtils.addResponseOption (this, '{$idOption}');"><i class="fa fa-plus"></i></button>
        </div>
    </div>
</div>
{/if}
<script type="text/html" id="MORE_THAT_ONE_OPTION-TEMPLATE-{$idOption}">
    <tr class="rule ">
        <td class="" style="vertical-align: top!important;width: 75%">
            <div class="checkbox pull-left"  style="margin-top: 0;width: 100%">
                <label class="col-md-12" style="margin-bottom: 1px">
                    <input type="checkbox" name="question[{$ID}][response][__ID__][selected]"  value="__ID__">
                    <input type="text" tabindex="" placeholder="Etiqueta" name="question[{$ID}][response][__ID__][label]" value="" class="form-control">
                </label>
                <textarea id="question_feedback" name="question[{$ID}][response][__ID__][feedback]" class="form-control"
                          tabindex="" placeholder="Feed back"  rows="2"></textarea>
            </div>
        </td>
        <td class="" style="vertical-align: top!important;width: 20%">
            <input type="text" tabindex=""  name="question[{$ID}][response][__ID__][value]" id="question_response" value="" class="form-control"  style="margin-bottom: 2px">
        </td>
        <td class="" style="vertical-align: top!important;width: 5%">
            </button>&nbsp;<button type="button" class="btn btn-link" data-tamplate="MORE_THAT_ONE_OPTION" title="Eliminar" onclick="QuestionUtils.delResponseOption (this, '{$idOption}');">
                <i class="fa fa-trash-o"></i>
            </button>
        </td>
    </tr>
</script>