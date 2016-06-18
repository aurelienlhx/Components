<?php

use A1\Routing\Route;
use A1\Routing\Router;

class RouterTest extends PHPUnit_Framework_TestCase{

		private $routes = [
			[
				'name' => 'home',
				'patterns' => 'welcome/to/home',
				'attributes' => [
					'controller' => 'HomeController',
				],
			],
			[
				'name' => 'post',
				'patterns' => 'post/{id:\d+}',
				'attributes' => [
					'controller' => 'PostController',
					'function' => 'read',
					'parent' => 'home',
				]
			],
			[
				'name' => 'login',
				'patterns' => 'auth/login',
				'attributes' => [
					'controller' => 'AuthController',
					'function' => 'login'
				]
 			],
 			[	
 				'name' => 'private_route',
				'patterns' => 'path/to/private/route',
				'attributes' => [
					'controller' => 'LoginController',
					'private' => true
				],
				'values' => [
					'foo' => 'bar'
				]
 			],
 			[	
 				'name' => 'api_route',
 				'patterns' => 'route/to/api/path',
 				'attributes' => [
 					'controller' => 'SomeApiController',
 					'domain' => 'api.mydomain.com',
 				]
 			]	
		];

		private $router;

		/**
		 * 
		 */
		public function testInitRouter(){
			$router = new Router();
			$router->bulkRegister($this->routes);
			return $router;
		}


		/**
		 * @depends testInitRouter
		 */
		public function testBasicRouter($router){

			$route1 = Route::createFromArray([
				'name' => 'home',
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
			$route2->conditions(function($route){
				return 1>0;
			});
			$finded2 = $router->find('post/1', $params);

			$this->assertEquals($route2,$finded2);
			$this->assertEquals($params,['id'=>'1']);
		}

		/**
		 * @depends testInitRouter
		 */
		public function testStaticParams($router){
			
			$finded = $router->find('path/to/private/route',$values);
			$this->assertEquals(['foo'=>'bar'],$values);
		}

		/**
		 * @depends testInitRouter
		 */
		public function testRouterParams($router){
			$router->params([
				'id'=>'\d+'
			]);
			$route = $router->add('path/to/a/ressource/with/{id}');
			$router->find('path/to/a/ressource/with/666',$values);

			$this->assertEquals($values,['id'=>666]);
			$route->params(['id'=>'[A-Z]+']);
			$find = $router->find('path/to/a/ressource/with/666',$values);
			$this->assertEquals(null,$find);
		}

}
