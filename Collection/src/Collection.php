<?php

namespace A1;

use \ArrayAccess;
use DomainException;
use InvalidArgumentException;

class Collection implements ArrayAccess{

	/**
	 * Collection to handle
	 * 
	 * @var array
	 */
	protected $collection =  [];

	/**
	 * @var bool
	 */
	protected $frozen = false;
	
	/**
	 * Collection constructor
	 *
	 * @param mixed array|object $collection
	 */
	public function __construct(array $collection = array()){
		$this->set($collection);
	}

	/**
	 * Implementation of ArrayAccess interface
	 */
	public function offsetExists($offset){
		return $this->has($offset);
	}

	
	/**
	 * 	Implementation of ArrayAccess interface
	 */
	public function offsetGet($offset) {
		return $this->get($offset);
    }

	/**
	 * 	Implementation of ArrayAccess interface
	 */
	public function offsetSet($offset, $value){
		$this->set($offset, $value);
	}

	/**
	 * 	Implementation of ArrayAccess interface
	 */
	public function offsetUnset($offset){
		$this->delete($offset);
	}

	/**
	 * Freeze the collection to prevent modification
	 */
	public function freeze(){
		$this->frozen = true;
	}

	/**
	 * Unfreeze the collection 
	 */
	public function unfreeze(){
		$this->frozen = false;
	}

	/**
	 * Recursively set a value in the collection
	 * If a key do not exists, it is automaticaly created
	 * Thanks to http://thereisamoduleforthat.com/content/dealing-deep-arrays-php
	 *
	 * @param string $key[n] Keys path
	 * @param mixed $value
	 *
	 */
	public function set(/* $key1, $key2, ..., $value */){

		if($this->frozen)
			return;

		$args = func_get_args();
		$offset =& $this->collection;
		$args = array_reverse($args);
		$nargs = count($args);

		if(0 === $nargs)
			throw new InvalidArgumentException('At least one parameter is required');

		$value = array_shift($args);
		$path = array_reverse($args);
		$n = count($path);
		$i=1;

		if(1 === $nargs){
			if(!is_array($value) && !is_object($value))
				throw new InvalidArgumentException('Single parameter must be an array as it replaces the collection fully');
			$this->collection = (array) $value;
		}
		else
		  	foreach($path as $key){
		  		
		  		if($i < $n){
		  			if(!isset($offset[$key]))
			  			$offset[$key] = array();
			   		$offset =& $offset[$key];
		  		}elseif($i===$n){
		  			
		  			if(!is_array($offset))
		  				$offset = array();
			  		if(empty($key))
			  			$offset[] = $value;
			  		else
			  			$offset[$key] = $value;	
		  		}
		 	 	$i++;
		 	}
	}

	/**
	 * Return a value
	 *
	 * @param string $key[n] Keys path
	 * if empty params return all the collection
	 *
	 * @return mixed
	 */
	public function get(/* $key1, $key2, ...*/){
		
		$path = func_get_args();
		$offset = $this->collection;

		foreach ($path as $key) {
			if(!empty($key) && (!is_array($offset) || !array_key_exists($key,$offset)))
				return;
            elseif(!empty($key))
            	$offset = $offset[$key];
        }
        return $offset;
	}

	/**
	 * Get recursively a value or the given fallback value 
	 *
	 * @param string $key[n] Keys path
	 * @param mixed fallback The fallback value returned if key not found
	 *
	 * @return mixed
	 */
	public function pick(/*$key1 , $key2, $key3, ... , $fallback*/){
		$path = func_get_args();
		
		if(count($path)<2)
			throw new InvalidArgumentException('At least 2 arguments (key and fallaback) expected');

		$fallback = array_pop($path);
		$value = call_user_func_array(array($this,'get'), $path);
		if(null === $value)
			return $fallback;
		else
			return $value;
	}


	/**
	 * Delete a value 
	 *
	 * @param string $key[n] Keys path
	 * if empty params, reset the collection with empty array
	 */
	public function delete(/* $key1, $key2, ...*/){

		if($this->frozen)
			return;
		
		if(0 === func_num_args())
			return $this->collection = [];

		$path = func_get_args();
		$offset =& $this->collection;
		$n = count($path);
		$i=1;
		
	  	foreach($path as $key){
		   	
	  		if($i===$n){
	  			unset($offset[$key]);	
	  			break;
	  		}
	  		$offset =& $offset[$key];

	 	 $i++;
	 	}
	}

