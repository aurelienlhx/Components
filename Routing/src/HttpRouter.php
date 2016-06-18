<?php

namespace A1\Routing;

class HttpRouter extends Router {

	/**
	 * @var A1\Routing\RequestInterface
	 */
	private $request;

	/**
	 * Create instance of HttpRouter
	 *
	 * @param A1\Routing\RequestInterface $request
	 */
	public function __construct(RequestInterface $request){
		$this->request = $request;
	}

	/**
	 * Methods allowed for http routes
	 *
	 * @var array
	 */
	protected $methods = ['get','post','put','patch','delete'];

	
	/**
	 * Create dynamically a route according to the method
	 *
	 * @param string $method
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function __call( $method, $args ){
		if(in_array($method,$this->methods))
			return $this->createRoute(
				$patterns = $args[1], 
				$method, 
				$controller = isset($args[2])? $args[2] : function(){}
			);
	}


	/**
	 * Create a route
	 *
	 * @param string $name
	 * @param mixed $patterns
	 * @param string $method
	 *
	 * @return A1\Routing\Route
	 */
	public function createRoute( $patterns, $method = 'get', Callable $controller = null){
		$route = parent::createRoute($patterns);
		$route->attributes([
			'controller' => $controller
		]);
		$route->conditions( $this->buildCondition($method) );
		$this->register($route);
		return $route;
	}

	/**
	 * Build a condition
	 *
	 * @param string $method
	 *
	 * @return Closure
	 */
	private function buildCondition($method){
		$method = strtoupper($method);
		return function() use ($method){
			// Create a condition checking the http method of the request and a 
			// _method param fallback too in case of http method can not be change by 
			// the client as for exemple <form> tag in HTML that can only use GET and POST
 			return $this->request->getMethod() === $method || $this->request->getData('_method') === $method;
		};
	}

}