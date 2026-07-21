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
			$compute = @create_function ('', 'return '.$input.';');
			if ($compute === false || !is_callable($compute)) {
				return 0;
			}
			return (0 + $compute());
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
