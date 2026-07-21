<div class="card-header platzilla-card-header rounded" style="{if $hiddenButton eq 'yes'}display:none;{/if}"
    xmlns="http://www.w3.org/1999/html">
    <div class="row">
        <div class="col-md-7 col-xs-7 pull-left text-left">
            <p class="text-center pull-left" style="font-weight: bold">{$boxContenet->getLabel()}</p>

        </div>
        <div class="col-md-5 col-xs-5">
            <div class="pull-right">
                {if $hiddenButton eq 'not'}
                    <a href="index.php?module=grid_view&action=GridViewAjaxUtils&record={$RECORD}&formodule={$MODULE}&boxtype={$boxContenet->getName()}&function=ITERATIONS&Ajax=true"
                        class="btn btn-success btn-circle" data-title="Reportes sobre actividad:" data-width="950"
                        data-toggle="lightbox" data-parent="" data-gallery="remoteload" class="link">
                        <i class="fa fa-eye fa-lg"></i></a>&nbsp;
                    <a href="index.php?module=grid_view&action=EditActivityReport&record={$RECORD}&formodule={$MODULE}&Ajax=true"
                        data-title="{*Reporte sobre una actividad:*}" data-width="850" data-toggle="lightbox" data-parent=""
                        style="margin-top: 2px" data-gallery="remoteload"
                        class="link pull-right btn btn-primary btn-circle btn-xs" title="Reportar la actividad realizada"><i
                            class="fa fa-plus fa-lg"></i>
                    </a>
                {/if}
            </div>
        </div>
    </div>
