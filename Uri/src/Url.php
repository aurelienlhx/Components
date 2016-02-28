<?php

namespace A1\Uri;

class Url{

	/**
	 * Scheme of the uri, often the protocol eg : http, ftp, mailto, javascript
	 *
	 * @var string
	 */
	private $scheme;
	
	/**
	 * Authority of the uri eg: username:password@www.google.com:123
	 *
	 * @var string
	 */
	private $authority;

	/**
	 * Userinfos, string before '@' eg : username:password
	 *
	 * @var string
	 */
	private $userinfos;

	/**
	 * Username
	 *
	 * @var string
	 */
	private $username;

	/**
	 * Password
	 *
	 * @var string
	 */
	private $password;

	/**
	 * Host of the uri eg: www.google.com or an ip adress
	 *
	 * @var string
	 */
	private $host;
	
	/**
	 * Domain of the host eg: www.google.com
	 *
	 * @var string
	 */
	private $domain;

	/**
	 * Subdomain of the domain eg: www
	 *
	 * @var string
	 */
	private $subdomain; 
	
	/**
	 * Second level domain eg: google
	 *
	 * @var string
	 */
	private $sld;

	/**
	 * Top level domain eg: com, fr, org
	 *
	 * @var string
	 */
	private $tld;
	
	/**
	 * Ip of the host eg: ipv4 127.0.0.1 or ipv4 [::1]
	 *
	 * @var string
	 */
	private $ip;
	
	/**
	 * Port eg: 80 or 8080
	 *
	 * @var string
	 */
	private $port;

	/**
	 * Path to the ressource path/to/a/ressource
	 * 
	 * @var string
	 */
	private $path;
	
	/**
	 * Query, string after ?
	 * 
	 * @var string 
	 */
	private $query;
	
	/**
	 * Fragment, infos after #
	 *
	 * @var string 
	 */
	private $fragment;
	
	/**
	 * Match an url
	 */
	private static $url_regex = array(
		'#(?:(?<scheme>\w+):)',
		'(?:\/\/(?P<authority>[^\/]*))?',
		'(?:\/(?P<path>[^\?]*))?',
		'(?:\?(?P<query>[^\#]*))?',
		'(?:\#(?P<fragment>.*))?$#i'
	);

	/**
	 * Match an authority
	 */
	private static $authority_regex = array(
		//(?Ji) : J == allow duplicated subpatterns / i == insensitive case
		'#^(?:(?P<userinfos>(?P<username>.*?)(?::(?P<password>.*))?)@)?',
		//host is...
		'(?P<host>[\[\]\w.:-]+?)',
		//port
		'(?:\:(?P<port>\d+))?$#i'
	);

	/**
	 * Match a host
	 */
	private static $host_regex = array(
		//..ipv4..
		'#(?Ji)^(?P<ip>(?:\d{1,3}\.){3}\d+)|',
		//..ipv6.. (this ipv6 regex is not a totally valid one but works enought here)
		'(?:\[(?P<ip>(?:[0-9A-F]{0,4}:){1,7}(?:[0-9A-F]{1,4}){1})\])|',
		//..domain..
		'(?:(?P<domain>(?:(?P<subdomain>.*)\.)?(?P<sld>.*?)\.(?P<tld>[^:]+)))$#',
	);

	/**
	 * Constructor of url object
	 *
	 * @param string $url
	 */
	public function __construct($uri = null){
		
		if(!is_string($uri))
			return;

		if(!self::parse($uri,$parts))
			return;

		foreach($parts as $name => $value)
			if(method_exists($this, $name))
				call_user_func(array($this,$name), $value);
	}

	
	/**
	 * Get or set the scheme
	 *
	 * @param string $scheme
	 *
	 * @return string
	 */
	public function scheme($scheme = null){
		if(0 === func_num_args())
			return $this->scheme;
		$this->scheme = $scheme;
	}

	/**
	 * Get or set the authority
	 *
	 * @param string $authority
	 *
	 * @return string
	 */	
	public function authority( $authority = null){
		//get
		if(0 === func_num_args()){
			$authority = '';
			if(!empty($this->username())){
				$authority = $this->username();
				if(!empty($this->password()))
					$authority .= ':'.$this->password();
				$authority .= '@';
			}
			$authority .= $this->host();
			if(!empty($this->port()))
				$authority .= ':'.$this->port();
			return $authority;
		}
		//set
		self::parseAuthority($authority, $parts);
		foreach($parts as $name => $value)
			if(method_exists($this, $name))
				call_user_func(array($this,$name), $value);
	}


	/**
	 * Get or set username
	 *
	 * @param string $username
	 *
	 * @return string
	 */
	public function username($username = null){
		if(0 === func_num_args())
			return $this->username;
		$this->username = $username;
	}

	/**
	 * Get or set user password
	 *
	 * @param string $password
	 *
	 * @return string
	 */
	public function password($password = null){
		if(0 === func_num_args())
			return $this->password;
		$this->password = $password;
	}

	/**
	 * Get or set host
	 *
	 * @param string $host
	 *
	 * @return string
	 */
	public function host($host = null){
		//get
		if(0 === func_num_args()){
			$host = '';
			if(!empty($this->ip()))
				$host = $this->ip();
			else{
				$host = '';
	 			if(!empty($this->subdomain()))
	 				$host.= $this->subdomain().'.';
	 			$host.= $this->sld().'.'.$this->tld();
			}
			return $host;
		}
		//set
		$this->host = $host;
		self::parseHost($host, $parts);
		foreach($parts as $name => $value){
			if(method_exists($this, $name))
				call_user_func(array($this,$name), $value);
		}
	}


