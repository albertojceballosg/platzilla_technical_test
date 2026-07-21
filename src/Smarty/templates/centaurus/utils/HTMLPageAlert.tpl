{block name="first-content"}
    <div class="container-fluid base-list-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="main-box clearfix">
                    <div class="main-box-header clearfix">
                        <div class="row">
                            <div class="col-md-8" style="margin-bottom: 20px">
                                <div class="row">
                                    <div class="col-md-3" style="padding-right: 0">
                                        {* /new combobxo button to  option *}
                                        <div class="btn-group">
                                            {* listView *}
                                            <a data-toggle="tab" href="#ListViewContents" class="btn btn-default"
                                               style=" font-size: 15px!important;"
                                               data-toggle="tab" title="Listado de registros"><i
                                                        class="fa fa-list-ul"></i></a>
                                            {if $WHO_ALERT eq 'GRAPHIC'}
                                                {* boxScore *}
                                            <a data-toggle="tab" href="#LIST-VIEW-BOX-SCORE" class="btn btn-default"
                                               style=" font-size: 15px!important;"
                                               onclick="ListViewTabUtils.activeBoxScoreTab (event)"
                                               data-toggle="tab"><i class="fa fa-heart-o"></i></a>
                                                {* graphics *}
                                            <button type="button" class="btn btn-primary"
                                                    style=" font-size: 15px!important;"><i
                                                        class="fa fa-bar-chart-o"></i></button>
                                                {* calendar *}
                                                <a data-toggle="tab" href="#LIST-VIEW-CALENDAR" class="btn btn-default" style=" font-size: 15px!important;"
                                                   onclick="ListViewTabUtils.activeCalendarTab (event)"
                                                   data-toggle="tab"><i class="fa fa-calendar"></i></a>
                                            {elseif ($WHO_ALERT eq 'BOX-SCORE')}
                                                {* boxScore *}
                                                <button type="button" class="btn btn-primary"
                                                        style=" font-size: 15px!important;"><i
                                                            class="fa fa-heart-o"></i>
                                                </button>
                                                {* graphics *}
                                                <a data-toggle="tab" href="#LIST-VIEW-GRAPHIC" class="btn btn-default"
                                                   onclick="ListViewTabUtils.activeGraphicTab (event)"
                                                   style=" font-size: 15px!important;"
                                                   data-toggle="tab"><i class="fa fa-bar-chart-o"></i></a>
                                                {* Calendar *}
                                                <a data-toggle="tab" href="#LIST-VIEW-CALENDAR" class="btn btn-default" style=" font-size: 15px!important;"
                                                   onclick="ListViewTabUtils.activeCalendarTab (event)"
                                                   data-toggle="tab"><i class="fa fa-calendar"></i></a>
                                            {elseif ($WHO_ALERT eq 'VIEW-CALENDAR')}
                                                {* boxScore *}
                                                <a data-toggle="tab" href="#LIST-VIEW-BOX-SCORE" class="btn btn-default"
                                                   style=" font-size: 15px!important;"
                                                   onclick="ListViewTabUtils.activeBoxScoreTab (event)"
                                                   data-toggle="tab"><i class="fa fa-heart-o"></i></a>
                                                {* grapchics *}
                                                <a data-toggle="tab" href="#LIST-VIEW-GRAPHIC" class="btn btn-default"
                                                   onclick="ListViewTabUtils.activeGraphicTab (event)"
                                                   style=" font-size: 15px!important;"
                                                   data-toggle="tab"><i class="fa fa-bar-chart-o"></i></a>
                                                {* calendar *}
                                                <button type="button" class="btn btn-primary"
                                                        style=" font-size: 15px!important;"><i<i class="fa fa-calendar"></i>
                                                </button>
                                            {/if}
                                        </div>
                                        {* new combobxo button to  option *}
                                    </div>
                                    <div class="col-md-9 pull-left list-view-grafich" style="padding-left: 0">

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="alert alert-{$ALERT_TYPE} center-block">{$ALERT_MESSAGE}</div>
                            </div>
                            <div class="col-md-12">
                                <div style="min-height: 550px">&nbsp;</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="row" style="display: none">
        <div class="col-md-12">
            &nbsp;
        </div>
    </div>
{/block}