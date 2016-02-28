<?php 

namespace A1\Routing;

class Route {
	
	/**
	 * Key of the route that will allow to retrieves the route
	 * 
	 * @var string $key
	 */
	private $key;
	
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
	private $patterns = array();

	/**
	 * Array of parameters from the url or alones
	 *
	 * @var array $params
	 */
	private $params = array();
	

	/**
	 * Array of attributes is whatever to bind to this route according his context
	 * eg: controller, action, method or anything else
	 *
	 * @var array $attributes
	 */
	private $attributes = array();


	/**
	 * Array of regrex transformed from sel::$patterns
	 * 
	 * @var array $regex
	 */
	private $regexs = array();

	
	/**
	 * Construct a route.
	 *
	 * @param array $route A route representation 
	 * or 
	 * @param string $key Route key
	 * @param mixed $pattern A string pattern or array of string pattern
	 * @param array $params Array of parameters and their value to used in pattern
	 *
	 */
	public function __construct( /* $route | [$key, $patterns, array $params = null] */ ){
		
		$args = func_get_args();
		$nargs = count($args);

		if(1 === $nargs){
			if(!is_array($args[0]))
				throw new \InvalidArgumentException('Array expected with only one argument');
			$this->hydrate($args[0]);
		}else if (2 <= $nargs && 3 >= $nargs){			
			$this->key($args[0]);
			$this->patterns($args[1]);
			
			if(isset($args[2])){
				if(!is_array($args[2]))
					throw new \InvalidArgumentException('Array expected for $params argument');
				$this->params($args[2]);	
			}
		}
		else
			throw new \InvalidArgumentException('1,2 or 3 arguments expected');
	}

	/**
	 * 
	 */
	private function hydrate(array $route){
		foreach($route as $k => $v){
			if(method_exists($this,$k))
				call_user_func(array($this,$k),$v);
			else
				throw new \DomainException(sprintf('"%s" key not expected in route array',$k));
		}
	}

	/**
	 * Init the current route formatting the regex according to the pattern route
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
					$value = isset($match[2])? $match[2] : (isset($this->params[$name])? $this->params[$name] : '.+');
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
	 * Set or get the route key
	 */
	public function key( $val=null ){
		if(null === $val)
			return $this->key;
		else
			$this->key = (string) $val;
	}


	/**
	 * Set or get the route's patterns
	 */
	public function patterns( /*$patterns*/ ){
		$args = func_get_args();
		$nargs = count($args);
		if(0 === $nargs)
			if(1 === count($this->patterns))
				return reset($this->patterns);
			else
				return $this->patterns;
		else{
			if(!is_array($args[0]))
				$args[0] = array($args[0]);
			$this->patterns = array_merge($this->patterns,$args[0]);
			$this->formatRegexs();
		}
	}


	/**
	 * Set or get the route's params
	 */
	public function params(/*$params*/){
		$args = func_get_args();
		$nargs = count($args);
		if(0 === $nargs)
			return $this->params;
		else if(1 === $nargs){
			if(is_array($args[0]))
				$this->params = array_merge($this->params,$args[0]);
			else
				return isset($this->params[$args[0]])? $this->params[$args[0]] : null;
		}
		else
			$this->params[$args[0]] = (string) $args[1];
	}

	
	/**
	 * Set or get route's attributes
	 */
	public function attributes(/*$attributes*/){
		$args = func_get_args();
		$nargs = count($args);
		if(0 === $nargs)
			return $this->attributes;
		else if(1 === $nargs){
			if(is_array($args[0]))
				$this->attributes = array_merge($this->attributes,$args[0]);
			else
				return isset($this->attributes[$args[0]])? $this->attributes[$args[0]] : null;
		}
		else
			$this->attributes[$args[0]] = (string) $args[1];
	}


	/**
	 * Match a path
	 *
	 * @param string $path Path to test with route regex
	 * @param string $attributes Array of attributes to test in addition of the path
	 * @param array &$params Array of params in case of the route path match the regex
	 *
	 * @return bool
	 */
	public function match($path, array $attributes = null, &$params = null ){
		
		$has_matched = false;
		foreach($this->regexs as $regex){
			if(preg_match($regex,$path,$matches)){
				//check attributes
				if(!empty($attributes)){
					foreach($attributes as $key => $val)
						//if at least one attribute is not in the matched route so do not match
						if(!$this->hasAttribute($key,$val)){
							break 2;
						}
				}

				//remove numerical keys
				foreach($matches as $key=>$val)
					if(is_numeric($key)) unset($matches[$key]);
				
				$params = array_merge($this->params,$matches);
				$has_matched = true;
				break;
			}
		}
		return $has_matched;
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
	 * Check if an attribute has a value or if contain the value in case of array
	 * 
	 * @param string $name Name of the attribute
	 * @param mixed $value Value to search into attribute value
	 *
	 * @return bool
	 */
	public function inAttribute($name,$value){
		if(!isset($this->attributes[$name]))
			return false;

		if(is_array($this->attributes[$name]) && in_array($value,$this->attributes[$name]))
			return true;
		
		if($this->attributes === $value)
			return true;

		return false;
	}

}