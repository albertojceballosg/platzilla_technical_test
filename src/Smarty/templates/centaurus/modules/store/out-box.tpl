
{block name="title"}
<title>Platzilla Management</title>
{/block}

{block name="css"}  
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/box-frame.css" />
{/block}

{block name="body"}
<div class="">
  <div class="row">
    <div class="col-xs-12">
      {block name="nabvar-center"}
      <div class="row">
        <div class="col-xs-12">
          <nav class="nav nav-bar-default navbar-static-top">
            <div class="">
              <ul class="header-nav nav navbar-nav navbar-center">
                <li class="active" ><a href="#">Apps</a></li>
                <li><a href="#">Precios</a></li>
                <li><a href="#">Nosotros</a></li>
                <li><a href="#">Contacto</a></li>
              </ul>
            </div>
          </nav>      
        </div>
      </div>          
      {/block}
      
      {block name ="content"}
      {/block}

    </div>
  </div>
</div>
{/block}


{block name="scripts"}
<!-- global scripts -->
<!--script src="themes/centaurus/js/demo-skin-changer.js"></script--> <!-- only for demo -->

<script src="themes/centaurus/js/jquery.js"></script>
<script src="themes/centaurus/js/bootstrap.js"></script>
<script src="themes/centaurus/js/jquery.nanoscroller.min.js"></script>

<!-- theme scripts -->
<script src="themes/centaurus/js/scripts.js"></script>

{block name="scripts"}
{/block}

{/block}
