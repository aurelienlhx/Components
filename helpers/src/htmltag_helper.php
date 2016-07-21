<?php

if(!function_exists('script')){
	/**
	 * 
	 */
	function script($attributes, $text = ''){
		
		if(!empty($text))
			$text = '//<![CDATA[ '."\n".$text."\n".'//]]>';
		else 
			$text = '';

		$script = '<script ';
		$script .= array_join_assoc($attributes,'="','" ');
		$script .= '>';
		$script .= $text;
		$script .= '</script>';

		return $script;
	}
}