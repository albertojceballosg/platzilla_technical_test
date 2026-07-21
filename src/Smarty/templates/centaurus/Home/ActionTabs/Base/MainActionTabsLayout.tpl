{strip}
    {block name="css"}{/block}
    <div class="main-box clearfix" style="margin-top: 0">
        <div class="row">
            <div class="col-lg-12" style="padding-right: 10px; padding-bottom: 0">
                <div class="pull-left">&nbsp;</div>
                <div class="pull-right">
                    <ul class="nav nav-tabs nav-platzilla">
                        {foreach $MODULE_TABS as $tab_name => $tab_label}
                            <li{if ($SELECTED_MODULE eq $tab_name)} class="active"{/if}>
                                <a data-toggle="tab" href="#{$tab_name}">{$tab_label}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                </div>
                <div class="tab-content">
                    {foreach $MODULE_TABS as $tab_name => $tab_label}
                        <div id="{$tab_name}"
                             class="tab-pane fade{if ($SELECTED_MODULE eq $tab_name)} active in{/if}">
                            <div>
                                {block name=$tab_name}{/block}
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
    </div>
    {block name="js"}{/block}
{/strip}