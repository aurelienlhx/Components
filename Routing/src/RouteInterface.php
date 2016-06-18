<?php
namespace A1\Routing;

interface RouteInterface {
		
	public function name( $name );
	public function params( array $params);
	public function getParams();
	public function attributes( array $attributes );
	public function getAttributes();
	public function match( $path, &$params);
	public static function createFromArray( array $array );
}