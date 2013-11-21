<?php
namespace JsonRpcTest;

use Jdolieslager\JsonRpc\Server;
use Jdolieslager\JsonRpc\Entity\Response;

require_once DATA_ROOT . 'ClientProtocolLayer.php';
require_once DATA_ROOT . 'Handler.php';

class ServerTest extends \PHPUnit_Framework_TestCase
{
	public function testRegisterHandlers()
	{
		$handlerA = new \Handler();
		$handlerB = new \Handler();
		$handlers = array(
			'a' => $handlerA,
			'b' => $handlerB,
		);
		
		$server = new Server();
		$result = $server->registerHandlers($handlers);
		
		$this->assertInstanceOf('Jdolieslager\\JsonRpc\\Server', $result);
	}

	public function testAddLayer()
	{
		$server = new Server('http://localhost');
		$result = $server->addProtocolLayer(new \ClientProtocolLayer(), Server::LAYER_PLACEMENT_TOP);
		$this->assertInstanceOf('Jdolieslager\\JsonRpc\\Server', $result);
	}
	
	public function testCreateRequestFromRawRequest()
	{
		$rawRequest = file_get_contents(DATA_ROOT . 'json/single_named_request.json');
		$server = new Server();
		
		$result = $server->createRequestFromRawRequest($rawRequest);
		
		$this->assertInstanceOf('Jdolieslager\\JsonRpc\\Collection\\Request', $result);
		$this->assertEquals(1, $result->count());
		
		$result->rewind();
		$request = $result->current();
		$this->assertInstanceOf('Jdolieslager\\JsonRpc\\Entity\\Request', $request);
		$this->assertEquals('2.0', $request->getJsonrpc());
		$this->assertEquals('subtract', $request->getMethod());
		$this->assertEquals(3, $request->getId());
		
		$params = $request->getParams();
		$this->assertInstanceOf('\ArrayIterator', $params);
		$this->assertEquals(2, $params->count());
		$this->assertEquals(true, $params->offsetExists('subtrahend'));
		$this->assertEquals(23, $params->offsetGet('subtrahend'));
		$this->assertEquals(true, $params->offsetExists('minuend'));
		$this->assertEquals(42, $params->offsetGet('minuend'));	
	}
	
	public function testParseError()
	{
		$rawRequest = file_get_contents(DATA_ROOT . 'json/single_parse_error.json');
		
		$server = new Server(false, false);
		$result = $server->createRequestFromRawRequest($rawRequest);
		
		$this->assertInstanceOf('Jdolieslager\\JsonRpc\\Collection\\Response', $result);
		$result->rewind();
		$response = $result->current();
		
		$this->assertEquals(null, $response->getId());
		$this->assertEquals(true, $response->hasError());
		$this->assertEquals(Server::PARSE_ERROR, $response->getError()->getCode());
		$this->assertEquals('Parse error', $response->getError()->getMessage());
	}
	
	public function testInvalidBatch()
	{
		// This request is parseable, but says nothing about a valid request
		$rawRequest = file_get_contents(DATA_ROOT . 'json/batch_invalid_request.json');
		
		$server = new Server(false, false);
		$result = $server->createRequestFromRawRequest($rawRequest);
		
		// Invalid request will be thrown later. So we expect empty request objects
		$this->assertInstanceOf('Jdolieslager\\JsonRpc\\Collection\\Request', $result);
		$this->assertEquals(3, $result->count());
		$result->rewind();
		
		foreach ($result as $request) {
			$this->assertEquals(null, $request->getId());
			$this->assertEquals(null, $request->getMethod());
			$this->assertEquals(0, $request->getParams()->count());
			$this->assertEquals(null, $request->getJsonRpc());
		}
	}
	
	public function testNamedSingleRequest()
	{
		$server 	= new Server(true, true);
		$server->registerHandler('Handler');
		$rawRequest = file_get_contents(DATA_ROOT . 'json/single_named_request.json');
		
		$result = $server->createResponseForRawRequest($rawRequest);
		
		$this->assertInstanceOf('Jdolieslager\\JsonRpc\\Collection\\Response', $result);
		$result->rewind();
		$response = $result->current();
		
		$this->assertEquals(3, $response->getId());
		$this->assertEquals(false, $response->hasError());
		$this->assertEquals(19, $response->getResult());
		$this->assertEquals(Response::VERSION_2, $response->getJsonrpc());	
	}

	public function testPositionalSingleRequest()
	{
		$server 	= new Server(true, true);
		$server->registerHandler('Handler');
		$rawRequest = file_get_contents(DATA_ROOT . 'json/single_positional_request.json');
	
		$result = $server->createResponseForRawRequest($rawRequest);
	
		$this->assertInstanceOf('Jdolieslager\\JsonRpc\\Collection\\Response', $result);
		$result->rewind();
		$response = $result->current();
	
		$this->assertEquals(1, $response->getId());
		$this->assertEquals(false, $response->hasError());
		$this->assertEquals(19, $response->getResult());
		$this->assertEquals(Response::VERSION_2, $response->getJsonrpc());
	}
	
	public function testSingleNotification()
	{
		$server 	= new Server(true, true);
		$server->registerHandler('Handler');
		$rawRequest = file_get_contents(DATA_ROOT . 'json/single_notification.json');
		
		$result = $server->createResponseForRawRequest($rawRequest);
		$this->assertEquals(0, $result->count());
	}
	
}
