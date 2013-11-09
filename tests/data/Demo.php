<?php
class Demo
{
	public function noArgument()
	{
		
	}
	
	public function oneArgument($argument1)
	{
		
	}
	
	public function oneArgumentOptional($argument1 = 'optional')
	{
		
	}
	
	public function twoArgument($argument1, $argument2)
	{
		
	}
	
	public function twoArgumentOneOptional($argument1, $argument2 = 'optional')
	{
		
	}
	
	public function twoArgumentTwoOptional($argument1 = 'optional', $argument2 = 'optional')
	{
		
	}
	
	protected function protectedNotReflected()
	{
		
	}
	
	protected function privateNotReflected()
	{
		
	}
}
