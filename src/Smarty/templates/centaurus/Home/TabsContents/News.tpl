{strip}
    <div class="main-box" style="margin-top: 27px">
        <div class="main-box-body clearfix">
            {if (!empty ($NEWS_DATA))}
                <div class="panel-group accordion" id="NewsContents">
                    {foreach $NEWS_CATEGORIES as $key => $category}
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle collapsed" data-toggle="collapse"
                                       data-parent="#NewsContents" href="#collapse_{$key}">
                                        {$category}
                                    </a>
                                </h4>
                            </div>
                            <div id="collapse_{$key}" class="panel-collapse collapse">
                                <div class="panel-body" id="tbl_{$key}">
                                    <ul class="media-list">
                                        {foreach $NEWS_DATA as $newsDataItem}
                                            {if $newsDataItem->getCategories() neq $key}{continue}{/if}
                                            <li class="media">
                                                <div class="media-body" style="border-bottom:1px solid #dee2e6!important; width: 100%">
                                                    <h4 class="media-heading"
                                                        style="font-weight: bold">{$newsDataItem->getTitle()}</h4>
                                                    {$newsDataItem->getContent()}
                                                </div>
                                            </li>
                                        {/foreach}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>
            {else}
                <div class="alert alert-info">No hay anuncios para el día de hoy</div>
            {/if}
        </div>
    </div>
{/strip}