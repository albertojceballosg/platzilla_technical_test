<link rel="stylesheet" href="{$THEME_PATH}css/compiled/store.css">



{literal}
<style>
	
</style>
{/literal}



<div class="row">
  <div class="col-lg-12 text-center">
    <h1>Comprar tu suscripción en PLATZILLA es muy fácil</h1>
  </div>
</div>


<form role="form" name="payment" method="POST" action="index.php" id="payment" onsubmit="return validaForm()">
  <input type="hidden" name="action" value="payment"/>
  <input type="hidden" name="module" value="store"/>
  <input type="hidden" name="amount" id="amount" value=""/>


  <div class="row">
    <div class="col-lg-12">
      <div class="main-box">
        <header class="main-box-header clearfix">
          <h1>Paso 1</h1>
          <h2>Confirma tus datos</h2>
        </header>
        <div class="main-box-body clearfix">
            <div class="form-group col-md-6">
              <label for="nombre">Nombre</label>
              <input class="form-control" id="nombre" name="nombre" placeholder="" value="{$ORGANIZATIONDETAILS.firstname}" type="text">
            </div>
            <div class="form-group col-md-6">
              <label for="apellido">Apellido</label>
              <input class="form-control" id="apellido" name="apellido" placeholder="" value="{$ORGANIZATIONDETAILS.lastname}" type="text">
            </div>
            <div class="form-group col-md-6">
              <label for="telefono">Teléfono</label>
              <input class="form-control" id="telefono" name="telefono" placeholder="" value="{$ORGANIZATIONDETAILS.phone}" type="text">
            </div>
          <div class="form-group col-md-6">
            <label for="pais">País</label>
            <select class="form-control" id="pais" name="pais">
              <option value=""></option>
                {foreach from=$PAISESFORSELECT item=pais}
                  <option value="{$pais.pais}">{$pais.pais}</option>
                {/foreach}
            </select>
          </div>
            <div class="form-group col-md-6">
              <label for="empresa">Empresa</label>
              <input class="form-control" id="empresa" name="empresa" placeholder="" value="{$ORGANIZATIONDETAILS.nombreempresa}" type="text">
            </div>
            <div class="form-group col-md-6">
              <label for="cif">CIF/NIF (Número)</label>
              <input class="form-control" id="cif" name="cif" placeholder="" value="{$ORGANIZATIONDETAILS.cif}" type="text">
            </div>
            <div class="form-group col-md-6">
              <label for="direccion">Dirección</label>
              <textarea class="form-control" id="direccion" name="direccion"></textarea>
            </div>
            <div class="form-group col-md-6">
              <label for="codigopostal">Código Postal</label>
              <input class="form-control" id="codigopostal" name="codigopostal" placeholder="" type="text">
            </div>
        </div>
      </div>
    </div>
  </div>


  <div class="row">
    <div class="col-lg-12">
      <div class="main-box">
        <header class="main-box-header clearfix">
          <h1>Paso 2</h1>
          <h2>Mira los detalles de tu suscripción</h2>
        </header>
        <div class="main-box-body clearfix">

          {foreach key=keyCat item=cat from=$APPSTOCONTRACT}

            <div class="row">
              <div class="col-lg-12">
                <div class="main-box">
                  <header class="main-box-header clearfix">
                  </header>
                  <div class="main-box-body clearfix">
                    <div class="row col-lg-12">
		      <!--[ TT11178 ] Fallos-Ajustes 1 - Store - Platzilla
			    Cambio de tamaño de icono de App
			    JA 21/06/2016-->
                      <div class="col-lg-2 text-center"><img src="{$APPSIMAGE_PATH}{$cat.app_code}.png" width="80"></div>
                      <div class="col-lg-8"><span class="app-title-confirmation">{$cat.app_name}</span>  {$cat.app_descripcion}</div>
                      <div class="col-lg-2 text-center"><span class="app-price-confirmation">{$cat.app_price} Euros</span> <br> <a href="index.php?module=store&action=confirmacion&deleteApp={$cat.app_code}" class="btn btn-danger btn-sm">ELIMINAR</a>  </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          {/foreach}


          <div class="row">
            <div class="col-lg-4 pull-right">

              
              <div class="table-responsive">
                <table class="table table-striped">
                  <tr>
                    <th>Suscripción</th>
                    <th></th>
                  </tr>
                  {* USERS INSTANCE DETAIL TODAY - AV 20170620*}
                  <tr>
                    <td colspan="2">Usuarios contratados para la fecha (<span id="usuariosContratadosAlMomento">{$USUARIOSCONTRATADOSALMOMENTO}</span> Usuarios)</td>
                    {*<td><strong><span id="actualTotalPriceForUser">{$TOTALPRICEFORUSERSALMOMENTO}</span> EUR</strong></td>*}
                  </tr>
                  {* USERS INSTANCE DETAIL TODAY - AV 20170620*}
                  <tr>
                    <td>
                      <input type="number" id="usersCounter" name="usersCounter" price="{$PRICEFORUSERS}" value="{$TOTALUSERS}" min="0" max="20" class="form-control" style="width:170px;float:right" required> Usuarios adicionales
                    </td>
                    <td><span id="priceforuser">{$PRICEFORUSERS}</span> EUR x Usuario</td>
                  </tr>
                  
                  <tr class="total">
                    <td>
                      <strong class="uppercase">Total</strong> / Mes
                    </td>
                    <td>
                      {*<strong><span id="totalpriceforuser">{$TOTALPRICEFORUSERS}</span> EUR</strong>*}
                      <strong><span id="totalpriceforuser">{$TOTALPRICEFORUSERS}</span> EUR</strong>
                    </td>
                  </tr>
                </table>
              </div>


              <div class="row col-lg-12 text-center">
                <input type="submit" class="btn btn-success btn-lg" value="Pagar Ahora"/><br>
                <img class="logo-braintree" src="storage/logos/braintree-badge-dark.png" />
              </div>

              

           </div>
          </div>



        </div>
      </div>
    </div>
  </div>



