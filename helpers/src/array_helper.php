<?php

//namespace A1\Helpers\ArrayHelpers;

if(!function_exists('array_copy')){
    /**
     * Copy an array recursively and all his values (clone objects)
     * 
     * @param array $array Array to copy
     *
     * @return array Copy of initial array
     */
    function array_copy( array $array ) {
        $copy = array();
        foreach( $array as $key => $val ) {
            if(is_array($val)) 
                $copy[$key] = array_copy($val);
            elseif (is_object( $val )) 
                $copy[$key] = clone $val;
            else 
                $copy[$key] = $val;
        }
        return $copy;
    }
}

if(!function_exists('array_numerize')){
    /**
     * Transform associative array as numeric array and keep order of keys values pairs
     *
     * @param array $arr Associative array to handle
     *
     * @return array Numeric array
     */
    function array_numerize(array $array)
    {		
    	$i = 0;	
    	$numerized = array();
    	foreach($array as $key => $value)
    	{
    		$numerized[$i] = $value;
    		$i++;
    	}
    	return $numerized;
    }
}
	
if(!function_exists('array_empty')){
    /**
     * Test recursively if an array is empty
     *
     * @param array $array
     *
     * @return bool 
     */
    function array_empty(array $array) 
    {
    	$bool = true;
        foreach ($array as $key => $value) {
    		if (is_array($value))
    		    $bool = array_empty($value);
    		else if(!empty($value))
                $bool = false;
    	}
    	return $bool;
    }
}

if(!function_exists('array_unshift_assoc')){
    /**
     * Add item in first position with a specific key
     *
     * @param array $array	array to handle
     * @param array $key	item key to unshift
     * @param array $value	item value to unshift
     *
     * @return array
     */
    function array_unshift_assoc(array $array, $key, $value) 
    { 
    	$array2 = array($key => $value);
        foreach($array as $key => $value){
            $array2[$key] = $value;
        }
    	return $array2; 
    } 
}

if(!function_exists('array_unset_numerics_keys')){
    /**
     * Remove item from array if key is numerical
     *
     * @param array $array The array to handle
     *
     * @return array The array cleaned
     */
    function array_unset_numerics_keys(array $array){
        
        foreach($array as $key => $value){
        	if(is_numeric($key))
        		unset($array[$key]);
        }
        return $array;
    }
}


if(!function_exists('array_join_assoc')){
    /**
     * Join an associative array with key and values
     * 
     * @param array $array
     * @param string $glue1 Glue between a key and his value
     * @param string $glue2 Glue between a pair of key and value
     *
     * @return string;
     */
    function array_join_assoc(array $array,$glue1,$glue2){
    	$joined = "";
        $i = 0; $n = count($array);
    	foreach($array as $key => $value){
    		$joined .= $key.$glue1.$value;
            if($i < $n-1) $joined .= $glue2;
    	$i++;}
    	return $joined;
    }
}

if(!function_exists('array_is_assoc')){
    /**
     * Test if an array is associative
     *
     * @param array $array
     *
     * @return bool
     */
    function array_is_assoc(array $array){
    	return array_keys($array) !== range(0, count($array) - 1);
    }
}

if(!function_exists('array_depth')){
    /**
     * Calculate the depth index of an array
     *
     * @param $array
     *
     * @return int;
     */
    function array_depth(array $array){
        $max_depth = 0;

        foreach ($array as $value){
            if (is_array($value)){
                $depth = array_depth($value) + 1;

                if ($depth > $max_depth)
                    $max_depth = $depth;
            }
        }
        return $max_depth;
    }
}

if(!function_exists('array_filter_keys')){
    /**
     * Filter an array by keys
     *
     * @param $array Array to filter
     * @param $keys An array of key to remove in $array
     * 
     * @return array
     */
    function array_filter_keys(array $array, array $keys){
        foreach($array as $key => $value){
            if(!in_array($key,$keys))
                unset($array[$key]);
        }
        return $array;
    }
}

if(!function_exists('array_join_array')){
    /**
     * Join two array with value of the first one as key and value of the second as value
     *
     * @param $array1
     *
     */
    function array_join_array(array $keys,array $values,$glue1,$glue2){
        $joined = '';
        $i=0;$n = count($keys);
        foreach($keys as $key){
            $joined .= current($keys).$glue1.current($values);
            if($i < $n-1) $joined.= $glue2;
            next($keys);next($values);
        $i++;}
        return $joined;
    }
}
if(!function_exists('array_flatten')){
    /**
     * Internal flatten
     *
     * @param array $array
     * @param string $separator
     * @param string $base
     *
     * @return array
     */
    function array_flatten(array $array,$separator = '.',$base = ''){
        $flattened = [];
        
        foreach($array as $key => $value){

            if(!empty($base))
                $key = $base.$separator.$key;

            if(is_array($value)){
                $tmp = array_flatten($value,$separator,$key);
                $flattened = $flattened + $tmp; //keep numerics keys whereas merge not
            }
            else{
                $flattened[$key] = $value;
            }
        }

        return $flattened;
    }
}

if(!function_exists('array_inflate')){
    /**
     * Inflate an array
     *
     * @param array $array
     * @param string $separator
     *
     * @return array
     */
    function array_inflate(array $array,$separator = '.'){
        
        $inflate = array();
        foreach ($array as $key => $val) 
        {
            $parts = explode($separator,$key);
            $leafpart = array_pop($parts);
            $parent = &$inflate;
            foreach ($parts as $part) 
            {
                if (!isset($parent[$part]))
                    $parent[$part] = array();
                else if (!is_array($parent[$part]))
                    $parent[$part] = array();
                        
                $parent = &$parent[$part];
            }
        
            if (empty($parent[$leafpart]))
                $parent[$leafpart] = $val;
        }
        return $inflate;
    }
}


if(!function_exists('array_of_array')){
    /**
     * Check if an array contain only array
     *
     * @param $array1
     *
     */
    function array_of_array(array $array){
        foreach($array as $arr)
            if(!is_array($arr))
                return false;
        return true;
    }
}
