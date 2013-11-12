<?php
namespace JsonRpcTest;

use Jdolieslager\JsonRpc\Client;
use ClientProtocolLayer;
use Jdolieslager\JsonRpc\Collection\Response;
use Jdolieslager\JsonRpc\Entity\Request;

require_once __DIR__ . '/../data/ClientProtocolLayer.php';

/**
 * Client test case.
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var Client
	 */
	private $client;
	
	private $fullClass = 'Jdolieslager\\JsonRpc\\Client';
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() 
	{
		parent::setUp ();
		$this->client = new Client('dummy_url');
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() 
	{
		$this->client = null;
		parent::tearDown ();
	}
	
	/**
	 * Tests Client->addProtocolLayer()
	 */
	public function testAddProtocolLayer() 
	{
		$result = $this->client->addProtocolLayer(
			new ClientProtocolLayer(),
			Client::LAYER_PLACEMENT_TOP 
		);
		
		$this->assertInstanceOf($this->fullClass, $result);
	}
	
	/**
	 * Tests Client->setUrl()
	 */
	public function testSetUrl() 
	{
		$result = $this->client->setUrl('dummy');
		$this->assertInstanceOf($this->fullClass, $result);
	}
	
	/**
	 * Tests Client->getUrl()
	 */
	public function testGetUrl() 
	{
		$this->client->setUrl('dummy');
		$this->assertEquals('dummy', $this->client->getUrl());
	}
	
	/**
	 * Tests Client->sendSingleRequest()
	 */
	public function testSendSingleRequest() 
	{
		// Create dummy response
		$response 	  = new Response();
		$singResponse = new \Jdolieslager\JsonRpc\Entity\Response();
		$singResponse->setId(1)->setResult('world');
		$response->append($singResponse);
		
		// Create request object
		$request = new Request();
		$request->setId(1);
		$request->setMethod('hello');
		$request->setJsonrpc($request::VERSION_2);
		
		// Create stub class for faking send request call
		$stub = $this->getMock($this->fullClass, array('sendRequest'), array('dummy'));
		$stub->expects($this->any())
			->method('sendRequest')
			->will($this->returnValue($response));
		
		// Simulate request
		$result = $stub->sendSingleRequest($request);
		$this->assertInstanceOf('Jdolieslager\\JsonRpc\\Entity\\Response', $result);
		$this->assertEquals('world', $result->getResult());
		$this->assertEquals(1, $result->getId());
	}
	
	/**
	 * Tests Client->sendSimpleRequest()
	 */
	public function testSendSimpleRequest() 
	{
		// Create dummy response
		$response 	  = new Response();
		$singResponse = new \Jdolieslager\JsonRpc\Entity\Response();
		$singResponse->setId(1)->setResult('Hello World!');
		$response->append($singResponse);
		
		// Create stub class for faking send request call
		$stub = $this->getMock($this->fullClass, array('sendRequest'), array('dummy'));
		$stub->expects($this->any())
			->method('sendRequest')
			->will($this->returnValue($response));
		
		$result = $stub->sendSimpleRequest('hello', array('world'));
		
		$this->assertInstanceOf('Jdolieslager\\JsonRpc\\Entity\\Response', $result);
		$this->assertEquals('Hello World!', $result->getResult());
		$this->assertEquals(1, $result->getId());
	}
	
	/**
	 * Tests Client->sendNotification()
	 */
	public function testSendNotification() 
	{
		// Create dummy response
		$response 	  = new Response();
		$singResponse = new \Jdolieslager\JsonRpc\Entity\Response();
		$singResponse->setId(1)->setResult('Hello World!');
		$response->append($singResponse);
		
		// Create stub class for faking send request call
		$stub = $this->getMock($this->fullClass, array('sendRequest'), array('dummy'));
		$stub->expects($this->any())
			->method('sendRequest')
			->will($this->returnValue($response));
		
		$this->assertEquals(null, $stub->sendNotification('hello', array('world')));
	}
	
	/**
	 * Tests Client->sendRequest()
	 */
	public function testSendRequestNotFound() 
	{
		$http = $this->getMock('Jdolieslager\\JsonRpc\\RequestInterface', array('getInfo', 'execute', 'close'));
		$http->expects($this->once())
			->method('getInfo')
			->will($this->returnValue(array('http_code' => 404)));
		
		$http->expects($this->once())
			->method('execute')
			->will($this->returnValue(null));
		
		// Mock the request adapter
		$client = $this->getMock($this->fullClass, array('getHttpRequest'), array('dummy'));
		$client->expects($this->any())
			->method('getHttpRequest')
			->will($this->returnValue($http));
		
		// Create fake request
		$request = new Request();
		$request->setId(1);
		$request->setMethod('failed');
		
		$requestCollection = new \Jdolieslager\JsonRpc\Collection\Request(array($request));
		
		$this->setExpectedException('Jdolieslager\JsonRpc\Exception\InvalidRequest');
		$client->sendRequest($requestCollection);
	}
	
	public function testSendRequestWithNonJsonResponse()
	{
		$http = $this->getMock('Jdolieslager\\JsonRpc\\RequestInterface', array('getInfo', 'execute', 'close'));
		$http->expects($this->once())
			->method('getInfo')
			->will($this->returnValue(array('http_code' => 200)));
		
		$http->expects($this->once())
		->method('execute')
		->will($this->returnValue('this is no json formatted string'));
		
		// Mock the request adapter
		$client = $this->getMock($this->fullClass, array('getHttpRequest'), array('dummy'));
		$client->expects($this->any())
			->method('getHttpRequest')
			->will($this->returnValue($http));
		
		// Create fake request
		$request = new Request();
		$request->setId(1);
		$request->setMethod('hello');
		
		$requestCollection = new \Jdolieslager\JsonRpc\Collection\Request(array($request));
		
		$this->setExpectedException('Jdolieslager\JsonRpc\Exception\InvalidResponse', 'Client parse error', Client::CLIENT_PARSE_ERROR);
		$client->sendRequest($requestCollection);
	}
	
	public function testSendRequestWithExpectedBatchResult()
	{
		$http = $this->getMock('Jdolieslager\\JsonRpc\\RequestInterface', array('getInfo', 'execute', 'close'));
		$http->expects($this->once())
			->method('getInfo')
			->will($this->returnValue(array('http_code' => 200)));
		
		$http->expects($this->once())
		->method('execute')
		->will($this->returnValue('{"id":1,"result":"a"}'));
		
		// Mock the request adapter
		$client = $this->getMock($this->fullClass, array('getHttpRequest'), array('dummy'));
		$client->expects($this->any())
			->method('getHttpRequest')
			->will($this->returnValue($http));
		
		// Create fake request
		$request = new Request();
		$request->setId(1);
		$request->setMethod('hello');
		
		$requestCollection = new \Jdolieslager\JsonRpc\Collection\Request(array($request, $request));
		
		$this->setExpectedException('Jdolieslager\JsonRpc\Exception\InvalidResponse', 'Client expected batch result', Client::CLIENT_EXPECTED_BATCH);
		$client->sendRequest($requestCollection);
	}
	
	public function testSendRequestWithExpectedSingleResult()
	{
		$http = $this->getMock('Jdolieslager\\JsonRpc\\RequestInterface', array('getInfo', 'execute', 'close'));
		$http->expects($this->once())
			->method('getInfo')
			->will($this->returnValue(array('http_code' => 200)));
		
		$http->expects($this->once())
		->method('execute')
		->will($this->returnValue('[{"id":1,"result":"a"},{"id":1,"result":"a"}]'));
		
		// Mock the request adapter
		$client = $this->getMock($this->fullClass, array('getHttpRequest'), array('dummy'));
		$client->expects($this->any())
			->method('getHttpRequest')
			->will($this->returnValue($http));
		
		// Create fake request
		$request = new Request();
		$request->setId(1);
		$request->setMethod('hello');
		
		$requestCollection = new \Jdolieslager\JsonRpc\Collection\Request(array($request));
		
		$this->setExpectedException('Jdolieslager\JsonRpc\Exception\InvalidResponse', 'Client expected single result', Client::CLIENT_EXPECTED_SINGLE);
		$client->sendRequest($requestCollection);
	}
}

