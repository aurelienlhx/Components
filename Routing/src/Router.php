<?php

namespace A1\Routing;

class Router{

	/**
	 * Array of recorded routes
	 *
	 * @var array
	 */
	private $routes = array(); 
	
	/**
	 *  Route matched by the router
	 * 
	 *  @var Route
	 */
	private $route; 
	

	/**
	 * Register a route
	 *
	 * @param mixed Route|array A single Route instance or an array of Route instances
	 */
	public function register($routes){

		if(!is_array($routes))
			$routes = array($routes);

		foreach($routes as $key => $route){
			
			//it is a route array representation
			if(is_array($route)){ 
				$route['key'] = $key;
				$route = new Route($route);
			}
			//it is a route object
			if(!$route instanceof Route) 
				throw new \InvalidArgumentException('$routes must an array of Route instances or a single Route instance');
			
			$this->routes[$route->key()] = $route; 
		}
	}

	/**
	 * Return all registered routes
	 */
	public function routes($key = null){
		if(null === $key)
			return $this->routes;
		if(!isset($this->routes[$key]))
			return;
		return $this->routes[$key];
	}


	/**
	 * Find route according to path and eventualy inner attributes
	 *
	 * @param string $path Path to test
	 * @param array $attributes Attributes to test in addition to the path
	 * @param array $params Reference for get back matched parameters
	 *
	 * @return mixed:array|false
	 */
	public function find( $path, array $attributes = null, &$params = null ){

		$catched_route = null;

		foreach($this->routes as $route){
			if($route->match($path,$attributes, $params)){
				$catched_route = $route;
				break;
			}
		}

		return $catched_route;
	}	



	

}