<?php

	abstract class FieldCalculate {

		const PATTERN = '/(?:\-?\d+(?:\.?\d+)?[\+\-\*\/])+\-?\d+(?:\.?\d+)?/';
		const PARENTHESIS_DEPTH = 10;

		private static function callback ($input) {
			if (is_numeric ($input[1])) {
				return $input [1];
			} else if (preg_match (self::PATTERN, $input[1], $match)) {
				return self::compute ($match [0]);
			}
			return 0;
		}

		/**
		 * @codingStandardsIgnoreStart
		 * Esta función realiza el cálculo de expresiones matematicas representadas en el string input
		 * Nota: aunque create_function is forbidden es la solución encontrada por ahora para actualizar las columnas con cálculo de
		 * un Grid
		 * @param string $input
		 *
		 * @return integer|float
		 */
		private static function compute ($input) {
			// Validar que el input solo contenga caracteres válidos para expresiones matemáticas
			if (!preg_match('/^[\d\.\+\-\*\/\(\)\s]+$/', $input)) {
				return 0;
			}
			// Evitar expresiones vacías o inválidas
			if (empty($input) || $input === '' || !is_string($input)) {
				return 0;
			}
			// create_function() fue eliminada en PHP 8.0. El $input ya está validado arriba
			// como expresión aritmética (solo dígitos . + - * / ( ) y espacios), sin superficie
			// de inyección, por lo que se evalúa con eval() envuelto en try/catch. Retro-compatible
			// con 5.6 (allí un fallo de eval devuelve false en vez de lanzar).
			try {
				$result = @eval ('return ('.$input.');');
			} catch (\Throwable $e) {
				return 0;
			}
			if (!is_numeric ($result)) {
				return 0;
			}
			return (0 + $result);
		}
		/** @codingStandardsIgnoreEnd */

		public static function calculate ($input) {
			$input = str_replace (' ','',$input);
			if(strpos ($input, '+') != null || strpos($input, '-') != null || strpos($input, '/') != null || strpos($input, '*') != null) {
				$input = str_replace (',', '.', $input);
				$input = preg_replace ('[^0-9\.\+\-\*\/\(\)]', '', $input);
				$i     = 0;
				while (strpos ($input, '(') || strpos ($input, ')')) {
					$input = preg_replace_callback ('/\(([^\(\)]+)\)/', 'self::callback', $input);

					$i++;
					if($i > self::PARENTHESIS_DEPTH) {
						break;
					}
				}

				if (preg_match (self::PATTERN, $input, $match)) {
					return self::compute ($match[0]);
				}

				return 0;
			}

			return $input;
		}

	}
