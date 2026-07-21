{math equation= rand() assign= "idOption"}
{if isset($ANSWERS_OPTIONS) && !empty($ANSWERS_OPTIONS)}
    {assign var="nextStep" value=null}
    {assign var="hasFeedBack" value='no'}
    <div id="response-question-{$ID_QUESTION}" class="table-responsive">
        <input type="hidden" name="survey[{$ID}][response][0][questionid]" value="{$ID_QUESTION}">
        <table class="table rules justify-content-center">
            <thead>
            <tr>
                <th class="" style="width: 10%">&nbsp;</th>
				<th style="width: 90%">&nbsp;</th>
            </tr>
            </thead>
            {assign var="maxRow" value=$ANSWERS_OPTIONS[0]->getAdditionalData()}
            <tbody id="TBODY-SORT_SIMPLE-{$idOption}" data-next-option="{$maxRow}">
            {foreach $ANSWERS_OPTIONS as $answerOption}
                <input type="hidden" name="survey[{$ID}][response][{$answerOption->getSequence()}][answereid]" value="{$answerOption->getId()}">
                <input type="hidden" name="survey[{$ID}][response][{$answerOption->getSequence()}][answere-name]" value="{$answerOption->getName()}">
                <tr class="rule ">
                    <td class="">
                        {if $answerOption->getSurveyNav() neq NULL}
                            {assign var="nextStep" value=$answerOption->getSurveyNav()->getQuestionId()}
                        {/if}
                        <div class="input-text" data-next-step="{$nextStep}" data-questin-name="{$answerOption->getName()}">
                            <select name="survey[{$ID}][response][{$answerOption->getSequence()}][selected]"
                                    class="col-md-2 col-xs-2 form-control for-filter required"
                                    tabindex="">
                                <option value="">&nbsp;&nbsp;</option>
                                {section name=bar start=1 loop=($maxRow +1) step=1}
                                    <option value="{$smarty.section.bar.index}">
                                        &nbsp;{$smarty.section.bar.index}&nbsp;
                                    </option>
                                {/section}
                            </select>
                        </div>
                    </td>
                    <td class="">
                        {$answerOption->getMainLabel()}
                    </td>
                    <td class="">
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
{/if}
