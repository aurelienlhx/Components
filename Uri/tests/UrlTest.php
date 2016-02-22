<?php

use Uri\Url;

class UrlTest extends PHPUnit_Framework_TestCase{

	/**
	 * 
	 */
	public function testParsingBasicUrl(){
		//basic url
		$s_url = 'http://www.google.fr:80/path/to/ressource?param1=value1&param2=value2#hash';
		$o_url = new Url($s_url);

		$this->assertEquals($o_url->scheme(),'http');
		$this->assertEquals($o_url->authority(),'www.google.fr:80');
		$this->assertEquals($o_url->host(),'www.google.fr');
		$this->assertEquals($o_url->subdomain(),'www');
		$this->assertEquals($o_url->sld(),'google');
		$this->assertEquals($o_url->tld(),'fr');
		$this->assertEquals($o_url->port(),'80');
		$this->assertEquals($o_url->path(),'path/to/ressource');
		$this->assertEquals($o_url->query(),'param1=value1&param2=value2');
		$this->assertEquals($o_url->queryParams(),[
			'param1' => 'value1',
			'param2' => 'value2'
		]);
		$this->assertEquals($o_url->fragment(),'hash');
	}


	/**
	 * 
	 */
	public function testParsingIpUrl(){
		//url with ip
		$s_url = 'http://127.0.0.1/path/to/ressource?param1=value1&param2=value2#hash';
		$o_url = new Url($s_url);

		$this->assertEquals($o_url->ip(),'127.0.0.1');

		$s_url = 'http://[2001:0db8:0000:85a3:0000:0000:ac1f:8001]/path/to/ressource?param1=value1&param2=value2#hash';
		$o_url = new Url($s_url);

		$this->assertEquals($o_url->ip(),'2001:0db8:0000:85a3:0000:0000:ac1f:8001');
	}

	/**
	 * 
	 */
	public function testParsingAuthenticatedUrl(){
		//url with user
		$s_url = 'http://aurelien@www.google.fr:80/path/to/ressource?param1=value1&param2=value2#hash';
		$o_url = new Url($s_url);

		$this->assertEquals($o_url->username(),'aurelien');

		$s_url = 'http://aurelien:lheureux@www.google.fr:80/path/to/ressource?param1=value1&param2=value2#hash';
		$o_url = new Url($s_url);

		$this->assertEquals($o_url->username(),'aurelien');
		$this->assertEquals($o_url->password(),'lheureux');

		$s_url = 'http://aurelien:lheureux@127.0.0.1:80/path/to/ressource?param1=value1&param2=value2#hash';
		$o_url = new Url($s_url);
		$this->assertEquals($o_url->username(),'aurelien');
		$this->assertEquals($o_url->password(),'lheureux');

		$s_url = 'http://aurelien:lheureux@[2001:0db8:0000:85a3:0000:0000:ac1f:8001]:80/path/to/ressource?param1=value1&param2=value2#hash';
		$o_url = new Url($s_url);
		$this->assertEquals($o_url->username(),'aurelien');
		$this->assertEquals($o_url->password(),'lheureux');
	}

	/**
	 * 
	 */
	public function testStringifyUrl(){
		$s_url = 'http://www.google.fr:80/path/to/ressource?param1=value1&param2=value2#hash';
		$o_url = new Url($s_url);
		$this->assertEquals($o_url->stringify(),$s_url);

		//single exclude
		$this->assertEquals($o_url->stringify(Url::NO_SCHEME),'//www.google.fr:80/path/to/ressource?param1=value1&param2=value2#hash');
		$this->assertEquals($o_url->stringify(Url::NO_PORT),'http://www.google.fr/path/to/ressource?param1=value1&param2=value2#hash');
		$this->assertEquals($o_url->stringify(Url::NO_PATH),'http://www.google.fr:80?param1=value1&param2=value2#hash');
		$this->assertEquals($o_url->stringify(Url::NO_QUERY),'http://www.google.fr:80/path/to/ressource#hash');
		$this->assertEquals($o_url->stringify(Url::NO_FRAGMENT),'http://www.google.fr:80/path/to/ressource?param1=value1&param2=value2');

		//multiple excludes
		$this->assertEquals($o_url->stringify(Url::NO_SCHEME,Url::NO_PATH,Url::NO_FRAGMENT),'//www.google.fr:80?param1=value1&param2=value2');
	}

	/**
	 * 
	 */
	public function testModificationUrl(){
		$s_url = 'http://www.google.fr:80/path/to/ressource?param1=value1&param2=value2#hash';
		$o_url = new Url($s_url);

		//change authority
		$o_url->authority('www.antoherwebsite.com:8080');
		$this->assertEquals($o_url->stringify(),'http://www.antoherwebsite.com:8080/path/to/ressource?param1=value1&param2=value2#hash');

		//change host ip
		$o_url->host('192.168.0.1');
		$this->assertEquals($o_url->stringify(),'http://192.168.0.1:8080/path/to/ressource?param1=value1&param2=value2#hash');

		//change host domain
		$o_url->host('antoherwebsite.fr');
		$this->assertEquals($o_url->stringify(),'http://antoherwebsite.fr:8080/path/to/ressource?param1=value1&param2=value2#hash');

		//change subdomain
		$o_url->subdomain('subdomain');
		$this->assertEquals($o_url->stringify(),'http://subdomain.antoherwebsite.fr:8080/path/to/ressource?param1=value1&param2=value2#hash');

		//change sld
		$o_url->sld('google');
		$this->assertEquals($o_url->stringify(),'http://subdomain.google.fr:8080/path/to/ressource?param1=value1&param2=value2#hash');

		//change tld
		$o_url->tld('org');
		$this->assertEquals($o_url->stringify(),'http://subdomain.google.org:8080/path/to/ressource?param1=value1&param2=value2#hash');

		//change port
		$o_url->port('');
		$this->assertEquals($o_url->stringify(),'http://subdomain.google.org/path/to/ressource?param1=value1&param2=value2#hash');

		//change path
		$o_url->path('');
		$this->assertEquals($o_url->stringify(),'http://subdomain.google.org?param1=value1&param2=value2#hash');
	}

}