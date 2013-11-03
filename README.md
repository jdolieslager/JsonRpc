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
$server->setHandleObject(new \Test());
$server->printResponseForRawRequest(file_get_contents('php://input'));
```

Example Usage (Client Single Request Really Simple)
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

Example Usage (Client Single Request Simple)
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


Example Usage (Client Single Request Notification)
=============
```php
$client   = new Jdolieslager\JsonRpc\Client('http://jsonrpc.mine');
$client->sendNotification('hello', array('name' => 'World'));
```

Example Usage (Client Multiple Requests)
```
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

