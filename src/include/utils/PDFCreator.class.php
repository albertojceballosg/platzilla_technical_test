<?php
	require_once (__DIR__ . '/../mpdf/mpdf.php');

	class PDFCreator {
		private static $INSTANCE = null;

		public function createPDFFromHTML ($htmlContents, $filename, $pageSize = 'Letter', $pageOrientation = 'P') {
			if (!$htmlContents) {
				throw new Exception ('No se ha suministrado el contenido');
			}
			if (!$filename) {
				throw new Exception ('No se ha suministrado el nombre del archivo a generar');
			}
			$format = $pageSize . ($pageOrientation != 'P' ? "-$pageOrientation" : '');

			$mpdf = new mPDF ('utf-8', $format, null, null, 0, 0, 0, 0, 0, 0);
			$mpdf->WriteHTML ($htmlContents);
			$mpdf->Output ($filename, 'D');
		}

		public static function getInstance () {
			if (self::$INSTANCE == null) {
				self::$INSTANCE = new PDFCreator ();
			}
			return self::$INSTANCE;
		}

	}
