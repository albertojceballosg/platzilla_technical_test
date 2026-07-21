{assign var="init" value=null}
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/bootstrap/bootstrap.min.css"/>
<link rel="stylesheet" type="text/css" href="modules/store/survey.css?v=1.1"/>
<style type="text/css">
.row p {
    font-size: 0.85em;
}
</style>
<div class="feedback row" style="max-height: 490px!important;">
    {*$VALUES_TEST|var_dump*}
    <div class="col-lg-12 col-md-12 col-xs-12" style="{if $init neq NULL}margin-top: 10px!important;{/if};text-align: center">
        <p class="text-justify" style="font-size: 12px">{$QUESTONNAIRE['descrption']}</p>
        {if $QUESTONNAIRE['objetive'] neq NULL}
            <p class="text-justify"><span style="font-weight: bold">Objetivo del cuestionario:&nbsp;</span>{$QUESTONNAIRE['objetive']}</p>
        {/if}
    </div>
    {if $FEDD_BACKS neq NULL}
    <div class="col-lg-12 col-md-12 col-xs-12">
        <h4>Mensaje de retroalimentación</h4>
    </div>
        {foreach $FEDD_BACKS as $feedback}
            <div class="col-lg-12 col-md-12 col-xs-12" style="{if $init neq NULL}margin-top: 10px!important;{/if}">
                <div class="col-lg-12 col-md-12 col-xs-12" style="padding-left: 0!important;">
                    <h3>{$feedback.question}</h3>
                </div>
                <div class="row">
                    <div <div class="col-lg-6 col-md-6 col-xs-6">
                        {if {$feedback['group']} neq NULL}
                        <p class="text-left"><span style="font-weight: bold">Grupo:&nbsp;</span>{$feedback['group']}</p>
                        {/if}
                    </div>
                    <div <div class="col-lg-6 col-md-6 col-xs-6">
                        {if $feedback['theme'] neq NULL}
                        <p class="text-left"><span style="font-weight: bold">Tema:&nbsp;</span>{$feedback['theme']}</p>
                        {/if}
                    </div>
                </div>
                <div class="col-lg-12 col-md-12 col-xs-12">
                 <p class="text-justify">{$feedback['feedback']}</p>
                </div>
            </div>
            {assign var="init" value='ready'}
        {/foreach}
    {/if}
</div>
<div class="row">
    <div class="col-lg-12 col-md-12 col-xs-12" style="padding: 0 20px; margin-top: 20px">
        <button type="button"
                onclick="refreshPage()"
                class="btn btn-primary btn-xs btn-block">Reiniciar el cuestionario</button>
    </div>
</div>
<script type="text/javascript" src="themes/centaurus/js/jquery.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap.js"></script>
<script type="text/javascript" src="themes/centaurus/js/jquery.nicescroll.js"></script>
<script type="text/javascript">
    jQuery(".feedback").niceScroll();  //
    function refreshPage(){
        window.location.reload();
    }
</script>
