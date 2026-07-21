<div class="row-parley justify-content-center">
        <div class="col-md-11 box_shadow" style="background-color: white;margin: 12px;padding: 24px">
            <p style="text-align: left;">Archivar en:</p>
        <form  class="form-inline" role="form" id="archive-mails" name="archived-mails" method="POST">
            <span style="color: #8A0808" class="help-block"></span>
            <input type="hidden" name="idEmail" value="0">
                        <div class="form-group">
                            <!-- <label>{$MOD.LBL_MODULE}:</label>  -->
                            <div class="input-group">
                                <select class="form-control" id="archveModule" name="archveModule" title="Buscar por modulo">
                                    <option value="" selected>Módulo</option>
                                    {foreach $PARLEY_MODULES as $module}
                                        <option value="{$module.module}">{$module.tablabel}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <!-- <label>Buscar en:</label> -->
                        <div class="input-group">
                            <select class="form-control" id="archveField" name="archveField" title="Buscar por campo">
                                <option value="" selected>Campo</option>

                            </select>
                        </div>
                        <!-- <label>:</label> -->
                        <div class="form-group">
                            <input type="text" class="form-control" name="searchField" placeholder="Contiene..">
                        </div>
            <button name="submitSearch" id="emailsSubmitSearch" class="btn btn-primary btn-sm"  type="button"><i class="fa fa-search" aria-hidden="true"></i></button>
        </form>
    </div>
    <div  class="col-md-11 archive-select">
    </div>
</div>
