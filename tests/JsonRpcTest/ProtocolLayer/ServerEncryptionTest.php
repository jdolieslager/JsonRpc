<?php
namespace JsonRpcTest\ProtocolLayer;

use Jdolieslager\JsonRpc\ProtocolLayer\ServerEncryption;

class ServerEncryptionTest extends \PHPUnit_Framework_TestCase
{
	public function testHandleRequest()
	{
		$config = array('key' => 'unit-test');
		$data = "xdghFU7O4WwFay+blAVSOCgVu3i1q4EItFVFrHeJj3CHL627x0Q=";
		$layer  = new ServerEncryption($config);
		
		$this->assertEquals("mydata", $layer->handleRequest($data));
	}	
	
	public function testHandleResponse()
	{
		$config = array('key' => 'unit-test');
		$data   = "mydata";
		$layer  = new ServerEncryption($config);
		
		$this->assertInternalType('string', $layer->handleResponse('mydata'));
	}
	
	public function testSettingLayer()
	{
		$layer  = new ServerEncryption();
		$layer->setLowerLayer($layer);
		$layer->setUpperLayer($layer);
		
		$this->assertEquals(true, $layer === $layer->getLowerLayer());
		$this->assertEquals(true, $layer === $layer->getUpperLayer());
	}
}
