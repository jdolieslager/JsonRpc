JsonRpc
=======

JSON RPC Server / Client library

Example Usage
=============
```php
class ProfileRpc
{
    public function getProfiles($limit, $offset, $includeDeleted = false)
    {
        return array(array('name' => 'Jesper', 'surname' => 'Dolieslager'));
    }
}

// Start server
$server  = new Jdolieslager\JsonRpc\Server(new ProfileRpc());

// Create response from raw data
$response = $server->getResponseForRawRequest(file_get_contents('php://input'));

// No Response object means no content ;)
if ($response === null) {
    header("HTTP/1.0 204 No Content");
    exit;
}

header('Content-Type: application/json');
echo $server->responseToRaw($response);
exit;
```



