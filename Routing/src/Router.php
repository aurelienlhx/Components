<?php

namespace A1\Routing;

use ReflectionClass;
use InvalidArgumentException;

class Router{

	/**
	 * Array of recorded routes
	 *
	 * @var array
	 */
	protected $routes = array(); 
	
	/**
	 *  Route matched by the router
	 * 
	 *  @var Route
	 */
	protected $route; 

	/**
	 * Route class used by the router
	 *
	 * @var string
	 */
	protected $classRoute = Route::class;

	/**
	 * Array of params patterns
	 *
	 * @var array
	 */
	protected $params = [];
	

	/**
	 * Register a route
	 *
	 * @param mixed RouteInterface|array $route
	 */
	public function register($route){

		if(!is_array($route) && !($route instanceof RouteInterface))
			throw new InvalidArgumentException('Array or RouteInterface expected');

		if(is_array($route))
			$route = call_user_func($this->classRoute.'::createFromArray', $route);

		if($route->getName())
			$this->routes[$route->getName()] = $route;
		else
			$this->routes[] = $route;

		$route->params(array_merge($this->params,$route->getParams()));
	}

	/**
	 * Mass assignment of routes registration
	 *
	 * @param array $route An array of routes representation
	 */
	public function bulkRegister(array $routes){
		
		foreach($routes as $route){
			$this->register($route);
		}
	}

	/**
	 * Shortcut to create and register a route
	 *
	 * @param string $name
	 * @param mixed $patterns
	 *
	 * @return Route
	 */
	public function add( $patterns ){
		$route = $this->createRoute( $patterns);
		$this->register($route);
		return $route;
	}

	/**
	 * Create dynamically a route instance
	 *
	 * @param string $name
	 * @param mixed $patterns
	 *
	 * @return Route
	 */
	protected function createRoute( $patterns ){
		$args = func_get_args();
		$ref = new ReflectionClass($this->classRoute);
		$route = $ref->newInstanceArgs($args);
		return $route;
	}

	/**
	 * Return all registered routes
	 *
	 * @param string $name
	 *
	 * @return mixed null|array
	 */
	public function routes($name = null){
		if(null === $name)
			return $this->routes;
		if(isset($this->routes[$name]))
			return $this->routes[$name];
	}


	/**
	 * Find route according to path and eventualy inner attributes
	 *
	 * @param string $path Path to test
	 * @param array $params Reference for get back matched parameters
	 *
	 * @return mixed:array|false
	 */
	public function find( $path, &$values = [] ){

		$catched_route = null;

		foreach($this->routes as $route){
			if($route->match($path, $values)){
				$catched_route = $route;
				break;
			}
		}
		return $this->route = $catched_route;
	}

	/**
	 * Set an array of params regex
	 * @param array $params
	 *
	 */
	public function params( array $params ){
		$this->params = $params;
	}

	/**
	 * Return the current route matched
	 */
	public function current(){
		return $this->route;
	}

	
	/**
	 * @todo group
	 */
	//public function group($params, \Closure $callback){
		//namespace 
		//prefix (prefix for route name)
		//conditions (conditions to apply to a group)
		//attributes 
	//}


}