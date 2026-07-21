    {if $SYSALERTS|@count gt 0}
        {foreach $SYSALERTS as $alert}
            <a href="#" rel="{$alert->getId ()}" title="Marcar como vista!" class="list-group-item" onclick="NotificationCenterUtils.disabledAlert (this)">
                <div class="row  rounded">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-4">
                                <p class="pull-left"><strong>Platzilla:</strong></p>
                            </div>
                            <div class="col-md-4">
                                <p class="associated-to"><small>{$alert->getFilter()->getModuleFilter ()|ucfirst}:&nbsp;{$alert->getName ()}</small></p>
                            </div>
                            <div class="col-md-4">
                                <p class="pull-right"><i class="fa fa-clock-o"></i>&nbsp;<small>{$alert->timeSince}</small></p>
                            </div>
                            <div class="col-md-12 ">
                                {$alert->getContents ()}
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        {/foreach}
    {else}
        <a href="#" class="list-group-item ">
        <div class="row parley-own rounded">
            <div class="col-md-1">
                <img class="img-circle img-responsive"  src="../themes/centaurus/img/platzillaman.png" alt="">
            </div>
            <div class="col-md-11">
                <div class="row">
                    <div class="col-md-4">
                        <p class="pull-left"><strong>Platzilla</strong></p>
                    </div>
                    <div class="col-md-4">
                        <p class="associated-to"><small></small></p>
                    </div>
                    <div class="col-md-4">
                        <p class="pull-right"><i class="fa fa-clock-o"></i>&nbsp;<small>00:00</small></p>
                    </div>
                    <div class="col-md-12">
                        <span>No se encontraron alertas<span>
                    </div>
                </div>
            </div>
        </div>
        </a>
    {/if}