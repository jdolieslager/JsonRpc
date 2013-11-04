<?php
namespace Jdolieslager\JsonRpc\ProtocolLayer;

/**
 * @category    Jdolieslager
 * @package     JsonRpc
 * @subpackage  ProtocolLayer
 */
class ServerEncryption extends AbstractEncrpytion
{
    /**
     * {@inheritdoc}
     */
    public function handleRequest($request)
    {
        return $this->decrypt($request);
    }

    /**
     * {@inheritdoc}
     */
    public function handleResponse($response)
    {
        return $this->encrypt($response);
    }
}
