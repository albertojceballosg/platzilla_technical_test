{math equation= rand() assign= "idOption"}
{if isset($ANSWERS_OPTIONS) && !empty($ANSWERS_OPTIONS)}
    {assign var="nextStep" value=null}
    {assign var="hasFeedBack" value='no'}
    <script type="text/javascript" src="themes/centaurus/js/jquery.js"></script>
    <div id="response-question-{$ID_QUESTION}" class="table-responsive survey_scroll">
        <input type="hidden" name="survey[{$ID}][response][0][questionid]" value="{$ID_QUESTION}">
        <table class="table rules justify-content-center">
            <thead>
            <tr>
                <th colspan="2" class="" style="width: 100%">&nbsp;</th>
            </tr>
            </thead>
            <tbody id="TBODY-BETWEEN_IMAGES-{$idOption}" data-next-option="1">
            {foreach $ANSWERS_OPTIONS as $answerOption}
                {math equation= rand() assign= "idRowOption"}
                {if $answerOption->getSurveyNav() neq NULL}
                    {assign var="nextStep" value=$answerOption->getSurveyNav()->getQuestionId()}
                {/if}
                <tr class="rule ">
                    <input type="hidden" name="survey[{$ID}][response][{$answerOption->getSequence()}][answereid]"
                           value="{$answerOption->getId()}">
                    <input type="hidden" name="survey[{$ID}][response][{$answerOption->getSequence()}][answere-name]" value="{$answerOption->getName()}">
                    <td style="width: 15%">
                        <img id="option-photo-{$idRowOption}" class="img-responsive center-block"
                             src='{$answerOption->getImage()}'/>
                    </td>
                    <td>
                        <input type="checkbox" class="pull-left required"
                               name="survey[{$ID}][response][{$answerOption->getSequence()}][selected]"
                               value="{$answerOption->getValue()}">
                        <span class="chekbox-label check-survey-requiered" for="survey[{$ID}][response][0][selected]" style="margin-bottom: 0; vertical-align: middle;padding-left: 12px">{$answerOption->getMainLabel()}</span>
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