	/**
	 * Get or set subdomain
	 *
	 * @param string $subdomain
	 *
	 * @return string
	 */
	public function subdomain($subdomain = null){
		if(0 === func_num_args())
			return $this->subdomain;
		$this->subdomain = $subdomain;
	}

	/**
	 * Get or set sld
	 *
	 * @param string $sld
	 *
	 * @return string
	 */
	public function sld($sld = null){
		if(0 === func_num_args())
			return $this->sld;
		$this->sld = $sld;
	}

	/**
	 * Get or set tld
	 *
	 * @param string $tld
	 *
	 * @return string
	 */
	public function tld($tld = null){
		if(0 === func_num_args())
			return $this->tld;
		$this->tld = $tld;
	}

	/**
	 * Get or set ip
	 *
	 * @param string $ip
	 *
	 * @return string
	 */
	public function ip($ip = null){
		if(0 === func_num_args())
			return $this->ip;
		$this->ip = $ip;
	}

	/**
	 * Get or set port
	 *
	 * @param string $port
	 *
	 * @return string
	 */
	public function port($port = null){
		if(0 === func_num_args())
			return $this->port;
		$this->port = $port;
	}

	/**
	 * Get or set path
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function path($path = null){
		if(0 === func_num_args())
			return $this->path;
		$this->path = $path;
	}

	/**
	 * Get or set query
	 *
	 * @param mixed string|array $query
	 *
	 * @return array
	 */
	public function queryParams(array $query = null){
		if(0 === func_num_args())
			return $this->query;

		$this->query = $query;
	}

	/**
	 * Get or set query
	 *
	 * @param string $query
	 *
	 * @return string 
	 */
	public function query($query = null){
		if(0 === func_num_args())
			return http_build_query($this->query); //create string from array
		if(!is_string($query))
			throw new \InvalidArgumentException('$query must be a string');
		parse_str($query,$query); //create array from string
		$this->query = $query;
	}

	/**
	 * Get or set fragment
	 *
	 * @param string $fragment
	 *
	 * @return string
	 */
	public function fragment($fragment = null){
		if(0 === func_num_args())
			return $this->fragment;
		$this->fragment = $fragment;
	}


	/**
	 * Stringify the url object
	 *
	 * @param int|array $exclude Single constant or array of constant
	 *
	 * @return string
	 */
	const NO_SCHEME = 'scheme';
	const NO_PORT = 'port';
	const NO_PATH = 'path';
	const NO_QUERY = 'query';
	const NO_FRAGMENT = 'hash';

	public function stringify( /*$exclude1, $exclude2,...*/){
		
		$exclude = func_get_args();

		$string ='';

		if(!in_array(self::NO_SCHEME,$exclude))
			$string .= $this->scheme.':';
		
		//trick to exclude port
		if(in_array(self::NO_PORT,$exclude)){
			$port = $this->port();
			$this->port('');
		}

		$string .= '//'.$this->authority();
		
		if(isset($port))
			$this->port($port);
	
		if(!in_array(self::NO_PATH,$exclude) && !empty($this->path()))
			$string .= '/'.$this->path();
		
		if(!in_array(self::NO_QUERY,$exclude) && !empty($this->query()))
			$string .= '?'.$this->query();
		
		if(!in_array(self::NO_FRAGMENT,$exclude) && !empty($this->fragment()))
			$string .= '#'.$this->fragment();

		return $string;
	}

	/**
	 * Parse an url string
	 *
	 * @param string $url Url to parse
	 * @param array &$parts Parts of the url
	 * @param int $until Parse until authority or host with UNTIL_AUTHORITY and UNTIL_HOST const
	 *
	 * @return array Components of parsed url
	 */
	const UNTIL_AUTHORITY = 1;
	const UNTIL_HOST = 2;

	public static function parse($url, &$parts, $until = null){
	
		if(!self::parseUrl($url,$parts))
			return false;

		if(self::UNTIL_AUTHORITY === $until)
			return true;

		if(!self::parseAuthority($parts['authority'],$parts2))
			return false;

		$parts = array_merge($parts,$parts2);
	
		if(self::UNTIL_HOST === $until)
			return true;

		if(!self::parseHost($parts2['host'],$parts3))
			return false;
		
		$parts = array_merge($parts,$parts3);	
		return true;
	}

	/**
	 * 
	 */
	private static function parseUrl($url, &$parts){
		if(!is_string($url))
			throw new \InvalidArgumentException('$url must be a string');

		$url_regex = implode(self::$url_regex);
		if(!preg_match($url_regex,$url,$matches))
			return false;
		
		foreach($matches as $key => $match)
			if(is_numeric($key)) unset($matches[$key]);

		$parts = $matches;
		return true;
	}

	/**
	 * 
	 */
	private static function parseAuthority($authority, &$parts){
		if(!is_string($authority))
			throw new \InvalidArgumentException('$authority must be a string');
		
		$authority_regex = implode(self::$authority_regex);
		if(!preg_match($authority_regex,$authority,$matches))
			return false;
			
		foreach($matches as $key => $match)
			if(is_numeric($key)) unset($matches[$key]);

		$parts = $matches;
		return true;
	}

	/**
	 * 
	 */
	private static function parseHost($host, &$parts){
		if(!is_string($host))
			throw new \InvalidArgumentException('$host must be a string');
		
		$host_regex = implode(self::$host_regex);
		if(!preg_match($host_regex,$host,$matches))
			return false;
			
		foreach($matches as $key => $match)
			if(is_numeric($key)) unset($matches[$key]);

		$parts = $matches;
		return true;
	}


	/**
	 * 
	 */
	public static function isValid($url){
		$url_regex = implode(self::$url_regex);
		return (bool) preg_match($url_regex,$url);
	}



}