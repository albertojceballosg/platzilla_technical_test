{strip}
    {math equation= rand() assign= "idPlatziIsabel"}
    {block name="css"}{/block}
    <div class="row module-buttons">
        <div class="col-lg-12" style="padding-right: 10px; padding-bottom: 0">
            <div class="pull-left">
                <h1>
                    <a href="index.php?module=platzi_issabel&action=ListView&parenttab="
                       title="Listado de Prospectos" style="text-decoration: none"><strong>Centralita</strong>
                        <span style="color: #777777;font-size: 0.8em;font-weight: bold">&nbsp;&gt;</span>
                    </a><small style="font-weight: bold"> {if isset($RECORDING) && $RECORDING neq NULL}{$RECORDING->getUniqueId()}{/if}</small>
                </h1>
            </div>
            <div class="pull-right">
            </div>
        </div>
    </div>
    {if (!empty ($MESSAGE))}
        <div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
            <strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
        </div>
    {/if}
    {block name="page_header"}{/block}
    <div class="container-fluid" style="background-color: transparent!important; ">
        <div class="main-box clearfix" style="height: 100% !important;background-color: transparent!important;">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-xs-12">
                    <div class="card rounded">
                        <div class="card-header platzilla-card-header rounded">
                            <div class="row">
                                {block name="card_contenet"}{/block}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {block name="modal_detalview"}{/block}
    {block name="js"}{/block}
{/strip}