	/**
	 * Check if the collection has a key and optionnaly a value // recusrive has
	 *
	 * @param string $key[n] Keys path
	 */
	public function has(/* $key1, $key2, ... */){
		
		$offset = $this->collection;
		$path = func_get_args();

		if(0 === count($path))
			throw new InvalidArgumentException('At least one argument is required');

		foreach ($path as $key) {
			if(!empty($key) && (!is_array($offset) || !array_key_exists($key,$offset)))
				return false;
            elseif(!empty($key))
            	$offset = $offset[$key];
        }
        return true;
	}


	/**
	 * Count the collection or a key level
	 *
	 * @param string $key[n] Keys path
	 *
	 * @return int
	 */
	public function count(/* $key1, $key2, ... */){
		$args = func_get_args();
		$nargs = count($args);

		if(0 === $nargs)
			return count($this->collection);
		
		if(!call_user_func_array(array($this,'has'), $args))
			throw new DomainException(sprintf('Impossible count, at least one key is undefined'));
		
		return count(call_user_func_array(array($this,'get'), $args));
	}

	/**
	 * Extend the current collection
	 * 
	 * @param mixed array|object|Collection $collection 
	 * @param bool $recursive
	 *
	 */
	public function add( $collection , $recursive = false){

		if($this->frozen)
			return;
		
		if($collection instanceof Collection)
			$collection = $collection->get();
		else if( is_object($collection) )
			$collection = (array) $collection;

		if(!is_array($collection))
			throw new InvalidArgumentException('$collection parameter must be a Collection instance, an object or an array');

		if($recursive)
			$this->collection = array_replace_recursive($this->collection, $collection);
		else
			$this->collection = array_replace($this->collection, $collection);
	}

	/**
	 * Return the depth of the collection
	 *
	 * @return int
	 */
	public function depth(){
		return $this->__depth($this->collection);
	}

	/**
	 * Internal depth
	 *
	 * @param $array
	 *
	 * @return int;
	 */
	 function __depth(array $array){
	    $max_depth = 0;

	    foreach ($array as $value){
			if (is_array($value)){
				$depth = $this->__depth($value) + 1;

			if ($depth > $max_depth)
				$max_depth = $depth;
			}
	   }
	   return $max_depth;
	}

	/**
	 * Return the array source
	 *
	 * @return array
	 */
	public function arrayfy(){
		return $this->collection;
	}

	/**
	 * Flatten all the collection on one level
	 *
	 * @return string
	 */
	public function flatten($separator = '.'){
		$flatten = $this->__flatten($this->collection,$separator);
		$this->collection = $flatten;
		return $this;
	}

	/**
	 * Internal flatten
	 *
	 * @param array $array
	 * @param string $separator
	 * @param string $base
	 *
	 * @return array
	 */
	public function __flatten(array $array,$separator = '.',$base = ''){
		$flattened = [];
		
		foreach($array as $key => $value){

			if(!empty($base))
				$key = $base.$separator.$key;

			if(is_array($value)){
				$tmp = $this->__flatten($value,$separator,$key);
				$flattened = $flattened + $tmp; //keep numerics keys whereas merge not
			}
			else{
				$flattened[$key] = $value;
			}
		}

		return $flattened;
	}

	/**
	 * Inflate an array
	 *
	 * @param array $array
	 * @param string $separator
	 *
	 * @return array
	 */
	public function inflate($separator = '.'){
		
	    $inflate = array();
	    foreach ($this->collection as $key => $val) 
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

	    $this->collection = $inflate;
	    return $this;
	}

	/**
	 * Stringify the array
	 *
	 * @return string
	 */
	public function stringify(){
		return json_encode($this->__stringify($this->collection));
	}

	/**
	 * Internal stringify
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	private function __stringify(array $array){

		foreach($array as $key => &$value){
			
			if(is_object($value)){
				$value = get_object_vars($value);
			}
			else if(is_array($value))
				$value = $this->__flatten($value);
		}
		return $array;
	}

	
	/**
	 * Make a collection echoable
	 *
	 * @return string
	 */
	public function __toString(){
		return $this->stringify();
	}

}