<?php
namespace Jdolieslager\JsonRpc\Collection;

/**
 * @category    Jdolieslager
 * @package     JsonRpc
 * @subpackage  Collection
 *
 * @method \Jdolieslager\JsonRpc\Entity\Parameter current
 * @method \Jdolieslager\JsonRpc\Entity\Parameter offsetGet
 */
class Parameter extends \ArrayIterator
{
    /**
     * {@inheritdoc}
     */
    public function getArrayCopy()
    {
        $result = parent::getArrayCopy();
        foreach ($result as $offset => $value) {
            $result[$offset] = $value->getArrayCopy();
            unset($result[$offset]['index']);
        }

        return $result;
    }
}
