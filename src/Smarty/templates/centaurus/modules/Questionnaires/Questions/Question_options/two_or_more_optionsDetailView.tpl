{math equation= rand() assign= "idOption"}
{if isset($ANSWERS_OPTIONS) && !empty($ANSWERS_OPTIONS)}
    <div class="row-question-view justify-content-center" style="margin-bottom: 20px">
        <div class="col-md-11 rounded border" style="padding: 10px; background-color: #f9f8f7">
            <div class="table-responsive">
                <table class="table rules justify-content-center">
                    <thead>
                    <tr>
                        <th class="" style="width: 75%">Opción</th>
                        <th class="" style="width: 20%">Valor</th>
                        <th class="" style="width: 5%">&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody id="TBODY-SIMPLE_SELECTION-{$idOption}" data-next-option="{$NUM_ROWS}">
                    {foreach $ANSWERS_OPTIONS as $answerOption}
                        <tr class="rule ">
                            <input type="hidden" name="question[{$ID}][response][{$answerOption->getSequence()}][answereid]" value="{$answerOption->getId()}">
                            <td class="" style="vertical-align: top!important;width: 75%">
                                <div class="field-container pull-left" style="width: 100%">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <input type="radio" class=" pull-right" disabled
                                                   name="question[{$ID}][response][0][selected]"
                                                   value="{$answerOption->getSequence()}" {if $answerOption->getSelected() neq NULL}checked {/if}>
                                        </div>
                                        <div class="col-md-10">
                                            <span class="form-control label-readonly"
                                                  style="width: 100%" data-toggle="">
                                                    {$answerOption->getMainLabel()}
                                            </span>

                                        </div>
                                        <div class="col-md-12">
                                            <span class="form-control " style="overflow-x: hidden;width: 100% resize: vertical; word-break: break-word; min-height: 50px;line-height: 1.35em !important;">
                                                {$answerOption->getFeedBack()}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="" style="vertical-align: top!important;">
                                <span class="form-control label-readonly"
                                      style="overflow-x: hidden;width: 100%; margin-bottom: 2px;" data-toggle="">
                                    {$answerOption->getValue()}
                                </span>
                            </td>
                            <td class="">
                                &nbsp;
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
            <div class="action-bar text-center">&nbsp;
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
                        <th class="" style="width: 49%">Opción</th>
                        <th class="" style="width: 49%">Valor</th>
                        <th class="" style="width: 12%">&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody id="TBODY-SIMPLE_SELECTION-{$idOption}" data-next-option="1">
                    <tr class="rule ">
                        <td class="" style="vertical-align: top!important;">
                            <div class="form-group field-container pull-right">
                                <div class="row" style="margin-top: 6px">
                                    <div class="col-md-3">
                                        <input type="radio" class=" pull-right" disabled
                                               name="question[{$ID}][response][0][selected]" value="0">
                                    </div>
                                    <div class="col-md-9">
                                        <small>Respuesta no encontrada.</small>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="">
                            <small>Respuesta no encontrada.</small>
                        </td>
                        <td class="">
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="action-bar text-center">&nbsp;
            </div>
        </div>
    </div>
{/if}