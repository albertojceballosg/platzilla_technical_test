{if isset($ANSWERS_OPTIONS) && !empty($ANSWERS_OPTIONS)}
    <div class="row-question-view justify-content-center" style="margin-bottom: 20px">
        <div class="col-md-11 rounded border" style="padding: 10px; background-color: #f9f8f7">
        {foreach $ANSWERS_OPTIONS as $answerOption}
            <div class="col-md-6" style="margin-top: 6px">
                <input type="hidden" name="question[{$ID}][response][{$answerOption->getSequence()}][answereid]" value="{$answerOption->getId()}">
            <div class="col-md-4">
                <div class="label-input">
                    <label for="presentation_video" class="animate__animated ">
                        Descripción breve</label>
                </div>
            </div>
            <div class="form-group col-md-8 field-container">
            <textarea name="question[{$ID}][response][{$answerOption->getSequence()}][value]" id="question_response"
                      class="form-control" tabindex="" rows="2">{$answerOption->getValue()}</textarea>
            </div>
        </div>
            <div class="col-md-6">
                <div class="col-md-4">
                    <div class="label-input">
                        <label for="question_feedback" class="animate__animated">Feed back</label>
                    </div>
                </div>
                <div class="form-group col-md-8 field-container" id="td_question_feedback">
                    <textarea id="question_feedback" name="question[{$ID}][response][{$answerOption->getSequence()}][feedback]" class="form-control" tabindex=""
                              rows="2">{$answerOption->getFeedBack()}</textarea>
                </div>
            </div>
        {/foreach}
        <div class="col-md-12 text-center">
            <small>Inserte la respuesta esperada.</small>
        </div>
    </div>
    </div>
{else}
    <div class="row-question-view justify-content-center" style="margin-bottom: 20px">
        <div class="col-md-11 rounded border" style="padding: 10px; background-color: #f9f8f7">
        <div class="col-md-6" style="margin-top: 6px">
            <div class="col-md-4">
                <div class="label-input">
                    <label for="presentation_video" class="animate__animated ">
                        Descripción breve</label>
                </div>
            </div>
            <div class="form-group col-md-8 field-container">
            <textarea name="question[{$ID}][response][0][value]" id="question_response" class="form-control" tabindex=""
                      rows="2"></textarea>
            </div>
        </div>
            <div class="col-md-6">
                <div class="col-md-4">
                    <div class="label-input">
                        <label for="question_feedback" class="animate__animated">Feed back</label>
                    </div>
                </div>
                <div class="form-group col-md-8 field-container" id="td_question_feedback">
                    <textarea id="question_feedback" name="question[{$ID}][response][0][feedback]" class="form-control"
                              tabindex=""
                              rows="2"></textarea>
                </div>
            </div>
        <div class="col-md-12 text-center">
            <small>Inserte la respuesta esperada.</small>
        </div>
    </div>
    </div>
{/if}