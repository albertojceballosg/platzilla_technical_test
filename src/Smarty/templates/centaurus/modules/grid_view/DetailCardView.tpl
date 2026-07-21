<div class="row">
    {if $GRID_VIEW neq NULL}
    {assign var='gridViewName' value=$GRID_VIEW->getGridViewName ()}
    {assign var='gridViewId' value=$GRID_VIEW->getId ()}
    {assign var='gridViewLabel' value=$GRID_VIEW->getLabel ()}
    {assign var='moduleName' value=$GRID_VIEW->getTabName ()}
    {assign var='gridViewBoxes' value=$GRID_VIEW->getGridViewBox ()}
    {assign var='gridViewStatus' value=$GRID_VIEW->getStatus ()}
    {assign var='gridPosition' value=$GRID_VIEW->getPosition()}
    {assign var='hiddenButton' value='not'}
    {foreach $gridViewBoxes as $box}
        {assign var='boxContenet' value=$box->getBoxContenet ()}
        <div class="{if ($gridPosition eq 'SIDE') ||($gridPosition eq MULL)}col-md-12{else}col-md-6{/if}"  style="margin-bottom: 12px">
        <div class="card rounded">
                {assign var='content' value=$box->getBoxContenet ()->getContent()}
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
        </div>
    </div>
    {/foreach}
{else}
<p class="text-center"><code>Imposible encontrar la vista cuadricula!,<br/> por favor crear una vista en la sección de configuración</code></p>
{/if}
</div>