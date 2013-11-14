<?php
namespace Jdolieslager\JsonRpc\ProtocolLayer;

use Jdolieslager\JsonRpc\Exception;

class ProtocolLayerStack
{
    const PLACEMENT_TOP    = 1,
          PLACEMENT_BOTTOM = 2,
          PLACEMENT_ABOVE  = 3,
          PLACEMENT_BELOW  = 4;

    /**
     * @var ProtocolLayerInterface | NULL
     */
    protected $topLayer;

    /**
     * @var ProtocolLayerInterface | NULL
     */
    protected $bottomLayer;

    /**
     * Holds exception list
     *
     * @var array
     */
    protected $exceptions = array(
        1 => 'Placement `%s` does not exists.',
    );

    /**
     * Process the response object
     *
     * @param  mixed $response
     * @return mixed
     */
    public function handleRequest($request)
    {
        if ($this->getBottomLayer() instanceof ProtocolLayerInterface) {
            $request = $this->getBottomLayer()->handleRequest($request);
        }

        return $request;
    }

    /**
     * Process the response object
     *
     * @param mixed $response
     * @return mixed
     */
    public function handleResponse($response)
    {
        if ($this->getTopLayer() instanceof ProtocolLayerInterface) {
            $response = $this->getTopLayer()->handleResponse($response);
        }

        return $response;
    }

    /**
     * @return ProtocolLayerInterface | NULL
     */
    public function getTopLayer()
    {
        return $this->topLayer;
    }

    protected function setTopLayer(ProtocolLayerInterface $layer)
    {
        $this->topLayer = $layer;

        return $this;
    }

    /**
     * @return ProtocolLayerInterface | NULL
     */
    public function getBottomLayer()
    {
        return $this->bottomLayer;
    }

    protected function setBottomLayer(ProtocolLayerInterface $layer)
    {
        $this->bottomLayer = $layer;

        return $this;
    }

    /**
     * Add a layer to the stack
     *
     * @param  ProtocolLayerInterface $layer
     * @param  constant $placement
     * @param  ProtocolLayerInterface $currentLayer
     * @return ProtocolLayerStack
     * @throws Exception\InvalidArgument
     */
    public function addLayer(
        ProtocolLayerInterface $layer,
                               $placement,
        ProtocolLayerInterface $currentLayer = null
    ) {
        // Reset layers
        $layer->setLowerLayer(null);
        $layer->setUpperLayer(null);

        if ($this->getTopLayer() === null) {
            $this->setTopLayer($layer);
            $this->setBottomLayer($layer);

            return $this;
        }

        // Reference to old situation
        $oldTopLayer    = $this->getTopLayer();
        $oldBottomLayer = $this->getBottomLayer();

        switch ($placement) {
            case self::PLACEMENT_ABOVE:
                $oldLayer = $currentLayer->getUpperLayer();
                $layer->setUpperLayer($oldLayer);
                $layer->setLowerLayer($currentLayer);
                $currentLayer->setUpperLayer($layer);

                if ($currentLayer === $oldTopLayer) {
                    $this->setTopLayer($layer);
                } else {
                    $oldLayer->setLowerLayer($layer);
                }
                break;
            case self::PLACEMENT_BELOW:
                $oldLayer = $currentLayer->getLowerLayer();
                $layer->setUpperLayer($currentLayer);
                $layer->setLowerLayer($oldLayer);
                $currentLayer->setLowerLayer($layer);

                if ($currentLayer === $oldBottomLayer) {
                    $this->setBottomLayer($layer);
                } else {
                    $oldBottomLayer->setUpperLayer($layer);
                }
                break;
            case self::PLACEMENT_TOP:
                $oldTopLayer->setUpperLayer($layer);
                $layer->setLowerLayer($oldTopLayer);
                $this->setTopLayer($layer);
                break;
            case self::PLACEMENT_BOTTOM:
                $oldBottomLayer->setLowerLayer($layer);
                $layer->setUpperLayer($oldBottomLayer);
                $this->setBottomLayer($layer);
                break;
            default:
                throw new Exception\InvalidArgument(
                    sprintf($this->exceptions[1], $placement),
                    1
                );
        }

        return $this;
    }
}
