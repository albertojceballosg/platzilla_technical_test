<?php

	interface FilterGroupInterface {

		/**
		 * @param FilterGroup $group
		 */
		public function copyValuesFrom ($group);

		/**
		 * @return FilterGroup
		 */
		public static function getInstance ();

	}
