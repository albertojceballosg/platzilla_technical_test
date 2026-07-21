<div class="modal fade"  id="{block name="modal-id"}{/block}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog {block name="modal-class"}{/block}">
    <div id="modal-help_tabs" class="modal-content">
      <form action="{block name="modal-action"}{/block}">
        <div class="md-help modal-header" >
          <button type="button" class="close" data-dismiss="modal">x</button>       
    
          {block name="modal-title"}       

          {/block}
        </div>
        <div class="modal-body help">
          {block name="modal-body"}

          {/block}        
        </div>
        <div class="modal-footer">
          {block name="modal-buttons"}
            <button type="button" class="btn modal-help-cancel" data-dismiss="modal">Cerrar</button>
          {/block}
        </div>
     </form>
    </div>
  </div>
</div>
