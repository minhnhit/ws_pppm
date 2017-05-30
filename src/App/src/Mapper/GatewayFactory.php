<?php
namespace App\Mapper;

class GatewayFactory
{
	/**
	* Internal storage for all available gateways
	*
	* @var array
	*/
	private $gateways = array();
	
	/**
	* All available gateways
	*
	* @return array An array of gateway names
	*/
	public function all()
	{
		return $this->gateways;
	}
	
	/**
	* Replace the list of available gateways
	*
	* @param array $gateways An array of gateway names
	*/
	public function replace(array $gateways)
	{
		$this->gateways = $gateways;
	}
	
	/**
	* Register a new gateway
	*
	* @param string $className Gateway name
	*/
	public function register($className)
	{
		if (!in_array($className, $this->gateways)) {
			$this->gateways[] = $className;
		}
	}
	
	/**
	* Automatically find and register all officially supported gateways
	*
	* @return array An array of gateway names
	*/
	public function find()
	{
		foreach ($this->getSupportedGateways() as $gateway) {
			$class = self::getGatewayClassName($gateway);
			if (class_exists($class)) {
				$this->register($gateway);
			}
		}
	
		ksort($this->gateways);
		return $this->all();
	}
	
	/**
	* Create a new gateway instance
	*
	* @param string               $class       Gateway name
	* @throws RuntimeException                 If no such gateway is found
	* @return GatewayInterface                 An object of class $class is created and returned
	*/
	public function create($class, array $config)
	{
		$class = self::getGatewayClassName($class);
		if (!class_exists($class)) {
			die("Class '$class' not found");
			throw new \RuntimeException("Class '$class' not found");
		}
		return new $class($config);
	}
	
	/**
	* Resolve a short gateway name to a full namespaced gateway class.
	*
	* Class names beginning with a namespace marker (\) are left intact.
	* Non-namespaced classes are expected to be in the \Omnipay namespace, e.g.:
	*
	*      \Custom\Gateway     => \Custom\Gateway
	*      \Custom_Gateway     => \Custom_Gateway
	*      Stripe              => \Omnipay\Stripe\Gateway
	*      PayPal\Express      => \Omnipay\PayPal\ExpressGateway
	*      PayPal_Express      => \Omnipay\PayPal\ExpressGateway
	*
	* @param  string  $shortName The short gateway name
	* @return string  The fully namespaced gateway class name
	*/
	public static function getGatewayClassName($shortName)
	{
		if (0 === strpos($shortName, '\\')) {
			return $shortName;
		}
	
		// replace underscores with namespace marker, PSR-0 style
		$shortName = str_replace('_', '\\', $shortName);
		if (false === strpos($shortName, '\\')) {
			$shortName .= '\\';
		}
		return '\\App\\Mapper\\'.$shortName.'Gateway';
	}
	
	/**
	* Get a list of supported gateways which may be available
	*
	* @return array
	*/
	public function getSupportedGateways()
	{
		$package = json_decode(file_get_contents(__DIR__.'/../../composer.json'), true);
		return $package['extra']['gateways'];
	}
}