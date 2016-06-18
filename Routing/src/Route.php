<?php 

namespace A1\Routing;

use DomainException;
use InvalidArgumentException;

class Route implements RouteInterface{
	
	/**
	 * Key of the route that will allow to retrieves the route
	 * 
	 * @var string $key
	 */
	private $name;
	
	/**
	 * Patterns used by the route.
	 *
	 * Multiple patterns is usefull for differents versions of a route accoding to a parameter
	 * A pattern synthax is whatever as a pattern is a regex
	 * It can include parameters between brackets eg: path/to/{word:\w+}
	 * or the parameters value can be specified in the parameters array eg: $params['word'] => \w+
	 *
	 * @var array $patterns
	 */
	private $patterns = [];

	/**
	 * Array of parameters regex which match in the route
	 *
	 * @var array $params
	 */
	private $params = [];

	/**
	 * An array of values found in the pattern route with the params regex
	 * Can contains dynamic (from params) or static values
	 *
	 * @var array $params
	 */
	private $values = [];
	

	/**
	 * Array of attributes is whatever to bind to this route according his context
	 * eg: controller, action, method or anything else
	 *
	 * @var array $attributes
	 */
	private $attributes = [];


	/**
	 * Array of regrex created from self::$patterns
	 * 
	 * @var array $regex
	 */
	private $regexs = [];

	/**
	 *  Array of custom conditions to add for match the route
	 *
	 * @var array
	 */
	private $conditions = [];

	
	/**
	 * Construct a route.
	 *
	 * @param string $name Route name
	 * @param mixed $pattern A string pattern or array of string pattern
	 *
	 * @return void
	 */
	public function __construct( $patterns ){

		$this->patterns($patterns);
	}

	/**
	 * Create a route from an array representation
	 *
	 * @param array $array
	 *
	 * @return Route
	 */
	public static function createFromArray(array $array){
		$route = new static($array['patterns']);
		
		if(isset($array['name']))
			$route->name($array['name']);
		if(isset($array['params']))
			$route->params($array['params']);
		if(isset($array['values']))
			$route->values($array['values']);
		if(isset($array['conditions']))
			$route->conditions($array['conditions']);
		if(isset($array['attributes']))
			$route->attributes($array['attributes']);

		return $route;
	}


	/**
	 * Set or get the route key
	 *
	 * @param mixed $val
	 * 
	 * @return mixed
	 */
	public function name( $name ){
		
		$this->name = (string) $name;
		return $this;
	}

	/**
	 * 
	 */
	public function getName( $name=null ){
		
		return $this->name;
	}


	/**
	 * Set the route's patterns
	 */
	public function patterns( $patterns ){

		if(!is_array($patterns))
			$patterns = array($patterns);

		$this->patterns = $patterns;
		return $this;
	}


	/**
	 * Set an array of parameters
	 */
	public function params( array $params ){
		
		$this->params = array_merge($this->params,$params);
		return $this;
	}

	/**
	 * 
	 */
	public function getParams(){
		return $this->params;
	}

	/**
	 * Set an array of values
	 */
	public function values( array $values ){
		
		$this->values = array_merge($this->values,$values);
		return $this;
	}

	/**
	 * Get a value
	 */
	public function getValue( $key ){
		return isset($this->values[$key])? $this->values[$key] : null;
	}

	/**
	 * Get all values
	 */
	public function getValues(){
		return $this->values;
	}
	
	/**
	 * Set an array of attributes
	 */
	public function attributes( array $attributes ){
		$this->attributes = $attributes;
		return $this;
	}

	/**
	 * Get an attribute
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function getAttributes($name = null){
		if(null === $name)
			return $this->attributes;
		if(isset($this->attributes[$name]))
			return $this->attributes[$name];
	}

	/**
	 * Check if an attribute exists, optionnaly check his value too
	 * 
	 * @param string $name Name of the attribute
	 * @param mixed $value Value of the attribute 
	 *
	 * @return bool
	 */
	public function hasAttribute($name,$value = null){
		if(!isset($this->attributes[$name]))
			return false;
		
		if(null === $value || (null !== $value && $this->attributes[$name] === $value))
			return true;
		else
			return false;
	}

	/**
	 * Set a condition or an array of conditions
	 *
	 * @param mixed $conditions
	 *
	 * @return void
	 */
	public function conditions( $conditions ){
		
		if(!is_array($conditions))
			$conditions = array($conditions);
		
		foreach($conditions as $condition)
			if(!is_callable($condition))
				throw new InvalidArgumentException('Contitions must be a callable or an array of callable');
		
		$this->conditions = array_merge($this->conditions,$conditions);
		return $this;
	}


	/**
	 * Formatting the route's regex according to the pattern route
	 *
	 * @return void
	 */
	private function formatRegexs(){
		$patterns = $this->patterns;
		$regexs = $this->regexs;
		
		//loop on patterns
		foreach($patterns as $key => $pattern){
			
			//looking for parameters in pattern
			preg_match_all("#{([a-z0-9_-]+)(?::([^}]+))?}#",$pattern,$matches,PREG_SET_ORDER);
			$regex = null;

			$i = 0;
			if(count($matches)>0){
				
				$subject = $pattern;
				foreach($matches as $match){

					if($i>0) 
						$subject = $regex;

					$name = preg_quote($match[1]);
					
					//check if a value is set with the regex parameter
					if(isset($match[2]))
						$value = $match[2];
					//else check if a value parameter is set in params array
					else if(isset($this->params[$name]))
						$value = $this->params[$name];
					else
						throw new DomainException(sprintf('Missing value for parameter %s',$name));

					$regex = str_replace($match[0], '(?P<'.$name.'>'.$value.')', $subject);
					$i++;
				}
			}
			else{
				$regex = $patterns[$key];
			}

			$regexs[$key] = '#^'.$regex.'$#i';
		}
		$this->regexs = $regexs;
	}


	/**
	 * Match a path
	 *
	 * @param string $path Path to test with route regex
	 * @param array &$params Array of params in case of the route path match the regex
	 *
	 * @return bool
	 */
	public function match($path, &$values = [] ){
		//format regex just before matching cause 
		//params can be set anytime
		$this->formatRegexs();

		$matched = false;
		foreach($this->regexs as $regex){
			if(preg_match($regex,$path,$matches)){
				//check attributes
				if(!empty($this->conditions)){
					foreach($this->conditions as $condition)
						//if a condition return false
						if(!!!call_user_func($condition, $this)){
							break 2;
						}
				}

				//remove numerical keys
				foreach($matches as $key=>$val)
					if(is_numeric($key)) unset($matches[$key]);
				
				$values = $this->values($matches)->getValues();

				$matched = true;
				break;
			}
		}
		return $matched;
	}

}