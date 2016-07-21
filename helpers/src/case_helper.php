<?php

if(!function_exists('hasUppercase')){
	/**
	 * Check if a string has an uppercase character
	 *
	 * @param string $string
	 *
	 */
	function hasUppercase($string){
		return (bool) preg_match('#[A-Z]#',$string);
	}
}

if(!function_exists('hasLowercase')){
	/**
	 * Check if a string has an uppercase character
	 *
	 * @param string $string
	 *
	 */
	function hasLowercase($string){
		return (bool) preg_match('#[a-z]#',$string);
	}
}

if(!function_exists('isUppercase')){
	/**
	 * Check if a string is fully uppercase
	 *
	 * @param string $string
	 *
	 */
	function isUppercase($string){
		return (bool) $string === strtoupper($string);
	}
}

if(!function_exists('isLowercase')){
	/**
	 * Check if a string is fully lowercase
	 *
	 * @param string $string
	 *
	 */
	function isLowercase($string){
		return (bool) $string === strtolower($string);
	}
}


if(!function_exists('camelize')){
	/**
	 * Format a string to a camel case
	 * 
	 * @param string $str
	 * @param bool $ucfirst
	 *
	 * @return string
	 */
	function camelize($str, $ucfirst = false){
		$str = explode(' ', $str);
		if(count($str) == 1){
			if($ucfirst) $str = ucfirst($str[0]);
			else $str = lcfirst($str[0]);
		} 
		else {
			$str = implode(array_map("ucfirst",$str),"");
			if($ucfirst) $str = ucfirst($str);
			else $str = lcfirst($str);
		}
		return $str;
	}
}

if(!function_exists('dasherize')){
	/**
	 * Format a case to lower case with specified dashes
	 *
	 * @param string $str
	 * @param string $separator
	 *
	 * @return $string
	 */
	function dasherize($str,$separator = "-"){
		$str = preg_split('#\s+#',trim($str));
		$str = strtolower(implode($str,$separator));
		return $str;
	}
}

if(!function_exists('slugify')){
	/**
	 * Format string to a slug case eg: Héll Wôrld ! => hello-world
	 * A slug is a string for url so we need to sanitize specials characters
	 * @see http://userguide.icu-project.org/transforms/general
	 * 
	 * @param string $str
	 *
	 * @return string
	 */
	function slugify($str){
		
		//transliterate te string
		$str = transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] Remove',$str);
		//donotwork: $str = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $str);
		
		//remove all subsistant special characters after transliterate
		$str = preg_replace('#[^a-z0-9\s-]#i', '', $str);

		return dasherize($str,'-');
	}
}

if(!function_exists('spinalize')){
	/**
	 * Same as slugify but without sanitize specials characters
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	function spinalize($str){
		return dasherize($str,'-');
	}
}

if(!function_exists('trainify')){
	/**
	 * Alias of spinalize
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	function trainify($str){
		return dasherize($str,'-');
	}
}

if(!function_exists('snakify')){
	/**
	 * Format a string to a snake case
	 *
	 * @param string $str
	 *
	 * @return $str
	 */
	function snakify($str){
		return dasherize($str,'_');
	}
}

if(!function_exists('normalize')){
	/**
	 * Format a string from [camelcase|snakecase|slugcase] to a normal case
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	function normalize($str){
		
		if(preg_match('#\w+[_-]\w+#',$str)){
			$normal = preg_replace('#[_-]#',' ',$str);
		}else
		{
			$chars = str_split($str);
			$normal = '';
			foreach($chars as $char){
				if($char === strtoupper($char))
					$normal .= ' '.strtolower($char);
				else $normal .= $char;
			}
		}
		$normal = ucfirst(trim($normal));
		return $normal;
	}
}