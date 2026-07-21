<div id="row_grid_views" class="row">
    {assign var='gridViewName' value=$GRID_VIEW->getGridViewName ()}
    {assign var='gridViewId' value=$GRID_VIEW->getId ()}
    {assign var='gridViewLabel' value=$GRID_VIEW->getLabel ()}
    {assign var='moduleName' value=$GRID_VIEW->getTabName ()}
    {assign var='gridViewBoxes' value=$GRID_VIEW->getGridViewBox ()}
    {assign var='gridViewStatus' value=$GRID_VIEW->getStatus ()}
    {foreach $gridViewBoxes as $box}
        {assign var='boxContenet' value=$box->getBoxContenet ()}
        <div class="col-lg-4 col-md-6 col-sm-6" style="margin: 5px 0">
            <div class="main-box clearfix project-box project-grid-box emerald-box">
                <div class="main-box-body clearfix">
                    <div class="project-box-header grid-viw-project-box-header">
                        <div class="name">
                            <a href="#">
                                {$boxContenet->getLabel()}
                            </a>
                        </div>
                    </div>
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
</div>