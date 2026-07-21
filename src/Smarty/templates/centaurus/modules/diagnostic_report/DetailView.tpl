{extends file='modules/diagnostic_report/base/DetailViewLayaout.tpl'}

{assign var='businessName' value=null}
{assign var='businessPhase' value=null}
{assign var='businessType' value=null}
{assign var='createdDate' value=null}
{assign var='currentSituation' value=null}
{assign var='currentStatus' value='BUSINESS_IDEA'}
{assign var='diagnosticData' value=null}
{assign var='functionResult' value=null}
{assign var='improvementOpportunities' value=null}
{assign var="explanatoryOpportunities" value=null}
{assign var='informativeVideo' value=null}
{assign var='prospectEmail' value=null}
{assign var='prospectName' value=null}
{assign var='questionnaireId' value=null}
{assign var='targetCategory' value=null}
{assign var='management_level' value=null}
{assign var='videoType' value='VIMEO'}

{*block name='test'*}
{foreach key=header item=detail from=$BLOCKS name=mainBlock}
    {assign var=detailD value=$detail}
    {foreach item=detail from=$detailD}
        {foreach key=label item=data from=$detail  name=detailBlock}
            {assign var=keyval value=$data.value}
            {assign var=keyfldname value=$data.fldname}
            {if $keyfldname eq 'business_name' && $keyval neq NULL}
                {assign var='businessName' value=$keyval}
            {elseif $keyfldname eq 'business_phase' && $keyval neq NULL}
                {assign var='businessPhase' value=$keyval}
            {elseif $keyfldname eq 'business_type' && $keyval neq NULL}
                {assign var='businessType' value=$keyval}
            {elseif $keyfldname eq 'createdtime' && $keyval neq NULL}
                {assign var='createdDate' value=$keyval}
            {elseif $keyfldname eq 'current_situation'}
                {assign var='currentSituation' value=$keyval}
            {elseif $keyfldname eq 'current_status' && $keyval neq NULL}
                {assign var='currentStatus' value=$keyval|trim}
            {elseif $keyfldname eq 'diagnostic_data'}
                {assign var='diagnosticData' value=$keyval}
            {elseif $keyfldname eq 'function_result'}
                {assign var='functionResult' value=$keyval}
            {elseif $keyfldname eq 'improvement_opportunities'}
                {assign var='improvementOpportunities' value=$keyval}
            {elseif $keyfldname eq 'explanatory_opportunities'}
                {assign var='explanatoryOpportunities' value=$keyval}
            {elseif $keyfldname eq 'informative_video' && $keyval neq NULL}
                {assign var='informativeVideo' value=$keyval}
            {elseif $keyfldname eq 'prospect_email' && $keyval neq NULL}
                {assign var='prospectEmail' value=$keyval}
            {elseif $keyfldname eq 'prospect_name' && $keyval neq NULL}
                {assign var='prospectName' value=$keyval}
            {elseif $keyfldname eq 'target_category' && $keyval neq NULL}
                {assign var='targetCategory' value=$keyval}
            {elseif $keyfldname eq 'management_level' && $keyval neq NULL}
                {assign var='management_level' value=$keyval}
            {/if}
            {*
            field name: {$keyfldname}
            <br>
            field value; {$keyval}
            <br> *}
        {/foreach}
    {/foreach}
{/foreach}
{*/block*}
{block name="js"}
    {if $VALUED_FUNCTIONS neq NULL}
    <script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
    {/if}
{/block}
{block name='current_status'}{$IMAGE_CURRENT_STATUS[$currentStatus]}{/block}
{block name='current_situation'}
    <div class="well well-sm"
         style="vertical-align: top;min-height: 120px; height: 100%; margin: 8px; background-color: white!important;">
        {if $currentSituation neq NULL}
            <div class="text-justify" style="vert-align: top">
                {str_replace('<br />', "", str_replace('<br>', "", $currentSituation))}
            </div>
        {else}
            <div class="alert alert-info">Análisis de la situación actual no definido!</div>
        {/if}
    </div>
{/block}
{block name='diagnostic_data'}
    <div style="vertical-align: top;height: 100%; margin: 8px">
        <p style="text-align: center;font-weight: bold">Datos de la empresa</p>
        <p style="text-align: left;">
            <span style="font-weight: bold">Fecha:&nbsp;</span>{$createdDate|date_format:'%d-%m-%Y'}
        </p>
        <p style="text-align: left;">{if $businessName neq NULL}
                <span style="font-weight: bold">Empresa:&nbsp;</span>
                {$businessName} {/if}</p>
        <p style="text-align: left;">{if $businessPhase neq NULL}
                <span style="font-weight: bold">Etapa:&nbsp;</span>
                {$businessPhase} {/if}</p>
        <p style="text-align: left;">{if $businessType neq NULL}
                <span style="font-weight: bold">Tipo de empresa:&nbsp;</span>
                {$businessType} {/if}</p>
        <p style="text-align: left;">{if $prospectName neq NULL}
                <span style="font-weight: bold">Prospecto:&nbsp;</span>
                {$prospectName} {/if}</p>
        <p style="text-align: left;">{if $targetCategory neq NULL}
                <span style="font-weight: bold">Categoría de destino:&nbsp;</span>
                {$targetCategory} {/if}</p>
        <p style="text-align: left;">{if $management_level neq NULL}
                <span style="font-weight: bold">Nivel de gestión:&nbsp;</span>
                {$management_level} {/if}</p>
        {$diagnosticData}
    </div>
{/block}
{block name='informative_video'}
    {if $informativeVideo neq NULL}
        <div>
            {if $informativeVideo|strpos:"vimeo" }
                {math equation= rand() assign= "idVideo"}
                <div id="video-{$idVideo}"
                     class="embed-responsive embed-responsive-16by9"
                     style="text-align: center;"
                     data-vimeo-url="{$informativeVideo}">
                </div>
                <script type="text/javascript"
                        src="https://player.vimeo.com/api/player.js"></script>
            {else}
                <div class="embed-responsive embed-responsive-16by9 video">
                    <iframe class="embed-responsive-item"
                            src="{$informativeVideo}"
                            allow="autoplay; fullscreen"
                            allowfullscreen="" frameborder="0">
                    </iframe>
                </div>
            {/if}
        </div>
    {/if}
{/block}
{block name='function_result'}
    {if $functionResult neq NULL}
    <div style="margin:  0 10px">
        {$functionResult}
    </div>
    {/if}
    {if $VALUED_FUNCTIONS neq NULL}
    <div style="margin:  0 10px">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="table-responsive">
                    <table class="table" style="margin: 0!important;">
                        {assign var="totalRow" value= $VALUED_FUNCTIONS|count}
                        {assign var="index" value=1}
                    {foreach $VALUED_FUNCTIONS as $fuction}
                        {math equation= rand() assign= "idChart"}
                        {if $fuction->getQuestion() neq NULL}
                            <tr>
                                <td colspan="4" style="padding: 6px 8px!important; border-top-color: #000">
                                    <em>{$fuction->getQuestion()}</em>
                                </td>
                            </tr>
                        {/if}
                        <tr>
                            <td style="width: 20%;text-align: left;padding-left: 1px;vertical-align: top"><strong>{$fuction->getFunctionName()}</strong></td>
                            <td style="width: 36%;text-align: justify;padding-left: 1px;vertical-align: top">
                                &nbsp;{$fuction->getFunctionLabel()}
                            </td>
                            <td style="width: 40%;text-align: left;padding-left: 1px">
                                <div id="myChart-{$idChart}" style="width:100%; height:85px;"></div>
                            </td>
                            <td style="width: 4%;text-align: center">
                                <button type="button" class="btn btn-primary btn-lg btn-circle"
                                        id="btn-{$idChart}"
                                        data-toggle="collapse"
                                        data-target="#description-{$idChart}">
                                    <i class="fa fa-arrow-circle-down fa-2x" aria-hidden="true"></i>
                                </button></td>
                        </tr>
                        <tr>
                            <td colspan="4"  style="border-top: #FFFFFF" >
                                <div id="description-{$idChart}" class="collapse" style="width: 100%!important;">
                                    {str_replace('<br />', "", str_replace('<br>', "", $fuction->getDescription()))}
                                </div>
                                {if $index eq $totalRow}<hr>{/if}

                            </td>
                        </tr>
                        {literal}
                        <script>
                            google.charts.load('current', {'packages':['corechart']});
                            google.charts.setOnLoadCallback(drawChart_{/literal}{$idChart}{literal});
                            function drawChart_{/literal}{$idChart}{literal}() {
                                var data = google.visualization.arrayToDataTable([
                                    ['Function', '%'],
                                    ['',{/literal}{$fuction->getFunctionValue()}{literal}]
                                ]);
                                var options = {
                                    hAxis: {
                                        minValue: 0,
                                        maxValue: 100
                                    },
                                    title:'{/literal}{$fuction->getFunctionName()}{literal}',
                                    legend: { position: 'none' },
                                    colors: ['{/literal}{$fuction->getBarColor()}{literal}']
                                };
                                var chart = new google.visualization.BarChart (document.getElementById('myChart-{/literal}{$idChart}{literal}'));
                                chart.draw (data, options);
                            }
                            jQuery ('#description-{/literal}{$idChart}{literal}').on('hidden.bs.collapse', function () {
                                jQuery ('#btn-{/literal}{$idChart}{literal}').html('<i class="fa fa-arrow-circle-down fa-2x" aria-hidden="true"></i>')
                            })
                            jQuery ('#description-{/literal}{$idChart}{literal}').on('show.bs.collapse', function () {
                                jQuery ('#btn-{/literal}{$idChart}{literal}').html('<i class="fa fa-arrow-circle-up fa-2x" aria-hidden="true"></i>')
                            })
                        </script>
                    {/literal}
                        {$index = $index + 1}
                    {/foreach}
                    </table>
                </div>
            </div>
        </div>
    </div>
    {/if}
{/block}
{block name='improvement_opportunities'}
    {if $explanatoryOpportunities neq NULL}
    <div style="margin:  0 10px">
        {str_replace('<br />', "", str_replace('<br>', "", $explanatoryOpportunities))}
    </div>
    {/if}
    {if $improvementOpportunities neq NULL}{/if}
    <div style="margin:  0 10px">
        <ul>
            {str_replace('<br />', "", str_replace('<br>', "", $improvementOpportunities))}
        </ul>
    </div>

{/block}