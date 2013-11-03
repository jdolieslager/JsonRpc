<?php
function __autoload($class)
{
    include __DIR__ . '/src/' . str_replace('\\', '/', $class). '.php';
}

use Jdolieslager\JsonRpc\Entity\Response;

// Create objects
$client     = new Jdolieslager\JsonRpc\Client('http://jsonrpc.mine');
$collection = new Jdolieslager\JsonRpc\Collection\Request();
$request    = new Jdolieslager\JsonRpc\Entity\Request();

// Create request one
$request->setId(1);
$request->setMethod('hello');
$request->addParam('World', 'name');
$collection->addRequest($request, function(Response $response) {
    if ($response->hasError()) {
        $error = $response->getError();
        throw new \Exception($error->getMessage(), $error->getCode());
    } else {
        echo $response->getResult();
    }
});

// Crete request two
$request = new Jdolieslager\JsonRpc\Entity\Request();
$request->setId(2);
$request->setMethod('hello');
$request->addParam('Jesper', 'name');
$collection->addRequest($request, function(Response $response) {
    if ($response->hasError()) {
        $error = $response->getError();
        throw new \Exception($error->getMessage(), $error->getCode());
    } else {
        echo $response->getResult();
    }
});

// Responses holds all the responses
// Index is the ID number
$responses = $client->sendRequest($collection);






