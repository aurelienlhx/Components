<?php

namespace A1\Collection;

use Iterator;
use Countable;
use ArrayAccess;
use InvalidArgumentException;

class Collection implements ArrayAccess, Iterator, Countable{

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
	 *
	 * @return void
	 */
	public function __construct(array $collection = array()){
		$this->set($collection);
	}

	/**
	 * Check if a key exists
	 * @interface ArrayAccess
	 *
	 * @param $offset
	 *
	 * @return bool
	 */
	public function offsetExists($offset){
		return $this->has($offset);
	}

	
	/**
	 * Retrieve a value with a key
	 * @interface ArrayAccess
	 *
	 * @param mixed $offset
	 *
	 * @return mixed
	 */
	public function offsetGet($offset) {
		return $this->get($offset);
    }

	/**
	 * Set a value with a key
	 * @interface ArrayAccess
	 *
	 * @param mixed $offset
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value){
		$this->set($offset, $value);
	}

	/**
	 * Delete a key and his value
	 * @interface ArrayAccess
	 *
	 * @param mixed $offset
	 *
	 * @return void
	 */
	public function offsetUnset($offset){
		$this->delete($offset);
	}

	/**
	 * Get the first element of the collection
	 *
	 * @param bool $key Return key insteadof value
	 *
	 * @return mixed
	 */
	public function first( $key = false){
		$collection = $this->collection;

		if($key){
			reset($collection);
			return key($collection);
		}
		else
			return reset($collection);
	}

	/**
	 * Get the last element of the collection
	 *
	 * @param bool $key Return key insteadof value
	 *
	 * @return mixed
	 */
	public function last( $key = false ){
		$collection = $this->collection;

		if($key){
			end($collection);
			return key($collection);
		}
		else
			return end($collection);
	}

	/**
	 * Get the previous element of the collection
	 *
	 * @return mixed
	 */
	public function previous(){
		return prev($this->collection);
	}

	/**
	 * Get the current element of the collection
	 * @interface Iterator
	 *
	 * @return mixed
	 */
	public function current(){
		return current($this->collection);
	}

	/**
	 * Get the next element of the collection
	 * @interface Iterator
	 *
	 * @return mixed
	 */
	public function next(){
		return next($this->collection);
	}

	/**
	 * Get the key of the current element of the collection
	 * @interface Iterator
	 *
	 * @return mixed
	 */
	public function key(){
		return key($this->collection);
	}

	/**
	 * Rewind to the first element of the collection
	 * @interface Iterator
	 *
	 * @return mixed
	 */
	public function rewind(){
		reset($this->collection);
	}

	/**
	 * Check if current key is valid
	 * @interface Iterator
	 *
	 * @return bool
	 */
	public function valid(){
		return key($this->collection) !== null;
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
	 * If one argument
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
				$value = array($value);
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
	 * Alias of self::get()
	 */
	public function all(){
		return $this->get();
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
			throw new InvalidArgumentException('At least 2 arguments (key and fallback) expected');

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
	 *
	 * @return bool
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
	 * @interface Countable
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
			throw new InvalidArgumentException(sprintf('Impossible to count, at least one key is undefined'));
		
		return count(call_user_func_array(array($this,'get'), $args));
	}

	/**
	 * Extend the current collection
	 * 
	 * @param mixed array|object|Collection $collection 
	 * @param bool $recursive
	 *
	 * @return void
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
	 * Throne a value to the beginning of the collection
	 * Preserve numeric keys in contrary to array_unshift
	 *
	 * @param mixed $value
	 * @param string $key
	 *
	 * @return void
	 */
	public function throne($value, $key = null){

		if($this->frozen)
			return;
	
		$reverse = array_reverse($this->collection, true);
		if(null !== $key)
			$reverse[$key] = $value;
		else{
			$reverse[] = $value;
		}
		$this->collection = array_reverse($reverse, true);
		
	}

	/**
	 * Push a value at the end of the collection
	 *
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function push($value){

		if($this->frozen)
			return;

		$this->collection[] = $value;
	}

	/**
	 * Get the value of a key and remove it
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function pull($key){

		if($this->frozen)
			return;

		if(!isset($this->collection[$key]))
			return;
		
		$value = $this->collection[$key];
		unset($this->collection[$key]);
		return $value;
	}

	/**
     * Get and remove the first item from the collection.
     * Preserve numeric keys in contrary to array_shift
     *
     * @param bool $getkey Retrieve the key insteadof the value
     *
     * @return mixed
     */
    public function shift($getkey = false)
    {
    	if($this->frozen)
			return;

        $collection = $this->collection;
        $value = reset($collection);
        $key = key($collection);
        unset($this->collection[$key]);
       
        if($getkey)
        	return $key;
        else
        	return $value;
  
    }

	/**
	 * Get the last value of the collection and remove it
	 * Preserve numeric keys in contrary to array_pop
	 *
	 * @param bool $getkey Retrieve the key insteadof the value
	 *
	 * @return mixed
	 */
	public function pop($getkey = false){

		if($this->frozen)
			return;
		
		$collection = $this->collection;
        $value = end($collection);
        $key = key($collection);
        unset($this->collection[$key]);
        
        if($getkey)
        	return $key;
        else
        	return $value;
	}

	/**
	 * Rewrite all numerical index from 0 and keep literal key
	 *
	 * @return void
	 */
	public function airy(){
		if($this->frozen)
			return;

		$this->collection = array_merge([],$this->collection);
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
	 * @return void
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
	}

	/**
	 * Execute a function throught the collection items
	 *
	 * @param $callback Expect 2 arguments by reference ($key,$value)
	 *
	 * @return bool
	 */
	public function walk(callable $callback, $recursive = false){
		
		if($this->frozen)
			return;
		
		if($recursive)
			return array_walk_recursive($this->collection, $callback);
		else
			return array_walk($this->collection,$callback);
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
				$value = $this->__stringify($value);
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

	/**
	 * Serialiaze the collection
	 *
	 * @return string
	 */
	public function serialize(){
		return serialize($this->collection);
	}

}