<?php
namespace App\Mapper;

abstract class AbstractGateway
{
	protected $config;
	
	protected $httpConfig = [
			'adapter' => 'Zend\Http\Client\Adapter\Curl',
			'sslverifypeer' => false,
			'curloptions' => array(
					CURLOPT_SSL_VERIFYHOST => false,
					CURLOPT_SSL_VERIFYPEER => false,
			),
			'keepalive' => true,
			'timeout'   => 60
	];
	
	private $serviceManager;
	
	public function __construct($config)
	{
		$this->config = $config;
	}
	
	public function getServiceManager()
	{
		return $this->serviceManager;
	}
	
	public function setServiceManager($serviceManager)
	{
		$this->serviceManager = $serviceManager;
		return $this;
	}
	
	public function getMailService()
	{
		return $this->getServiceManager()->get('mailService');
	}
	
	protected function generateJWT($user)
	{
		$secret = env("JWT_SECRET", openssl_random_pseudo_bytes(64));
		$userInfo = [
				'uid' => $user->getId(),
				'identity' => $user->getUsername(),
				'credential' => $user->getPassword()
		];
		$jwtConfig = getConfigToken();
		$payload = $jwtConfig + $userInfo;
		$token = \Firebase\JWT\JWT::encode($payload, $secret, env("JWT_HASH", "HS256"));
		$uid = $user->getId();
		$uMobile = $user->getMobile();
		if($uMobile['mobile'] = ''){
		    $uMobile = null;
        }
		if($uid instanceof \MongoDB\BSON\ObjectID) {
		    $uid = $uid->__toString();
        }
		$result = ["code" => 1, 'result' =>['token' => $token, 'uid' => $uid,
				'username' => $user->getUsername(), 'mobile' => $uMobile]];
		return $result;
	}
}