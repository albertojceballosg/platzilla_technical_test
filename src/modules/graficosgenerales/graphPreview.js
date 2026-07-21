/**
 * Funcionalidad de previsualización de gráficos en modal
 */

// Función para abrir el modal de previsualización
window.openGraphPreviewModal = function (
  graphId,
  applicationCode,
  graphType,
  graphTitle
) {
  // Actualizar título del modal
  jQuery("#graphPreviewModalLabel").text(graphTitle);

  // Limpiar contenedor y mostrar loading
  jQuery("#graphPreviewContainer").html(
    '<img src="themes/images/loading.gif" alt="Cargando..." class="img-responsive center-block" />'
  );

  // Abrir modal
  jQuery("#graphPreviewModal").modal("show");

  // Obtener el ID del contenedor original del gráfico
  var originalContainerId = applicationCode + "-" + graphType + "-" + graphId;

  var captureGraph = function () {
    var originalContainer = document.getElementById(originalContainerId);
    var originalSvg = null;
    var originalCanvas = null;

    console.log("Capturando gráfico con ID:", originalContainerId);
    console.log("Contenedor encontrado:", originalContainer);

    if (!originalContainer) {
      console.log("El contenedor no existe en el DOM");
      jQuery("#graphPreviewContainer").html(
        '<div class="alert alert-warning">El gráfico no está visible en la página actual. Por favor, asegúrate de que el gráfico esté cargado antes de previsualizarlo.</div>'
      );
      return;
    }

    // Verificar si el contenedor tiene una alerta de "sin datos" visible
    var alertElement = originalContainer.querySelector(".alert");
    if (alertElement) {
      var alertStyle = window.getComputedStyle(alertElement);
      if (alertStyle.display !== "none") {
        console.log("El gráfico muestra alerta de sin datos");
        jQuery("#graphPreviewContainer").html(
          '<div class="alert alert-info">Este gráfico no tiene datos para mostrar en el rango de fechas seleccionado.</div>'
        );
        return;
      }
    }

    // Verificar si el contenedor aún está cargando
    var loadingImg = originalContainer.querySelector('img[alt="Loading"]');
    if (loadingImg) {
      var imgStyle = window.getComputedStyle(loadingImg);
      if (imgStyle.display !== "none") {
        console.log("El gráfico aún está cargando");
        jQuery("#graphPreviewContainer").html(
          '<div class="alert alert-info">El gráfico aún se está cargando. Por favor, espera un momento e intenta nuevamente.</div>'
        );
        return;
      }
    }

    // Verificar si el contenedor está visible
    if (
      originalContainer.offsetParent === null &&
      originalContainer.style.display !== "none"
    ) {
      console.log("El contenedor existe pero no está visible");
    }

    originalSvg = originalContainer.querySelector("svg");
    console.log("SVG encontrado directamente:", originalSvg);

    if (!originalSvg) {
      var iframe = originalContainer.querySelector("iframe");
      console.log("Iframe encontrado:", iframe);

      if (iframe && iframe.contentDocument) {
        try {
          originalSvg = iframe.contentDocument.querySelector("svg");
          console.log("SVG encontrado dentro del iframe:", originalSvg);
        } catch (e) {
          console.log("Error accediendo al iframe:", e);
        }
      }
    }

    if (originalSvg) {
      console.log("Clonando SVG...");
      var previewContainerId = "preview-" + originalContainerId;
      jQuery("#graphPreviewContainer").html(
        '<div id="' +
          previewContainerId +
          '" style="width: 100%; height: 100%;"></div>'
      );

      var clonedSvg = originalSvg.cloneNode(true);

      // Intentar obtener dimensiones configuradas del contenedor
      var configuredWidth = originalContainer.getAttribute("data-configured-width");
      var configuredHeight = originalContainer.getAttribute("data-configured-height");
      
      var originalWidth = configuredWidth || 
        originalSvg.getAttribute("width") ||
        originalSvg.width.baseVal.value ||
        900;
      var originalHeight = configuredHeight ||
        originalSvg.getAttribute("height") ||
        originalSvg.height.baseVal.value ||
        400;

      console.log(
        "Dimensiones configuradas del gráfico:",
        originalWidth,
        "x",
        originalHeight
      );

      // Establecer viewBox con las dimensiones configuradas
      clonedSvg.setAttribute(
        "viewBox",
        "0 0 " + originalWidth + " " + originalHeight
      );

      // Eliminar dimensiones fijas
      clonedSvg.removeAttribute("width");
      clonedSvg.removeAttribute("height");

      // Usar preserveAspectRatio='xMidYMid meet' para mantener proporciones
      // Esto escala el gráfico proporcionalmente y lo centra en el espacio disponible
      clonedSvg.setAttribute("preserveAspectRatio", "xMidYMid meet");

      // Establecer dimensiones para que respete el tamaño original sin estirarse
      clonedSvg.style.width = "auto";
      clonedSvg.style.height = "auto";
      clonedSvg.style.maxWidth = "100%";
      clonedSvg.style.maxHeight = "100%";
      clonedSvg.style.display = "block";
      clonedSvg.style.margin = "0 auto";

      jQuery("#" + previewContainerId)
        .html("")
        .append(clonedSvg);
      console.log("SVG clonado con preserveAspectRatio='xMidYMid meet' para mantener proporciones");
    } else {
      console.log(
        "No se encontró ningún SVG, intentando capturar canvas (Flot)"
      );
      originalCanvas = originalContainer.querySelector("canvas");
      console.log("Canvas encontrado:", originalCanvas);

      if (originalCanvas && typeof originalCanvas.toDataURL === "function") {
        var previewContainerId = "preview-" + originalContainerId;
        jQuery("#graphPreviewContainer").html(
          '<div id="' +
            previewContainerId +
            '" style="width: 100%; height: 100%; display:flex; justify-content:center; align-items:center; padding: 20px; position: relative;"></div>'
        );

        // Capturar el canvas
        var img = document.createElement("img");
        img.src = originalCanvas.toDataURL("image/png");
        img.style.maxWidth = "100%";
        img.style.maxHeight = "100%";
        img.style.objectFit = "contain";
        document.getElementById(previewContainerId).appendChild(img);

        // Capturar la leyenda si existe
        var legend = originalContainer.querySelector(".legend");
        if (legend) {
          console.log("Leyenda encontrada, clonando...");
          var clonedLegend = legend.cloneNode(true);
          clonedLegend.style.position = "absolute";
          clonedLegend.style.top = "80px";
          clonedLegend.style.right = "80px";
          clonedLegend.style.margin = "0";
          clonedLegend.style.backgroundColor = "rgba(255, 255, 255, 0.9)";
          clonedLegend.style.padding = "8px";
          clonedLegend.style.borderRadius = "4px";
          clonedLegend.style.boxShadow = "0 2px 4px rgba(0,0,0,0.1)";

          // Forzar que cada elemento de la leyenda esté en una sola línea
          var legendItems = clonedLegend.querySelectorAll("td");
          for (var i = 0; i < legendItems.length; i++) {
            legendItems[i].style.whiteSpace = "nowrap";
          }

          document.getElementById(previewContainerId).appendChild(clonedLegend);
        } else {
          console.log("No se encontró leyenda en el contenedor");
        }

        console.log("Canvas capturado y convertido a imagen con leyenda");
      } else {
        console.log("No se encontró SVG ni canvas");
        // Intentar activar la pestaña del gráfico si está oculta
        var parentTab = jQuery(originalContainer).closest(".tab-pane");
        if (parentTab.length > 0 && !parentTab.hasClass("active")) {
          var tabId = parentTab.attr("id");
          console.log("El gráfico está en una pestaña inactiva:", tabId);
          jQuery("#graphPreviewContainer").html(
            '<div class="alert alert-warning">El gráfico está en una pestaña que no está activa. Por favor, activa la pestaña "' +
              tabId +
              '" primero y luego intenta previsualizar el gráfico.</div>'
          );
        } else {
          jQuery("#graphPreviewContainer").html(
            '<div class="alert alert-warning">No se pudo encontrar el SVG o canvas del gráfico. Asegúrate de que el gráfico esté completamente cargado.</div>'
          );
        }
      }
    }
  };

  // Verificar si el gráfico está en una pestaña inactiva y activarla
  var checkAndActivateTab = function () {
    var container = document.getElementById(originalContainerId);
    if (container) {
      var parentTab = jQuery(container).closest(".tab-pane");
      if (parentTab.length > 0 && !parentTab.hasClass("active")) {
        var tabId = parentTab.attr("id");
        console.log("Activando pestaña inactiva:", tabId);
        // Buscar el enlace de la pestaña y hacer clic
        var tabLink = jQuery('a[href="#' + tabId + '"]');
        if (tabLink.length > 0) {
          tabLink.tab("show");
          return true; // Pestaña activada
        }
      }
    }
    return false; // No hay pestaña inactiva
  };

  var captureExecuted = false;
  var wrappedCaptureGraph = function () {
    if (captureExecuted) {
      console.log("Captura ya ejecutada, ignorando llamada adicional");
      return;
    }
    captureExecuted = true;
    captureGraph();
  };

  // Para gráficos de embudo (funnel), esperar al evento de renderizado
  if (graphType === "funnel" || graphType === "embudo") {
    var eventFired = false;
    var timeoutId;
    var tabActivated = checkAndActivateTab();

    // Escuchar el evento de renderizado del gráfico
    jQuery("#" + originalContainerId).one("funnelGraphRendered", function () {
      console.log("Evento funnelGraphRendered recibido");
      eventFired = true;
      clearTimeout(timeoutId);
      setTimeout(wrappedCaptureGraph, 200);
    });

    // Timeout de seguridad en caso de que el evento no se dispare
    // Si activamos una pestaña, dar más tiempo para que se renderice
    var timeout = tabActivated ? 5000 : 3000;
    timeoutId = setTimeout(function () {
      if (!eventFired) {
        console.log("Timeout alcanzado, capturando gráfico sin esperar evento");
        wrappedCaptureGraph();
      }
    }, timeout);
  } else {
    // Para otros tipos de gráficos, verificar pestaña y usar timeout
    checkAndActivateTab();
    setTimeout(wrappedCaptureGraph, 500);
  }
};

// Limpiar el modal al cerrarlo
jQuery(document).ready(function () {
  jQuery("#graphPreviewModal").on("hidden.bs.modal", function () {
    jQuery("#graphPreviewContainer").html(
      '<img src="themes/images/loading.gif" alt="Cargando..." class="img-responsive center-block" />'
    );
  });
});
