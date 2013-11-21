<?php
namespace Jdolieslager\JsonRpc\Request;

/**
 * @category	Jdolieslager
 * @package		JsonRpc
 * @subpackage	Request
 */
interface RequestInterface
{
	/**
	 * Consttruct the request
	 * 
	 * @param string $url
	 */
	public function __construct($url = null);
	
	/**
	 * Set an option for the request
	 * 
	 * @param  mixed $option The option name
	 * @param  mixed $value  The value of the option
	 * @return void
	 */
	public function setOption($option, $value);
	
	/**
	 * Execute the request and return the result
	 * 
	 * @return string The response of the request
	 */
	public function execute();
	
	/**
	 * Get information about the request
	 * 
	 * @return array The request information
	 */
	public function getInfo();
	
	/**
	 * Close the connection
	 * 
	 * @return void
	 */
	public function close();	
}
