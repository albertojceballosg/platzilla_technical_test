{if isset($ANSWERS_OPTIONS) && !empty($ANSWERS_OPTIONS)}
    <div class="row-question-view justify-content-center" style="margin-bottom: 20px">
        <div class="col-md-11 rounded border" style="padding: 10px; background-color: #f9f8f7">
        {foreach $ANSWERS_OPTIONS as $answerOption}
        <div class="col-md-6" style="margin-top: 8px">
            <input type="hidden" name="question[{$ID}][response][{$answerOption->getSequence()}][answereid]" value="{$answerOption->getId()}">
            <div class="col-md-4">
                <div class="label-input">

                    <label for="fecha_de_emision" class="animate__animated ">
                        Respuesta de fecha</label>
                </div>
            </div>
            <div class="form-group col-md-8 field-container">
                <div class="input-group" style="width: 100%;">
                    <div class="input-group-addon" style="border: 1px solid #ddd !important">
                        <i class="fa fa-calendar" id="jscal_trigger_question_response"></i>
                    </div>
                    <input type="text" id="jscal_question_response" name="question[{$ID}][response][{$answerOption->getSequence()}][value]"
                           value="{$answerOption->getValue()}"
                           class="form-control pull-right input-readonly b-left" tabindex="" size="11" maxlength="18"
                           readonly="readonly" placeholder="">
                    <script type="text/javascript">
                        jQuery('#jscal_question_response').datepicker({literal}{format: 'yyyy-mm-dd', language: 'es', weekStart: 1}{/literal});
                    </script>
                </div>
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
    <div class="col-md-6" style="margin-top: 8px">
        <div class="col-md-4">
            <div class="label-input">

                <label for="fecha_de_emision" class="animate__animated ">
                    Respuesta de fecha</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container">
            <div class="input-group" style="width: 100%;">
                <div class="input-group-addon" style="border: 1px solid #ddd !important">
                    <i class="fa fa-calendar" id="jscal_trigger_question_response"></i>
                </div>
                <input type="text" id="jscal_question_response" name="question[{$ID}][response][0][value]" value=""
                       class="form-control pull-right input-readonly b-left" tabindex="" size="11" maxlength="18"
                       readonly="readonly" placeholder="">
                <script type="text/javascript">
                    jQuery('#jscal_question_response').datepicker({literal}{format: 'yyyy-mm-dd', language: 'es', weekStart: 1}{/literal});
                </script>
            </div>
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
{/if}