</div>
<div class="card-body" {if ($hiddenButton neq 'yes') && ($content neq NULL)}style="height: 140px; !important;"
    {elseif ($content eq NULL) &&($ORPHAN_FEEDBACKS eq NULL)} style="height: 55px; !important;" 
    {/if}>
    <div class="grid-container" {if ($hiddenButton neq 'yes') && ($content neq NULL)}style="height: 120px; !important;"
        {elseif $content eq NULL && $ORPHAN_FEEDBACKS eq NULL} style="height: 50px; !important;" 
        {/if}>
        {* Orphan feedbacks *}
        {if ($ORPHAN_FEEDBACKS neq NULL) && (($hiddenButton eq NULL) || ($hiddenButton eq 'yes')) }
            {if $content eq NULL}
                <div class="project-box-content" style="height: 400px">
                {/if}
                {foreach $ORPHAN_FEEDBACKS as $feeback}
                    <div class="col-sm-12 col-md-12 col-lg-12" style="padding: 0.25em;margin: 0 1.5em">
                        <div class="col-md-1">&nbsp;</div>
                        <div class="col-md-9">
                            <div class="grid-item border-bottom border-secondary" style="height: 1.6em">
                                <small style="float: left"><i
                                        class="fa fa-calendar"></i>&nbsp;&nbsp;{$feeback->getFeedbackDate()}
                                </small>
                                <strong style="float: right">{$feeback->getUserName()}</strong>
                            </div>
                            <div class="grid-item">
                                <i>{$feeback->getTitle()}</i>
                                &nbsp;{$feeback->getFeedback()}
                                <hr style="width:90%;text-align:center;">
                            </div>
                        </div>
                        <div class="col-md-1 text-center" style="margin-right: -4px">
                            <figure class="grid-img">
                                <img src="{$feeback->getUserAvatar()}" class="img-responsive">
                            </figure>
                        </div>
                    </div>
                {/foreach}
                {if $content eq NULL}
                </div>
            {/if}
        {/if}
        {if $content neq NULL}
            <div class="project-box-content">
                {foreach $content as $report}
                    {* Report row *}
                    <div class="row" style="margin-bottom: 4px">
                        <div class="{if (($hiddenButton eq NULL) || ($hiddenButton eq 'yes'))}col-md-1{else}col-md-2{/if} text-center"
                            style="margin-right: -4px">
                            <figure class="grid-img" style="float: right">
                                <img src="{$report->getUserAvatar()}" class="img-responsive">
                            </figure>
                        </div>
                        <div class="col-md-10">
                            <div class="grid-item border-bottom border-secondary">
                                <strong>{$report->getUserName()}</strong>
                                <small style="float: right"><i class="fa fa-calendar"></i>&nbsp;&nbsp;{$report->getReportDate()}
                                </small>
                            </div>
                            <div class="grid-item">
                                <a href="index.php?module=grid_view&action=EditActivityReport&record={$RECORD}&formodule={$MODULE}&reportid={$report->getId()}&Ajax=true"
                                    data-title="{*Reporte sobre una actividad:*}" data-width="850" data-toggle="lightbox"
                                    data-parent="" data-gallery="remoteload" class="link">
                                    <i class="fa fa-pencil"></i></a>
                                {if $hiddenButton neq 'not'}
                                    <i {if $report->isHeld()}style="text-decoration: line-through;"
                                        {/if}>{$report->getTitle()}</i><br>
                                    <small style="color: red">Unidades
                                        empleadas:&nbsp;{$NUMBERING_HELPER->setNumberFormat($report->getTimeDuration())}&nbsp;{if $report->getEstimatedTimeUnit() neq null}{$report->getEstimatedTimeUnit()}{else}Hora{/if}.&nbsp;Avance
                                        reportado:&nbsp;{$report->getCeilProgress()}%{if $report->getActualCost() neq null}.&nbsp;Costo
                                        ejecutado:&nbsp;${$NUMBERING_HELPER->setNumberFormat($report->getActualCost())}{/if}</small><br />
                                    {$report->getReport()}
                                    <hr style="width:90%;text-align:center;">
                                {else}
                                    <span {if $report->isHeld()}style="text-decoration: line-through;" {/if}>{$report->getTitle()}
                                        {*- $report->getCeilProgress()%*}</span>
                                {/if}
                            </div>
                        </div>
                        {if (($hiddenButton eq NULL) || ($hiddenButton eq 'yes'))}
                            <div class="col-md-1">&nbsp;</div>
                        {/if}
                    </div>
                    {* Report row *}
                    {* Feedback row *}
                    {if ($report->getFeedbacks() neq NULL) && (($hiddenButton eq NULL) || ($hiddenButton eq 'yes')) }
                        {assign var='feebacks' value= $report->getFeedbacks()}
                        {foreach $feebacks as $feeback}
                            <div class="row" style="margin-bottom: 4px">
                                <div class="col-sm-12 col-md-12 col-lg-12" style="padding: 0.25em;margin: 0 1.5em">
                                    <div class="col-md-1">&nbsp;</div>
                                    <div class="col-md-9">
                                        <div class="grid-item border-bottom border-secondary" style="height: 1.6em">
                                            <small style="float: left"><i
                                                    class="fa fa-calendar"></i>&nbsp;&nbsp;{$feeback->getFeedbackDate()}
                                            </small>
                                            <strong style="float: right">{$feeback->getUserName()}</strong>
                                        </div>
                                        <div class="grid-item">
                                            <i>{$feeback->getTitle()}</i>
                                            &nbsp;{$feeback->getFeedback()}
                                            <hr style="width:90%;text-align:center;">
                                        </div>
                                    </div>
                                    <div class="col-md-1 text-center" style="margin-right: -4px">
                                        <figure class="grid-img">
                                            <img src="{$feeback->getUserAvatar()}" class="img-responsive">
                                        </figure>
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                    {/if}
                    {* Feedback row *}
                {/foreach}
            </div>
        {elseif ($ORPHAN_FEEDBACKS eq NULL) && (($hiddenButton neq NULL) || ($hiddenButton neq 'yes'))}
            {* ----- *}
            <h4 class="text-center" style="margin-bottom: 6px; margin-top: -2px; z-index: 1000;top: -4px">
                <small>Sin&nbsp;{$boxContenet->getLabel()}.&nbsp;¡Crea la primera!</small>
            </h4>
            {* ----- *}
        {/if}
    </div>
    <div class="project-box-footer clearfix">
    </div>
    <div class="project-box-ultrafooter clearfix">
</div>