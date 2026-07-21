{math equation= rand() assign= "moreOptions"}
{if isset($ANSWERS_OPTIONS) && !empty($ANSWERS_OPTIONS)}
    {assign var="hasFeedBack" value='no'}
    {assign var="hasSurveyNav" value=null}
    <script type="text/javascript" src="themes/centaurus/js/jquery.js"></script>
    <div id="response-question-{$ID_QUESTION}"  class="table-responsive survey_scroll">
        <input type="hidden" name="survey[{$ID}][response][0][questionid]" value="{$ID_QUESTION}">
        {foreach $ANSWERS_OPTIONS as $answerOption}
        <div class="input-text">
            <input type="radio" class="pull-left required"
                   name="survey[{$ID}][response][0][selected]"
                    {if ($answerOption->getSurveyNav() neq NULL) && $hasSurveyNav eq NULL}{assign var="hasSurveyNav" value=$answerOption->getSurveyNav()->getQuestionId()}{/if}
                    {if $answerOption->getFeedBack() neq NULL}{assign var="hasFeedBack" value="yes"}{/if}
                   onclick="optionSelected_{$moreOptions}('{$answerOption->getName()}', '{$moreOptions}', '{$hasSurveyNav}', '{$hasFeedBack}')"
                   value="{$answerOption->getValue()}">
            <span class="chekbox-label" for="survey[{$ID}][response][0][selected]" style="margin-bottom: 0; vertical-align: middle;padding-left: 12px">{$answerOption->getMainLabel()}</span>
            <input type="hidden" name="survey[{$ID}][response][{$answerOption->getSequence()}][answereid]"
                   value="{$answerOption->getId()}">
            <input type="hidden" name="survey[{$ID}][response][{$answerOption->getSequence()}][answere-name]" value="{$answerOption->getName()}">
                <input id="selected-name-{$answerOption->getName()}"
                       class="question-name-{$moreOptions}"
                       type="hidden"
                       name="survey[{$ID}][response][{$answerOption->getSequence()}][question_name]"
                       value="">
        </div>
            {assign var="hasSurveyNav" value=null}
            {assign var="hasFeedBack" value='no'}
        {/foreach}
    </div>
    {assign var="hasFeedBack" value='no'}
    <div id="response-{$ID_QUESTION}"  class="table-responsive  hide">
        {foreach $ANSWERS_OPTIONS as $option}
            {if $option->getFeedBack() eq NULL}{continue}{/if}
            {assign var="hasFeedBack" value="yes"}
            <div id="fb-{$moreOptions}-{$option->getName()}" class="col-lg-12 col-md-12 col-xs-12">
                {$option->getFeedBack()}
            </div>
        {/foreach}
        <div id="dq-{$moreOptions}" class="data-question" data-feed-back="" data-next-step="" data-prev-step="">&nbsp;</div>
        <div class="help-block text-center" style="color: red;font-size: small"></div>
    </div>
    <script type="text/javascript">
        function optionSelected_{$moreOptions} (name, id, step, hasFeedBack) {
            if (step !== 'null') {
                jQuery('#dq-' + id).attr('data-next-step', step);
            }
            if (hasFeedBack === 'yes') {
                jQuery('div[id ^= fb-' + id + ']').addClass('hide');
                jQuery('#fb-' + id + '-' + name).removeClass('hide');
                jQuery('#dq-' + id).attr('data-feed-back', 'yes');
            } else {
                jQuery('#dq-' + id).attr('data-feed-back', '');
            }

            jQuery ('.question-name-' + id).val('');
            jQuery('#selected-name-' + name).val(name);
        }
    </script>
{else}

{/if}
