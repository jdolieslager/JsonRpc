<?php
function __autoload($class)
{
    include __DIR__ . '/../src/' . str_replace('\\', '/', $class). '.php';
}

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
