{math equation= rand() assign= "idOption"}
{if isset($ANSWERS_OPTIONS) && !empty($ANSWERS_OPTIONS)}
    <div class="row-question-view justify-content-center" style="margin-bottom: 20px">
        <div class="col-md-11 rounded border" style="padding: 10px; background-color: #f9f8f7">
            <div class="table-responsive">
                <table class="table rules justify-content-center">
                    <thead>
                    <tr>
                        <th class="" style="width: 49%">Puntuación</th>
                        <th class="" style="width: 49%">Concepto</th>
                    </tr>
                    </thead>
                    <tbody id="TBODY-SORT_SIMPLE-{$idOption}" data-next-option="{$NUM_ROWS}">
                    <tr>
                        <td><p class="text-right">Cantidad de categorías:</p></td>
                        <td colspan="2">
                            <div class="col-md-2 col-xs-2" style="margin-left: 0; padding-left: 0">
                                <span class="form-control label-readonly"
                                      style="overflow-x: hidden;width: 100%" data-toggle="">
                                            {$ANSWERS_OPTIONS[0]->getAdditionalData()}
                                    </span>
                            </div>
                        </td>
                    </tr>
                    {foreach $ANSWERS_OPTIONS as $answerOption}
                        <tr class="rule ">
                            <input type="hidden" name="question[{$ID}][response][{$answerOption->getSequence()}][answereid]" value="{$answerOption->getId()}">
                            <td class="" style="vertical-align: top!important;">
                                <div class="col-md-2 col-xs-2 pull-right">
                                    <span class="form-control label-readonly"
                                          style="overflow-x: hidden;width: 100%" data-toggle="">
                                            {$answerOption->getValue()}
                                    </span>
                                </div>
                            </td>
                            <td class="" style="vertical-align: top!important;">
                                <span class="form-control label-readonly"
                                      style="width: 100%;margin-bottom: 2px" data-toggle="">
                                    {$answerOption->getMainLabel()}
                                </span>
                                <span class="form-control " style="overflow-x: hidden;width: 100% resize: vertical;
                                word-break: break-word; min-height: 50px;line-height: 1.35em !important;">
                                                {$answerOption->getFeedBack()}
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
    <div class="row-question-view justify-content-center" style="margin-bottom: 20px">
        <div class="col-md-8 rounded border" style="padding: 10px; background-color: #f9f8f7">
            <div class="table-responsive">
                <table class="table rules justify-content-center">
                    <thead>
                    <tr>
                        <th class="" style="width: 49%">Puntuación</th>
                        <th class="" style="width: 49%">Concepto</th>
                        <th class="" style="width: 12%">&nbsp;</th>
                    </tr>

                    </thead>
                    <tbody id="TBODY-SORT_SIMPLE-{$idOption}" data-next-option="1">
                    <tr class="rule ">
                        <td class="">
                            <div class="col-md-2 col-xs-2 pull-right">
                                <small>Respuesta no encontrada.</small>
                            </div>
                        </td>
                        <td class="">
                            <small>Respuesta no encontrada.</small>
                        </td>
                        <td class="">
                            &nbsp;
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