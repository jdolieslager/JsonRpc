<?php
namespace Jdolieslager\JsonRpc\ProtocolLayer;

/**
 * @category    Jdolieslager
 * @package     JsonRpc
 * @subpackage  ProtocolLayer
 */
class ClientEncryption extends AbstractEncrpytion
{
    /**
     * {@inheritdoc}
     */
    public function handleRequest($request)
    {
        return $this->encrypt($request);
    }

    /**
     * {@inheritdoc}
     */
    public function handleResponse($response)
    {
        return $this->decrypt($response);
    }
}
