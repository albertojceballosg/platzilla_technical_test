{math equation= rand() assign= "idOption"}
{if isset($ANSWERS_OPTIONS) && !empty($ANSWERS_OPTIONS)}
    <div class="row-question-view justify-content-center" style="margin-bottom: 20px">
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
                    <tbody id="TBODY-SIMPLE_SELECTION-{$idOption}" data-next-option="{$NUM_ROWS}">
                    {foreach $ANSWERS_OPTIONS as $answerOption}
                        <tr class="rule">
                            <input type="hidden" name="question[{$ID}][response][{$answerOption->getSequence()}][answereid]" value="{$answerOption->getId()}">
                            <td class="" style="vertical-align: top!important;width: 70%">
                                <div class="form-group field-container pull-left" style="width: 100%">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <input type="radio" class=" pull-right"
                                                   name="question[{$ID}][response][0][selected]"
                                                   value="{$answerOption->getSequence()}" {if $answerOption->getSelected() neq NULL}checked {/if}>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="text" tabindex="" placeholder="Etiqueta"
                                                   name="question[{$ID}][response][{$answerOption->getSequence()}][label]" value="{$answerOption->getMainLabel()}" class="form-control">
                                        </div>
                                        <div class="col-md-12" style="margin-top: 1px">
                                            <textarea id="question_feedback" name="question[{$ID}][response][{$answerOption->getSequence()}][feedback]" class="form-control"
                                                      tabindex="" placeholder="Feed back"  rows="2">{$answerOption->getFeedBack()}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="" style="vertical-align: top!important;width: 20%">
                                <input type="text" tabindex="" placeholder="Valor de la opción" style="margin-bottom: 2px"
                                       name="question[{$ID}][response][{$answerOption->getSequence()}][value]" value="{$answerOption->getValue()}" class="form-control">

                            </td>
                            <td class="" style="vertical-align: top!important;width: 5%">
                                </button>&nbsp;<button type="button" class="btn btn-link" data-tamplate="SIMPLE_SELECTION"
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
                <button type="button" class="btn btn-link" title="Agregar opción" data-tamplate="SIMPLE_SELECTION"
                        onclick="QuestionUtils.addResponseOption (this, '{$idOption}');"><i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
    </div>
{else}
    {math equation= rand() assign= "idOption"}
    <div class="row-question-view justify-content-center" style="margin-bottom: 20px">
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
                    <tbody id="TBODY-SIMPLE_SELECTION-{$idOption}" data-next-option="1">
                    <tr class="rule ">
                        <td class="" style="vertical-align: top!important;width: 75%">
                            <div class="field-container pull-left"  style="margin-top: 0;width: 100%">
                                <div class="row">
                                    <div class="col-md-2">
                                        <input type="radio" class=" pull-right"
                                               name="question[{$ID}][response][0][selected]" value="0">
                                    </div>
                                    <div class="col-md-10">
                                        <input type="text" tabindex="" placeholder="Etiqueta"
                                               name="question[{$ID}][response][0][label]" value="" class="form-control">
                                    </div>
                                    <div class="col-md-12" style="margin-top: 1px">
                                        <textarea id="question_feedback" name="question[{$ID}][response][0][feedback]" class="form-control"
                                                  tabindex="" placeholder="Feed back"  rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="" style="vertical-align: top!important;width: 20%">
                            <input type="text" tabindex="" placeholder="Valor de la opción" style="margin-bottom: 2px"
                                   name="question[{$ID}][response][0][value]" value="" class="form-control">
                        </td>
                        <td class="" style="vertical-align: top!important;width: 5%">
                            </button>&nbsp;<button type="button" class="btn btn-link" data-tamplate="SIMPLE_SELECTION"
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
                    <small>Seleccione la respuesta esperada.</small>
                </div>
                <button type="button" class="btn btn-link" title="Agregar opción" data-tamplate="SIMPLE_SELECTION"
                        onclick="QuestionUtils.addResponseOption (this, '{$idOption}');"><i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
    </div>
{/if}
<script type="text/html" id="SIMPLE_SELECTION-TEMPLATE-{$idOption}">
    <tr class="rule ">
        <td class=""  style="vertical-align: top!important;width: 75%">
            <div class="field-container pull-left" style="margin-top: 0;width: 100%">
                <div class="row">
                    <div class="col-md-2">
                        <input type="radio" class=" pull-right" name="question[{$ID}][response][0][selected]"
                               value="__ID__">
                    </div>
                    <div class="col-md-10">
                        <input type="text" tabindex="" placeholder="Etiqueta"
                               name="question[{$ID}][response][__ID__][label]" value="" class="form-control">
                    </div>
                    <div class="col-md-12" style="margin-top: 1px">
                        <textarea id="question_feedback" name="question[{$ID}][response][__ID__][feedback]" class="form-control"
                                  tabindex="" placeholder="Feed back"  rows="2"></textarea>
                    </div>
                </div>
            </div>
        </td>
        <td class=""  style="vertical-align: top!important;width: 20%">
            <input type="text" tabindex="" placeholder="Valor de la opción" style="margin-bottom: 2px"
                   name="question[{$ID}][response][__ID__][value]" value="" class="form-control">

        </td>
        <td class=""  style="vertical-align: top!important;width: 5%">
            </button>&nbsp;<button type="button" class="btn btn-link" data-tamplate="SIMPLE_SELECTION" title="Eliminar"
                                   onclick="QuestionUtils.delResponseOption (this, '{$idOption}');">
                <i class="fa fa-trash-o"></i>
            </button>
        </td>
    </tr>
</script>