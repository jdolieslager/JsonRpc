<?php
function __autoload($class)
{
    include __DIR__ . '/src/' . str_replace('\\', '/', $class). '.php';
}

$string = '[
    {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
    {"jsonrpc": "2.0", "method": "notify_hello", "params": [7]},
    {"jsonrpc": "2.0", "method": "subtract", "params": [42,23], "id": "2"},
    {"foo": "boo"},
    {"jsonrpc": "2.0", "method": "foo.get", "params": {"name": "myself"}, "id": "5"},
    {"jsonrpc": "2.0", "method": "get_data", "id": "9"}
]';

$server = new Jdolieslager\JsonRpc\Server(true);
$server->printResponseForRawRequest($string);