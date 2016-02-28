<?php

namespace A1;

class Autoloader {

	/**
	 * Registered classes
	 * 
	 * 	@var array
	 */
	private $classes = array();
	
	/**
	 * Registered aliases
	 *
	 * @var array
	 */
	private $aliases = array();

	/**
	 * 	Allowed extensions for autoload
	 * 
	 * @var array
	 */
	private $extensions = array('php');

	/**
	 * Constructor
	 */
	public function __construct(){}

	/**
	 * Return allowed extensions
	 */
	public function extensions(){
		return $this->extensions;
	}

	/**
	 * Allow extensions	
	 */
	public function allow($ext /*, $ext2,... */){
		$extensions = func_get_args();
		$this->extensions = array_unique(array_merge($this->extensions,$extensions));
	}

	/**
	 * 	Disalow extensions
	 */
	public function disallow($ext /*, $ext2,... */){
		$extensions = func_get_args();
		//array_values reset index
		$this->extensions = array_values(array_diff($this->extensions,$extensions));
	}

	/**
	 * Return loaded classes
	 * 
	 * @return array
	 */
	public function classes(){
		return $this->classes;
	}


	/**
	 * Add dir path or file path to the autoloader pile
	 *
	 * @param mixed string|array $path Include this path(s) with optionnaly namespace as key
	 *
	 * @param mixed string|array $path Exclude this path(s) with optionnaly namespace as key
	 *
	 */
	public function register($paths, $notpath = null){

		if(!is_array($paths))
			$paths = array($paths);

		foreach($paths as $namespace => $path){			
			if(!file_exists($path))
				throw new \InvalidArgumentException(sprintf('File or dir "%s" do not exists', (string) $path ));
	
			$this->classes = array_merge($this->classes, $this->scan_class($path,$namespace));
		}
		
		if(empty($notpath))
			return;

		$notclasses = [];
		if(!is_array($notpath))
			$notpath = array($notpath);

		foreach($notpath as $namespace => $np){
			
			if(!file_exists($np))
				throw new \InvalidArgumentException(sprintf('File or dir "%s" do not exists', (string) $np ));
			
			$np = realpath($np);
			if(is_dir($np)){
				$np .= DIRECTORY_SEPARATOR;
			}
			$notclasses = array_merge($notclasses, $this->scan_class($np,$namespace));
		}
		$this->classes = array_diff($this->classes,$notclasses);

	}

	/**
	 * Delete a path from the autoloader pile
	 */
	public function unregister($path){
		if(!empty($key = array_search($path,$this->classes)))
			unset($this->classes[$key]);
	}

	/**
	 * Include class file if exists
	 *
	 * @param string $class Class to load
	 */
	private function autoload($class) {

		if(isset($this->classes[$class])){
	    	return require_once $this->classes[$class];
		}

		else{
			$alias = $class;
			$class = $this->alias_class('get',$alias);
		}

	    if(!empty($class) 
	    	&& class_exists($class)
	    	&& !empty($alias) 
	    	&& !class_exists($alias)) 
	    	class_alias($class,$alias);
	}

	/**
	 * Scan all classes from a dir path or file path and check extensions
	 *
	 * @param string $path
	 *
	 * @return array
	 */
	private function scan_class($path,$namespace = null){
		$classes = array();
		//check realpath for vfsStream mockup compatibility
		$path = (file_exists($path) && ($realpath = realpath($path) !== false)) ? $realpath : $path;

		if(is_file($path) 
			&& in_array(pathinfo($path,PATHINFO_EXTENSION),$this->extensions)){

			$key = (empty($namespace)? '' : $namespace.'\\').pathinfo($path,PATHINFO_FILENAME);
			$classes[$key] = $path;
			
		}
		else if(is_dir($path)){
			foreach (scandir($path) as $k => $file)
			{	
				if(	   $file != "." 
					&& $file != ".." 
				){
					//ensure that is ending by a a directory separator
					$path = rtrim($path,'\\/').'/'; 
					$classes = array_merge($classes,$this->scan_class($path.$file,$namespace));
				}
			}
		}
		return $classes;
	}

	/**
	 * Add an alias to a class
	 *
	 * @param string $method get|set 
	 *
	 * @param string $alias 
	 *
	 * @param string $class
	 *
	 * @return mixed string|bool
	 */
	public function alias_class($method, $alias = null, $class = null){
		
		if($method=='set')
			$this->aliases[$alias] = $class;
		else if($method=='get'){
			if(is_null($alias))
				return $this->aliases;
			else if(isset($this->aliases[$alias]))
			 	return $this->aliases[$alias];
			else 
				return false;
		} 
	}

}



