<div class="modal fade" id="block-field-{$FIELD_ID}-tamplate" tabindex="-1" role="dialog"
     aria-labelledby="builder-variables-Label" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="builder-variables-Label">Insertar variables <span id="field-name"></span></h4>
            </div>
            <div class="modal-body">
                <div class="row-fluid justify-content-center">
                    <div class="col-lg-10 col-md-10 col-sm-10">
                        <select class="form-control answer" id="attrbutes"
                                onchange="DiagnosticRerportBuilderUtls.setBuilderTemplateVariables(this,'{$FIELD_ID}')">
                            {if isset($QUESTIONS)}
                                <option value="">Seleccione una respuesta y respuesta</option>
                                {foreach $QUESTIONS as $question}
                                    <optgroup label="{$question->getQuestion()}">
                                        {foreach $question->getResponseOption() as $response}
                                            {if $question->getQuestionForm() eq 'OPEN_QUESTION'}
                                                {assign var="label" value='Pregunta abierta'}
                                            {else}
                                                {if $response->getSecondLabel eq NULL}
                                                    {assign var="label" value=$response->getMainLabel()}
                                                {else}
                                                    {assign var="label" value=$response->getMainLabel()|cat:' - '|cat:$response->getSecondLabel()}
                                                {/if}
                                            {/if}
                                            <option value="{$response->getName()}" data-question="{$question->getId()}">{$label}</option>
                                        {/foreach}
                                    </optgroup>
                                {/foreach}
                            {else}
                                <option value="">Upoo! no hay preguntas</option>
                            {/if}
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
