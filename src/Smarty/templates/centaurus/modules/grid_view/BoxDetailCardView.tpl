<div class="row">
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        <div class="col-md-12">
            <div class="alert alert-danger">
                <strong>Error:&nbsp;</strong> {$MESSAGE}
            </div>
        </div>
    {/if}
    {assign var='hiddenButton' value='yes'}
    {assign var='boxContenet' value=$GRID_BOX}
    <div class="col-md-12" style="margin-bottom: 12px">
        {if $boxContenet neq NULL}
        <div class="card">
            {assign var='content' value=$boxContenet->getContent()}
            {if $boxContenet->getName() eq 'TASKS'}
                {include file='modules/grid_view/BoxContenets/Tasks.tpl'}
            {elseif $boxContenet->getName() eq 'GUEST_REVIEWS'}
                {include file='modules/grid_view/BoxContenets/Comments.tpl'}
            {elseif $boxContenet->getName() eq 'DOCUMENTS'}
                {include file='modules/grid_view/BoxContenets/Documents.tpl'}
            {elseif $boxContenet->getName() eq 'MESSAGES'}
                {include file='modules/grid_view/BoxContenets/Messages.tpl'}
            {elseif $boxContenet->getName() eq 'CALENDAR'}
                {include file='modules/grid_view/BoxContenets/Calendar.tpl'}
            {elseif $boxContenet->getName() eq 'REPORT_ACTIVITY'}
                {include file='modules/grid_view/BoxContenets/ActivityReport.tpl'}
            {/if}
        </div>
        {/if}
    </div>
</div>
</div>