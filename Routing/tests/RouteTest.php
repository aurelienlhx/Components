<?php 

use A1\Routing\Route;

class RouteTest extends PHPUnit_Framework_TestCase{


    /**
     * 
     */
    public function testBasicRoute(){
    	$route = new Route('home','path/to/home'); 

        $this->assertEquals($route->key()		,'home');
        $this->assertEquals($route->patterns()	,'path/to/home');
        $this->assertEquals($route->params()	,[]);
        $this->assertEquals($route->attributes(), []);

        $route = [
            'key' => 'home',
            'patterns' => 'path/to/home',
            'attributes' => [
                'controller' => 'HomeController'
            ],
            'params' => []
        ];
        new Route($route);

        $this->setExpectedException(\DomainException::class);
        $route['foo'] = 'bar';
        new Route($route);
    }

    /**
     * 
     */
    public function testMediumRoute(){
    	$route = new Route('product','path/to/product/{id:\d+}',['foo'=>'bar']);

    	$this->assertEquals($route->key()			,'product');
        $this->assertEquals($route->patterns()		,'path/to/product/{id:\d+}');
        $this->assertEquals($route->params()		,['foo'=>'bar']);
        $this->assertEquals($route->params('foo')	,'bar');
        $this->assertEquals($route->attributes()	,[]);
    }

    /**
     * 
     */
    public function testMaxiRoute(){
    	$route = new Route('product','{locale}/path/to/product/{id:\d+}',[
    		'foo'=>'bar',
    		'locale'=>'[\w-]+'
    	]);
    	$route->attributes('controller','RessourceController');
    	$route->attributes('action','index');
    	$route->attributes('method','POST');

    	$this->assertEquals($route->key()			,'product');
        $this->assertEquals($route->patterns()		,'{locale}/path/to/product/{id:\d+}');
        $this->assertEquals($route->params()		,[
        	'foo'=>'bar',
        	'locale'=>'[\w-]+'
        ]);
        $this->assertEquals($route->params('foo')	,'bar');
        $this->assertEquals($route->attributes()	,[
        	'controller'=>'RessourceController',
        	'action' => 'index',
        	'method' => 'POST'
        ]);
    }


    /**
     * 
     */
    public function testPatternsRoute(){
    	$route = new Route('page',[
    		'{locale}/chemin/de/la/page',
    		'{locale}/path/to/page'
    	],
    	[
    		'locale' => '[\w-]+'
    	]); 

    	$this->assertEquals($route->patterns(),[
    		'{locale}/chemin/de/la/page',
    		'{locale}/path/to/page'
    	]);
    }

    /**
     * 
     */
    public function testParamsRoute(){
    	$route = new Route('page',[
    		'{locale}/chemin/de/la/page',
    		'{locale}/path/to/page'
    	],
    	[
    		'locale' => '[\w-]+'
    	]);

    	$this->assertEquals($route->params('locale'),'[\w-]+');
    	$this->assertEquals($route->params(),['locale' => '[\w-]+']);
    	$route->params('foo','bar');
    	$this->assertEquals($route->params('foo') , 'bar');
    	$route->params([
    		'faa' => 'bir',
    		'fuu' => 'ber' 
    	]);
    	$this->assertEquals($route->params('faa') , 'bir');
    	$this->assertEquals($route->params('fuu') , 'ber');
    	$this->assertEquals($route->params() , 
    		[
    		'locale' => '[\w-]+',
    		'foo' => 'bar',
    		'faa' => 'bir',
    		'fuu' => 'ber' 
    		]
    	);

    }

    /**
     * 
     */
    public function testAttributesRoute(){
    	$route = new Route('page','path/to/page');

    	$this->assertEquals($route->attributes(), []);
    	
        $route->attributes('controller','PageController');
    	$this->assertEquals($route->attributes('controller') , 'PageController');
    	
        $route->attributes([
    		'action' => 'index',
        	'method' => 'POST'
    	]);
    	$this->assertEquals($route->attributes() , [
			'controller'=>'PageController',
    		'action' => 'index',
        	'method' => 'POST'
    	]);
    }
}