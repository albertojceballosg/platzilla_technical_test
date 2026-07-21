<?php

	class ConfigEditorHandler extends VTEventHandler {

		public function handleEvent ($eventName, $data) {
			if ($eventName == 'vtiger.entity.beforesave') {
				return;
			}
			if ($eventName == 'vtiger.entity.aftersave') {
				return;
			}
		}
		
	}
