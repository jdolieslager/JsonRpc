JsonRpc
=======

JSON RPC Server / Client library

Example Usage (Server url: http://josnrpc.mine)
=============
```php
class Test
{
    public function hello($name)
    {
        return "Hello {$name}!";
    }
}

$server = new Jdolieslager\JsonRpc\Server(true);
$server->registerHandler('Test');   // Second argument you can define a namespace
                                    // Methods are than seperated with <namespace>.<orignal_method_name>
$server->printResponseForRawRequest(file_get_contents('php://input'));
```

Client Single Request (Argument based)
=============
```php
$client   = new Jdolieslager\JsonRpc\Client('http://jsonrpc.mine');
$response = $client->sendSimpleRequest('hello', array('name' => 'World'));

if ($response->hasError()) {
    $error = $response->getError();
    throw new \Exception($error->getMessage(), $error->getCode());
} else {
    echo $response->getResult();
}
```

Client Single Request (Object based)
=============
```php
$request = new Jdolieslager\JsonRpc\Entity\Request();
$request->setId(1);
$request->setJsonrpc(Jdolieslager\JsonRpc\Client::VERSION_1);
$request->setMethod('hello');
$request->addParam('World', 'name');

$client   = new Jdolieslager\JsonRpc\Client('http://jsonrpc.mine');
$response = $client->sendSingleRequest($request);

if ($response->hasError()) {
    $error = $response->getError();
    throw new \Exception($error->getMessage(), $error->getCode());
} else {
    echo $response->getResult();
}

```

Client Single Request Notification (Argument based)
=============
```php
$client   = new Jdolieslager\JsonRpc\Client('http://jsonrpc.mine');
$client->sendNotification('hello', array('name' => 'World'));
```

Client Multiple Requests
==============
```php
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

// Create request two
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
```

