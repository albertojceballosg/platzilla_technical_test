
<div class="row">
  <div id="login-box" {block name="style-box"}{/block}>
    <div class="row">
      <div class="col-xs-12">
        {block name="begin-special-content"}{/block}
        <div id="login-box-holder">
          <div class="row">
            <div class="col-xs-12">
              <header class="main-box-header clearfix" id="login-header">
                <div id="login-logo" {block name="header-class"}{/block}>
                  {block name="header-logo"}
                  
                  {/block}
                </div>                        
                <div style="background: white;">
                  <hr class="linea" {block name="style-line"}{/block}>
                </div>
              </header>
              <div id="login-box-inner">
                <div class="row">
                  <div class="col-xs-12">
                    {block name="box-title"}                    
                    {/block}
                  </div>
                </div>
                <div class="row">
                  <div class="col-xs-12">
                    {block name="box-form"}                      
                    {/block}

                    <div class="row">
                      <div class="col-xs-12">
                        <div class="login-error">
                          <p class="social-text" style="text-align:justify">{$LOGIN_ERROR}</p>
                        </div>
                      </div>
                    </div>

                    {block name="box-content"}
                    {/block}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        {block name="end-special-contend"}{/block}
      </div>
    </div>
  </div>
</div>

{block name="extra-content"}
{/block}


