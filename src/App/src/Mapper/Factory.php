<?php
namespace App\Mapper;

class Factory
{
	private static $factory;
	
	public static function getFactory()
	{
		if(is_null(static::$factory)) {
			static::$factory = new GatewayFactory();
		}
	
		return static::$factory;
	}
	
	public static function setFactory(GatewayFactory $factory = null)
	{
		static::$factory = $factory;
	}
	
	public static function __callStatic($method, $parameters)
	{
		$factory = static::getFactory();
	
		return call_user_func_array(array($factory, $method), $parameters);
	}
}