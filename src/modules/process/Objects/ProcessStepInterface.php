<?php
	
	interface ProcessStepInterface {
		const QUALITY_EXECUTION = array ('BAD' => 'Malo', 'GOOD' => 'Bueno','REGULAR' => 'Regular',);
		const SCORING_MATRIX    = array (
			'BAD'     => array ('AT_RISK' => '#FF0000', 'IN_TIME' => '#FFA500', 'OUT_TIME' => '#A30000'),
			'GOOD'    => array ('AT_RISK' => '#89B300', 'IN_TIME' => '#008000', 'OUT_TIME' => '#FFA500'),
			'REGULAR' => array ('AT_RISK' => '#FF8200', 'IN_TIME' => ' #ECE50C', 'OUT_TIME' => '#FF0000'),
		);
	}