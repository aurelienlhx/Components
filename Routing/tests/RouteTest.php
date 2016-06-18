<?php 

use A1\Routing\Route;

class RouteTest extends PHPUnit_Framework_TestCase{


    /**
     * 
     */
    public function testBasicRoute(){
    	$route = (new Route('path/to/home'))->name('home'); 

        $this->assertEquals($route->getName(),'home');

        $route = Route::createFromArray([
            'name' => 'home',
            'patterns' => 'path/to/home',
            'attributes' => [
                'controller' => 'HomeController'
            ],
            'params' => [],
            'conditions' => []
        ]);
    }

    /**
     * 
     */
    public function testFromArray(){
        $route = Route::createFromArray([
            'name'=>'home',
            'patterns'=>'path/to/home',
            'attributes' => [
                'controller' => 'HomeController'
            ],
            'params' => ['id'=>'\d+'],
            'conditions' => []
        ]);
    }
}