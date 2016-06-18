<?php
namespace A1\Routing;

interface RequestInterface {
		
	/**
	 * Return the method of the request
	 */
	public function getMethod();
	
	/**
	 * Return input data from body request
	 */
	public function getInput($name = null);
	
	/**
	 * Return query data from url request
	 */
	public function getQuery($name = null);
	
	/**
	 * Return request data (query and input data)
	 */
	public function getData($name = null);
}