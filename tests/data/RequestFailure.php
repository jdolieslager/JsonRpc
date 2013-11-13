<?php
class RequestFailure extends \Jdolieslager\JsonRpc\Entity\Request
{
	public function getArrayCopy()
	{
		$result 			= parent::getArrayCopy();
		$result['resource'] = fopen('php://input', 'r');
		
		return $result;
	}
}
