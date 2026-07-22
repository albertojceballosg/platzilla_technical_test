<?php
	
	class CustomDateTime extends DateTime {
		
		/**
		 * Note: This function is not called by PHP's date/time functions.
		 * Override "modify()
		 *
		 * @param $string
		 * @return CustomDateTime|DateTime|false
		 */
		#[\ReturnTypeWillChange]
		public function modify($string) {
			//$weekday = $this->format('w');
		      // Change the modifier string if needed
		      if ( $this->format('N') == 1 ) { // It's Sunday and we're calculating a day using relative weeks
		          $matches = array();
		          $pattern = '/this week|next week|previous week|last week/i';
		          if ( preg_match( $pattern, $string, $matches )) {
		              $string = str_replace($matches[0], '-5 days '.$matches[0], $string);
		          }
		      }
		      return parent::modify($string);
		  
		  }
	}