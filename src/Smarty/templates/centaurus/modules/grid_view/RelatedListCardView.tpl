<div class="row">
    <style>
        .related-list-card {
            max-height: 200px!important;
            min-height: 200px!important;
            overflow-y: auto;
            padding-right: 4px;
            scrollbar-width: thin;
        }
        .title-overflow {
            font-size: small !important;
        }
        .table {
            scrollbar-width: thin;
        }
        .table td,
        .table th {
            font-size: small !important;
        }
        .table  button{
            padding: 1px 5px;
            font-size: 12px;
            line-height: 1.5;
            border-radius: 3px;
        }
        .table .fa-caret-up {
            display: none!important;
        }
        .table .none {
            display: none!important;
        }
        .btn-circle {
            width: 30px;
            height: 30px;
            padding: 6px 0px;
            border-radius: 15px;
            text-align: center;
            font-size: 12px;
            line-height: 1.42857;
        }
        .platzilla-card-header {
            background-color: #FFFFFF;
            border-bottom-color: #FFFFFF;
            font-family: helvetica, arial, sans-serif;
            font-size: 1.5em
        }
        .platzilla-card-header {
            background-color: #FFFFFF;
            border-bottom-color: #FFFFFF;
            font-family: helvetica, arial, sans-serif;
            font-size: 1.5em
        }
        .rounded {
            border-radius:.75rem!important
        }
        .card-body-related {
            max-height: 260px!important;
            min-height: 210px!important;
        }
    </style>
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        <div class="col-md-12">
            <div class="alert alert-danger">
                <strong>Error:&nbsp;</strong> {$MESSAGE}
            </div>
        </div>
    {/if}
    {foreach $RELATED_LIST_CARD as $box}
        {assign var='boxContenet' value=$box['card']}
        {assign var='boxLabel' value=$box['header']}
        {assign var='currentModule' value=$box['currentModule']}
        {assign var='customButton' value=$box['customButton']}
        {assign var='navi' value=$box['navi']}
        {if isset($HiDDEN_BUTTON)}
            {assign var='hiddenButton' value=$HiDDEN_BUTTON}
        {else}
        {assign var='hiddenButton' value='not'}
        {/if}
        <div class="col-md-{if $hiddenButton eq 'yes'}12{else}6{/if}" style="margin-bottom: 20px;min-height: 300px;max-height: 370px">
            <div class="card rounded">
                <div class="card-header platzilla-card-header rounded" style="{if $hiddenButton eq 'yes'}display:none{/if}">
                    <div class="row">
                        <div class="col-md-7">
                            <p class="text-center pull-left" style="font-weight: bold">
                                {$boxLabel}
                            </p>
                        </div>
                        <div class="col-md-5">
                            <div class="pull-right">
                                {if $hiddenButton eq 'not'}
                                {$customButton}
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body card-body-related" id="RLContents">
                    {$boxContenet}
                </div>
                <div class="card-footer text-muted text-center rounded" style="background-color: #FFFFFF;!important;border-top-color: #FFFFFF!important;">
                    {$navi[1]}
                </div>
            </div>
        </div>
    {/foreach}
</div>