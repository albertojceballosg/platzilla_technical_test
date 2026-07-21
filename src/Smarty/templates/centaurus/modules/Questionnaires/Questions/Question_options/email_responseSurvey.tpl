{if isset($ANSWERS_OPTIONS) && !empty($ANSWERS_OPTIONS)}
    {assign var="nextStep" value=null}
    {assign var="hasFeedBack" value='no'}
    <div id="response-question-{$ID_QUESTION}" class="table-responsive">
        <input type="hidden" name="survey[{$ID}][response][0][questionid]" value="{$ID_QUESTION}">
        {foreach $ANSWERS_OPTIONS as $answerOption}
            {if $answerOption->getSurveyNav() neq NULL}
                {assign var="nextStep" value=$answerOption->getSurveyNav()->getQuestionId()}
            {/if}
            <div class="input-text">
                <input type="text" placeholder="ejemplo@dominio.com"
                       class="required"
                       onblur="SurveyUtils.checkEmail(this)"
                       name="survey[{$ID}][response][0][selected]">
                <span id="email-error-message" style="color: red;font-size: small" class="help-block"></span>
                <input type="hidden" name="survey[{$ID}][response][0][answereid]" value="{$answerOption->getId()}">
                <input type="hidden" name="survey[{$ID}][response][0][answere-name]" value="{$answerOption->getName()}">
            </div>
        {/foreach}
    </div>
    <div id="response-{$ID_QUESTION}" class="table-responsive  hide">
        {foreach $ANSWERS_OPTIONS as $option}
            {if $option->getFeedBack() eq NULL}{continue}{/if}
            {assign var="hasFeedBack" value="yes"}
            <div id="fb-{$option->getName()}" class="col-lg-12 col-md-12 col-xs-12">
                {$option->getFeedBack()}
            </div>
        {/foreach}
        <div class="data-question" data-feed-back="{$hasFeedBack}" data-next-step="{$nextStep}" data-prev-step="">
            &nbsp;
        </div>
        <div class="help-block text-center" style="color: red;font-size: small"></div>
    </div>
{/if}
