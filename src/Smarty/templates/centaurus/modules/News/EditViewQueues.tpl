{strip}
    {if $AD_QUEUE neq NULL}
        {assign var='queueId' value=$AD_QUEUE->getId ()}
        {assign var='queueName' value=$AD_QUEUE->getName ()}
        {assign var='period' value=$AD_QUEUE->getPeriod ()}
        {assign var='status' value=$AD_QUEUE->getStatus ()}
        {assign var='description' value=$AD_QUEUE->getDescription ()}
    {else}
        {assign var='queueId' value=null}
        {assign var='queueName' value=null}
        {assign var='period' value=null}
        {assign var='status' value=null}
        {assign var='description' value=null}
    {/if}
    <link rel="stylesheet" type="text/css" href="modules/News/News.css"/>
    <form method="post" action="index.php" {*onsubmit="return NewsUtils.validateForm (this);"*}>
        <input type="hidden" name="module" value="News"/>
        <input type="hidden" name="action" value="SaveAdQueue"/>
        <input type="hidden" name="record" value="{$queueId}"/>
        <div class="row">
            <div class="col-md-12">
                <h1 class="pull-left">
                    <a href="index.php?module=News&action=ListView&parenttab=Settings">Cola de anuncios</a>
                </h1>
                <div class="action-bar pull-right">
                    <button type="submit" class="btn btn-info">Guardar</button>
                    {if $queueId neq NULL}
                    &nbsp;<a href="index.php?module=News&action=EditViewQueues&parenttab=Settings" class="btn btn-primary"><i
                                class="fa fa-plus-circle"></i> Crear cola de anuncios</a>{/if}
                    <a href="index.php?module=News&action=ListView&tab=ad-queue-tab&parenttab=Settings" class="btn btn-warning"
                       style="margin-left: 5px;">Cancelar</a>
                </div>
            </div>
        </div>
        {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
            <div class="row">
                <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
                    <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
                </div>
            </div>
        {/if}

        <div class="row">
            <div class="col-md-12">
                <div class="main-box">
                    <header class="main-box-header clearfix">
                        <h2 class="pull-left">Información general</h2>
                    </header>
                    <div class="main-box-body">
                        <div class="row form-group">
                            <label for="queuename" class="col-md-2 control-label">Nombre</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" placeholder="Nombre de la cola" id="quue-name" name="queuename"  value="{$queueName}">
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="quuedescription" class="col-md-2 control-label">Descripción de la cola</label>
                            <div class="col-md-10">
                                <textarea id="queue-description" name="quuedescription" class="form-control" rows="3" placeholder="breve descripción de la cola">{$description}</textarea>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="period" class="col-md-2 control-label">Periodo</label>
                            <div class="col-md-10">
                                <select class="form-control" name="period" id="period">
                                    {foreach $AVAILABLE_PERIODS as $key => $availablePeriod}
                                        <option value="{$key}"
                                                {if $key eq $period}selected{/if} >{$MOD[$availablePeriod]}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="period" class="col-md-2 control-label">Estado</label>
                            <div class="col-md-10">
                                <select class="form-control" name="status" id="status">
                                    {foreach $AVAILABLE_STATUS as $availableStatus}
                                        <option value="{$availableStatus}"
                                                {if $availableStatus eq $status}selected{/if} >{$MOD[$availableStatus]}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <script type="text/javascript" src="modules/News/news-utils.js"></script>
{/strip}