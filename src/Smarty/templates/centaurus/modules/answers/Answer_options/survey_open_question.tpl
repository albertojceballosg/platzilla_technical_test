<div class="row">
    <div class="col-lg-12 col-md-12 col-xs-12">
        <div class="platzilla-card-header">
            <p class="pull-left" style="font-weight: bold">Evaluación de respuestas</p>
            <p class="pull-left" style="margin-bottom: 2px"><span style="font-weight: bold">Pregunta:</span>&nbsp;{$ASKING_FOR->getQuestion()}</p>
            <p class="pull-left" style="margin-bottom: 2px"><span style="font-weight: bold">Grupo:</span>&nbsp;{$ASKING_FOR->getQuestionGroupId()}</p>
            <p class="pull-left"><span style="font-weight: bold">Tema:</span>&nbsp;{$ASKING_FOR->getQuestionStageId()}</p>
        </div>
    </div>
    <div class="col-lg-12 col-md-12 col-xs-12">
        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                {foreach $HEADER as $thead}
                    {if $thead eq 'Pregunta' || $thead eq 'Grupo' || $thead eq 'Tema'}{continue}{/if}
                    <th align="left"><small>&nbsp;{$thead}</small>
                    <div style="width: 100%">&nbsp;</div>
                    </th>
                {/foreach}
            </tr>
            </thead>
            <tbody>
            {foreach $DATA as  $row}
            <tr>
                {foreach $row as $key => $value}
                    {if $key eq 'question' || $key eq 'questiongroup'  || $key eq 'questionstage'}{continue}{/if}
                <td ><small>{$value}</small></td>
                {/foreach}
            </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>