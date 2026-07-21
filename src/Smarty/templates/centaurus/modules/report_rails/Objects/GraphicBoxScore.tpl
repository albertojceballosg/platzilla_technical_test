{strip}
    <div class="row justify-content-center">
        {if $category['indicators'] neq NULL}
            {assign var="hasData" value=false}
            {foreach $category['indicators'] as $indicator}
                {if $indicator['objective'] neq $graphic}{continue}{/if}
                {assign var="hasData" value=true}
                <div class="col-lg-6 col-md-6 col-xs-6" style="padding-top: 10px;padding-left: 10px">
                    <div class="center-block" id="{$indicator['name']}" style="width:100%; height: 300px;"></div>
                </div>
            {/foreach}
            {if !$hasData}
                <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 15px">
                    <h2 style="margin-left: 15px">Sin indicadores programados</h2>
                </div>
            {/if}
        {else}
            <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 15px">
                <h2 style="margin-left: 15px">Sin indicadores programados</h2>
            </div>
        {/if}
    </div>
{/strip}