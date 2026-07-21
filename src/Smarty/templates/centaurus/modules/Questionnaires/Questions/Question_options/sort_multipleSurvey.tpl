{math equation= rand() assign= "idOption"}
{if isset($ANSWERS_OPTIONS) && !empty($ANSWERS_OPTIONS)}
    <script type="text/javascript" src="themes/centaurus/js/jquery.js"></script>
    <div id="response-question-{$ID_QUESTION}" class="table-responsive survey_scroll">
        <input type="hidden" name="survey[{$ID}][response][0][questionid]" value="{$ID_QUESTION}">
        <table class="table rules justify-content-center">
            <thead>
            <tr>
                <th colspan="3" class="" style="width: 100%">Concepto</th>
            </tr>
            </thead>
            <tbody id="TBODY-SIMPLE_SELECTION-{$idOption}" data-next-option="{$NUM_ROWS}">
            {foreach $ANSWERS_OPTIONS as $answerOption}
                {assign var="checked" value=$answerOption->getSelected()}
                <tr class="rule ">
                    <input type="hidden" name="survey[{$ID}][response][{$answerOption->getSequence()}][answereid]"
                           value="{$answerOption->getId()}">
                    <input type="hidden" name="survey[{$ID}][response][{$answerOption->getSequence()}][answere-name]" value="{$answerOption->getName()}">
                    <td class="pull-right">
                        <p class="">{$answerOption->getMainLabel()}</p>
                    </td>
                    <td>
                        <table width="100%" border="0">
                            <tr >
                                <td class="chekbox-label">
                                    <div class="form-group field-container text-center">
                                        <input type="radio" class="pull-right required"
                                               name="survey[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                value="0">
                                    </div>
                                </td>
                                <td class="chekbox-label">
                                    <div class="form-group field-container text-center">
                                        <input type="radio" class="pull-right required"
                                               name="survey[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                value="1">
                                    </div>
                                </td>
                                <td class="chekbox-label">
                                    <div class="form-group field-container text-center">
                                        <input type="radio" class="pull-right required"
                                               name="survey[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                value="2">
                                    </div>
                                </td>
                                <td class="chekbox-label">
                                    <div class="form-group field-container text-center">
                                        <input type="radio" class="pull-right required"
                                               name="survey[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                value="3">
                                    </div>
                                </td>
                                <td class="chekbox-label">
                                    <div class="form-group field-container text-center">
                                        <input type="radio" class=" pull-right"
                                               name="survey[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                value="4">
                                    </div>
                                </td>
                                <td class="chekbox-label">
                                    <div class="form-group field-container text-center">
                                        <input type="radio" class="pull-right required"
                                               name="survey[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                value="5">
                                    </div>
                                </td>
                                <td class="chekbox-label">
                                    <div class="form-group field-container text-center">
                                        <input type="radio" class="pull-right required"
                                               name="survey[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                                value="6">
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td class="pull-left">
                        <p class="">{$answerOption->getSecondLabel()}</p>

                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
    <div id="response-{$ID_QUESTION}"  class="table-responsive  hide">
        {foreach $ANSWERS_OPTIONS as $option}
            {if $option->getFeedBack() eq NULL}{continue}{/if}
            {assign var="hasFeedBack" value="yes"}
            <div id="fb-{$option->getName()}" class="col-lg-12 col-md-12 col-xs-12">
                {$option->getFeedBack()}
            </div>
        {/foreach}
        <div class="data-question" data-feed-back="{$hasFeedBack}" data-next-step="{$nextStep}" data-prev-step="">&nbsp;</div>
        <div class="help-block text-center" style="color: red;font-size: small"></div>
    </div>
{else}

{/if}
