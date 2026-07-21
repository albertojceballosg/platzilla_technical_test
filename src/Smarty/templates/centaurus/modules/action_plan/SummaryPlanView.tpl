{math equation= rand() assign= "idSummaryPlan"}
<script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
<link rel="stylesheet" type="text/css" href="modules/model_action_plan/model-action-plan.css">
<section class="">
    <div class="container" id="main-{$idSummaryPlan}">
        <div class="row">
            <div class="card rounded" style="margin-bottom: 2px!important;padding 0.25em 1.2em!important;">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-xs-12 card-header platzilla-card-header">
                            <p class="text-center" style="font-weight: bold;margin-bottom: 10px">{$PLAN['action_plan_name']}</p>
                        </div>
                        <div class="col-lg-12 col-md-12 col-xs-12">
                            <div class="row row-same-height">
                                <div class="col-lg-7 col-md-7 col-xs-7" ">
                                    <div class="well well-sm" style="vertical-align: top;height: 100%">
                                        {if $DESTINATION neq null}
                                        <a href="index.php?module={$DESTINATION['record_module']}&parenttab=&action=DetailView&record={$DESTINATION['record_id']}"
                                           target="_blank"
                                           title="{$DESTINATION['destination_name']}"
                                           style="text-decoration: none;color: #000000">
                                            <p class="text-left" style="font-weight: bold;margin-bottom: 10px">&nbsp;{$DESTINATION['destination_name']}</p></a>
                                        <div class="text-justify">
                                            {$DESTINATION['destination_description']}
                                        </div>
                                        {else}
                                           {* <div class="alert alert-info">Este plan de acción no se encuentra asociado a ningún destino</div> *}
                                            <div>
                                                <p class="text-justify">Los planes de acción suelen estar vinculados a «objetivos de gestión» a corto plazo o a «destinos/retos» a mediano plazo (equivalentes a visiones del equipo).</p>
                                                <p class="text-justify">En caso de no contar, por el momento, con un objetivo de gestión o una visión asociada, el plan de acción debe tener un propósito específico que se logrará mediante la implementación de las iniciativas previstas.</p>
                                                <p class="text-justify">Lo ideal es que vincules el plan de acción con un objetivo de gestión o un destino a alcanzar en un plazo determinado.</p>
                                            </div>
                                        {/if}
                                    </div>
                                </div>
                                <div class="col-lg-5 col-md-5 col-xs-5">
                                    {if $PLAN['video_type'] neq NULL}
                                        <div>
                                            {if $PLAN['video_type'] eq 'VIMEO'}
                                                {math equation= rand() assign= "idVideo"}
                                                <div id="video-{$idVideo}"
                                                     class="embed-responsive embed-responsive-16by9"
                                                     style="text-align: center;"
                                                     data-vimeo-url="{$PLAN['informative_video']}">
                                                </div>
                                                <script type="text/javascript"
                                                        src="https://player.vimeo.com/api/player.js"></script>
                                            {elseif ($PLAN['video_type'] eq 'YOUTUBE')}
                                                <div class="embed-responsive embed-responsive-16by9 video">
                                                    <iframe class="embed-responsive-item"
                                                            src="{$PLAN['informative_video']}"
                                                            allow="autoplay; fullscreen"
                                                            allowfullscreen="" frameborder="0">
                                                    </iframe>
                                                </div>
                                            {/if}
                                        </div>
                                        {* image *}
                                    {elseif $IMAGE_ACTION_PLAN neq NULL}
                                        <div>
                                            <img src="{$IMAGE_ACTION_PLAN['uri']}" alt="{$IMAGE_ACTION_PLAN['name']}"
                                                 alt="Plan de acción" class="img-responsive">
                                        </div>
                                    {else}
                                        <p class="text-center" style="font-weight: bold">
                                            Presentación del plan</p>
                                    {/if}
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12 col-md-12 col-xs-12" style="padding-top: 15px">
                            <p class="text-justify">{$PLAN['plan_summary']}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>