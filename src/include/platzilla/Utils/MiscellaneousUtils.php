<?php

	class MiscellaneousUtils {

		/**
		 * @param array $thisArray
		 * @param array $thatArray
		 *
		 * @return boolean
		 */
		public static function areArrayValuesEqual ($thisArray, $thatArray) {
			if (($thisArray === null) && ($thatArray === null)) {
				return true;
			} else if (
				(empty ($thisArray) !== empty ($thatArray)) ||
				(!is_array ($thisArray)) ||
				(!is_array ($thatArray)) ||
				(count ($thisArray) != count ($thatArray))
			) {
				return false;
			} else {
				$theseValues = array_values ($thisArray);
				$thoseValues = array_values ($thatArray);
				sort ($theseValues);
				sort ($thoseValues);
				return ($theseValues == $thoseValues);
			}
		}

		/**
		 * @param array $theseElements
		 * @param array $thoseElements
		 *
		 * @return boolean
		 */
		public static function areObjectArraysEqual ($theseElements, $thoseElements) {
			if ((empty ($theseElements)) && (empty ($thoseElements))) {
				return true;
			} else if (
				(empty ($theseElements) !== empty ($thoseElements)) ||
				(!is_array ($thoseElements)) ||
				(count ($theseElements) != count ($thoseElements))
			) {
				return false;
			} else {
				foreach ($theseElements as $thisElement) {
					$equals = false;
					foreach ($thoseElements as $thatElement) {
						/** @noinspection PhpUndefinedMethodInspection */
						if ((is_callable (array ($thisElement, 'isEqualTo'))) && ($thisElement->isEqualTo ($thatElement))) {
							$equals = true;
							break;
						}
					}
					if (!$equals) {
						return false;
					}
				}
				return true;
			}
		}

		/**
		 * @param object $thisObject
		 * @param object $thatObject
		 *
		 * @return boolean
		 */
		public static function areObjectsEqual ($thisObject, $thatObject) {
			if ((empty ($thisObject)) && (empty ($thatObject))) {
				return true;
			} else if (empty ($thisObject) !== empty ($thatObject)) {
				return false;
			} else if (is_callable (array ($thisObject, 'isEqualTo'))) {
				/** @noinspection PhpUndefinedMethodInspection */
				return $thisObject->isEqualTo ($thatObject);
			} else {
				return $thisObject == $thatObject;
			}
		}

	}
