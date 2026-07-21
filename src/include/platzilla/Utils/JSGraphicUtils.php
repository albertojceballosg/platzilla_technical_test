<?php
	class JSGraphicUtils {

		/** @var JSGraphicUtils[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		/** @var string */
		private $chartDiv;

		/** @var array */
		private $chartPackages;

		/** @var array */
		private $chartVisualization;

		/** @var string */
		private $footer;

		/** @var array */
		private $generalOption;

		/** @var string */
		private $header;
		
		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
			$this->initialize ();
		}

		private function fetchData ($graphicData) {
			$data = array(
				array('Year', 'Sales'),
				array('2004', 400),
				array('2005', 300),
				array('2006', 120),
			);

			$data = json_encode ($graphicData, JSON_NUMERIC_CHECK);
			return "var data = google.visualization.arrayToDataTable (\n{$data}\n)\n";
		}

		private function getChartOptions ($myGraphic) {
			if (empty ($myGraphic ['graphicoptions'])) {
				$options = $this->generalOption;
			} else {
				$options = json_decode($myGraphic ['graphicoptions'], true);
			}
			$containerDiv = "{$myGraphic ['applicationcode']}-{$myGraphic ['tipografico']}-{$myGraphic ['graficoid']}";
			$graphType = $myGraphic['tipografico'];
			$isPreview = ($myGraphic['applicationcode'] === 'preview');
	
			$js  = "var container = document.getElementById('{$containerDiv}');\n";
			$js .= 'var options = ' . json_encode($options, JSON_FORCE_OBJECT) . ";\n";
			$js .= "var isPreview = " . ($isPreview ? 'true' : 'false') . ";\n";
			$js .= "var graphType = '{$graphType}';\n";

			// Guardar dimensiones configuradas antes de cualquier conversión
			$js .= "var configuredWidth = options.width;\n";
			$js .= "var configuredHeight = options.height;\n";
		
			// Convertir strings numéricos a números
			$js .= "if (typeof configuredWidth === 'string' && /^\\d+$/.test(configuredWidth)) { configuredWidth = parseInt(configuredWidth, 10); }\n";
			$js .= "if (typeof configuredHeight === 'string' && /^\\d+$/.test(configuredHeight)) { configuredHeight = parseInt(configuredHeight, 10); }\n";

			// REGLA 1: En galería, usar espacio disponible del contenedor
			// REGLA 2: En preview, usar dimensiones configuradas
			$js .= "if (container) {\n";
			$js .= "  if (!isPreview) {\n";
			$js .= "    // GALERÍA: Calcular ancho basado en el contenedor disponible\n";
			$js .= "    \n";
			$js .= "    // Tratamiento especial para tablas\n";
			$js .= "    if (graphType === 'table') {\n";
			$js .= "      // Las tablas usan width 100% y height estándar de 380px con scroll interno\n";
			$js .= "      options.width = '100%';\n";
			$js .= "      options.height = 380;\n";
			$js .= "      // Configurar paginación para tablas con muchas filas\n";
			$js .= "      if (!options.page) { options.page = 'enable'; }\n";
			$js .= "      if (!options.pageSize) { options.pageSize = 10; }\n";
			$js .= "      container.style.height = '380px';\n";
			$js .= "      container.style.overflowY = 'auto';\n";
			$js .= "    } else {\n";
			$js .= "      // Para gráficos de charts (pie, column, bar, etc.)\n";
			$js .= "      var availableWidth;\n";
			$js .= "      \n";
			$js .= "      // Verificar si el contenedor está visible\n";
			$js .= "      var tabPane = container.closest('.tab-pane');\n";
			$js .= "      var isVisible = !tabPane || tabPane.classList.contains('active');\n";
			$js .= "      \n";
			$js .= "      if (!isVisible) {\n";
			$js .= "        // Si está en una pestaña inactiva, calcular basándose en el ancho del viewport\n";
			$js .= "        var viewportWidth = window.innerWidth || document.documentElement.clientWidth;\n";
			$js .= "        // Asumir que col-lg-6 es 50% del contenedor principal\n";
			$js .= "        // Restar márgenes y padding aproximados\n";
			$js .= "        availableWidth = (viewportWidth * 0.5) - 100;\n";
			$js .= "      } else {\n";
			$js .= "        // Buscar el contenedor de columna Bootstrap más cercano (col-lg-*, col-md-*, etc.)\n";
			$js .= "        var colContainer = container.closest('[class*=\"col-\"]');\n";
			$js .= "        if (colContainer) {\n";
			$js .= "          // Usar el ancho de la columna Bootstrap\n";
			$js .= "          var colWidth = colContainer.offsetWidth || colContainer.clientWidth;\n";
			$js .= "          // Buscar si hay un .rounded dentro de la columna\n";
			$js .= "          var roundedContainer = container.closest('.rounded');\n";
			$js .= "          if (roundedContainer) {\n";
			$js .= "            // Restar padding del .rounded (10px left + 20px right = 30px total)\n";
			$js .= "            availableWidth = colWidth - 30;\n";
			$js .= "          } else {\n";
			$js .= "            // Sin .rounded, usar el ancho de la columna directamente\n";
			$js .= "            availableWidth = colWidth - 20;\n";
			$js .= "          }\n";
			$js .= "        } else {\n";
			$js .= "          // Fallback: buscar .main-box-body (BasicGraph.tpl antiguo)\n";
			$js .= "          var mainBoxBody = container.closest('.main-box-body');\n";
			$js .= "          if (mainBoxBody) {\n";
			$js .= "            var bodyWidth = mainBoxBody.offsetWidth || mainBoxBody.clientWidth;\n";
			$js .= "            availableWidth = bodyWidth - 60;\n";
			$js .= "          } else {\n";
			$js .= "            // Fallback final: usar ancho del contenedor directamente\n";
			$js .= "            availableWidth = container.offsetWidth || container.clientWidth || 355;\n";
			$js .= "          }\n";
			$js .= "        }\n";
			$js .= "      }\n";
			$js .= "      \n";
			$js .= "      options.width = Math.max(availableWidth, 280);\n";
			$js .= "      \n";
			$js .= "      // Reducir 20% adicional para gráficos de torta (pie/donut)\n";
			$js .= "      if (graphType === 'pie' || graphType === 'donut') {\n";
			$js .= "        options.width = Math.floor(options.width * 0.8);\n";
			$js .= "      }\n";
			$js .= "      \n";
			$js .= "      // Altura: usar la configurada o la del contenedor\n";
			$js .= "      if (typeof configuredHeight === 'number' && configuredHeight > 0) {\n";
			$js .= "        options.height = configuredHeight;\n";
			$js .= "        container.style.height = configuredHeight + 'px';\n";
			$js .= "      } else {\n";
			$js .= "        options.height = 380; // Altura por defecto\n";
			$js .= "        container.style.height = '380px';\n";
			$js .= "      }\n";
			$js .= "    }\n";
			$js .= "  } else {\n";
			$js .= "    // PREVIEW: Usar dimensiones configuradas\n";
			$js .= "    container.style.marginLeft = '0';\n";
			$js .= "    container.style.marginRight = '0';\n";
			$js .= "    \n";
			$js .= "    // Para gráficos de columnas y barras en preview, usar todo el ancho disponible del modal\n";
			$js .= "    if (graphType === 'column' || graphType === 'bar') {\n";
			$js .= "      var modalContent = container.closest('.modal-content');\n";
			$js .= "      if (modalContent) {\n";
			$js .= "        var modalWidth = modalContent.offsetWidth || modalContent.clientWidth;\n";
			$js .= "        // Restar padding del modal-body (aproximadamente 30px por lado)\n";
			$js .= "        options.width = Math.max(modalWidth - 60, 600);\n";
			$js .= "      } else {\n";
			$js .= "        options.width = 900;\n";
			$js .= "      }\n";
			$js .= "    } else {\n";
			$js .= "      // Para otros tipos de gráficos, usar width configurado\n";
			$js .= "      if (typeof configuredWidth === 'number' && configuredWidth > 0) {\n";
			$js .= "        options.width = configuredWidth;\n";
			$js .= "      } else {\n";
			$js .= "        options.width = 900; // Fallback para preview\n";
			$js .= "      }\n";
			$js .= "    }\n";
			$js .= "    \n";
			$js .= "    // Usar height configurado\n";
			$js .= "    if (typeof configuredHeight === 'number' && configuredHeight > 0) {\n";
			$js .= "      options.height = configuredHeight;\n";
			$js .= "    } else {\n";
			$js .= "      options.height = 400; // Fallback para preview\n";
			$js .= "    }\n";
			$js .= "    \n";
			$js .= "    // Almacenar dimensiones configuradas en el contenedor para graphPreview.js\n";
			$js .= "    container.setAttribute('data-configured-width', options.width);\n";
			$js .= "    container.setAttribute('data-configured-height', options.height);\n";
			$js .= "  }\n";
			$js .= "}\n";
		
			// Ajustar chartArea según tipo de gráfico para maximizar espacio
			$js .= "if (!options.chartArea) { options.chartArea = {}; }\n";
			$js .= "if (graphType === 'pie' || graphType === 'donut') {\n";
			$js .= "    options.chartArea.width = '90%';\n";
			$js .= "    options.chartArea.height = '85%';\n";
			$js .= "    options.chartArea.left = '5%';\n";
			$js .= "    options.chartArea.top = '10%';\n";
			$js .= "  } else if (graphType === 'column') {\n";
			$js .= "    // Gráficos de columnas (verticales)\n";
			$js .= "    if (isPreview) {\n";
			$js .= "      options.chartArea.width = '93%';\n";
			$js .= "      options.chartArea.height = '82%';\n";
			$js .= "      options.chartArea.left = '5%';\n";
			$js .= "      options.chartArea.top = '5%';\n";
			$js .= "      options.chartArea.right = '2%';\n";
			$js .= "      options.chartArea.bottom = '13%';\n";
			$js .= "      if (!options.bar) { options.bar = {}; }\n";
			$js .= "      options.bar.groupWidth = '95%';\n";
			$js .= "      if (!options.hAxis) { options.hAxis = {}; }\n";
			$js .= "      options.hAxis.viewWindowMode = 'maximized';\n";
			$js .= "      if (!options.vAxis) { options.vAxis = {}; }\n";
			$js .= "      options.vAxis.viewWindowMode = 'maximized';\n";
			$js .= "    } else {\n";
			$js .= "      options.chartArea.width = '75%';\n";
			$js .= "      options.chartArea.height = '70%';\n";
			$js .= "      options.chartArea.left = '10%';\n";
			$js .= "      options.chartArea.top = '10%';\n";
			$js .= "    }\n";
			$js .= "  } else if (graphType === 'bar') {\n";
			$js .= "    // Gráficos de barras (horizontales) - necesitan más espacio para etiquetas del eje Y\n";
			$js .= "    if (isPreview) {\n";
			$js .= "      options.chartArea.width = '60%';\n";
			$js .= "      options.chartArea.height = '85%';\n";
			$js .= "      options.chartArea.left = '35%';\n";
			$js .= "      options.chartArea.top = '5%';\n";
			$js .= "      options.chartArea.right = '5%';\n";
			$js .= "      options.chartArea.bottom = '10%';\n";
			$js .= "      if (!options.bar) { options.bar = {}; }\n";
			$js .= "      options.bar.groupWidth = '90%';\n";
			$js .= "    } else {\n";
			$js .= "      options.chartArea.width = '60%';\n";
			$js .= "      options.chartArea.height = '75%';\n";
			$js .= "      options.chartArea.left = '30%';\n";
			$js .= "      options.chartArea.top = '10%';\n";
			$js .= "    }\n";
			$js .= "  } else {\n";
			$js .= "    // Otros tipos de gráficos\n";
			$js .= "    if (isPreview) {\n";
			$js .= "      options.chartArea.width = '88%';\n";
			$js .= "      options.chartArea.height = '80%';\n";
			$js .= "      options.chartArea.left = '8%';\n";
			$js .= "      options.chartArea.top = '8%';\n";
			$js .= "    } else {\n";
			$js .= "      options.chartArea.width = '80%';\n";
			$js .= "      options.chartArea.height = '75%';\n";
			$js .= "      options.chartArea.left = '10%';\n";
			$js .= "      options.chartArea.top = '10%';\n";
			$js .= "    }\n";
			$js .= "  }\n";
		
			// Ajustar leyenda para no ocupar demasiado espacio
			$js .= "if (options.legend && options.legend.position === 'right') {\n";
			$js .= "  options.legend.maxLines = 10;\n";
			$js .= "  if (!options.legend.textStyle) { options.legend.textStyle = {}; }\n";
			$js .= "  options.legend.textStyle.fontSize = 11;\n";
			$js .= "}\n";
		
			// Configuración especial para leyendas en top/bottom
			$js .= "if (options.legend && (options.legend.position === 'top' || options.legend.position === 'bottom')) {\n";
			$js .= "  // Maximizar ancho disponible para la leyenda\n";
			$js .= "  options.legend.alignment = 'center';\n";
			$js .= "  // Permitir múltiples líneas si es necesario\n";
			$js .= "  options.legend.maxLines = 3;\n";
			$js .= "  // Reducir tamaño de fuente para aprovechar mejor el espacio\n";
			$js .= "  if (!options.legend.textStyle) { options.legend.textStyle = {}; }\n";
			$js .= "  options.legend.textStyle.fontSize = 10;\n";
			$js .= "  // Ajustar chartArea para dar más espacio a la leyenda\n";
			$js .= "  if (options.legend.position === 'bottom') {\n";
			$js .= "    if (options.chartArea) { options.chartArea.bottom = 80; }\n";
			$js .= "  } else if (options.legend.position === 'top') {\n";
			$js .= "    if (options.chartArea) { options.chartArea.top = 80; }\n";
			$js .= "  }\n";
			$js .= "}\n";
		
			return $js;
		}

		private function getChartVisualization ($myGraphic) {
			return "var chart = new google.{$this->chartVisualization [$myGraphic['tipografico']]} (document.getElementById ('{$this->chartDiv}'));\n";
		}

		/**
		 * @param string $type
		 *
		 * @return string
		 */
		private function getPackages ($type) {
			return json_encode ($this->chartPackages [$type]);
		}

		private function initialize () {
			$this->chartPackages = array (
				'area'   => array ('corechart'),
				'bar'    => array ('corechart'),
				'column' => array ('corechart'),
				'combo'  => array ('corechart'),
				'donut'  => array ('corechart'),
				'line'   => array ('corechart'),
				'pie'    => array ('corechart'),
				'table'  => array ('table'),
			);
			$this->chartVisualization = array (
				'area'   => 'visualization.AreaChart',
				'bar'    => 'visualization.BarChart',
				'column' => 'visualization.ColumnChart',
				'combo'  => 'visualization.ComboChart',
				'donut'  => 'visualization.PieChart',
				'line'   => 'visualization.LineChart',
				'pie'    => 'visualization.PieChart',
				'table'  => 'visualization.Table',
			);

			$this->generalOption = array (
				'width'       => '100%',
				'height'      => 382,
				'chartArea'   => array ('width' => '85%', 'height' => '78%'),
				'forceIFrame' => false,
				'legend'      => array ('position' => 'right', 'alignment' => 'center', 'textStyle' => array('fontSize' => 12)),
			);

			$this->header = '<script type="text/javascript">' . "\n";
			$this->footer = '</script>';
		}

		/**
		 * @param array $params
		 * @param Smarty $smarty
		 *
		 * @return string
		 */
		public function fetchGoogleChartJs ($params, &$smarty) {
			if (!count ($params)) {
				return '';
			}

			$myGraphic      = $params ['objGraphic'];
			$this->chartDiv = "{$myGraphic ['applicationcode']}-{$myGraphic ['tipografico']}-{$myGraphic ['graficoid']}";
			if (empty ($myGraphic ['dataGrafico'])) {
				$js  = "jQuery ('#{$this->chartDiv}').children ('.alert').show ();";
				$js .= "jQuery ('#{$this->chartDiv}').children ('#loading-graphic').remove();";
				return $this->header . $js . $this->footer;
			}

			$functionName = 'draw' . ucfirst ($myGraphic['tipografico']) . $myGraphic ['graficoid'];

			$js  = "google.charts.load('current', {'packages':{$this->getPackages ($myGraphic ['tipografico'])}});\n";
			$js .= "google.charts.setOnLoadCallback({$functionName});\n";
			$js .= "function {$functionName} () {\n";
			$js .= $this->fetchData ($myGraphic ['dataGrafico']);
			$js .= $this->getChartOptions ($myGraphic);
			$js .= $this->getChartVisualization ($myGraphic);
			$js .= "chart.draw (data, options);\n}";

			return $this->header . $js . $this->footer;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return JSGraphicUtils
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCES === null) {
				self::$INSTANCES = array ();
			}
			if (!isset (self::$INSTANCES [ $adb->dbName ])) {
				self::$INSTANCES [ $adb->dbName ] = new self ($adb);
			}
			return self::$INSTANCES [ $adb->dbName ];
		}

	}
