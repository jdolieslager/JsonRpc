<?php
use Jdolieslager\JsonRpc\ProtocolLayer\ProtocolLayerInterface;

class ClientProtocolLayer implements ProtocolLayerInterface
{
	/**
	 * @var ProtocolLayerInterface
	 */
	protected $upperLayer;
	
	/**
	 * @var ProtocolLayerInterface
	 */
	protected $lowerpLayer;
	
	/**
	 * @see \Jdolieslager\JsonRpc\ProtocolLayer\ProtocolLayerInterface::handleRequest()
	 */
	public function handleRequest($request) 
	{
		if ($request === 'hello') {
			return 'world';
		}		
		
		return $request;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Jdolieslager\JsonRpc\ProtocolLayer\ProtocolLayerInterface::handleResponse()
	 */
	public function handleResponse($response) 
	{
		if ($response === 'world') {
			return 'hello';
		}	
		
		return $response;
	}

	/* (non-PHPdoc)
	 * @see \Jdolieslager\JsonRpc\ProtocolLayer\ProtocolLayerInterface::setLowerLayer()
	 */
	public function setLowerLayer(\Jdolieslager\JsonRpc\ProtocolLayer\ProtocolLayerInterface $layer = null) 
	{
		$this->lowerpLayer = $layer;
		
		return $this;		
	}

	/* (non-PHPdoc)
	 * @see \Jdolieslager\JsonRpc\ProtocolLayer\ProtocolLayerInterface::getLowerLayer()
	 */
	public function getLowerLayer() 
	{
		return $this->lowerpLayer;		
	}

	/* (non-PHPdoc)
	 * @see \Jdolieslager\JsonRpc\ProtocolLayer\ProtocolLayerInterface::setUpperLayer()
	 */
	public function setUpperLayer(\Jdolieslager\JsonRpc\ProtocolLayer\ProtocolLayerInterface $layer = null) 
	{
		$this->upperLayer = $layer;

		return $this;
	}

	/* (non-PHPdoc)
	 * @see \Jdolieslager\JsonRpc\ProtocolLayer\ProtocolLayerInterface::getUpperLayer()
	 */
	public function getUpperLayer() 
	{
		return $this->upperLayer;	
	}
}
