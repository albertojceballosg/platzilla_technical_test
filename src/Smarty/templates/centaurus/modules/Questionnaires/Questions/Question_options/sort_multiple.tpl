{math equation= rand() assign= "idOption"}
{if isset($ANSWERS_OPTIONS) && !empty($ANSWERS_OPTIONS)}
    <div class="row-question-view justify-content-center" style="margin-bottom: 20px">
        <div class="col-md-8 rounded border" style="padding: 10px; background-color: #f9f8f7">
            <div class="table-responsive">
                <table class="table rules justify-content-center">
                    <thead>
                    <tr>
                        <th class="" style="width: 30%">Concepto</th>
                        <th class="" style="width: 30%">Opción</th>
                        <th class="" style="width: 30%">Concepto</th>
                        <th class="" style="width: 10%">Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="TBODY-SIMPLE_SELECTION-{$idOption}" data-next-option="{$NUM_ROWS}">
                    {foreach $ANSWERS_OPTIONS as $answerOption}
                        {assign var="checked" value=$answerOption->getSelected()}
                        <tr class="rule ">
                            <input type="hidden" name="question[{$ID}][response][{$answerOption->getSequence()}][answereid]" value="{$answerOption->getId()}">
                            <td class="pull-right" style="vertical-align:top!important;{*height: 118px!important;*}">
                                <input type="text" tabindex="" placeholder="Valor de la opción"
                                       name="question[{$ID}][response][{$answerOption->getSequence()}][label][a]"
                                       value="{$answerOption->getMainLabel()}" class="form-control">
                            </td>
                            <td class="" style="vertical-align: top!important;">
                                <table width="100%" border="0">
                                    <tr>
                                        <td>
                                            <div class="form-group field-container text-center">
                                                <input type="radio" class=" pull-right" name="question[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                       {if $answerOption->getSelected() eq "0"}checked {/if} value="0">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group field-container text-center">
                                                <input type="radio" class=" pull-right" name="question[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                       {if $answerOption->getSelected() eq "1"}checked {/if} value="1">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group field-container text-center">
                                                <input type="radio" class=" pull-right" name="question[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                       {if $answerOption->getSelected() eq "2"}checked {/if}  value="2">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group field-container text-center">
                                                <input type="radio" class=" pull-right" name="question[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                       {if $answerOption->getSelected() eq "3"}checked {/if} value="3">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group field-container text-center">
                                                <input type="radio" class=" pull-right" name="question[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                       {if $answerOption->getSelected() eq "4"}checked {/if}  value="4">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group field-container text-center">
                                                <input type="radio" class=" pull-right" name="question[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                       {if $answerOption->getSelected() eq "5"}checked {/if} value="5">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group field-container text-center">
                                                <input type="radio" class=" pull-right" name="question[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                       {if $answerOption->getSelected() eq "6"}checked {/if} value="6">
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td class="pull-left" style="vertical-align:top!important;{*height: 118px!important;*}">
                                <input type="text" tabindex="" placeholder="Valor de la opción" style="margin-bottom: 2px"
                                       name="question[{$ID}][response][{$answerOption->getSequence()}][label][b]"
                                       value="{$answerOption->getSecondLabel()}" class="form-control">
                                <!--
                                <textarea id="question_feedback" name="question[{$ID}][response][0][feedback]" class="form-control"
                                          tabindex="" placeholder="Feed back"  rows="2"></textarea>
                                -->
                            </td>
                            <td class="">
                                </button>&nbsp;<button type="button" class="btn btn-link" data-tamplate="SIMPLE_SELECTION" title="Eliminar"
                                                       onclick="QuestionUtils.delResponseOption (this, '{$idOption}');">
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <textarea id="question_feedback" name="question[{$ID}][response][0][feedback]" class="form-control"
                                          tabindex="" placeholder="Feed back"  rows="2"></textarea>
                            </td>
                            <td>&nbsp;</td>
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
    <div class="row-question-view justify-content-center" style="margin-bottom: 20px">
        <div class="col-md-8 rounded border" style="padding: 10px; background-color: #f9f8f7">
            <div class="table-responsive">
                <table class="table rules justify-content-center">
                    <thead>
                    <tr>
                        <th class="" style="width: 30%">Concepto</th>
                        <th class="" style="width: 30%">Opción</th>
                        <th class="" style="width: 30%">Concepto</th>
                        <th class="" style="width: 10%">Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="TBODY-SIMPLE_SELECTION-{$idOption}" data-next-option="1">
                    <tr class="rule ">
                        <td class="pull-right" style="vertical-align: top!important;">
                            <input type="text" tabindex="" placeholder="Valor de la opción"
                                   name="question[{$ID}][response][0][label][a]" value="" class="form-control">
                        </td>
                        <td class="" style="vertical-align: top!important;">
                            <table width="100%" border="0">
                                <tr>
                                    <td>
                                        <div class="form-group field-container text-center">
                                            <input type="radio" class=" pull-right"
                                                   name="question[{$ID}][response][0][selected]" value="0">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group field-container text-center">
                                            <input type="radio" class=" pull-right"
                                                   name="question[{$ID}][response][0][selected]" value="1">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group field-container text-center">
                                            <input type="radio" class=" pull-right"
                                                   name="question[{$ID}][response][0][selected]" value="2">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group field-container text-center">
                                            <input type="radio" class=" pull-right"
                                                   name="question[{$ID}][response][0][selected]" value="3">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group field-container text-center">
                                            <input type="radio" class=" pull-right"
                                                   name="question[{$ID}][response][0][selected]" value="4">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group field-container text-center">
                                            <input type="radio" class=" pull-right" name="question[{$ID}][response][0]"
                                                   value="5">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group field-container text-center">
                                            <input type="radio" class=" pull-right"
                                                   name="question[{$ID}][response][0][selected]" value="6">
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="pull-left" style="vertical-align: top!important;">
                            <input type="text" tabindex="" placeholder="Valor de la opción"
                                   style="margin-bottom: 2px"
                                   name="question[{$ID}][response][0][label][b]" value="" class="form-control">
                        </td>
                        <td class="">
                            </button>&nbsp;<button type="button" class="btn btn-link" data-tamplate="SIMPLE_SELECTION"
                                                   title="Eliminar"
                                                   onclick="QuestionUtils.delResponseOption (this, '{$idOption}');">
                                <i class="fa fa-trash-o"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <textarea id="question_feedback" name="question[{$ID}][response][0][feedback]" class="form-control"
                                      tabindex="" placeholder="Feed back"  rows="2"></textarea>
                        </td>
                        <td>&nbsp;</td>
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
        <td class="pull-right"  style="vertical-align:top!important">
            <input type="text" tabindex="" placeholder="Valor de la opción"
                   name="question[{$ID}][response][__ID__][label][a]" value="" class="form-control">
        </td>
        <td class="" style="vertical-align: top!important;">
            <table width="100%" border="0">
                <tr>
                    <td>
                        <div class="form-group field-container text-center">
                            <input type="radio" class=" pull-right" name="question[{$ID}][response][__ID__][selected]"
                                   value="0">
                        </div>
                    </td>
                    <td>
                        <div class="form-group field-container text-center">
                            <input type="radio" class=" pull-right" name="question[{$ID}][response][__ID__][selected]"
                                   value="1">
                        </div>
                    </td>
                    <td>
                        <div class="form-group field-container text-center">
                            <input type="radio" class=" pull-right" name="question[{$ID}][response][__ID__][selected]"
                                   value="2">
                        </div>
                    </td>
                    <td>
                        <div class="form-group field-container text-center">
                            <input type="radio" class=" pull-right" name="question[{$ID}][response][__ID__][selected]"
                                   value="3">
                        </div>
                    </td>
                    <td>
                        <div class="form-group field-container text-center">
                            <input type="radio" class=" pull-right" name="question[{$ID}][response][__ID__][selected]"
                                   value="4">
                        </div>
                    </td>
                    <td>
                        <div class="form-group field-container text-center">
                            <input type="radio" class=" pull-right" name="question[{$ID}][response][__ID__][selected]"
                                   value="5">
                        </div>
                    </td>
                    <td>
                        <div class="form-group field-container text-center">
                            <input type="radio" class=" pull-right" name="question[{$ID}][response][__ID__][selected]"
                                   value="6">
                        </div>
                    </td>
                </tr>
            </table>
        </td>
        <td class="pull-left" style="vertical-align: top!important;">
            <input type="text" tabindex="" placeholder="Valor de la opción" style="margin-bottom: 2px"
                   name="question[{$ID}][response][__ID__][label][b]" value="" class="form-control">
        </td>
        <td class="">
            </button>&nbsp;<button type="button" class="btn btn-link" data-tamplate="SIMPLE_SELECTION" title="Eliminar"
                                   onclick="QuestionUtils.delResponseOption (this, '{$idOption}');">
                <i class="fa fa-trash-o"></i>
            </button>
        </td>
    </tr>
    <tr>
        <td colspan="3">
        <textarea id="question_feedback" name="question[{$ID}][response][__ID__][feedback]" class="form-control"
                  tabindex="" placeholder="Feed back"  rows="2"></textarea>
        </td>
        <td>&nbsp;</td>
    </tr>
</script>