/**
 * WorkTaskActivityModal - Reusable component for viewing tasks in a modal
 * This component can be invoked from anywhere in Platzilla to display task information
 *
 * Usage:
 *   WorkTaskActivityModal.openView(activityId);
 *   WorkTaskActivityModal.openEdit(activityId); // For backward compatibility
 */

(function () {
  "use strict";

  var WorkTaskActivityModal = {
    /**
     * Modal container ID
     */
    modalId: "taskViewModalContainer",

    /**
     * Opens the task view modal
     * @param {number} activityId - The ID of the activity/task to display
     */
    openView: function (activityId) {

      if (!activityId || activityId <= 0) {
                console.error('WorkTaskActivityModal: Invalid activity ID provided');
        return;
      }

      this._showLoadingModal();
      this._fetchTaskData(activityId);
    },

    /**
     * Alias for openView - for backward compatibility with existing code
     * @param {number} activityId - The ID of the activity/task to display
     */
    openEdit: function (activityId) {
      this.openView(activityId);
    },

    /**
     * Shows a loading modal while fetching data
     * @private
     */
    _showLoadingModal: function () {
      var spinnerStyles =
        "<style>" +
        ".task-modal-spinner { margin: 40px auto; width: 50px; height: 40px; text-align: center; font-size: 10px; }" +
        ".task-modal-spinner > div { background-color: #337ab7; height: 100%; width: 6px; display: inline-block; margin: 0 1px; " +
        "-webkit-animation: sk-stretchdelay 1.2s infinite ease-in-out; animation: sk-stretchdelay 1.2s infinite ease-in-out; }" +
        ".task-modal-spinner .rect2 { -webkit-animation-delay: -1.1s; animation-delay: -1.1s; }" +
        ".task-modal-spinner .rect3 { -webkit-animation-delay: -1.0s; animation-delay: -1.0s; }" +
        ".task-modal-spinner .rect4 { -webkit-animation-delay: -0.9s; animation-delay: -0.9s; }" +
        ".task-modal-spinner .rect5 { -webkit-animation-delay: -0.8s; animation-delay: -0.8s; }" +
        "@-webkit-keyframes sk-stretchdelay { 0%, 40%, 100% { -webkit-transform: scaleY(0.4) } 20% { -webkit-transform: scaleY(1.0) } }" +
        "@keyframes sk-stretchdelay { 0%, 40%, 100% { transform: scaleY(0.4); -webkit-transform: scaleY(0.4); } " +
        "20% { transform: scaleY(1.0); -webkit-transform: scaleY(1.0); } }" +
        "</style>";

      var loadingHtml =
        '<div style="text-align: center; padding: 60px 20px;">' +
        '<div class="task-modal-spinner">' +
        '<div class="rect1"></div>' +
        '<div class="rect2"></div>' +
        '<div class="rect3"></div>' +
        '<div class="rect4"></div>' +
        '<div class="rect5"></div>' +
        "</div>" +
        '<p style="margin-top: 30px; font-size: 16px; color: #666;">Cargando información de la tarea...</p>' +
        "</div>";

      var modalHtml =
        '<div class="modal fade" id="' + this.modalId + '" tabindex="-1" role="dialog">' +
        	'<div class="modal-dialog modal-lg" role="document">' +
        		'<div class="modal-content">' +
			        spinnerStyles +
			        '<div class="modal-body">' +
				        loadingHtml +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';

      // Remove existing modal if present
      jQuery("#" + this.modalId).remove();

      // Add modal to body
      jQuery("body").append(modalHtml);

      // Show modal
      jQuery("#" + this.modalId).modal({
        backdrop: "static",
        keyboard: false,
      });

      // Remove focus from all elements before hiding modal to avoid aria-hidden warning
      jQuery("#" + this.modalId).on("hide.bs.modal", function () {
        jQuery(this).find(":focus").blur();
      });
    },

    /**
     * Fetches task data from server
     * @private
     * @param {number} activityId - The ID of the activity/task
     */
    _fetchTaskData: function (activityId) {
      var self = this;
      var ajaxUrl =
        "index.php?module=Calendar&action=CalendarAjax&function=VIEW-TASK-MODAL&activityid=" +
        activityId;

      jQuery.ajax({
        url: ajaxUrl,
        type: "GET",
        dataType: "json",
        beforeSend: function () {
        },
        success: function (response) {
          if (response.success) {
            self._displayTaskModal(response.html, response.taskExists);
          } else {
            self._showError(response.error || "Error desconocido al cargar la tarea");
          }
        },
        error: function (xhr, status, error) {
          console.error("[ERROR] WorkTaskActivityModal: AJAX error", error);
          console.error("[ERROR] Response status:", xhr.status);
          console.error("[ERROR] Response text:", xhr.responseText);

          var errorMsg = "Error de comunicación con el servidor";
          if (xhr.responseText) {
            // Si la respuesta es HTML, mostrar un mensaje más específico
            if (
              xhr.responseText.indexOf("<!DOCTYPE") !== -1 ||
              xhr.responseText.indexOf("<html") !== -1
            ) {
              errorMsg =
                "El servidor devolvió una página HTML en lugar de datos JSON. Esto puede indicar un error de autenticación o un problema en el servidor.";
            }
          }

          self._showError(errorMsg);
        },
      });
    },

    /**
     * Displays the task modal with fetched content
     * @private
     * @param {string} html - The HTML content to display
     * @param {boolean} taskExists - Whether the task exists
     */
    _displayTaskModal: function (html, taskExists) {
      var modalContent =
        '<div class="modal fade" id="' + this.modalId + '" tabindex="-1" role="dialog">' +
        	'<div class="modal-dialog modal-lg" role="document">' +
        		'<div class="modal-content">' +
       			 	html +
        		'</div>' +
        	'</div>' +
        '</div>';

      // Remove existing modal
      jQuery("#" + this.modalId).remove();

      // Add new modal
      jQuery("body").append(modalContent);

      // Show modal
      jQuery("#" + this.modalId).modal("show");

      // Bind close button to properly close modal
      var self = this;
      jQuery("#" + this.modalId).find('[data-dismiss="modal"]').on("click", function () {
          self.closeModal();
        });

      // Remove focus from all elements before hiding modal to avoid aria-hidden warning
      jQuery("#" + this.modalId).on("hide.bs.modal", function () {
        jQuery(this).find(":focus").blur();
      });

      // Clean up on close
      jQuery("#" + this.modalId).on("hidden.bs.modal", function () {
        jQuery(this).remove();
        jQuery(".modal-backdrop").remove();
        jQuery("body").removeClass("modal-open");
      });
    },

    /**
     * Shows an error message in the modal
     * @private
     * @param {string} errorMessage - The error message to display
     */
    _showError: function (errorMessage) {
      var errorHtml =
        '<div class="modal-header">' +
        '<button type="button" class="close" data-dismiss="modal">&times;</button>' +
        '<h4 class="modal-title">' +
        '<i class="bi bi-exclamation-triangle text-danger"></i> Error' +
        "</h4>" +
        "</div>" +
        '<div class="modal-body">' +
        '<div class="alert alert-danger">' +
        '<i class="bi bi-exclamation-circle"></i> ' +
        errorMessage +
        "</div>" +
        "</div>" +
        '<div class="modal-footer">' +
        '<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>' +
        "</div>";

      this._displayErrorModal(errorHtml);
    },

    /**
     * Displays the error modal with focus management
     * @private
     * @param {string} html - The HTML content to display
     */
    _displayErrorModal: function (html) {
      var modalContent =
        '<div class="modal fade" id="' + this.modalId + '" tabindex="-1" role="dialog">' +
        	'<div class="modal-dialog modal-lg" role="document">' +
        		'<div class="modal-content">' +
       			 	html +
        		'</div>' +
        	'</div>' +
        '</div>';

      // Remove existing modal
      jQuery("#" + this.modalId).remove();

      // Add new modal
      jQuery("body").append(modalContent);

      // Show modal
      jQuery("#" + this.modalId).modal("show");

      // Remove focus from all elements before hiding modal to avoid aria-hidden warning
      jQuery("#" + this.modalId).on("hide.bs.modal", function () {
        jQuery(this).find(":focus").blur();
      });

      // Clean up on close
      jQuery("#" + this.modalId).on("hidden.bs.modal", function () {
        jQuery(this).remove();
        jQuery(".modal-backdrop").remove();
        jQuery("body").removeClass("modal-open");
      });
    },

    /**
     * Closes the modal completely (including backdrop)
     * @public
     */
    closeModal: function () {
      var $modal = jQuery("#" + this.modalId);
      if ($modal.length) {
        $modal.modal("hide");
        // Force remove backdrop and reset body
        setTimeout(function () {
          jQuery(".modal-backdrop").remove();
          jQuery("body").removeClass("modal-open");
          $modal.remove();
        }, 300);
      }
    },
  };

  // Expose to global scope
  window.WorkTaskActivityModal = WorkTaskActivityModal;
})();
