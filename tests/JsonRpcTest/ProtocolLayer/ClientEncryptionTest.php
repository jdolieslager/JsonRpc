<?php
namespace JsonRpcTest\ProtocolLayer;

use Jdolieslager\JsonRpc\ProtocolLayer\ClientEncryption;
class ClientEncryptionTest extends \PHPUnit_Framework_TestCase
{
	public function testHandleRequest()
	{
		$config = array('key' => 'unit-test');
		$data   = "mydata";
		$layer  = new ClientEncryption($config);
		
		$this->assertInternalType('string', $layer->handleRequest('mydata'));
	}	
	
	public function testHandleResponse()
	{
		$config = array('key' => 'unit-test');
		$data = "xdghFU7O4WwFay+blAVSOCgVu3i1q4EItFVFrHeJj3CHL627x0Q=";
		$layer  = new ClientEncryption($config);
		
		$this->assertEquals("mydata", $layer->handleResponse($data));
	}
	
	
	public function testSettingLayer()
	{
		$layer  = new ClientEncryption();
		$layer->setLowerLayer($layer);
		$layer->setUpperLayer($layer);
		
		$this->assertEquals(true, $layer === $layer->getLowerLayer());
		$this->assertEquals(true, $layer === $layer->getUpperLayer());
	}
}
