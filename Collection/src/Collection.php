<?php

class Collection{

	/**
	 * @var array
	 */
	protected $collection = array();

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

}