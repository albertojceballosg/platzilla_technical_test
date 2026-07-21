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
                    <span class="form-control label-readonly"
                          style="overflow-x: hidden;width: 100%" data-toggle="">
                            {$answerOption->getValue()}
                        </span>
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
                    <span class="form-control " style="overflow-x: hidden;width: 100% resize: vertical; word-break: break-word; min-height: 50px;line-height: 1.35em !important;">
                        {$answerOption->getFeedBack()}
                    </span>
                </div>
            </div>
        {/foreach}
        <div class="col-md-12 text-center">
            &nbsp;
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
                <small>Respuesta no encontrada.</small>
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
                    <span class="form-control " style="overflow-x: hidden;width: 100% resize: vertical; word-break: break-word; min-height: 50px;line-height: 1.35em !important;">
                        Respueta no encontrada.
                    </span>
            </div>
        </div>
    <div class="col-md-12 text-center">

    </div>
</div>
{/if}