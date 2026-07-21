<div class='row'>
    <div class='col-lg-12'>
        <h1><a href='index.php?module={$MODULE}&action=index'>Previsualización</a></h1>
    </div>
</div>

<div class='row'>
    <div id='wtwid' class='col-lg-12'>
        <div class='row'>
            <div class='col-md-offset-4 col-lg-3'>
                <div class='main-box infographic-box'>
                    <i class='{$ICONWIDGET} {$COLORWIDGET}'></i>
                    <span class='value  {$COLORVALUE}' style='text-align: left;'>{if $VALOR eq NULL}0{else}{$VALOR}{/if}</span>
                    <span class='headline' style='text-align: left; font-size: 1em;'>{$TXTWIDGET}</span>
                </div>
            </div>
        </div>
    </div>
</div>