</form>























  <!--script type="text/javascript" src="https://code.jquery.com/jquery-2.2.1.min.js"></script-->
  <!--script type="text/javascript" src="store.js"></script-->





  <script type="text/javascript">


  jQuery(document).ready(function() {ldelim} 

    jQuery("[id^='addApp_']").on('click', function(e){ldelim}

      var id = this.id;
      id = id.split('_');
      id = id[1];

      //alert("tocando boton Instalar " + id);
      var param = 'id=' + id;

          new Ajax.Request(
            'index.php',
            {ldelim}queue: {ldelim}position: 'end', scope: 'command'},
                  method: 'post',
                  postBody: 'module=store&action=calculaApps&file=calculaApps&Ajax=true&'+param,
                  onComplete: function(response) {ldelim}
                  
                    var respuesta = JSON.parse(response.responseText);

                    var apps = respuesta['appsToContract'];
                    var activo = 0;

                    for (Appindex = 0; Appindex < apps.length; ++Appindex) {ldelim}
                      if(id == apps[Appindex]['appId'] ) {ldelim}
                        activo = 1;
                       {rdelim}
                    {rdelim}

                    if (activo == 1 ){ldelim}
                      jQuery("[id^='addApp_"+id+"']").addClass('btn-danger');
                      jQuery("[id^='addApp_"+id+"']").html('DESINSTALAR');
                    {rdelim}else{ldelim}
                      jQuery("[id^='addApp_"+id+"']").removeClass('btn-danger');
                      jQuery("[id^='addApp_"+id+"']").html('INSTALAR');
                    {rdelim}
                    


                    // Llenando tabla Mensual
                    jQuery('#mensual-tab-content').html('');
                    jQuery('#mensual-tab-content').html(respuesta['htmlTablaMensual']); 

                    // Llenando tabla Mensual
                    jQuery('#anual-tab-content').html('');
                    jQuery('#anual-tab-content').html(respuesta['htmlTablaAnual']);                    
                      
                  {rdelim}
            {rdelim} 
      );

    
    {rdelim});

  {rdelim});


  


  // Evento para calcular total por usuarios
  jQuery('#usersCounter').on('change', function(e){ldelim}

    var priceforuser = jQuery('#usersCounter').attr('price'); 
    var usersCounter = jQuery('#usersCounter').val();
    // ADDED
    // var actualTotalPriceForUser = jQuery('#usersCounter').attr('actualTotalPriceForUser');
    // ADDED

    // var totalpriceforuser = (usersCounter * priceforuser) + parseFloat(actualTotalPriceForUser);
      var totalpriceforuser = (usersCounter * priceforuser);
    jQuery('#totalpriceforuser').html(totalpriceforuser);

  {rdelim});


  // Validando form
  function validaForm(){ldelim}

    if (!jQuery('#nombre').val()){ldelim}
      alert('Escriba su nombre');
      return false;
    {rdelim}
    if (!jQuery('#apellido').val()){ldelim}
      alert('Escriba su apellido');
      return false;
    {rdelim}
    if (!jQuery('#telefono').val()){ldelim}
      alert('Escriba su Teléfono');
      return false;
    {rdelim}
    if (!jQuery('#pais').val()){ldelim}
        alert('Seleccione el país');
        return false;
    {rdelim}
    if (!jQuery('#cif').val()){ldelim}
      alert('Escriba su CIF/NIF');
      return false;
    {rdelim}
    if (!jQuery('#empresa').val()){ldelim}
      alert('Escriba el nombre de la Empresa');
      return false;
    {rdelim}

  return true;


  {rdelim}




  </script>








{literal}
  <style media="screen">
    .row-body{
      margin-top: 3em;
      margin-bottom: 3em;
    }
  </style>




{/literal}







