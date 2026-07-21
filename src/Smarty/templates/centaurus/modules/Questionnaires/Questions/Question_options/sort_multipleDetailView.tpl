{math equation= rand() assign= "idOption"}
{if isset($ANSWERS_OPTIONS) && !empty($ANSWERS_OPTIONS)}
    <div class="row-question-view justify-content-center" style="margin-bottom: 20px">
        <div class="col-md-11 rounded border" style="padding: 10px; background-color: #f9f8f7">
            <div class="table-responsive">
                <table class="table rules justify-content-center" style="width: 100%!important;">
                    <thead>
                    <tr>
                        <th class="" style="width: 35%">Concepto</th>
                        <th class="" style="width: 30%">Opción</th>
                        <th class="" style="width: 35%">Concepto</th>
                    </tr>
                    </thead>
                    <tbody id="TBODY-SIMPLE_SELECTION-{$idOption}" data-next-option="{$NUM_ROWS}">
                    {foreach $ANSWERS_OPTIONS as $answerOption}
                        {assign var="checked" value=$answerOption->getSelected()}
                        <tr class="rule ">
                            <input type="hidden" name="question[{$ID}][response][{$answerOption->getSequence()}][answereid]" value="{$answerOption->getId()}">
                            <td class="pull-right" style="vertical-align: top!important;">
                                <span class="form-control label-readonly"
                                      style="width: 100%" data-toggle="">
                                                    {$answerOption->getMainLabel()}
                                            </span>
                            </td>
                            <td class="" style="vertical-align: top!important;">
                                <table width="100%" border="0">
                                    <tr>
                                        <td>
                                            <div class="form-group field-container text-center">
                                                <input type="radio" class=" pull-right" name="question[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                       disabled
                                                       {if $answerOption->getSelected() eq "0"}checked {/if} value="0">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group field-container text-center">
                                                <input type="radio" class=" pull-right" name="question[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                       disabled
                                                       {if $answerOption->getSelected() eq "1"}checked {/if} value="1">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group field-container text-center">
                                                <input type="radio" class=" pull-right" name="question[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                       disabled
                                                       {if $answerOption->getSelected() eq "2"}checked {/if}  value="2">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group field-container text-center">
                                                <input type="radio" class=" pull-right" name="question[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                       disabled
                                                       {if $answerOption->getSelected() eq "3"}checked {/if} value="3">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group field-container text-center">
                                                <input type="radio" class=" pull-right" name="question[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                       disabled
                                                       {if $answerOption->getSelected() eq "4"}checked {/if}  value="4">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group field-container text-center">
                                                <input type="radio" class=" pull-right" name="question[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                       disabled
                                                       {if $answerOption->getSelected() eq "5"}checked {/if} value="5">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group field-container text-center">
                                                <input type="radio" class=" pull-right" name="question[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                       disabled
                                                       {if $answerOption->getSelected() eq "6"}checked {/if} value="6">

                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td class="pull-left" style="vertical-align: top!important;">
                                <span class="form-control label-readonly"
                                      style="width: 100%;margin-bottom: 2px" data-toggle="">
                                                    {$answerOption->getSecondLabel()}
                                            </span>

                            </td>
                        </tr>
                        {if $answerOption->getFeedBack() neq NULL}
                        <tr>
                            <td colspan="3" style="vertical-align: top!important;">
                                <span class="form-control " style="overflow-x: hidden;width: 100% resize: vertical;
                                word-break: break-word; min-height: 50px;line-height: 1.35em !important;">
                                                {$answerOption->getFeedBack()}
                                </span>
                            </td>
                        </tr>
                        {/if}
                    {/foreach}
                    </tbody>
                </table>
            </div>
            <div class="action-bar text-center">
                <div class="col-md-12 text-center">&nbsp;
                </div>
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
                    </tr>
                    </thead>
                    <tbody id="TBODY-SIMPLE_SELECTION-{$idOption}" data-next-option="1">
                    <tr class="rule ">
                        <td class="pull-right">
                            <small>Respuesta no encontrada.</small>
                        </td>
                        <td class="">
                            <table width="100%" border="0">
                                <tr>
                                    <td>
                                        <div class="form-group field-container text-center">
                                            <input type="radio" class=" pull-right" disabled
                                                   name="question[{$ID}][response][0][selected]" value="0">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group field-container text-center">
                                            <input type="radio" class=" pull-right" disabled
                                                   name="question[{$ID}][response][0][selected]" value="1">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group field-container text-center">
                                            <input type="radio" class=" pull-right" disabled
                                                   name="question[{$ID}][response][0][selected]" value="2">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group field-container text-center">
                                            <input type="radio" class=" pull-right" disabled
                                                   name="question[{$ID}][response][0][selected]" value="3">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group field-container text-center">
                                            <input type="radio" class=" pull-right" disabled
                                                   name="question[{$ID}][response][0][selected]" value="4">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group field-container text-center">
                                            <input type="radio" class=" pull-right" name="question[{$ID}][response][0]"
                                                   disabled
                                                   value="5">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group field-container text-center">
                                            <input type="radio" class=" pull-right"disabled
                                                   name="question[{$ID}][response][0][selected]" value="6">
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="pull-left">
                            <small>Respuesta no encontrada.</small>
                        </td>
                        <td class="">
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="action-bar text-center">
                <div class="col-md-12 text-center">&nbsp;
                </div>&nbsp;</div>
        </div>
    </div>
{/if}