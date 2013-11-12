<?php
namespace Jdolieslager\JsonRpc\Request;

/**
 * @category	Jdolieslager
 * @package		JsonRpc
 * @subpackage	Request
 */
class Curl implements RequestInterface
{
	/**
	 * @var resource | NULL
	 */
	protected $curl;
	
	/**
	 * @var array | NULL
	 */
	protected $info;
	
	/* 
	 * Construct the curl request
	 */
	public function __construct($url = null) 
	{
		$this->curl = curl_init($url);	
	}
	
	/**
	 * Close the connection if open
	 */
	public function __destruct()
	{
		$this->close();
	}

	/* 
	 * {@inheritdoc}
	 */
	public function setOption($option, $value) 
	{
		curl_setopt($this->curl, $option, $value);
	}

	/* 
	 * {@inheritdoc}
	 */
	public function execute() 
	{
		$result     = curl_exec($this->curl);
		$this->info = curl_getinfo($this->curl);

		return $result;
	}

	/* 
	 * {@inheritdoc}
	 */
	public function getInfo() 
	{
		return $this->info;
	}

	/* 
	 * {@inheritdoc}
	 */
	public function close() 
	{
		if ($this->curl !== null) {
			curl_close($this->curl);
		}		
	}
}
