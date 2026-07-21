<?php
	/**
	 * Created by PhpStorm.
	 * User: Wilfredo
	 * Date: 26/10/2021
	 * Time: 2:55 PM
	 */
	
	interface SystemalertsInterface {
		const SCALE        = array ('Month', 'Week');
		const STATUS       = array(0, 1);
		const SOURCE_ALERT = array ('Indicators', 'Task_object_no_cump', 'Task_prog');
	}