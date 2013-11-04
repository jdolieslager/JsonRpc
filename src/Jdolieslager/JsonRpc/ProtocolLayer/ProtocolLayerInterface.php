<?php
namespace Jdolieslager\JsonRpc\ProtocolLayer;

/**
 * @category    Jdolieslager
 * @package     JsonRpc
 * @subpackage  ProtocolLayer
 */
interface ProtocolLayerInterface
{
    /**
     * Handles the request data
     *
     * @return mixed
     */
    public function handleRequest($request);

    /**
     * Handles the response data
     *
     * @return mixed
     */
    public function handleResponse($response);

    /**
     * Set the lower layer
     *
     * @param  ProtocolLayerInterface $layer
     * @return void
     */
    public function setLowerLayer(ProtocolLayerInterface $layer = null);

    /**
     * Get the lower layer
     *
     * @return ProtocolLayerInterface | NULL
     */
    public function getLowerLayer();

    /**
     * Set the upper layer
     *
     * @param  ProtocolLayerInterface $layer
     * @return void
     */
    public function setUpperLayer(ProtocolLayerInterface $layer = null);

    /**
     * Get the upper layer
     *
     * @return ProtocolLayerInterface | NULL
     */
    public function getUpperLayer();
}
