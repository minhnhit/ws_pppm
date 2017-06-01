<?php
namespace App\Service;

use App\Mapper\Factory;

class Passport extends AbstractService
{
	public function __construct($container)
	{
		parent::__construct($container);
		$this->mapper = Factory::create(env('PASSPORT_GATEWAY', 'MongoDb'), $this->config)
									->setServiceManager($this->serviceManager);
	}
	
    public function login($params)
    {
    	$errCode = $this->validateParams($params, ['username', 'password']);
    	if ($errCode !== 1) {
    		return ['code' => $errCode];
    	}
    	$result = $this->mapper->login($params);
    	return $result;
    }
    
    public function register($params)
    {
    	$errCode = $this->validateParams($params, ['username', 'password']);
    	if ($errCode !== 1) {
    		return ['code' => $errCode];
    	}
    	
    	$result = $this->mapper->insertUser($params);
    	return $result;
    }
    
    public function oauth($params)
    {
    	$errCode = $this->validateParams($params, ['oauth_id', 'client']);
    	if ($errCode !== 1) {
    		return ['code' => $errCode];
    	}
    	$result = $this->mapper->loginOauth($params);
    	return $result;
    }

    public function linkOauth($params)
    {
        $errCode = $this->validateParams($params, ['username', 'oauth_id', 'client']);
        if ($errCode !== 1) {
            return ['code' => $errCode];
        }
        $result = $this->mapper->linkOauth($params);
        return $result;
    }
	
    public function update($params)
    {
    	$result = $this->mapper->loginOauth($params);
    	return $result;
    }
    
    public function getProfileById($id)
    {
    	return $this->mapper->getById($id);
    }

    public function getProfileByUsername($username)
    {
        return $this->mapper->findByUsername($username);
    }

    public function forgotPass($params)
    {
        $errCode = $this->validateParams($params, ['username']);
        if ($errCode !== 1) {
            return ['code' => $errCode];
        }
        $result = $this->mapper->forgotPassword($params);
        return $result;
    }

    public function resetPass($params)
    {
        $errCode = $this->validateParams($params, ['username', 'password', 'code']);
        if ($errCode !== 1) {
            return ['code' => $errCode];
        }
        $result = $this->mapper->resetPassword($params);
        return $result;
    }

    public function changePass($params)
    {
        $params['password'] = $params['newPassword'];
        $errCode = $this->validateParams($params, ['username', 'password', 'oldPassword']);
        if ($errCode !== 1) {
            return ['code' => $errCode];
        }
        $result = $this->mapper->updatePassword($params);
        return $result;
    }

    public function updateEmail($params)
    {
        $errCode = $this->validateParams($params, ['username', 'email']);
        if ($errCode !== 1) {
            return ['code' => $errCode];
        }
        $result = $this->mapper->updateEmail($params);
        return $result;
    }

    /**
     * @param $params
     * @return array
     */
    public function getEmail($params)
    {
        $errCode = $this->validateParams($params, ['username']);
        if ($errCode !== 1) {
            return ['code' => $errCode];
        }
        $result = $this->mapper->getEmail($params);
        return $result;
    }

    public function updateMobile($params)
    {
        $result = $this->mapper->updateMobile($params);
        return $result;
    }

    public function updateIdentityNumber($params)
    {
        $result = $this->mapper->updateIdentityNumber($params);
        return $result;
    }

    public function addGold($uid, $amount)
    {
        $result = $this->mapper->addGold($uid, $amount);
        return $result;
    }

    public function getBalance($params)
    {
        $errCode = $this->validateParams($params, ['username']);
        if ($errCode !== 1) {
            return ['code' => $errCode];
        }
        return $this->mapper->getBalance($params);
    }
}
