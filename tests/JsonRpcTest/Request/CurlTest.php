<?php
namespace JsonRpcTest\Request;

use Jdolieslager\JsonRpc\Request\Curl;

class CurlTest extends \PHPUnit_Framework_TestCase
{
	public function testCurl()
	{
		$curl = new Curl('php://temp');
		$this->assertEquals(false, $curl->execute());
		$this->assertInternalType('array', $curl->getInfo());
	}
}
