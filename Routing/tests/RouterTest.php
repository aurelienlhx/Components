<?php

use A1\Routing\Route;
use A1\Routing\Router;

class RouterTest extends PHPUnit_Framework_TestCase{

		private $routes = [
			'home' => [
				'patterns' => 'welcome/to/home',
				'attributes' => [
					'controller' => 'HomeController',
				],
			],
			'post' => [
				'patterns' => 'post/{id:\d+}',
				'attributes' => [
					'controller' => 'PostController',
					'function' => 'read',
					'parent' => 'home',
					'method' => 'GET'
				]
			],
			'login' =>[
				'patterns' => 'auth/login',
				'attributes' => [
					'controller' => 'AuthController',
					'function' => 'login'
				]
 			],
 			'private_route' =>[
				'patterns' => 'path/to/private/route',
				'attributes' => [
					'controller' => 'LoginController',
					'private' => true
				],
				'params' => [
					'foo' => 'bar'
				]
 			],
 			'api_route' => [
 				'patterns' => 'route/to/api/path',
 				'attributes' => [
 					'controller' => 'SomeApiController',
 					'domain' => 'api.mydomain.com',
 					'method' => 'GET'
 				]
 			]	
		];

		private $router;

		/**
		 * 
		 */
		public function testInitRouter(){
			$router = new Router();
			$router->register($this->routes);
			return $router;
		}


		/**
		 * @depends testInitRouter
		 */
		public function testBasicRouter($router){

			$route1 = new Route([
				'key' => 'home',
				'patterns' => 'welcome/to/home',
				'attributes' => [
					'controller' => 'HomeController',
				]
			]);
			$route2 = $router->routes('home');
			$this->assertEquals($route1, $route2);
		}

		/**
		 * @depends testInitRouter
		 */
		public function testFindRoute($router){
			$route1  = $router->routes('home');
			$finded1 = $router->find('welcome/to/home');
			
			$this->assertEquals($route1,$finded1);

			$route2 = $router->routes('post');
			$finded2 = $router->find('post/1',['method'=>'GET'], $params);

			$this->assertEquals($route2,$finded2);
			$this->assertEquals($params,['id'=>'1']);
		}

		/**
		 * @depends testInitRouter
		 */
		public function testStaticParams($router){
			$finded = $router->find('path/to/private/route',null,$params);
			$this->assertEquals($params,['foo'=>'bar']);
		}

}
